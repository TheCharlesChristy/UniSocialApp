<?php
/**
 * Search Users API Endpoint
 * 
 * Searches for users by name or username
 * Endpoint: GET /api/users/search
 */

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Require authentication
$requireAuth = true;

// Get database connection
$Database = require_once dirname(dirname(dirname(__FILE__))) . '/db_handler/connection.php';

// Include authentication middleware (this will exit if not authorized)
require_once dirname(dirname(__FILE__)) . '/auth/auth_middleware.php';

// If code execution reaches here, user is authenticated
// $authUser contains the authenticated user data

// Check if query parameter is provided
if (!isset($_GET['query']) || trim($_GET['query']) === '') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Search query is required'
    ]);
    exit;
}

// Get search parameters
$query = trim($_GET['query']);
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

// Validate pagination parameters
if ($page < 1) $page = 1;
if ($limit < 1) $limit = 10;
if ($limit > 50) $limit = 50; // Maximum limit to prevent overload

$offset = ($page - 1) * $limit;

// Create search tokens for the query
$searchTerms = explode(' ', $query);
$searchPattern = '';

foreach ($searchTerms as $term) {
    $term = trim($term);
    if (!empty($term)) {
        $searchPattern .= '%' . $term . '%';
    }
}

// If query is a single term, also search with it directly
if (count($searchTerms) === 1) {
    $singleTerm = '%' . $query . '%';
}

// Build the search SQL based on the number of terms
if (count($searchTerms) > 1) {
    // For multi-word searches, we need to check combinations
    $totalQuery = "SELECT COUNT(*) as total FROM users
                  WHERE (
                      CONCAT(first_name, ' ', last_name) LIKE ? OR
                      username LIKE ? OR
                      email LIKE ?
                  )
                  AND user_id != ? AND account_status = 'active'";
                  
    $searchQuery = "SELECT user_id, username, first_name, last_name, profile_picture, bio FROM users
                  WHERE (
                      CONCAT(first_name, ' ', last_name) LIKE ? OR
                      username LIKE ? OR
                      email LIKE ?
                  )
                  AND user_id != ? AND account_status = 'active'
                  ORDER BY 
                      CASE 
                          WHEN username = ? THEN 1
                          WHEN first_name = ? OR last_name = ? THEN 2
                          ELSE 3
                      END
                  LIMIT ? OFFSET ?";
                  
    $totalParams = [$searchPattern, $singleTerm, $singleTerm, $authUser['user_id']];
    $searchParams = [
        $searchPattern, $singleTerm, $singleTerm, $authUser['user_id'], 
        $query, $query, $query, 
        $limit, $offset
    ];
} else {
    // For single-word searches, simpler query
    $totalQuery = "SELECT COUNT(*) as total FROM users
                  WHERE (
                      first_name LIKE ? OR
                      last_name LIKE ? OR
                      username LIKE ?
                  )
                  AND user_id != ? AND account_status = 'active'";
                  
    $searchQuery = "SELECT user_id, username, first_name, last_name, profile_picture, bio FROM users
                  WHERE (
                      first_name LIKE ? OR
                      last_name LIKE ? OR
                      username LIKE ?
                  )
                  AND user_id != ? AND account_status = 'active'
                  ORDER BY 
                      CASE 
                          WHEN username = ? THEN 1
                          WHEN first_name = ? OR last_name = ? THEN 2
                          ELSE 3
                      END
                  LIMIT ? OFFSET ?";
                  
    $totalParams = [$singleTerm, $singleTerm, $singleTerm, $authUser['user_id']];
    $searchParams = [
        $singleTerm, $singleTerm, $singleTerm, $authUser['user_id'], 
        $query, $query, $query, 
        $limit, $offset
    ];
}

// Get total results count
$totalResult = $Database->query($totalQuery, $totalParams);
$total = $totalResult[0]['total'];

// Calculate total pages
$totalPages = ceil($total / $limit);

// Get search results with pagination
$results = $Database->query($searchQuery, $searchParams);

// Format the results
$formattedResults = [];
if ($results && !empty($results)) {
    foreach ($results as $user) {
        // Get friendship status
        $friendship = $Database->query(
            "SELECT status FROM friendships 
             WHERE (user_id_1 = ? AND user_id_2 = ?) OR (user_id_1 = ? AND user_id_2 = ?)",
            [$authUser['user_id'], $user['user_id'], $user['user_id'], $authUser['user_id']]
        );
        
        $friendshipStatus = null;
        if ($friendship && !empty($friendship)) {
            $friendshipStatus = $friendship[0]['status'];
        }
        
        $formattedResults[] = [
            'user_id' => $user['user_id'],
            'username' => $user['username'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'profile_picture' => $user['profile_picture'],
            'bio' => $user['bio'],
            'friendship_status' => $friendshipStatus
        ];
    }
}

// Return search results
echo json_encode([
    'success' => true,
    'users' => $formattedResults,
    'total_results' => $total,
    'current_page' => $page,
    'total_pages' => $totalPages
]);
