<?php
function get_user_by_id($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute(['id' => $user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function create_user($pdo, $username, $password_hash, $email, $is_admin = false) {
    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, email, is_admin) VALUES (:username, :password_hash, :email, :is_admin)");
    return $stmt->execute([
        'username' => $username,
        'password_hash' => $password_hash,
        'email' => $email,
        'is_admin' => $is_admin
    ]);
}

function update_user($pdo, $user_id, $username = null, $password_hash = null, $email = null, $is_admin = null) {
    $fields = [];
    $params = ['id' => $user_id];
    if ($username !== null) {
        $fields[] = 'username = :username';
        $params['username'] = $username;
    }
    if ($password_hash !== null) {
        $fields[] = 'password_hash = :password_hash';
        $params['password_hash'] = $password_hash;
    }
    if ($email !== null) {
        $fields[] = 'email = :email';
        $params['email'] = $email;
    }
    if ($is_admin !== null) {
        $fields[] = 'is_admin = :is_admin';
        $params['is_admin'] = $is_admin;
    }
    $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

function delete_user($pdo, $user_id) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
    return $stmt->execute(['id' => $user_id]);
}

function get_user_by_username($pdo, $username) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_user_by_email($pdo, $email) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_users($pdo) {
    $stmt = $pdo->query("SELECT * FROM users");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function search_users_username($pdo, $search) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username LIKE :search");
    $stmt->execute(['search' => "%$search%"]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function search_users_email($pdo, $search) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email LIKE :search");
    $stmt->execute(['search' => "%$search%"]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function search_users($pdo, $search) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username LIKE :search OR email LIKE :search");
    $stmt->execute(['search' => "%$search%"]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>