<?php
function create_report($conn, $reporter_id, $reported_id, $reason) {
    $sql = "INSERT INTO reports (reporter_id, reported_id, reason) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $reporter_id, $reported_id, $reason);
    return $stmt->execute();
}

function get_report_by_id($conn, $id) {
    $sql = "SELECT * FROM reports WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function get_all_reports($conn) {
    $sql = "SELECT * FROM reports ORDER BY reported_at DESC";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function update_report($conn, $id, $reason) {
    $sql = "UPDATE reports SET reason = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $reason, $id);
    return $stmt->execute();
}

function delete_report($conn, $id) {
    $sql = "DELETE FROM reports WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

function get_reports_by_reporter_id($conn, $reporter_id) {
    $sql = "SELECT * FROM reports WHERE reporter_id = ? ORDER BY reported_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $reporter_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function get_reports_by_reported_id($conn, $reported_id) {
    $sql = "SELECT * FROM reports WHERE reported_id = ? ORDER BY reported_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $reported_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function search_reports($conn, $keyword) {
    $sql = "SELECT * FROM reports WHERE reason LIKE ? ORDER BY reported_at DESC";
    $stmt = $conn->prepare($sql);
    $likeKeyword = "%" . $keyword . "%";
    $stmt->bind_param("s", $likeKeyword);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

?>