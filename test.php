<?php
// Simple test file to check if PHP is working
echo json_encode([
    'success' => true,
    'message' => 'PHP is working',
    'server_info' => [
        'php_version' => phpversion(),
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown'
    ]
]);
?>
