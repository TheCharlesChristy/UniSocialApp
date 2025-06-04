<?php

require_once 'api-handler.php';
class AdminAPI extends APIHandler {
    
    public function __construct($baseURL = '/backend/src/api') {
        parent::__construct($baseURL);
    }

    // =============================================================================
    // DASHBOARD & ANALYTICS METHODS
    // =============================================================================

    /**
     * Get admin dashboard overview statistics
     * 
     * @return array Dashboard data with statistics and recent users
     * @throws Exception on authentication or permission errors
     */
    public function getDashboard() {
        return $this->authenticatedRequest('/admin/dashboard');
    }

    /**
     * Get detailed post analytics and statistics
     * 
     * @param string|null $startDate Start date (YYYY-MM-DD), defaults to 30 days ago
     * @param string|null $endDate End date (YYYY-MM-DD), defaults to today
     * @return array Post analytics data
     * @throws Exception on authentication, permission, or validation errors
     */
    public function getPostAnalytics($startDate = null, $endDate = null) {
        $params = [];
        if ($startDate) $params['start_date'] = $startDate;
        if ($endDate) $params['end_date'] = $endDate;

        return $this->get('/admin/analytics_posts', $params);
    }

    /**
     * Get detailed user analytics and statistics
     * 
     * @param string|null $startDate Start date (YYYY-MM-DD), defaults to 30 days ago
     * @param string|null $endDate End date (YYYY-MM-DD), defaults to today
     * @return array User analytics data
     * @throws Exception on authentication, permission, or validation errors
     */
    public function getUserAnalytics($startDate = null, $endDate = null) {
        $params = [];
        if ($startDate) $params['start_date'] = $startDate;
        if ($endDate) $params['end_date'] = $endDate;

        return $this->get('/admin/analytics_users', $params);
    }

    // =============================================================================
    // USER MANAGEMENT METHODS
    // =============================================================================

    /**
     * Get paginated list of all users with management options
     * 
     * @param int $page Page number (default: 1)
     * @param int $limit Users per page (1-100, default: 20)
     * @param string|null $status Filter by status: 'active', 'suspended'
     * @param string|null $role Filter by role: 'user', 'admin'
     * @param string|null $search Search username, email, or name
     * @return array Users data with pagination info
     * @throws Exception on authentication or permission errors
     */
    public function getUsers($page = 1, $limit = 20, $status = null, $role = null, $search = null) {
        $params = [
            'page' => max(1, (int)$page),
            'limit' => max(1, min(100, (int)$limit))
        ];

        if ($status) $params['status'] = $status;
        if ($role) $params['role'] = $role;
        if ($search) $params['search'] = $search;

        return $this->get('/admin/users', $params);
    }

    /**
     * Update user profile information
     * 
     * @param int $userId User ID to update
     * @param array $userData Array containing user data to update
     *                       Possible keys: first_name, last_name, email, username, bio, account_status, role
     * @return array Updated user data
     * @throws Exception on authentication, permission, validation, or not found errors
     */
    public function updateUser($userId, $userData) {
        $data = array_merge(['user_id' => (int)$userId], $userData);
        return $this->authenticatedRequest('/admin/update_user', [
            'method' => 'PUT',
            'body' => json_encode($data)
        ]);
    }

    /**
     * Suspend a user account
     * 
     * @param int $userId User ID to suspend
     * @param string $reason Reason for suspension
     * @return array Suspended user data
     * @throws Exception on authentication, permission, validation, or not found errors
     */
    public function suspendUser($userId, $reason) {
        $data = [
            'user_id' => (int)$userId,
            'reason' => $reason
        ];

        return $this->authenticatedRequest('/admin/suspend_user', [
            'method' => 'PUT',
            'body' => json_encode($data)
        ]);
    }

    /**
     * Activate a suspended user account
     * 
     * @param int $userId User ID to activate
     * @return array Activated user data
     * @throws Exception on authentication, permission, validation, or not found errors
     */
    public function activateUser($userId) {
        $data = ['user_id' => (int)$userId];

        return $this->authenticatedRequest('/admin/activate_user', [
            'method' => 'PUT',
            'body' => json_encode($data)
        ]);
    }

