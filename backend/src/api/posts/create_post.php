<?php
/**
 * Create Post API Endpoint
 * 
 * Creates a new post (text, photo, or video)
 * Endpoint: POST /api/posts/create_post
 */

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

// Require authentication
$requireAuth = true;

// Get database connection
$Database = require_once dirname(dirname(dirname(__FILE__))) . '/db_handler/connection.php';

// Include authentication middleware
require_once dirname(dirname(__FILE__)) . '/auth/auth_middleware.php';

// Function to validate and sanitize input
function validateInput($data, $required = false, $maxLength = null) {
    if ($required && empty($data)) {
        return false;
    }
    $data = trim($data);
    if ($maxLength && strlen($data) > $maxLength) {
        return false;
    }
    return $data;
}

// Get input data
$input = [];
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';

if (strpos($contentType, 'application/json') !== false) {
    // JSON input
    $jsonInput = json_decode(file_get_contents('php://input'), true);
    if ($jsonInput) {
        $input = $jsonInput;
    }
} else {
    // Form data input
    $input = $_POST;
}

// Validate required fields
$postType = validateInput($input['post_type'] ?? '', true);
$privacyLevel = validateInput($input['privacy_level'] ?? '', true);
$caption = validateInput($input['caption'] ?? '', false);
$locationName = validateInput($input['location_name'] ?? '', false, 255);
$locationLat = isset($input['location_lat']) ? floatval($input['location_lat']) : null;
$locationLng = isset($input['location_lng']) ? floatval($input['location_lng']) : null;

// Validate post type
if (!in_array($postType, ['text', 'photo', 'video'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid post type. Must be text, photo, or video']);
    exit();
}

// Validate privacy level
if (!in_array($privacyLevel, ['public', 'friends', 'private'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid privacy level. Must be public, friends, or private']);
    exit();
}

// Validate caption for text posts
if ($postType === 'text' && empty($caption)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Caption is required for text posts']);
    exit();
}

// Validate caption length < 2000 characters
if ($caption && strlen($caption) > 2000) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Caption exceeds maximum length of 2000 characters']);
    exit();
}

// Validate coordinates if provided
if (($locationLat !== null && ($locationLat < -90 || $locationLat > 90)) ||
    ($locationLng !== null && ($locationLng < -180 || $locationLng > 180))) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid coordinates']);
    exit();
}

// Handle media upload for photo/video posts
$mediaUrl = null;
if (in_array($postType, ['photo', 'video'])) {
    if (empty($_FILES['media']) || $_FILES['media']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Media file is required for photo/video posts']);
        exit();
    }
    
    $uploadFile = $_FILES['media'];
    $allowedTypes = [
        'photo' => ['image/jpeg', 'image/png', 'image/gif'],
        'video' => ['video/mp4', 'video/avi', 'video/quicktime']
    ];
    
    $allowedExtensions = [
        'photo' => ['jpg', 'jpeg', 'png', 'gif'],
        'video' => ['mp4', 'avi', 'mov']
    ];
    
    // Validate file type
    $fileType = mime_content_type($uploadFile['tmp_name']);
    $extension = strtolower(pathinfo($uploadFile['name'], PATHINFO_EXTENSION));
    
    if (!in_array($fileType, $allowedTypes[$postType]) || 
        !in_array($extension, $allowedExtensions[$postType])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid file type']);
        exit();
    }
    
    // Validate file size (50MB max for posts)
    if ($uploadFile['size'] > 50 * 1024 * 1024) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'File size too large. Maximum 50MB allowed']);
        exit();
    }
    
    // Generate unique filename
    $filename = $authUser['user_id'] . '_' . time() . '_' . uniqid() . '.' . $extension;
    $uploadDir = dirname(dirname(dirname(dirname(__FILE__)))) . '/media/images/posts/';
    $uploadPath = $uploadDir . $filename;
    
    // Create directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Move uploaded file
    if (!move_uploaded_file($uploadFile['tmp_name'], $uploadPath)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
        exit();
    }
    
    $mediaUrl = 'media/images/posts/' . $filename;
}

// Insert post into database
$currentDateTime = date('Y-m-d H:i:s');

$sql = "
    INSERT INTO posts (
        user_id, caption, post_type, media_url, 
        created_at, updated_at, privacy_level, 
        location_lat, location_lng, location_name
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
";

$params = [
    $authUser['user_id'],
    $caption,
    $postType,
    $mediaUrl,
    $currentDateTime,
    $currentDateTime,
    $privacyLevel,
    $locationLat,
    $locationLng,
    $locationName
];

$result = $Database->execute($sql, $params);

if ($result === false) {
    // If insert failed and we uploaded a file, clean it up
    if ($mediaUrl && file_exists($uploadPath)) {
        unlink($uploadPath);
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to create post']);
    exit();
}

// Get the created post ID
$postId = $Database->query("SELECT LAST_INSERT_ID() as post_id")[0]['post_id'];

echo json_encode([
    'success' => true,
    'message' => 'Post created successfully',
    'post_id' => $postId
]);
