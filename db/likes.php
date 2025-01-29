<?php
function create_like($conn, $post_id, $user_id) {
    $sql = "INSERT INTO likes (post_id, user_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $post_id, $user_id);
    return $stmt->execute();
}

function get_like_by_id($conn, $id) {
    $sql = "SELECT * FROM likes WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function get_all_likes($conn) {
    $sql = "SELECT * FROM likes ORDER BY created_at DESC";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function delete_like($conn, $id) {
    $sql = "DELETE FROM likes WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

function get_likes_by_post_id($conn, $post_id) {
    $sql = "SELECT * FROM likes WHERE post_id = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function get_likes_by_user_id($conn, $user_id) {
    $sql = "SELECT * FROM likes WHERE user_id = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function check_if_user_liked_post($conn, $post_id, $user_id) {
    $sql = "SELECT * FROM likes WHERE post_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $post_id, $user_id);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}
?>