    /**
     * Soft delete a user account (sets status to 'deleted')
     * 
     * @param int $userId User ID to delete
     * @return array Deleted user data
     * @throws Exception on authentication, permission, or not found errors
     */
    public function deleteUser($userId) {
        $data = ['user_id' => (int)$userId];

        return $this->authenticatedRequest('/admin/delete_user', [
            'method' => 'DELETE',
            'body' => json_encode($data)
        ]);
    }

    // =============================================================================
    // REPORT MANAGEMENT METHODS
    // =============================================================================

    /**
     * Get paginated list of all reports for admin review
     * 
     * @param int $page Page number (default: 1)
     * @param int $limit Reports per page (1-100, default: 20)
     * @param string|null $status Filter by status: 'pending', 'reviewed', 'action_taken', 'dismissed'
     * @param string|null $contentType Filter by type: 'user', 'post', 'comment'
     * @return array Reports data with pagination info
     * @throws Exception on authentication or permission errors
     */
    public function getReports($page = 1, $limit = 20, $status = null, $contentType = null) {
        $params = [
            'page' => max(1, (int)$page),
            'limit' => max(1, min(100, (int)$limit))
        ];

        if ($status) $params['status'] = $status;
        if ($contentType) $params['content_type'] = $contentType;

        return $this->get('/admin/reports', $params);
    }

    /**
     * Update report status and admin notes
     * 
     * @param int $reportId Report ID to update
     * @param string|null $status New status: 'pending', 'reviewed', 'action_taken', 'dismissed'
     * @param string|null $adminNotes Admin notes for the report
     * @return array Updated report data
     * @throws Exception on authentication, permission, validation, or not found errors
     */
    public function updateReport($reportId, $status = null, $adminNotes = null) {
        $data = ['report_id' => (int)$reportId];
        
        if ($status) $data['status'] = $status;
        if ($adminNotes) $data['admin_notes'] = $adminNotes;

        return $this->authenticatedRequest('/admin/update_report', [
            'method' => 'PUT',
            'body' => json_encode($data)
        ]);
    }    // =============================================================================
    // REAL-TIME UPDATES METHODS
    // =============================================================================

    /**
     * Get Server-Sent Events stream for live report updates
     * Note: This method returns the SSE URL that should be used with EventSource in JavaScript
     * 
     * @param int|null $lastId Last report ID received to avoid duplicates
     * @return string SSE endpoint URL
     * @throws Exception if no authentication token is available
     */
    public function getLiveReportsURL($lastId = null) {
        $token = $this->getAuthToken();
        if (!$token) {
            throw new Exception('No authentication token found for SSE connection');
        }

        $params = ['token' => $token];
        if ($lastId) $params['last_id'] = (int)$lastId;

        $queryString = http_build_query($params);
        return '/backend/src/api/admin/live_reports.php?' . $queryString;
    }

    /**
     * Get Server-Sent Events stream for live user management updates
     * Note: This method returns the SSE URL that should be used with EventSource in JavaScript
     * 
     * @return string SSE endpoint URL
     * @throws Exception if no authentication token is available
     */
    public function getLiveUsersURL() {
        $token = $this->getAuthToken();
        if (!$token) {
            throw new Exception('No authentication token found for SSE connection');
        }

        $params = ['token' => $token];
        $queryString = http_build_query($params);
        return '/backend/src/api/admin/live_users.php?' . $queryString;
    }

    // =============================================================================
    // CONVENIENCE METHODS
    // =============================================================================

    /**
     * Get pending reports count
     * 
     * @return int Number of pending reports
     * @throws Exception on authentication or permission errors
     */
    public function getPendingReportsCount() {
        $response = $this->getReports(1, 1, 'pending');
        return $response['pagination']['total_reports'] ?? 0;
    }

    /**
     * Get active users count
     * 
     * @return int Number of active users
     * @throws Exception on authentication or permission errors
     */
    public function getActiveUsersCount() {
        $response = $this->getUsers(1, 1, 'active');
        return $response['pagination']['total_users'] ?? 0;
    }

