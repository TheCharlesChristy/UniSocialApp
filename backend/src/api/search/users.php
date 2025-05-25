<?php
/**
 * Search Users API Endpoint
 * 
 * Searches for users by name or username with advanced filtering
 * Endpoint: GET /api/search/users
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

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
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

// Get search parameters
$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$role = isset($_GET['role']) ? trim($_GET['role']) : ''; // Optional role filter
$status = isset($_GET['status']) ? trim($_GET['status']) : 'active'; // Account status filter

// Validate parameters
if (empty($query)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Search query is required']);
    exit();
}

if ($page < 1) {
    $page = 1;
}
if ($limit < 1 || $limit > 50) {
    $limit = 10;
}

// Validate status parameter
if (!in_array($status, ['active', 'suspended', 'all'])) {
    $status = 'active';
}

// Calculate offset
$offset = ($page - 1) * $limit;
$userId = $authUser['user_id'];

try {
    // Prepare search terms
    $searchTerms = explode(' ', $query);
    $searchPattern = '';
    
    foreach ($searchTerms as $term) {
        $term = trim($term);
        if (!empty($term)) {
            $searchPattern .= '%' . $term . '%';
        }
    }
    
    $singleTerm = '%' . $query . '%';

    // Build WHERE clause for filters
    $whereConditions = [];
    $whereParams = [];

    // Account status filter
    if ($status !== 'all') {
        $whereConditions[] = "account_status = ?";
        $whereParams[] = $status;
    } else {
        $whereConditions[] = "account_status != 'deleted'";
    }

    // Role filter (optional)
    if (!empty($role) && in_array($role, ['user', 'admin', 'moderator'])) {
        $whereConditions[] = "role = ?";
        $whereParams[] = $role;
    }

    // Exclude current user
    $whereConditions[] = "user_id != ?";
    $whereParams[] = $userId;

    $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);

    // Build search queries based on search terms
    if (count($searchTerms) > 1) {
        // Multi-word search with combined name matching
        $searchCondition = "(
            CONCAT(first_name, ' ', last_name) LIKE ? OR
            username LIKE ? OR
            email LIKE ?
        )";
        
        $totalQuery = "
            SELECT COUNT(*) as total FROM users
            $whereClause AND $searchCondition
        ";
        
        $searchQuery = "
            SELECT 
                user_id, 
                username, 
                first_name, 
                last_name, 
                profile_picture, 
                bio,
                role,
                account_status,
                registration_date
            FROM users
            $whereClause AND $searchCondition
            ORDER BY 
                CASE 
                    WHEN username = ? THEN 1
                    WHEN first_name = ? OR last_name = ? THEN 2
                    WHEN CONCAT(first_name, ' ', last_name) = ? THEN 3
                    ELSE 4
                END,
                username ASC
            LIMIT ? OFFSET ?
        ";
        
        $totalParams = array_merge($whereParams, [$searchPattern, $singleTerm, $singleTerm]);
        $searchParams = array_merge($whereParams, [
            $searchPattern, $singleTerm, $singleTerm,
            $query, $query, $query, $query,
            $limit, $offset
        ]);
        
    } else {
        // Single-word search
        $searchCondition = "(
            first_name LIKE ? OR
            last_name LIKE ? OR
            username LIKE ? OR
            email LIKE ?
        )";
        
        $totalQuery = "
            SELECT COUNT(*) as total FROM users
            $whereClause AND $searchCondition
        ";
        
        $searchQuery = "
            SELECT 
                user_id, 
                username, 
                first_name, 
                last_name, 
                profile_picture, 
                bio,
                role,
                account_status,
                registration_date
            FROM users
            $whereClause AND $searchCondition
            ORDER BY 
                CASE 
                    WHEN username = ? THEN 1
                    WHEN first_name = ? OR last_name = ? THEN 2
                    ELSE 3
                END,
                username ASC
            LIMIT ? OFFSET ?
        ";
        
        $totalParams = array_merge($whereParams, [$singleTerm, $singleTerm, $singleTerm, $singleTerm]);
        $searchParams = array_merge($whereParams, [
            $singleTerm, $singleTerm, $singleTerm, $singleTerm,
            $query, $query, $query,
            $limit, $offset
        ]);
    }

    // Get total results count
    $totalResult = $Database->query($totalQuery, $totalParams);
    if ($totalResult === false) {
        throw new Exception('Database error in count query: ' . $Database->getLastError());
    }
    
    $total = $totalResult[0]['total'];
    $totalPages = ceil($total / $limit);

    // Get search results
    $results = $Database->query($searchQuery, $searchParams);
    if ($results === false) {
        throw new Exception('Database error in search query: ' . $Database->getLastError());
    }

    // Format results with friendship status
    $formattedResults = [];
    if ($results && !empty($results)) {
        foreach ($results as $user) {
            // Get friendship status with current user
            $friendship = $Database->query(
                "SELECT status FROM friendships 
                 WHERE (user_id_1 = ? AND user_id_2 = ?) OR (user_id_1 = ? AND user_id_2 = ?)",
                [$userId, $user['user_id'], $user['user_id'], $userId]
            );
            
            $friendshipStatus = null;
            if ($friendship && !empty($friendship)) {
                $friendshipStatus = $friendship[0]['status'];
            }

            // Get additional user stats (post count, friend count)
            $userStats = $Database->query(
                "SELECT 
                    (SELECT COUNT(*) FROM posts WHERE user_id = ? AND post_type IS NOT NULL) as posts_count,
                    (SELECT COUNT(*) FROM friendships WHERE (user_id_1 = ? OR user_id_2 = ?) AND status = 'accepted') as friends_count
                ",
                [$user['user_id'], $user['user_id'], $user['user_id']]
            );

            $postsCount = 0;
            $friendsCount = 0;
            if ($userStats && !empty($userStats)) {
                $postsCount = $userStats[0]['posts_count'];
                $friendsCount = $userStats[0]['friends_count'];
            }

            $formattedResults[] = [
                'user_id' => $user['user_id'],
                'username' => $user['username'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'profile_picture' => $user['profile_picture'],
                'bio' => $user['bio'],
                'role' => $user['role'],
                'account_status' => $user['account_status'],
                'registration_date' => $user['registration_date'],
                'friendship_status' => $friendshipStatus,
                'stats' => [
                    'posts_count' => (int)$postsCount,
                    'friends_count' => (int)$friendsCount
                ]
            ];
        }
    }

    // Return search results
    echo json_encode([
        'success' => true,
        'message' => 'User search completed successfully',
        'query' => $query,
        'filters' => [
            'status' => $status,
            'role' => $role ?: null
        ],
        'users' => $formattedResults,
        'pagination' => [
            'total_results' => (int)$total,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'per_page' => $limit
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'User search failed: ' . $e->getMessage()
    ]);
}
