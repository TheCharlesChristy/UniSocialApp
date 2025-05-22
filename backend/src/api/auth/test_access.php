<?php
// Simple test file to verify the auth folder is accessible
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'Auth folder is accessible',
    'timestamp' => date('Y-m-d H:i:s')
]);
?>
