<?php
/**
 * Admin Update Report API Endpoint
 * 
 * Updates report status and admin notes (admin only)
 * Endpoint: PUT /api/admin/reports/:reportId
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

// Only allow PUT and POST requests
if (!in_array($_SERVER['REQUEST_METHOD'], ['PUT', 'POST'])) {
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

// Get request body
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit();
}

// Get report ID from request body
$reportId = isset($input['report_id']) ? (int)$input['report_id'] : null;

if (!$reportId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Report ID is required']);
    exit();
}

try {
    // Check if report exists
    $existingReport = $Database->query(
        "SELECT report_id, status, reviewed_by FROM reports WHERE report_id = ?",
        [$reportId]
    );
    
    if ($existingReport === false) {
        throw new Exception('Database error: ' . $Database->getLastError());
    }
    
    if (empty($existingReport)) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Report not found']);
        exit();
    }
    
    $report = $existingReport[0];

    // Initialize update fields
    $updateFields = [];
    $updateParams = [];
    
    // Check and validate status
    if (isset($input['status'])) {
        $status = trim($input['status']);
        if (!in_array($status, ['pending', 'reviewed', 'action_taken', 'dismissed'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid status. Must be: pending, reviewed, action_taken, or dismissed']);
            exit();
        }
        
        $updateFields[] = "status = ?";
        $updateParams[] = $status;
        
        // If status is being changed from pending, set reviewer and review time
        if ($report['status'] === 'pending' && $status !== 'pending') {
            $updateFields[] = "reviewed_by = ?";
            $updateFields[] = "reviewed_at = NOW()";
            $updateParams[] = $authUser['user_id'];
        }
    }
    
    // Check admin notes
    if (isset($input['admin_notes'])) {
        $updateFields[] = "admin_notes = ?";
        $updateParams[] = trim($input['admin_notes']);
    }
    
    if (empty($updateFields)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No valid fields provided for update']);
        exit();
    }
    
    // Perform the update
    $updateSql = "UPDATE reports SET " . implode(", ", $updateFields) . " WHERE report_id = ?";
    $updateParams[] = $reportId;
    
    $result = $Database->execute($updateSql, $updateParams);
    
    if ($result === false) {
        throw new Exception('Database error: ' . $Database->getLastError());
    }
    
    // Get updated report data with related information
    $updatedReport = $Database->query(
        "SELECT 
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
            reported.username as reported_username,
            reviewer.username as reviewer_username
         FROM reports r
         INNER JOIN users reporter ON r.reporter_id = reporter.user_id
         INNER JOIN users reported ON r.reported_id = reported.user_id
         LEFT JOIN users reviewer ON r.reviewed_by = reviewer.user_id
         WHERE r.report_id = ?",
        [$reportId]
    );
    
    if ($updatedReport === false) {
        throw new Exception('Database error: ' . $Database->getLastError());
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Report updated successfully',
        'report' => $updatedReport[0]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error: ' . $e->getMessage()
    ]);
}
