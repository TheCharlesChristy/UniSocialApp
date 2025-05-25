<?php
/**
 * Search Posts API Endpoint
 * 
 * Searches posts by content, location, and other criteria with advanced filtering
 * Endpoint: GET /api/search/posts
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
$postType = isset($_GET['post_type']) ? trim($_GET['post_type']) : ''; // photo, video, text
$privacy = isset($_GET['privacy']) ? trim($_GET['privacy']) : ''; // public, friends
$author = isset($_GET['author']) ? trim($_GET['author']) : ''; // username filter
$dateFrom = isset($_GET['date_from']) ? trim($_GET['date_from']) : ''; // YYYY-MM-DD
$dateTo = isset($_GET['date_to']) ? trim($_GET['date_to']) : ''; // YYYY-MM-DD
$location = isset($_GET['location']) ? trim($_GET['location']) : ''; // location name search
$sortBy = isset($_GET['sort_by']) ? trim($_GET['sort_by']) : 'relevance'; // relevance, date, likes

// Validate parameters
if (empty($query) && empty($location) && empty($author)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'At least one search parameter is required (q, location, or author)']);
    exit();
}

if ($page < 1) {
    $page = 1;
}
if ($limit < 1 || $limit > 50) {
    $limit = 10;
}

// Validate post type
if (!empty($postType) && !in_array($postType, ['photo', 'video', 'text'])) {
    $postType = '';
}

// Validate privacy level
if (!empty($privacy) && !in_array($privacy, ['public', 'friends'])) {
    $privacy = '';
}

// Validate sort order
if (!in_array($sortBy, ['relevance', 'date', 'likes', 'comments'])) {
    $sortBy = 'relevance';
}

// Calculate offset
$offset = ($page - 1) * $limit;
$userId = $authUser['user_id'];

try {
    // Prepare search terms
    $searchTerm = !empty($query) ? "%$query%" : '';
    $locationTerm = !empty($location) ? "%$location%" : '';
    
    // Build WHERE conditions and parameters
    $whereConditions = [];
    $whereParams = [];
    $joinConditions = [];

    // Base conditions
    $whereConditions[] = "u.account_status = 'active'";

    // Privacy filtering - user can see their own posts, public posts, and friends' posts
    $privacyCondition = "(
        p.user_id = ? OR  -- User's own posts
        p.privacy_level = 'public' OR  -- Public posts
        (p.privacy_level = 'friends' AND (f1.friendship_id IS NOT NULL OR f2.friendship_id IS NOT NULL))  -- Friends' posts
    )";
    $whereConditions[] = $privacyCondition;
    $whereParams[] = $userId;

    // Search conditions
    $searchConditions = [];
    
    if (!empty($query)) {
        $searchConditions[] = "p.caption LIKE ?";
        $whereParams[] = $searchTerm;
    }
    
    if (!empty($location)) {
        $searchConditions[] = "p.location_name LIKE ?";
        $whereParams[] = $locationTerm;
    }
    
    if (!empty($author)) {
        $searchConditions[] = "u.username LIKE ?";
        $whereParams[] = "%$author%";
    }

    if (!empty($searchConditions)) {
        $whereConditions[] = "(" . implode(' OR ', $searchConditions) . ")";
    }

    // Post type filter
    if (!empty($postType)) {
        $whereConditions[] = "p.post_type = ?";
        $whereParams[] = $postType;
    }

    // Privacy filter (if specified, further restrict results)
    if (!empty($privacy)) {
        $whereConditions[] = "p.privacy_level = ?";
        $whereParams[] = $privacy;
    }

    // Date range filters
    if (!empty($dateFrom)) {
        $whereConditions[] = "DATE(p.created_at) >= ?";
        $whereParams[] = $dateFrom;
    }
    
    if (!empty($dateTo)) {
        $whereConditions[] = "DATE(p.created_at) <= ?";
        $whereParams[] = $dateTo;
    }

    $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);

    // Build ORDER BY clause
    $orderBy = '';
    switch ($sortBy) {
        case 'date':
            $orderBy = 'ORDER BY p.created_at DESC';
            break;
        case 'likes':
            $orderBy = 'ORDER BY likes_count DESC, p.created_at DESC';
            break;
        case 'comments':
            $orderBy = 'ORDER BY comments_count DESC, p.created_at DESC';
            break;
        case 'relevance':
        default:
            $relevanceOrder = '';
            if (!empty($query) && !empty($location)) {
                $relevanceOrder = "
                    CASE 
                        WHEN p.caption LIKE ? AND p.location_name LIKE ? THEN 3
                        WHEN p.caption LIKE ? THEN 2
                        WHEN p.location_name LIKE ? THEN 1
                        ELSE 0
                    END DESC,";
                // Add parameters for relevance scoring
                array_unshift($whereParams, $searchTerm, $locationTerm, $searchTerm, $locationTerm);
            } elseif (!empty($query)) {
                $relevanceOrder = "
                    CASE 
                        WHEN p.caption LIKE ? THEN 2
                        ELSE 0
                    END DESC,";
                array_unshift($whereParams, $searchTerm);
            } elseif (!empty($location)) {
                $relevanceOrder = "
                    CASE 
                        WHEN p.location_name LIKE ? THEN 2
                        ELSE 0
                    END DESC,";
                array_unshift($whereParams, $locationTerm);
            }
            $orderBy = "ORDER BY $relevanceOrder p.created_at DESC";
            break;
    }

    // Count query
    $countQuery = "
        SELECT COUNT(*) as total 
        FROM posts p
        INNER JOIN users u ON p.user_id = u.user_id
        LEFT JOIN friendships f1 ON (f1.user_id_1 = ? AND f1.user_id_2 = p.user_id AND f1.status = 'accepted')
        LEFT JOIN friendships f2 ON (f2.user_id_2 = ? AND f2.user_id_1 = p.user_id AND f2.status = 'accepted')
        $whereClause
    ";
    
    // For count query, we need to add the friendship parameters
    $countParams = array_merge([$userId, $userId], $whereParams);
    
    // Main search query
    $searchQuery = "
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
            CASE WHEN user_likes.like_id IS NOT NULL THEN 1 ELSE 0 END as user_has_liked
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
        $whereClause
        $orderBy
        LIMIT ? OFFSET ?
    ";

    // For main query, add friendship and user like parameters
    $searchParams = array_merge([$userId, $userId, $userId], $whereParams, [$limit, $offset]);

    // Execute count query
    $totalResult = $Database->query($countQuery, $countParams);
    if ($totalResult === false) {
        throw new Exception('Database error in count query: ' . $Database->getLastError());
    }
    
    $total = $totalResult[0]['total'];
    $totalPages = ceil($total / $limit);

    // Execute search query
    $results = $Database->query($searchQuery, $searchParams);
    if ($results === false) {
        throw new Exception('Database error in search query: ' . $Database->getLastError());
    }

    // Return search results
    echo json_encode([
        'success' => true,
        'message' => 'Post search completed successfully',
        'search_params' => [
            'query' => $query,
            'location' => $location,
            'author' => $author,
            'post_type' => $postType ?: null,
            'privacy' => $privacy ?: null,
            'date_from' => $dateFrom ?: null,
            'date_to' => $dateTo ?: null,
            'sort_by' => $sortBy
        ],
        'posts' => $results ?: [],
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
        'message' => 'Post search failed: ' . $e->getMessage()
    ]);
}
