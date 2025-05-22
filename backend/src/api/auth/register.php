<?php
/**
 * User Registration Endpoint
 * 
 * Creates a new user account and sends verification email
 * Requires: username, email, password, first_name, last_name, date_of_birth
 */

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Only POST requests are allowed']);
    exit();
}

// Get database connection
$Database = require_once dirname(dirname(dirname(__FILE__))) . '/db_handler/connection.php';
require_once 'auth_utils.php';

// Check database connection
if (!$Database->isConnected()) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection error']);
    exit();
}

// Get request body
$requestBody = file_get_contents('php://input');
$data = json_decode($requestBody, true);

// Check if data is valid JSON
if ($data === null) {
    http_response_code(400); // Bad request
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit();
}

// Validate required fields
$requiredFields = ['username', 'email', 'password', 'first_name', 'last_name', 'date_of_birth'];
foreach ($requiredFields as $field) {
    if (!isset($data[$field]) || empty(trim($data[$field]))) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "The field {$field} is required"]);
        exit();
    }
}

// Sanitize and validate input
$username = trim($data['username']);
$email = filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL);
$password = $data['password'];
$firstName = trim($data['first_name']);
$lastName = trim($data['last_name']);
$dateOfBirth = trim($data['date_of_birth']);

// Validate email
if (!$email) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit();
}

// Validate date of birth
$dobDate = date_create_from_format('Y-m-d', $dateOfBirth);
if (!$dobDate) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid date of birth format. Use YYYY-MM-DD']);
    exit();
}

// Validate password strength (at least 8 characters with letters and numbers)
if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'Password must be at least 8 characters long and contain both letters and numbers'
    ]);
    exit();
}

// Check if username already exists
$checkUsername = $Database->query("SELECT COUNT(*) AS count FROM users WHERE username = ?", [$username]);
if ($checkUsername === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $Database->getLastError()]);
    exit();
}

if ((int)$checkUsername[0]['count'] > 0) {
    http_response_code(409); // Conflict
    echo json_encode(['success' => false, 'message' => 'Username already exists']);
    exit();
}

// Check if email already exists
$checkEmail = $Database->query("SELECT COUNT(*) AS count FROM users WHERE email = ?", [$email]);
if ($checkEmail === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $Database->getLastError()]);
    exit();
}

if ((int)$checkEmail[0]['count'] > 0) {
    http_response_code(409); // Conflict
    echo json_encode(['success' => false, 'message' => 'Email already exists']);
    exit();
}

// Hash password
$hashedPassword = AuthUtils::hashPassword($password);

// Begin transaction
$Database->beginTransaction();

try {
    // Insert user
    $sql = "INSERT INTO users (
                username, email, password, first_name, last_name, 
                date_of_birth, registration_date, account_status, role
            ) VALUES (?, ?, ?, ?, ?, ?, NOW(), 'active', 'user')";
    
    $result = $Database->execute($sql, [
        $username, $email, $hashedPassword, $firstName, $lastName, $dateOfBirth
    ]);

    if ($result === false) {
        throw new Exception('Failed to register user: ' . $Database->getLastError());
    }
    
    // Get new user ID
    $userId = $Database->query("SELECT LAST_INSERT_ID() AS id")[0]['id'];
    
    // Generate verification token
    $verificationToken = AuthUtils::generateToken($userId, 'verify');
    
    // Send verification email
    AuthUtils::sendVerificationEmail($email, $verificationToken);
    
    // Commit transaction
    $Database->commit();
    
    // Return success response
    http_response_code(201); // Created
    echo json_encode([
        'success' => true,
        'message' => 'User registered successfully. Check your email to verify your account.',
        'user_id' => $userId
    ]);
} catch (Exception $e) {
    // Rollback transaction on error
    $Database->rollBack();
    
    // Return error response
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
