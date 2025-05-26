<?php
/**
 * Get Report API Endpoint
 * 
 * Retrieves a specific report by ID (admin only)
 * Endpoint: GET /api/reports/:reportId
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

// Get report ID from URL path
$reportId = null;

// Parse the report ID from URL path
$requestUri = $_SERVER['REQUEST_URI'];
$pattern = '/\/api\/reports\/(\d+)/';

if (preg_match($pattern, $requestUri, $matches)) {
    $reportId = (int)$matches[1];
} else {
    // Check if it's passed as a query parameter (fallback)
    if (isset($_GET['id'])) {
        $reportId = (int)$_GET['id'];
    }
}

// Validate report ID
if (!$reportId || $reportId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Valid report ID is required']);
    exit();
}

try {    // Get report with detailed information    
    $reportSql = "
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
            reporter.email as reporter_email,
            reporter.profile_picture as reporter_profile_picture_url,
            reported.username as reported_username,
            reported.first_name as reported_first_name,
            reported.last_name as reported_last_name,
            reported.email as reported_email,
            reported.profile_picture as reported_profile_picture_url,
            reviewer.username as reviewer_username,
            reviewer.first_name as reviewer_first_name,
            reviewer.last_name as reviewer_last_name,
            reviewer.profile_picture as reviewer_profile_picture_url
        FROM reports r
        INNER JOIN users reporter ON r.reporter_id = reporter.user_id
        INNER JOIN users reported ON r.reported_id = reported.user_id
        LEFT JOIN users reviewer ON r.reviewed_by = reviewer.user_id
        WHERE r.report_id = ?
    ";

    $report = $Database->query($reportSql, [$reportId]);
    
    if ($report === false) {
        throw new Exception('Database error: ' . $Database->getLastError());
    }

    // Check if report exists
    if (empty($report)) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Report not found']);
        exit();
    }

    $report = $report[0];

    // Get additional content details based on content type
    $contentDetails = null;
    
    switch ($report['content_type']) {
        case 'post':
            $postDetails = $Database->query(
                "SELECT post_id, caption, post_type, privacy_level, location_name, created_at FROM posts WHERE post_id = ?",
                [$report['content_id']]
            );            if ($postDetails && !empty($postDetails)) {
                $contentDetails = [
                    'post_id' => $postDetails[0]['post_id'],
                    'caption' => $postDetails[0]['caption'],
                    'post_type' => $postDetails[0]['post_type'],
                    'privacy_level' => $postDetails[0]['privacy_level'],
                    'location_name' => $postDetails[0]['location_name'],
                    'created_at' => $postDetails[0]['created_at']
                ];
            }
            break;
            
        case 'comment':
            $commentDetails = $Database->query(
                "SELECT comment_id, content, created_at, post_id FROM comments WHERE comment_id = ?",
                [$report['content_id']]
            );            if ($commentDetails && !empty($commentDetails)) {
                $contentDetails = [
                    'comment_id' => $commentDetails[0]['comment_id'],
                    'comment_text' => $commentDetails[0]['content'],
                    'post_id' => $commentDetails[0]['post_id'],
                    'created_at' => $commentDetails[0]['created_at']
                ];
            }
            break;
              case 'user':
            // For user reports, the content_id should match reported_id
            $contentDetails = [
                'user_id' => $report['reported_id'],
                'username' => $report['reported_username'],
                'name' => trim($report['reported_first_name'] . ' ' . $report['reported_last_name']),
                'email' => $report['reported_email']
            ];
            break;
    }    // Format the response
    $formattedReport = [
        'report_id' => (int)$report['report_id'],
        'reporter' => [
            'user_id' => (int)$report['reporter_id'],
            'username' => $report['reporter_username'],
            'first_name' => $report['reporter_first_name'],
            'last_name' => $report['reporter_last_name'],
            'email' => $report['reporter_email'],
            'profile_picture_url' => $report['reporter_profile_picture_url']
        ],
        'reported_user' => [
            'user_id' => (int)$report['reported_id'],
            'username' => $report['reported_username'],
            'first_name' => $report['reported_first_name'],
            'last_name' => $report['reported_last_name'],
            'email' => $report['reported_email'],
            'profile_picture_url' => $report['reported_profile_picture_url']
        ],
        'content_type' => $report['content_type'],
        'content_id' => (int)$report['content_id'],
        'content_details' => $contentDetails,
        'reason' => $report['reason'],
        'description' => $report['description'],
        'status' => $report['status'],
        'created_at' => $report['created_at'],
        'admin_notes' => $report['admin_notes'],
        'reviewed_by' => $report['reviewed_by'] ? [
            'user_id' => (int)$report['reviewed_by'],
            'username' => $report['reviewer_username'],
            'first_name' => $report['reviewer_first_name'],
            'last_name' => $report['reviewer_last_name'],
            'profile_picture_url' => $report['reviewer_profile_picture_url']
        ] : null,
        'reviewed_at' => $report['reviewed_at']
    ];

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Report retrieved successfully',
        'report' => $formattedReport
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error: ' . $e->getMessage()
    ]);
}