    /**
     * Get suspended users count
     * 
     * @return int Number of suspended users
     * @throws Exception on authentication or permission errors
     */
    public function getSuspendedUsersCount() {
        $response = $this->getUsers(1, 1, 'suspended');
        return $response['pagination']['total_users'] ?? 0;
    }

    /**
     * Search users by username, email, or name
     * 
     * @param string $searchTerm Search term
     * @param int $page Page number (default: 1)
     * @param int $limit Results per page (default: 20)
     * @return array Users matching search criteria
     * @throws Exception on authentication or permission errors
     */
    public function searchUsers($searchTerm, $page = 1, $limit = 20) {
        return $this->getUsers($page, $limit, null, null, $searchTerm);
    }

    /**
     * Get reports by content type
     * 
     * @param string $contentType Content type: 'user', 'post', 'comment'
     * @param int $page Page number (default: 1)
     * @param int $limit Results per page (default: 20)
     * @return array Reports of specified content type
     * @throws Exception on authentication or permission errors
     */
    public function getReportsByContentType($contentType, $page = 1, $limit = 20) {
        return $this->getReports($page, $limit, null, $contentType);
    }

    /**
     * Get reports by status
     * 
     * @param string $status Status: 'pending', 'reviewed', 'action_taken', 'dismissed'
     * @param int $page Page number (default: 1)
     * @param int $limit Results per page (default: 20)
     * @return array Reports with specified status
     * @throws Exception on authentication or permission errors
     */
    public function getReportsByStatus($status, $page = 1, $limit = 20) {
        return $this->getReports($page, $limit, $status);
    }

    /**
     * Quick actions for common admin tasks
     */

    /**
     * Approve a report and mark as action taken
     * 
     * @param int $reportId Report ID
     * @param string $adminNotes Notes about the action taken
     * @return array Updated report data
     */
    public function approveReport($reportId, $adminNotes = 'Report approved and action taken') {
        return $this->updateReport($reportId, 'action_taken', $adminNotes);
    }

    /**
     * Dismiss a report
     * 
     * @param int $reportId Report ID
     * @param string $adminNotes Notes about why the report was dismissed
     * @return array Updated report data
     */
    public function dismissReport($reportId, $adminNotes = 'Report reviewed and dismissed') {
        return $this->updateReport($reportId, 'dismissed', $adminNotes);
    }

    /**
     * Mark report as reviewed but no action needed
     * 
     * @param int $reportId Report ID
     * @param string $adminNotes Notes about the review
     * @return array Updated report data
     */
    public function reviewReport($reportId, $adminNotes = 'Report reviewed - no action required') {
        return $this->updateReport($reportId, 'reviewed', $adminNotes);
    }

    // =============================================================================
    // BULK OPERATIONS
    // =============================================================================

    /**
     * Perform bulk operations on multiple users
     * Note: This performs individual API calls for each user
     * 
     * @param array $userIds Array of user IDs
     * @param string $action Action to perform: 'suspend', 'activate', 'delete'
     * @param array $params Additional parameters (e.g., reason for suspension)
     * @return array Results for each user operation
     */
    public function bulkUserAction($userIds, $action, $params = []) {
        $results = [];
        
        foreach ($userIds as $userId) {
            try {
                switch ($action) {
                    case 'suspend':
                        $reason = $params['reason'] ?? 'Bulk suspension';
                        $results[$userId] = $this->suspendUser($userId, $reason);
                        break;
                    case 'activate':
                        $results[$userId] = $this->activateUser($userId);
                        break;
                    case 'delete':
                        $results[$userId] = $this->deleteUser($userId);
                        break;
                    default:
                        $results[$userId] = ['success' => false, 'message' => 'Invalid action'];
                }
            } catch (Exception $e) {
                $results[$userId] = ['success' => false, 'message' => $e->getMessage()];
            }
        }

        return $results;
    }

