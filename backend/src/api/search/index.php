<?php
/**
 * Global Search API Endpoint
 * 
 * Performs a global search across users and posts
 * Endpoint: GET /api/search/index
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
$type = isset($_GET['type']) ? trim($_GET['type']) : 'all'; // all, users, posts

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

// Validate search type
if (!in_array($type, ['all', 'users', 'posts'])) {
    $type = 'all';
}

// Calculate offset
$offset = ($page - 1) * $limit;
$userId = $authUser['user_id'];

try {
    $searchResults = [
        'users' => [],
        'posts' => []
    ];

    // Prepare search terms
    $searchTerm = "%$query%";

    if ($type === 'all' || $type === 'users') {
        // Search users
        $userLimit = $type === 'all' ? min(5, $limit) : $limit;
        $userOffset = $type === 'all' ? 0 : $offset;

        $userSearchSql = "
            SELECT 
                user_id, 
                username, 
                first_name, 
                last_name, 
                profile_picture, 
                bio
            FROM users
            WHERE (
                first_name LIKE ? OR
                last_name LIKE ? OR
                username LIKE ? OR
                CONCAT(first_name, ' ', last_name) LIKE ?
            )
            AND user_id != ? 
            AND account_status = 'active'
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

        $userResults = $Database->query($userSearchSql, [
            $searchTerm, $searchTerm, $searchTerm, $searchTerm,
            $userId,
            $query, $query, $query, $query,
            $userLimit, $userOffset
        ]);

        if ($userResults === false) {
            throw new Exception('Database error in user search: ' . $Database->getLastError());
        }

        // Format user results with friendship status
        foreach ($userResults as $user) {
            $friendship = $Database->query(
                "SELECT status FROM friendships 
                 WHERE (user_id_1 = ? AND user_id_2 = ?) OR (user_id_1 = ? AND user_id_2 = ?)",
                [$userId, $user['user_id'], $user['user_id'], $userId]
            );
            
            $friendshipStatus = null;
            if ($friendship && !empty($friendship)) {
                $friendshipStatus = $friendship[0]['status'];
            }

            $searchResults['users'][] = [
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

    if ($type === 'all' || $type === 'posts') {
        // Search posts with privacy filtering
        $postLimit = $type === 'all' ? min(5, $limit) : $limit;
        $postOffset = $type === 'all' ? 0 : $offset;

        $postSearchSql = "
            SELECT 
                p.post_id,
                p.user_id,
                p.caption,
                p.post_type,
                p.media_url,
                p.created_at,
                p.updated_at,
                p.privacy_level,
                p.location_lat,
                p.location_lng,
                p.location_name,
                u.username,
                u.first_name,
                u.last_name,
                u.profile_picture,
                COALESCE(like_counts.likes_count, 0) as likes_count,
                COALESCE(comment_counts.comments_count, 0) as comments_count,
                CASE WHEN user_likes.like_id IS NOT NULL THEN 1 ELSE 0 END as user_has_liked,
                -- Relevance scoring
                CASE 
                    WHEN p.caption LIKE ? THEN 2
                    WHEN p.location_name LIKE ? THEN 1
                    ELSE 0
                END as relevance_score
            FROM posts p
            INNER JOIN users u ON p.user_id = u.user_id
            LEFT JOIN friendships f1 ON (f1.user_id_1 = ? AND f1.user_id_2 = p.user_id AND f1.status = 'accepted')
            LEFT JOIN friendships f2 ON (f2.user_id_2 = ? AND f2.user_id_1 = p.user_id AND f2.status = 'accepted')
            LEFT JOIN (
                SELECT post_id, COUNT(*) as likes_count
                FROM likes
                WHERE post_id IS NOT NULL
                GROUP BY post_id
            ) like_counts ON p.post_id = like_counts.post_id
            LEFT JOIN (
                SELECT post_id, COUNT(*) as comments_count
                FROM comments
                GROUP BY post_id
            ) comment_counts ON p.post_id = comment_counts.post_id
            LEFT JOIN likes user_likes ON p.post_id = user_likes.post_id AND user_likes.user_id = ?
            WHERE u.account_status = 'active'
            AND (p.caption LIKE ? OR p.location_name LIKE ?)
            AND (
                p.user_id = ? OR  -- User's own posts
                p.privacy_level = 'public' OR  -- Public posts
                (p.privacy_level = 'friends' AND (f1.friendship_id IS NOT NULL OR f2.friendship_id IS NOT NULL))  -- Friends' posts
            )
            ORDER BY relevance_score DESC, p.created_at DESC
            LIMIT ? OFFSET ?
        ";

        $exactSearchTerm = "%$query%";
        $postResults = $Database->query($postSearchSql, [
            $exactSearchTerm, $exactSearchTerm,
            $userId, $userId, $userId,
            $searchTerm, $searchTerm,
            $userId,
            $postLimit, $postOffset
        ]);

        if ($postResults === false) {
            throw new Exception('Database error in post search: ' . $Database->getLastError());
        }

        $searchResults['posts'] = $postResults ?: [];
    }

    // Get total counts for pagination (only when searching specific type)
    $totalUsers = 0;
    $totalPosts = 0;
    $totalPages = 1;

    if ($type !== 'all') {
        if ($type === 'users') {
            $userCountSql = "
                SELECT COUNT(*) as total FROM users
                WHERE (
                    first_name LIKE ? OR
                    last_name LIKE ? OR
                    username LIKE ? OR
                    CONCAT(first_name, ' ', last_name) LIKE ?
                )
                AND user_id != ? 
                AND account_status = 'active'
            ";
            $userCountResult = $Database->query($userCountSql, [
                $searchTerm, $searchTerm, $searchTerm, $searchTerm, $userId
            ]);
            if ($userCountResult !== false) {
                $totalUsers = $userCountResult[0]['total'];
                $totalPages = ceil($totalUsers / $limit);
            }
        } elseif ($type === 'posts') {
            $postCountSql = "
                SELECT COUNT(*) as total 
                FROM posts p
                INNER JOIN users u ON p.user_id = u.user_id
                LEFT JOIN friendships f1 ON (f1.user_id_1 = ? AND f1.user_id_2 = p.user_id AND f1.status = 'accepted')
                LEFT JOIN friendships f2 ON (f2.user_id_2 = ? AND f2.user_id_1 = p.user_id AND f2.status = 'accepted')
                WHERE u.account_status = 'active'
                AND (p.caption LIKE ? OR p.location_name LIKE ?)
                AND (
                    p.user_id = ? OR
                    p.privacy_level = 'public' OR
                    (p.privacy_level = 'friends' AND (f1.friendship_id IS NOT NULL OR f2.friendship_id IS NOT NULL))
                )
            ";
            $postCountResult = $Database->query($postCountSql, [
                $userId, $userId, $searchTerm, $searchTerm, $userId
            ]);
            if ($postCountResult !== false) {
                $totalPosts = $postCountResult[0]['total'];
                $totalPages = ceil($totalPosts / $limit);
            }
        }
    }

    // Return search results
    $response = [
        'success' => true,
        'message' => 'Search completed successfully',
        'query' => $query,
        'type' => $type,
        'results' => $searchResults
    ];

    // Add pagination info for specific type searches
    if ($type !== 'all') {
        $response['pagination'] = [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_results' => $type === 'users' ? $totalUsers : $totalPosts
        ];
    } else {
        $response['counts'] = [
            'users' => count($searchResults['users']),
            'posts' => count($searchResults['posts'])
        ];
    }

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Search failed: ' . $e->getMessage()
    ]);
}
