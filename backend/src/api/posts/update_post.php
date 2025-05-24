<?php
/**
 * Update Post API Endpoint
 * 
 * Updates an existing post (only owner can update)
 * Endpoint: PUT /api/posts/update_post
 */

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT, POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Allow PUT and POST requests
if (!in_array($_SERVER['REQUEST_METHOD'], ['PUT', 'POST'])) {
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
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

// Validate post ID
$postId = isset($input['post_id']) ? (int)$input['post_id'] : null;
if (!$postId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Post ID is required']);
    exit();
}

// Check if post exists and user owns it
$checkSql = "SELECT user_id, post_type FROM posts WHERE post_id = ?";
$post = $Database->query($checkSql, [$postId]);

if ($post === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit();
}

if (empty($post)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Post not found']);
    exit();
}

if ($post[0]['user_id'] != $authUser['user_id']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'You can only update your own posts']);
    exit();
}

// Validate updateable fields
$caption = isset($input['caption']) ? validateInput($input['caption'], false, 2000) : null;
$privacyLevel = isset($input['privacy_level']) ? validateInput($input['privacy_level'], false) : null;
$locationName = isset($input['location_name']) ? validateInput($input['location_name'], false, 255) : null;
$locationLat = isset($input['location_lat']) ? floatval($input['location_lat']) : null;
$locationLng = isset($input['location_lng']) ? floatval($input['location_lng']) : null;

// Check if validation failed (returned false)
if (isset($input['caption']) && $caption === false) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Caption is too long (max 2000 characters)']);
    exit();
}

// Validate privacy level if provided
if ($privacyLevel !== null && !in_array($privacyLevel, ['public', 'friends', 'private'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid privacy level. Must be public, friends, or private']);
    exit();
}

// Note: Empty captions are allowed for updates (user may want to clear caption)

// Validate coordinates if provided
if (($locationLat !== null && ($locationLat < -90 || $locationLat > 90)) ||
    ($locationLng !== null && ($locationLng < -180 || $locationLng > 180))) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid coordinates']);
    exit();
}

// Build update query dynamically
$updateFields = [];
$params = [];

if (isset($input['caption'])) {
    $updateFields[] = "caption = ?";
    $params[] = $caption;
}

if ($privacyLevel !== null) {
    $updateFields[] = "privacy_level = ?";
    $params[] = $privacyLevel;
}

if ($locationName !== null) {
    $updateFields[] = "location_name = ?";
    $params[] = $locationName;
}

if ($locationLat !== null) {
    $updateFields[] = "location_lat = ?";
    $params[] = $locationLat;
}

if ($locationLng !== null) {
    $updateFields[] = "location_lng = ?";
    $params[] = $locationLng;
}

// Always update the updated_at timestamp
$updateFields[] = "updated_at = ?";
$params[] = date('Y-m-d H:i:s');

if (empty($updateFields)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No fields to update']);
    exit();
}

// Add post ID to params
$params[] = $postId;

// Execute update
$sql = "UPDATE posts SET " . implode(', ', $updateFields) . " WHERE post_id = ?";
$result = $Database->execute($sql, $params);

if ($result === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to update post']);
    exit();
}

echo json_encode([
    'success' => true,
    'message' => 'Post updated successfully'
]);
