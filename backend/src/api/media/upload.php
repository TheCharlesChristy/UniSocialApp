<?php
/**
 * Media Upload API Endpoint
 * 
 * Handles file uploads for profile pictures and post media
 * Endpoint: POST /api/media/upload
 */

// Suppress PHP warnings and errors that could break JSON output
error_reporting(0);
ini_set('display_errors', 0);

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Check if POST content length exceeds PHP's limit
$contentLength = $_SERVER['CONTENT_LENGTH'] ?? 0;
$maxPostSize = ini_get('post_max_size');
$maxPostSizeBytes = parseIniSize($maxPostSize);

// Also check if POST data was truncated (another sign of size limit exceeded)
$postDataMissing = empty($_POST) && $contentLength > 0;

if ($contentLength > $maxPostSizeBytes || $postDataMissing) {
    http_response_code(413); // 413 Payload Too Large
    echo json_encode([
        'success' => false, 
        'message' => 'File size too large. Maximum allowed size is ' . $maxPostSize,
        'uploaded_size' => formatBytes($contentLength),
        'max_size' => $maxPostSize
    ]);
    exit();
}

// Helper function to parse ini size values (e.g., "40M", "2G")
function parseIniSize($size) {
    $size = trim($size);
    $last = strtolower($size[strlen($size)-1]);
    $value = (int) $size;
    
    switch ($last) {
        case 'g':
            $value *= 1024;
        case 'm':
            $value *= 1024;
        case 'k':
            $value *= 1024;
    }
    
    return $value;
}

// Helper function to format bytes into human readable format
function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for ($i = 0; $bytes > 1024; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

// Require authentication
$requireAuth = true;

// Get database connection
$Database = require_once dirname(dirname(dirname(__FILE__))) . '/db_handler/connection.php';

// Include authentication middleware
require_once dirname(dirname(__FILE__)) . '/auth/auth_middleware.php';

// Function to sanitize filename
function sanitizeFilename($filename) {
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
    return $filename;
}

// Validate upload type
$uploadType = isset($_POST['type']) ? $_POST['type'] : '';
if (!in_array($uploadType, ['profile_picture', 'post_media'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid upload type. Must be profile_picture or post_media']);
    exit();
}

// Check if file was uploaded
if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE => 'File size exceeds server limit',
        UPLOAD_ERR_FORM_SIZE => 'File size exceeds form limit',
        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
    ];
    
    $error = $_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE;
    $message = $errorMessages[$error] ?? 'Unknown upload error';
    
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $message]);
    exit();
}

$uploadFile = $_FILES['file'];

// Define allowed file types and sizes
$allowedTypes = [
    'profile_picture' => [
        'mime_types' => ['image/jpeg', 'image/png', 'image/gif'],
        'extensions' => ['jpg', 'jpeg', 'png', 'gif'],
        'max_size' => 5 * 1024 * 1024, // 5MB
        'directory' => 'media/images/'
    ],
    'post_media' => [
        'mime_types' => ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/avi', 'video/quicktime'],
        'extensions' => ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'avi', 'mov'],
        'max_size' => 50 * 1024 * 1024, // 50MB
        'directory' => 'media/images/posts/'
    ]
];

$config = $allowedTypes[$uploadType];

// Validate file extension
$extension = strtolower(pathinfo($uploadFile['name'], PATHINFO_EXTENSION));
if (!in_array($extension, $config['extensions'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Allowed: ' . implode(', ', $config['extensions'])]);
    exit();
}

// Validate MIME type
$mimeType = mime_content_type($uploadFile['tmp_name']);
if (!in_array($mimeType, $config['mime_types'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid file format']);
    exit();
}

// Validate file size
if ($uploadFile['size'] > $config['max_size']) {
    http_response_code(413); // 413 Payload Too Large
    echo json_encode([
        'success' => false, 
        'message' => 'File size too large. Maximum allowed size is ' . formatBytes($config['max_size']),
        'uploaded_size' => formatBytes($uploadFile['size']),
        'max_size' => formatBytes($config['max_size'])
    ]);
    exit();
}

// Rate limiting check (simple implementation)
$rateLimitKey = 'upload_' . $authUser['user_id'] . '_' . date('Y-m-d-H');
$uploadCount = 0;

// You might want to implement proper rate limiting with Redis or database
// For now, we'll just proceed

// Generate unique filename
$timestamp = time();
$randomString = uniqid();
$sanitizedOriginalName = sanitizeFilename(pathinfo($uploadFile['name'], PATHINFO_FILENAME));
$filename = $authUser['user_id'] . '_' . $timestamp . '_' . $randomString . '.' . $extension;

// Determine upload directory
$uploadDir = dirname(dirname(dirname(dirname(__FILE__)))) . '/' . $config['directory'];
$uploadPath = $uploadDir . $filename;

// Create directory if it doesn't exist
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to create upload directory']);
        exit();
    }
}

// Check if directory is writable
if (!is_writable($uploadDir)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Upload directory is not writable']);
    exit();
}

// Move uploaded file
if (!move_uploaded_file($uploadFile['tmp_name'], $uploadPath)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to save uploaded file']);
    exit();
}

// Return relative path for database storage
$relativePath = $config['directory'] . $filename;

// If it's a profile picture, update user's profile
if ($uploadType === 'profile_picture') {
    $updateSql = "UPDATE users SET profile_picture = ? WHERE user_id = ?";
    $result = $Database->execute($updateSql, [$relativePath, $authUser['user_id']]);
    
    if ($result === false) {
        // Clean up uploaded file
        unlink($uploadPath);
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update profile picture']);
        exit();
    }
}

echo json_encode([
    'success' => true,
    'message' => 'File uploaded successfully',
    'file_path' => $relativePath,
    'file_size' => $uploadFile['size'],
    'file_type' => $mimeType
]);
