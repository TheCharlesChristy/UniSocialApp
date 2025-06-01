<?php

require_once 'api-handler.php';

/**
 * Reports API Interface Class
 * 
 * Provides a comprehensive interface for all reports-related API endpoints
 * including user reports, admin management, and real-time updates.
 * 
 * Available Endpoints:
 * - POST /api/reports - Create new report
 * - GET /api/reports/{reportId} - Get specific report (admin only)
 * - GET /api/admin/reports - Get all reports with filtering (admin only)
 * - PUT /api/admin/update_report - Update report status (admin only)
 * - GET /api/admin/live_reports - Real-time report updates (admin only)
 * - GET /api/users/{userId}/reports - Get user's reports (admin only)
 */
class ReportsAPI extends APIHandler {
    
    /**
     * Create a new report for inappropriate content
     * 
     * @param int $reportedId ID of the user being reported
     * @param string $contentType Type of content: 'user', 'post', or 'comment'
     * @param int $contentId ID of the specific content being reported
     * @param string $reason Reason for the report (max 100 characters)
     * @param string|null $description Optional additional details
     * @return array API response
     * @throws Exception if request fails
     */
    public function createReport($reportedId, $contentType, $contentId, $reason, $description = null) {
        // Validate required parameters
        if (!$reportedId || !is_numeric($reportedId)) {
            throw new Exception('Valid reported_id is required');
        }
        
        if (!in_array($contentType, ['user', 'post', 'comment'])) {
            throw new Exception('content_type must be one of: user, post, comment');
        }
        
        if (!$contentId || !is_numeric($contentId)) {
            throw new Exception('Valid content_id is required');
        }
        
        if (empty(trim($reason))) {
            throw new Exception('Reason is required');
        }
        
        if (strlen($reason) > 100) {
            throw new Exception('Reason must be 100 characters or less');
        }
        
        $data = [
            'reported_id' => (int)$reportedId,
            'content_type' => $contentType,
            'content_id' => (int)$contentId,
            'reason' => $reason
        ];
        
        if ($description !== null) {
            $data['description'] = $description;
        }
        
        return $this->authenticatedRequest('/reports/create_report', [
            'method' => 'POST',
            'body' => json_encode($data)
        ]);
    }
    
    /**
     * Get detailed information about a specific report (Admin only)
     * 
     * @param int $reportId ID of the report to retrieve
     * @return array API response with detailed report information
     * @throws Exception if request fails or user lacks admin permissions
     */
    public function getReport($reportId) {
        if (!$reportId || !is_numeric($reportId)) {
            throw new Exception('Valid report ID is required');
        }
        
        return $this->authenticatedRequest("/reports/get_report?id={$reportId}", [
            'method' => 'GET'
        ]);
    }
    
    /**
     * Get all reports with filtering and pagination (Admin only)
     * 
     * @param array $options Filtering and pagination options
     *   - page: Page number (default: 1)
     *   - limit: Reports per page (1-100, default: 20)
     *   - status: Filter by status ('pending', 'reviewed', 'action_taken', 'dismissed')
     *   - content_type: Filter by content type ('user', 'post', 'comment')
     * @return array API response with paginated reports list
     * @throws Exception if request fails or user lacks admin permissions
     */
    public function getAllReports($options = []) {
        // Set default options
        $params = array_merge([
            'page' => 1,
            'limit' => 20
        ], $options);
        
        // Validate pagination parameters
        if ($params['page'] < 1) {
            $params['page'] = 1;
        }
        
        if ($params['limit'] < 1 || $params['limit'] > 100) {
            $params['limit'] = 20;
        }
        
        // Validate status filter
        if (isset($params['status']) && !in_array($params['status'], ['pending', 'reviewed', 'action_taken', 'dismissed'])) {
            throw new Exception('Invalid status filter. Must be one of: pending, reviewed, action_taken, dismissed');
        }
        
        // Validate content_type filter
        if (isset($params['content_type']) && !in_array($params['content_type'], ['user', 'post', 'comment'])) {
            throw new Exception('Invalid content_type filter. Must be one of: user, post, comment');
        }
        
        // Remove empty parameters
        $params = array_filter($params, function($value) {
            return $value !== null && $value !== '';
        });
        
        $queryString = http_build_query($params);
        return $this->authenticatedRequest('/admin/reports?' . $queryString, [
            'method' => 'GET'
        ]);
    }
    
    /**
     * Update report status and add admin notes (Admin only)
     * 
     * @param int $reportId ID of the report to update
     * @param string|null $status New status ('pending', 'reviewed', 'action_taken', 'dismissed')
     * @param string|null $adminNotes Optional admin notes
     * @return array API response with updated report
     * @throws Exception if request fails or user lacks admin permissions
     */
    public function updateReport($reportId, $status = null, $adminNotes = null) {
        if (!$reportId || !is_numeric($reportId)) {
            throw new Exception('Valid report ID is required');
        }
        
        if ($status !== null && !in_array($status, ['pending', 'reviewed', 'action_taken', 'dismissed'])) {
            throw new Exception('Invalid status. Must be one of: pending, reviewed, action_taken, dismissed');
        }
        
        $data = ['report_id' => (int)$reportId];
        
        if ($status !== null) {
            $data['status'] = $status;
        }
        
        if ($adminNotes !== null) {
            $data['admin_notes'] = $adminNotes;
        }
        
        if (count($data) === 1) {
            throw new Exception('At least one field (status or admin_notes) must be provided for update');
        }
        
        return $this->authenticatedRequest('/admin/update_report', [
            'method' => 'PUT',
            'body' => json_encode($data)
        ]);
    }
    
