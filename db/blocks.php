<?php
function create_block($conn, $blocker_id, $blocked_id) {
    $sql = "INSERT INTO blocks (blocker_id, blocked_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $blocker_id, $blocked_id);
    return $stmt->execute();
}

function get_block_by_id($conn, $id) {
    $sql = "SELECT * FROM blocks WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function get_all_blocks($conn) {
    $sql = "SELECT * FROM blocks ORDER BY blocked_at DESC";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function delete_block($conn, $id) {
    $sql = "DELETE FROM blocks WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

function get_blocks_by_blocker_id($conn, $blocker_id) {
    $sql = "SELECT * FROM blocks WHERE blocker_id = ? ORDER BY blocked_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $blocker_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function get_blocks_by_blocked_id($conn, $blocked_id) {
    $sql = "SELECT * FROM blocks WHERE blocked_id = ? ORDER BY blocked_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $blocked_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function check_if_user_blocked($conn, $blocker_id, $blocked_id) {
    $sql = "SELECT * FROM blocks WHERE blocker_id = ? AND blocked_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $blocker_id, $blocked_id);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}
?>