    /**
     * Perform bulk operations on multiple reports
     * 
     * @param array $reportIds Array of report IDs
     * @param string $status New status for all reports
     * @param string $adminNotes Admin notes for all reports
     * @return array Results for each report operation
     */
    public function bulkReportAction($reportIds, $status, $adminNotes = '') {
        $results = [];
        
        foreach ($reportIds as $reportId) {
            try {
                $results[$reportId] = $this->updateReport($reportId, $status, $adminNotes);
            } catch (Exception $e) {
                $results[$reportId] = ['success' => false, 'message' => $e->getMessage()];
            }
        }

        return $results;
    }

    // =============================================================================
    // UTILITY METHODS
    // =============================================================================

    /**
     * Validate admin permissions before making requests
     * This is automatically called by authenticatedRequest, but can be used for early validation
     * 
     * @return bool True if user has admin permissions
     */
    public function hasAdminPermissions() {
        try {
            // Try to get dashboard data as a permission check
            $this->getDashboard();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get comprehensive admin statistics
     * 
     * @return array Complete statistics from dashboard and analytics
     */
    public function getAllStatistics() {
        try {
            $dashboard = $this->getDashboard();
            $userAnalytics = $this->getUserAnalytics();
            $postAnalytics = $this->getPostAnalytics();

            return [
                'dashboard' => $dashboard,
                'user_analytics' => $userAnalytics,
                'post_analytics' => $postAnalytics,
                'generated_at' => date('c')
            ];
        } catch (Exception $e) {
            throw new Exception('Failed to retrieve comprehensive statistics: ' . $e->getMessage());
        }
    }

    /* 
     * =============================================================================
     * USAGE EXAMPLES
     * =============================================================================
     * 
     * // Initialize the admin API handler
     * $adminAPI = new AdminAPIHandler();
     * 
     * // Set authentication token (usually from session or login)
     * $adminAPI->setAuthToken($authToken);
     * 
     * // Check if user has admin permissions
     * if (!$adminAPI->hasAdminPermissions()) {
     *     throw new Exception('Admin access required');
     * }
     * 
     * // DASHBOARD & ANALYTICS
     * $dashboard = $adminAPI->getDashboard();
     * $userAnalytics = $adminAPI->getUserAnalytics('2024-01-01', '2024-01-31');
     * $postAnalytics = $adminAPI->getPostAnalytics();
     * 
     * // USER MANAGEMENT
     * $users = $adminAPI->getUsers(1, 20, 'active'); // Get active users
     * $searchResults = $adminAPI->searchUsers('john'); // Search for users named 'john'
     * 
     * // Update user
     * $updatedUser = $adminAPI->updateUser(123, [
     *     'first_name' => 'John',
     *     'last_name' => 'Doe',
     *     'account_status' => 'active'
     * ]);
     * 
     * // Suspend/activate users
     * $suspendedUser = $adminAPI->suspendUser(123, 'Policy violation');
     * $activatedUser = $adminAPI->activateUser(123);
     * 
     * // REPORT MANAGEMENT
     * $reports = $adminAPI->getReports(1, 20, 'pending'); // Get pending reports
     * $postReports = $adminAPI->getReportsByContentType('post');
     * 
     * // Handle reports
     * $approvedReport = $adminAPI->approveReport(456, 'Content removed');
     * $dismissedReport = $adminAPI->dismissReport(789, 'False report');
     * 
     * // BULK OPERATIONS
     * $results = $adminAPI->bulkUserAction([123, 456, 789], 'suspend', ['reason' => 'Bulk suspension']);
     * $reportResults = $adminAPI->bulkReportAction([111, 222, 333], 'reviewed', 'Mass review completed');
     * 
     * // REAL-TIME UPDATES (for JavaScript EventSource)
     * $liveReportsURL = $adminAPI->getLiveReportsURL();
     * $liveUsersURL = $adminAPI->getLiveUsersURL();
     * 
     * // COMPREHENSIVE STATISTICS
     * $allStats = $adminAPI->getAllStatistics();
     * 
     * // COUNTS
     * $pendingCount = $adminAPI->getPendingReportsCount();
     * $activeUsersCount = $adminAPI->getActiveUsersCount();
     * $suspendedUsersCount = $adminAPI->getSuspendedUsersCount();
     */
}