    /**
     * Get reports filed by a specific user (Admin only)
     * 
     * @param int $userId ID of the user whose reports to retrieve
     * @param array $options Filtering and pagination options
     *   - page: Page number (default: 1)
     *   - limit: Reports per page (1-50, default: 20)
     *   - content_type: Filter by content type ('user', 'post', 'comment')
     *   - reported_user_id: Filter by reported user ID
     *   - reporter_id: Filter by reporter ID
     *   - reason: Filter by reason
     *   - status: Filter by status ('pending', 'reviewed', 'action_taken', 'dismissed')
     * @return array API response with user's reports
     * @throws Exception if request fails or user lacks admin permissions
     */
    public function getUserReports($userId, $options = []) {
        if (!$userId || !is_numeric($userId)) {
            throw new Exception('Valid user ID is required');
        }
        
        // Set default options
        $params = array_merge([
            'page' => 1,
            'limit' => 20
        ], $options);
        
        // Validate pagination parameters
        if ($params['page'] < 1) {
            $params['page'] = 1;
        }
        
        if ($params['limit'] < 1 || $params['limit'] > 50) {
            $params['limit'] = 20;
        }
        
        // Validate filters
        if (isset($params['content_type']) && !in_array($params['content_type'], ['user', 'post', 'comment'])) {
            throw new Exception('Invalid content_type filter. Must be one of: user, post, comment');
        }
        
        if (isset($params['status']) && !in_array($params['status'], ['pending', 'reviewed', 'action_taken', 'dismissed'])) {
            throw new Exception('Invalid status filter. Must be one of: pending, reviewed, action_taken, dismissed');
        }
        
        // Remove empty parameters
        $params = array_filter($params, function($value) {
            return $value !== null && $value !== '';
        });
        
        return $this->authenticatedRequest("/users/get_user_reports?userId={$userId}&" . http_build_query($params), [
            'method' => 'GET'
        ]);
    }
    
    /**
     * Get real-time report updates via Server-Sent Events (Admin only)
     * Note: This returns the URL for SSE connection, not the actual data
     * 
     * @return string SSE endpoint URL for real-time report updates
     */
    public function getLiveReportsURL() {
        $token = $this->getAuthToken();
        if (!$token) {
            throw new Exception('Authentication required for live reports');
        }
        
        return $this->baseURL . '/admin/live_reports.php?token=' . urlencode($token);
    }
    
    /**
     * Helper method to create a report for a post
     * 
     * @param int $reportedUserId ID of the user who created the post
     * @param int $postId ID of the post being reported
     * @param string $reason Reason for the report
     * @param string|null $description Optional additional details
     * @return array API response
     */
    public function reportPost($reportedUserId, $postId, $reason, $description = null) {
        return $this->createReport($reportedUserId, 'post', $postId, $reason, $description);
    }
    
    /**
     * Helper method to create a report for a comment
     * 
     * @param int $reportedUserId ID of the user who created the comment
     * @param int $commentId ID of the comment being reported
     * @param string $reason Reason for the report
     * @param string|null $description Optional additional details
     * @return array API response
     */
    public function reportComment($reportedUserId, $commentId, $reason, $description = null) {
        return $this->createReport($reportedUserId, 'comment', $commentId, $reason, $description);
    }
    
    /**
     * Helper method to create a report for a user
     * 
     * @param int $reportedUserId ID of the user being reported
     * @param string $reason Reason for the report
     * @param string|null $description Optional additional details
     * @return array API response
     */
    public function reportUser($reportedUserId, $reason, $description = null) {
        return $this->createReport($reportedUserId, 'user', $reportedUserId, $reason, $description);
    }
    
    /**
     * Helper method to get pending reports (Admin only)
     * 
     * @param int $page Page number (default: 1)
     * @param int $limit Reports per page (default: 20)
     * @return array API response with pending reports
     */
    public function getPendingReports($page = 1, $limit = 20) {
        return $this->getAllReports([
            'status' => 'pending',
            'page' => $page,
            'limit' => $limit
        ]);
    }
    
    /**
     * Helper method to get reports by content type (Admin only)
     * 
     * @param string $contentType Content type ('user', 'post', 'comment')
     * @param int $page Page number (default: 1)
     * @param int $limit Reports per page (default: 20)
     * @return array API response with filtered reports
     */
    public function getReportsByContentType($contentType, $page = 1, $limit = 20) {
        return $this->getAllReports([
            'content_type' => $contentType,
            'page' => $page,
            'limit' => $limit
        ]);
    }
    
    /**
     * Helper method to mark a report as reviewed (Admin only)
     * 
     * @param int $reportId ID of the report
     * @param string|null $adminNotes Optional admin notes
     * @return array API response
     */
    public function markReportAsReviewed($reportId, $adminNotes = null) {
        return $this->updateReport($reportId, 'reviewed', $adminNotes);
    }
    
    /**
     * Helper method to mark a report as action taken (Admin only)
     * 
     * @param int $reportId ID of the report
     * @param string|null $adminNotes Optional admin notes describing the action taken
     * @return array API response
     */
    public function markReportAsActionTaken($reportId, $adminNotes = null) {
        return $this->updateReport($reportId, 'action_taken', $adminNotes);
    }
    
    /**
     * Helper method to dismiss a report (Admin only)
     * 
     * @param int $reportId ID of the report
     * @param string|null $adminNotes Optional admin notes explaining dismissal
     * @return array API response
     */
    public function dismissReport($reportId, $adminNotes = null) {
        return $this->updateReport($reportId, 'dismissed', $adminNotes);
    }
    
    /**
     * Get report statistics for dashboard (Admin only)
     * Note: This uses the admin dashboard endpoint to get report statistics
     * 
     * @return array Dashboard data including report statistics
     */
    public function getReportStatistics() {
        return $this->authenticatedRequest('/admin/dashboard', [
            'method' => 'GET'
        ]);
    }
}
