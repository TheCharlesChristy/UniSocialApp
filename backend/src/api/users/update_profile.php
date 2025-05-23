<?php
/**
 * Update User Profile API Endpoint
 * 
 * Updates current user's profile information
 * Endpoint: PUT /api/users/me
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

// Only allow PUT/POST requests (use POST as a fallback for clients that don't support PUT)
if ($_SERVER['REQUEST_METHOD'] != 'PUT' && $_SERVER['REQUEST_METHOD'] != 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

// Require authentication
$requireAuth = true;

// Get database connection
$Database = require_once dirname(dirname(dirname(__FILE__))) . '/db_handler/connection.php';

// Include authentication middleware (this will exit if not authorized)
require_once dirname(dirname(__FILE__)) . '/auth/auth_middleware.php';

// If code execution reaches here, user is authenticated
// $authUser contains the authenticated user data

// Parse input data
// For PUT requests or application/json content type
$input = json_decode(file_get_contents('php://input'), true);

// For multipart/form-data (when uploading files)
if (empty($input) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = $_POST;
}

// Initialize array to track updated fields
$updatedFields = [];
$updateSql = "UPDATE users SET ";
$updateParams = [];
$updates = 0;

// Check and validate each field that can be updated
if (isset($input['first_name']) && trim($input['first_name']) !== '') {
    $updateSql .= "first_name = ?, ";
    $updateParams[] = trim($input['first_name']);
    $updatedFields[] = 'first_name';
    $updates++;
}

if (isset($input['last_name']) && trim($input['last_name']) !== '') {
    $updateSql .= "last_name = ?, ";
    $updateParams[] = trim($input['last_name']);
    $updatedFields[] = 'last_name';
    $updates++;
}

if (isset($input['bio'])) {
    // Allow empty bio (to clear it)
    $updateSql .= "bio = ?, ";
    $updateParams[] = trim($input['bio']);
    $updatedFields[] = 'bio';
    $updates++;
}

// Handle profile picture upload
$profilePicPath = null;
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
    // Create media directory if it doesn't exist
    $uploadDir = dirname(dirname(dirname(__FILE__))) . '/media/images/profile/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Get file info
    $fileName = $_FILES['profile_picture']['name'];
    $fileSize = $_FILES['profile_picture']['size'];
    $fileTmp = $_FILES['profile_picture']['tmp_name'];
    $fileType = $_FILES['profile_picture']['type'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    // Allowed extensions
    $extensions = ['jpeg', 'jpg', 'png', 'gif'];
    
    // Validate file type
    if (in_array($fileExt, $extensions)) {
        // Check file size (limit to 5MB)
        $maxSize = 5 * 1024 * 1024;
        if ($fileSize <= $maxSize) {
            // Generate unique file name
            $newFileName = $authUser['user_id'] . '_' . time() . '.' . $fileExt;
            $uploadPath = $uploadDir . $newFileName;
            
            // Move the file from temp directory to the upload directory
            if (move_uploaded_file($fileTmp, $uploadPath)) {
                // Use relative path for storage in database
                $profilePicPath = 'media/images/profile/' . $newFileName;
                
                $updateSql .= "profile_picture = ?, ";
                $updateParams[] = $profilePicPath;
                $updatedFields[] = 'profile_picture';
                $updates++;
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to upload profile picture'
                ]);
                exit;
            }
        } else {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'File size exceeds limit (5MB)'
            ]);
            exit;
        }
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid file format. Allowed formats: JPEG, JPG, PNG, GIF'
        ]);
        exit;
    }
}

// If no updates were provided
if ($updates == 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'No profile updates provided'
    ]);
    exit;
}

// Finalize the SQL query
$updateSql = rtrim($updateSql, ', ');
$updateSql .= " WHERE user_id = ?";
$updateParams[] = $authUser['user_id'];

// Execute the update query
$result = $Database->execute($updateSql, $updateParams);

if ($result !== false) {
    echo json_encode([
        'success' => true,
        'message' => 'Profile updated successfully',
        'updated_fields' => $updatedFields,
        'profile_picture' => $profilePicPath
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update profile'
    ]);
}
