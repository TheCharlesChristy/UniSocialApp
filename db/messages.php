<?php
function create_message($conn, $sender_id, $receiver_id, $content) {
    $sql = "INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $sender_id, $receiver_id, $content);
    return $stmt->execute();
}

function get_message_by_id($conn, $id) {
    $sql = "SELECT * FROM messages WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function get_all_messages($conn) {
    $sql = "SELECT * FROM messages ORDER BY sent_at DESC";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function update_message($conn, $id, $content) {
    $sql = "UPDATE messages SET content = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $content, $id);
    return $stmt->execute();
}

function delete_message($conn, $id) {
    $sql = "DELETE FROM messages WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

function get_messages_by_sender_id($conn, $sender_id) {
    $sql = "SELECT * FROM messages WHERE sender_id = ? ORDER BY sent_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $sender_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function get_messages_by_receiver_id($conn, $receiver_id) {
    $sql = "SELECT * FROM messages WHERE receiver_id = ? ORDER BY sent_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $receiver_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function get_conversation_between_users($conn, $user1_id, $user2_id) {
    $sql = "SELECT * FROM messages 
            WHERE (sender_id = ? AND receiver_id = ?) 
               OR (sender_id = ? AND receiver_id = ?) 
            ORDER BY sent_at ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiii", $user1_id, $user2_id, $user2_id, $user1_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>