<?php

require_once 'api-handler.php';

class UsersAPI extends APIHandler {
    
    public function __construct() {
        // Initialize with users-specific base URL
        parent::__construct('/webdev/backend/src/api/users');
    }

    /**
     * Get current authenticated user's profile
     * 
     * @return array User profile data
     * @throws Exception If request fails
     */
    public function getMyProfile() {
        return $this->authenticatedRequest('/me', ['method' => 'GET']);
    }

    /**
     * Update current user's profile information
     * 
     * @param array $profileData Profile data to update (first_name, last_name, bio)
     * @return array Response data
     * @throws Exception If update fails
     */
    public function updateProfile($profileData) {
        return $this->authenticatedRequest('/update_profile', [
            'method' => 'PUT',
            'body' => json_encode($profileData)
        ]);
    }

    /**
     * Update profile with file upload (for profile picture)
     * 
     * @param array $formData Form data including files
     * @return array Response data
     * @throws Exception If update fails
     */
    public function updateProfileWithFile($formData) {
        $token = $this->getAuthToken();
        if (!$token) {
            throw new Exception('No authentication token found');
        }

        $headers = [
            'Authorization' => "Bearer $token"
            // Don't set Content-Type for multipart data
        ];

        return $this->request('/update_profile', [
            'method' => 'PUT',
            'headers' => $headers,
            'body' => $formData
        ]);
    }

    /**
     * Update current user's password
     * 
     * @param string $currentPassword Current password for verification
     * @param string $newPassword New password (minimum 8 characters)
     * @return array Response data
     * @throws Exception If password update fails
     */
    public function updatePassword($currentPassword, $newPassword) {
        $data = [
            'current_password' => $currentPassword,
            'new_password' => $newPassword
        ];

        return $this->authenticatedRequest('/update_password', [
            'method' => 'PUT',
            'body' => json_encode($data)
        ]);
    }

    /**
     * Get a specific user's profile information
     * 
     * @param int $userId Target user's ID
     * @return array User profile data
     * @throws Exception If request fails
     */
    public function getUser($userId) {
        if (!is_numeric($userId)) {
            throw new Exception('Invalid user ID format');
        }

        return $this->authenticatedRequest('/get_user', [
            'method' => 'GET',
            'body' => json_encode(['userId' => (int)$userId])
        ]);
    }

    /**
     * Search for users by name or username
     * 
     * @param string $query Search term (name or username)
     * @param int $page Page number (default: 1)
     * @param int $limit Results per page (1-50, default: 10)
     * @return array Search results
     * @throws Exception If search fails
     */
    public function searchUsers($query, $page = 1, $limit = 10) {
        if (empty($query)) {
            throw new Exception('Search query is required');
        }

        $params = [
            'query' => $query,
            'page' => max(1, (int)$page),
            'limit' => max(1, min(50, (int)$limit))
        ];

        return $this->authenticatedRequest('/search_users?' . http_build_query($params), [
            'method' => 'GET'
        ]);
    }

    /**
     * Get suggested users for friendship based on mutual connections
     * 
     * @param int $limit Number of suggestions (1-50, default: 10)
     * @return array User suggestions
     * @throws Exception If request fails
     */
    public function getUserSuggestions($limit = 10) {
        $params = [
            'limit' => max(1, min(50, (int)$limit))
        ];

        return $this->authenticatedRequest('/get_suggestions?' . http_build_query($params), [
            'method' => 'GET'
        ]);
    }

    /**
     * Get posts from a specific user with privacy filtering
     * 
     * @param int $userId Target user's ID
     * @param int $page Page number (default: 1)
     * @param int $limit Posts per page (1-50, default: 10)
     * @return array User posts
     * @throws Exception If request fails
     */
    public function getUserPosts($userId, $page = 1, $limit = 10) {
        if (!is_numeric($userId)) {
            throw new Exception('Invalid user ID format');
        }

        $params = [
            'userId' => (int)$userId,
            'page' => max(1, (int)$page),
            'limit' => max(1, min(50, (int)$limit))
        ];

        return $this->authenticatedRequest('/get_user_posts?' . http_build_query($params), [
            'method' => 'GET'
        ]);
    }

    /**
     * Block a specific user
     * 
     * @param int $userId User ID to block
     * @return array Response data
     * @throws Exception If blocking fails
     */
    public function blockUser($userId) {
        if (!is_numeric($userId)) {
            throw new Exception('Invalid user ID format');
        }

        $data = ['userId' => (int)$userId];

        return $this->authenticatedRequest('/block_user', [
            'method' => 'POST',
            'body' => json_encode($data)
        ]);
    }

