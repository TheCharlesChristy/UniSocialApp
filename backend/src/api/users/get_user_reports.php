<?php
/**
 * Get User Reports API Endpoint
 * 
 * Gets reports filed by current user
 * Endpoint: GET /api/users/:userId/reports
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

// Get the user ID from URL or query parameters
$userId = null;

// Method 1: Check URL path (when nice URLs are working with .htaccess)
$requestUri = $_SERVER['REQUEST_URI'];
$pattern = '/\/api\/users\/(\d+)\/reports/';
if (preg_match($pattern, $requestUri, $matches)) {
    $userId = $matches[1];
}

// Method 2: Check if user ID is passed as a GET parameter (alternative method)
if (!$userId && isset($_GET['userId'])) {
    $userId = $_GET['userId'];
}

// Method 3: Extract from URL path segments
if (!$userId) {
    $pathSegments = explode('/', trim($requestUri, '/'));
    $userIdIndex = array_search('users', $pathSegments);
    if ($userIdIndex !== false && isset($pathSegments[$userIdIndex + 1]) && is_numeric($pathSegments[$userIdIndex + 1])) {
        $userId = $pathSegments[$userIdIndex + 1];
    }
}

// If we still don't have a user ID, return an error
if (!$userId) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'User ID is required'
    ]);
    exit;
}

$userId = (int)$userId;

// Check if the authenticated user can access reports for this user ID
// Users can only view their own reports
if ($authUser['user_id'] != $userId) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'You can only view your own reports'
    ]);
    exit;
}

// Get pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;

// Get filter parameters
$contentType = isset($_GET['content_type']) ? trim($_GET['content_type']) : null;
$reportedUserId = isset($_GET['reported_user_id']) ? (int)$_GET['reported_user_id'] : null;
$reporterIdFilter = isset($_GET['reporter_id']) ? (int)$_GET['reporter_id'] : null;
$reason = isset($_GET['reason']) ? trim($_GET['reason']) : null;
$status = isset($_GET['status']) ? trim($_GET['status']) : null;

// Validate pagination parameters
if ($page < 1) {
    $page = 1;
}
if ($limit < 1 || $limit > 50) {
    $limit = 20;
}

// Validate filter parameters
$validContentTypes = ['user', 'post', 'comment'];
$validReasons = ['spam', 'harassment', 'inappropriate', 'violence', 'other'];
$validStatuses = ['pending', 'reviewed', 'action_taken', 'dismissed'];

if ($contentType && !in_array($contentType, $validContentTypes)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid content type. Must be one of: ' . implode(', ', $validContentTypes)
    ]);
    exit;
}

if ($reason && !in_array($reason, $validReasons)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid reason. Must be one of: ' . implode(', ', $validReasons)
    ]);
    exit;
}

if ($status && !in_array($status, $validStatuses)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid status. Must be one of: ' . implode(', ', $validStatuses)
    ]);
    exit;
}

// Build WHERE conditions for filtering
$whereConditions = ['r.reporter_id = ?'];
$queryParams = [$userId];

if ($contentType) {
    $whereConditions[] = 'r.content_type = ?';
    $queryParams[] = $contentType;
}

if ($reportedUserId) {
    $whereConditions[] = 'r.reported_id = ?';
    $queryParams[] = $reportedUserId;
}

if ($reporterIdFilter && $reporterIdFilter === $userId) {
    // This filter is redundant since we already filter by reporter_id = $userId
    // But we keep it for API consistency and future admin features
}

if ($reason) {
    $whereConditions[] = 'r.reason = ?';
    $queryParams[] = $reason;
}

if ($status) {
    $whereConditions[] = 'r.status = ?';
    $queryParams[] = $status;
}

$whereClause = 'WHERE ' . implode(' AND ', $whereConditions);

// Calculate offset
$offset = ($page - 1) * $limit;

try {
    // Get total count of reports by this user with filters
    $countQuery = "SELECT COUNT(*) as total FROM reports r " . $whereClause;
    $countResult = $Database->query($countQuery, $queryParams);
    
    if ($countResult === false) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error while counting reports']);
        exit();
    }
    
    $totalReports = $countResult[0]['total'];
    $totalPages = ceil($totalReports / $limit);
    
    // Get reports with related information and filters
    $reportsQuery = "SELECT 
            r.report_id,
            r.reported_id,
            r.content_type,
            r.content_id,
            r.reason,
            r.description,
            r.created_at,
            r.status,
            r.admin_notes,
            r.reviewed_at,
            u_reported.username as reported_username,
            u_reported.first_name as reported_first_name,
            u_reported.last_name as reported_last_name,
            u_reported.profile_picture as reported_profile_picture,
            CASE 
                WHEN r.content_type = 'post' THEN p.caption
                WHEN r.content_type = 'comment' THEN c.content
                ELSE NULL
            END as content_preview
        FROM reports r
        INNER JOIN users u_reported ON r.reported_id = u_reported.user_id
        LEFT JOIN posts p ON (r.content_type = 'post' AND r.content_id = p.post_id)
        LEFT JOIN comments c ON (r.content_type = 'comment' AND r.content_id = c.comment_id)
        " . $whereClause . "
        ORDER BY r.created_at DESC
        LIMIT ? OFFSET ?";
    
    // Add limit and offset to query parameters
    $reportsQueryParams = array_merge($queryParams, [$limit, $offset]);
    
    $reports = $Database->query($reportsQuery, $reportsQueryParams);
    
    if ($reports === false) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error while fetching reports']);
        exit();
    }
    
    // Format the reports data
    $formattedReports = [];
    
    if (!empty($reports)) {
        foreach ($reports as $report) {
            $formattedReport = [
                'report_id' => (int)$report['report_id'],
                'reported_user' => [
                    'user_id' => (int)$report['reported_id'],
                    'username' => $report['reported_username'],
                    'first_name' => $report['reported_first_name'],
                    'last_name' => $report['reported_last_name'],
                    'profile_picture' => $report['reported_profile_picture']
                ],
                'content_type' => $report['content_type'],
                'content_id' => (int)$report['content_id'],
                'reason' => $report['reason'],
                'description' => $report['description'],
                'status' => $report['status'],
                'created_at' => $report['created_at'],
                'reviewed_at' => $report['reviewed_at'],
                'admin_notes' => $report['admin_notes']
            ];
            
            // Add content preview if available
            if ($report['content_preview']) {
                $formattedReport['content_preview'] = substr($report['content_preview'], 0, 100) . (strlen($report['content_preview']) > 100 ? '...' : '');
            }
            
            $formattedReports[] = $formattedReport;
        }
    }
      // Return the reports data
    echo json_encode([
        'success' => true,
        'reports' => $formattedReports,
        'total_reports' => (int)$totalReports,
        'current_page' => (int)$page,
        'total_pages' => (int)$totalPages,
        'filters_applied' => [
            'content_type' => $contentType,
            'reported_user_id' => $reportedUserId,
            'reporter_id' => $reporterIdFilter,
            'reason' => $reason,
            'status' => $status
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred while fetching reports']);
}
