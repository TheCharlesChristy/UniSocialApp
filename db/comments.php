<?php
function create_comment($conn, $post_id, $user_id, $content) {
    $sql = "INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $post_id, $user_id, $content);
    return $stmt->execute();
}

function get_comment_by_id($conn, $id) {
    $sql = "SELECT * FROM comments WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function get_all_comments($conn) {
    $sql = "SELECT * FROM comments ORDER BY created_at DESC";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function update_comment($conn, $id, $content) {
    $sql = "UPDATE comments SET content = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $content, $id);
    return $stmt->execute();
}

function delete_comment($conn, $id) {
    $sql = "DELETE FROM comments WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

function get_comments_by_post_id($conn, $post_id) {
    $sql = "SELECT * FROM comments WHERE post_id = ? ORDER BY created_at ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function get_comments_by_user_id($conn, $user_id) {
    $sql = "SELECT * FROM comments WHERE user_id = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function search_comments($conn, $keyword) {
    $sql = "SELECT * FROM comments WHERE content LIKE ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $likeKeyword = "%" . $keyword . "%";
    $stmt->bind_param("s", $likeKeyword);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>