    /**
     * Unblock a previously blocked user
     * 
     * @param int $userId User ID to unblock
     * @return array Response data
     * @throws Exception If unblocking fails
     */
    public function unblockUser($userId) {
        if (!is_numeric($userId)) {
            throw new Exception('Invalid user ID format');
        }

        $data = ['userId' => (int)$userId];

        return $this->authenticatedRequest('/unblock_user', [
            'method' => 'DELETE',
            'body' => json_encode($data)
        ]);
    }

    /**
     * Get the current user's list of blocked users
     * 
     * @param int $page Page number (default: 1)
     * @param int $limit Results per page (1-50, default: 20)
     * @return array Blocked users list
     * @throws Exception If request fails
     */
    public function getBlockedUsers($page = 1, $limit = 20) {
        $params = [
            'page' => max(1, (int)$page),
            'limit' => max(1, min(50, (int)$limit))
        ];

        return $this->authenticatedRequest('/get_blocked?' . http_build_query($params), [
            'method' => 'GET'
        ]);
    }

    /**
     * Soft delete a user account (current user or admin deleting another user)
     * 
     * @param int|null $userId User ID to delete (admin only, defaults to current user)
     * @return array Response data
     * @throws Exception If deletion fails
     */
    public function deleteUser($userId = null) {
        $data = [];
        if ($userId !== null) {
            if (!is_numeric($userId)) {
                throw new Exception('Invalid user ID format');
            }
            $data['userId'] = (int)$userId;
        }

        return $this->authenticatedRequest('/delete_user', [
            'method' => 'DELETE',
            'body' => json_encode($data)
        ]);
    }

    /**
     * Get reports filed by a specific user (admin only)
     * 
     * @param int $userId ID of user whose reports to retrieve
     * @param int $page Page number (default: 1)
     * @param int $limit Results per page (1-50, default: 20)
     * @param array $filters Optional filters (content_type, reported_user_id, reason, status)
     * @return array User reports
     * @throws Exception If request fails or insufficient permissions
     */
    public function getUserReports($userId, $page = 1, $limit = 20, $filters = []) {
        if (!is_numeric($userId)) {
            throw new Exception('Invalid user ID format');
        }

        $params = [
            'userId' => (int)$userId,
            'page' => max(1, (int)$page),
            'limit' => max(1, min(50, (int)$limit))
        ];

        // Add optional filters
        $validFilters = ['content_type', 'reported_user_id', 'reason', 'status'];
        foreach ($validFilters as $filter) {
            if (isset($filters[$filter]) && !empty($filters[$filter])) {
                $params[$filter] = $filters[$filter];
            }
        }

        return $this->authenticatedRequest('/get_user_reports?' . http_build_query($params), [
            'method' => 'GET'
        ]);
    }

    /**
     * Convenience method to check if current user can access admin features
     * 
     * @return bool True if user has admin access
     */
    public function isAdmin() {
        try {
            $profile = $this->getMyProfile();
            return isset($profile['user']['role']) && $profile['user']['role'] === 'admin';
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Validate user ID parameter
     * 
     * @param mixed $userId User ID to validate
     * @return int Valid user ID
     * @throws Exception If user ID is invalid
     */
    private function validateUserId($userId) {
        if (!is_numeric($userId) || $userId <= 0) {
            throw new Exception('Invalid user ID format');
        }
        return (int)$userId;
    }

    /**
     * Validate pagination parameters
     * 
     * @param int $page Page number
     * @param int $limit Items per page
     * @param int $maxLimit Maximum allowed limit
     * @return array Validated [page, limit]
     */
    private function validatePagination($page = 1, $limit = 10, $maxLimit = 50) {
        return [
            'page' => max(1, (int)$page),
            'limit' => max(1, min($maxLimit, (int)$limit))
        ];
    }

    /**
     * Helper method for GET requests with pagination
     * 
     * @param string $endpoint API endpoint
     * @param array $params Query parameters
     * @return array Response data
     */
    private function getPaginated($endpoint, $params = []) {
        $queryString = http_build_query($params);
        $fullEndpoint = $queryString ? "$endpoint?$queryString" : $endpoint;
        
        return $this->authenticatedRequest($fullEndpoint, ['method' => 'GET']);
    }
}
