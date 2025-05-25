<?php
/**
 * Admin Get Reports API Endpoint
 * 
 * Retrieves paginated list of all reports for admin review
 * Endpoint: GET /api/admin/reports
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

// Include authentication middleware (this will exit if not authorized)
require_once dirname(dirname(__FILE__)) . '/auth/auth_middleware.php';

// Check if user is admin
if ($authUser['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Admin access required']);
    exit();
}

// Get query parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$contentType = isset($_GET['content_type']) ? trim($_GET['content_type']) : '';

// Validate pagination parameters
if ($page < 1) {
    $page = 1;
}
if ($limit < 1 || $limit > 100) {
    $limit = 20;
}

// Calculate offset
$offset = ($page - 1) * $limit;

// Build WHERE clause
$whereConditions = ["1=1"];
$params = [];

// Add status filter if provided
if (!empty($status) && in_array($status, ['pending', 'reviewed', 'action_taken', 'dismissed'])) {
    $whereConditions[] = "r.status = ?";
    $params[] = $status;
}

// Add content type filter if provided
if (!empty($contentType) && in_array($contentType, ['user', 'post', 'comment'])) {
    $whereConditions[] = "r.content_type = ?";
    $params[] = $contentType;
}

$whereClause = "WHERE " . implode(" AND ", $whereConditions);

try {
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM reports r $whereClause";
    $totalResult = $Database->query($countSql, $params);
    
    if ($totalResult === false) {
        throw new Exception('Database error: ' . $Database->getLastError());
    }
    
    $totalReports = $totalResult[0]['total'];
    $totalPages = ceil($totalReports / $limit);

    // Get reports with detailed information
    $reportsSql = "
        SELECT 
            r.report_id,
            r.reporter_id,
            r.reported_id,
            r.content_type,
            r.content_id,
            r.reason,
            r.description,
            r.created_at,
            r.status,
            r.admin_notes,
            r.reviewed_by,
            r.reviewed_at,
            reporter.username as reporter_username,
            reporter.first_name as reporter_first_name,
            reporter.last_name as reporter_last_name,
            reported.username as reported_username,
            reported.first_name as reported_first_name,
            reported.last_name as reported_last_name,
            reviewer.username as reviewer_username,
            reviewer.first_name as reviewer_first_name,
            reviewer.last_name as reviewer_last_name
        FROM reports r
        INNER JOIN users reporter ON r.reporter_id = reporter.user_id
        INNER JOIN users reported ON r.reported_id = reported.user_id
        LEFT JOIN users reviewer ON r.reviewed_by = reviewer.user_id
        $whereClause
        ORDER BY 
            CASE r.status 
                WHEN 'pending' THEN 1 
                WHEN 'reviewed' THEN 2 
                WHEN 'action_taken' THEN 3 
                WHEN 'dismissed' THEN 4 
            END,
            r.created_at DESC
        LIMIT ? OFFSET ?
    ";

    $reportsParams = array_merge($params, [$limit, $offset]);
    $reports = $Database->query($reportsSql, $reportsParams);
    
    if ($reports === false) {
        throw new Exception('Database error: ' . $Database->getLastError());
    }

    // For each report, get additional context based on content type
    foreach ($reports as &$report) {
        $contentDetails = null;
        
        switch ($report['content_type']) {
            case 'post':
                $postDetails = $Database->query(
                    "SELECT post_id, caption, post_type, created_at FROM posts WHERE post_id = ?",
                    [$report['content_id']]
                );
                if ($postDetails && !empty($postDetails)) {
                    $contentDetails = [
                        'id' => $postDetails[0]['post_id'],
                        'caption' => $postDetails[0]['caption'],
                        'type' => $postDetails[0]['post_type'],
                        'created_at' => $postDetails[0]['created_at']
                    ];
                }
                break;
                
            case 'comment':
                $commentDetails = $Database->query(
                    "SELECT comment_id, content, created_at, post_id FROM comments WHERE comment_id = ?",
                    [$report['content_id']]
                );
                if ($commentDetails && !empty($commentDetails)) {
                    $contentDetails = [
                        'id' => $commentDetails[0]['comment_id'],
                        'content' => $commentDetails[0]['content'],
                        'post_id' => $commentDetails[0]['post_id'],
                        'created_at' => $commentDetails[0]['created_at']
                    ];
                }
                break;
                
            case 'user':
                // For user reports, the content_id should match reported_id
                $contentDetails = [
                    'id' => $report['reported_id'],
                    'username' => $report['reported_username'],
                    'name' => trim($report['reported_first_name'] . ' ' . $report['reported_last_name'])
                ];
                break;
        }
        
        $report['content_details'] = $contentDetails;
    }

    // Return reports data
    echo json_encode([
        'success' => true,
        'message' => 'Reports retrieved successfully',
        'reports' => $reports,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_reports' => (int)$totalReports,
            'reports_per_page' => $limit
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error: ' . $e->getMessage()
    ]);
}
