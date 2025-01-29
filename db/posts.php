<?php
function create_post($conn, $user_id, $content, $image_url = null, $location = null) {
    $sql = "INSERT INTO posts (user_id, content, image_url, location) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $user_id, $content, $image_url, $location);
    return $stmt->execute();
}

function get_post_by_id($conn, $id) {
    $sql = "SELECT * FROM posts WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function get_all_posts($conn) {
    $sql = "SELECT * FROM posts ORDER BY created_at DESC";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function update_post($conn, $id, $content, $image_url = null, $location = null) {
    $sql = "UPDATE posts SET content = ?, image_url = ?, location = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $content, $image_url, $location, $id);
    return $stmt->execute();
}

function delete_post($conn, $id) {
    $sql = "DELETE FROM posts WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

function get_posts_by_user_id($conn, $user_id) {
    $sql = "SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function search_posts($conn, $keyword) {
    $sql = "SELECT * FROM posts WHERE content LIKE ? OR location LIKE ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $likeKeyword = "%" . $keyword . "%";
    $stmt->bind_param("ss", $likeKeyword, $likeKeyword);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function get_user_by_post_id($conn, $post_id) {
    $sql = "SELECT user_id FROM posts WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['user_id'];
}
?>