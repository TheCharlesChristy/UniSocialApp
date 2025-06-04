<?php

require_once 'api-handler.php';

class NotificationsAPI extends APIHandler {
    private $notificationsEndpoint = '/notifications';

    public function __construct($baseURL = '/backend/src/api') {
        parent::__construct($baseURL);
    }

    /**
     * Create a new notification for a specified recipient
     * 
     * @param int $recipientId ID of the user who will receive the notification
     * @param string $type Type of notification (like, comment, friend_request, friend_accept, mention, tag)
     * @param string $relatedContentType Type of content (post, comment, user, message)
     * @param int $relatedContentId ID of the related content
     * @return array API response
     * @throws Exception on API error
     */
    public function createNotification($recipientId, $type, $relatedContentType, $relatedContentId) {
        $data = [
            'recipient_id' => $recipientId,
            'type' => $type,
            'related_content_type' => $relatedContentType,
            'related_content_id' => $relatedContentId
        ];

        return $this->authenticatedRequest($this->notificationsEndpoint . '/create_notification', [
            'method' => 'POST',
            'body' => json_encode($data)
        ]);
    }

    /**
     * Get notifications for the authenticated user with pagination and filtering
     * 
     * @param int $page Page number for pagination (default: 1)
     * @param int $limit Number of notifications per page (default: 20, max: 50)
     * @param string $filter Filter notifications ("all" or "unread", default: "all")
     * @return array API response with notifications data
     * @throws Exception on API error
     */
    public function getNotifications($page = 1, $limit = 20, $filter = 'all') {
        $params = [
            'page' => $page,
            'limit' => min($limit, 50), // Enforce max limit
            'filter' => $filter
        ];

        return $this->authenticatedRequest($this->notificationsEndpoint . '/get_notifications', [
            'method' => 'GET',
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getAuthToken()
            ]
        ]);
    }

    /**
     * Get notifications with query parameters appended to URL
     * 
     * @param int $page Page number for pagination
     * @param int $limit Number of notifications per page
     * @param string $filter Filter notifications
     * @return array API response
     */
    public function getNotificationsWithParams($page = 1, $limit = 20, $filter = 'all') {
        $params = [
            'page' => $page,
            'limit' => min($limit, 50),
            'filter' => $filter
        ];

        $queryString = http_build_query($params);
        $endpoint = $this->notificationsEndpoint . '/get_notifications?' . $queryString;

        return $this->authenticatedRequest($endpoint, [
            'method' => 'GET'
        ]);
    }

    /**
     * Delete a specific notification
     * 
     * @param int $notificationId ID of the notification to delete
     * @return array API response
     * @throws Exception on API error
     */
    public function deleteNotification($notificationId) {
        return $this->authenticatedRequest($this->notificationsEndpoint . '/delete_notification', [
            'method' => 'DELETE',
            'body' => json_encode(['notificationId' => $notificationId])
        ]);
    }

    /**
     * Delete notification using URL parameter
     * 
     * @param int $notificationId ID of the notification to delete
     * @return array API response
     */
    public function deleteNotificationByParam($notificationId) {
        $endpoint = $this->notificationsEndpoint . '/delete_notification?notificationId=' . $notificationId;
        
        return $this->authenticatedRequest($endpoint, [
            'method' => 'DELETE'
        ]);
    }

    /**
     * Mark a specific notification as read
     * 
     * @param int $notificationId ID of the notification to mark as read
     * @return array API response
     * @throws Exception on API error
     */
    public function markNotificationAsRead($notificationId) {
        return $this->authenticatedRequest($this->notificationsEndpoint . '/mark_read', [
            'method' => 'PUT',
            'body' => json_encode(['notificationId' => $notificationId])
        ]);
    }

    /**
     * Mark notification as read using URL parameter
     * 
     * @param int $notificationId ID of the notification to mark as read
     * @return array API response
     */
    public function markNotificationAsReadByParam($notificationId) {
        $endpoint = $this->notificationsEndpoint . '/mark_read?notificationId=' . $notificationId;
        
        return $this->authenticatedRequest($endpoint, [
            'method' => 'PUT'
        ]);
    }

    /**
     * Mark notification as read using GET method (alternative)
     * 
     * @param int $notificationId ID of the notification to mark as read
     * @return array API response
     */
    public function markNotificationAsReadGet($notificationId) {
        return $this->authenticatedRequest($this->notificationsEndpoint . '/mark_read', [
            'method' => 'GET',
            'body' => json_encode(['notificationId' => $notificationId])
        ]);
    }

    /**
     * Mark all unread notifications as read for the authenticated user
     * 
     * @return array API response with count of marked notifications
     * @throws Exception on API error
     */
    public function markAllNotificationsAsRead() {
        return $this->authenticatedRequest($this->notificationsEndpoint . '/mark_all_read', [
            'method' => 'PUT'
        ]);
    }

    /**
     * Mark all notifications as read using GET method
     * 
     * @return array API response
     */
    public function markAllNotificationsAsReadGet() {
        return $this->authenticatedRequest($this->notificationsEndpoint . '/mark_all_read', [
            'method' => 'GET'
        ]);
    }

    /**
     * Mark all notifications as read using POST method
     * 
     * @return array API response
     */
    public function markAllNotificationsAsReadPost() {
        return $this->authenticatedRequest($this->notificationsEndpoint . '/mark_all_read', [
            'method' => 'POST'
        ]);
    }

    /**
     * Get the count of unread notifications for the authenticated user
     * 
     * @return array API response with unread count
     * @throws Exception on API error
     */
    public function getUnreadCount() {
        return $this->authenticatedRequest($this->notificationsEndpoint . '/unread_count', [
            'method' => 'GET'
        ]);
    }

    /**
     * Get the Server-Sent Events URL for live notifications
     * Note: This returns the URL, not the actual SSE stream
     * 
     * @param string|null $token Authentication token (optional if using session)
     * @param int|null $lastId Last notification ID received to avoid duplicates
     * @return string SSE endpoint URL
     */
    public function getLiveNotificationsUrl($token = null, $lastId = null) {
        $params = [];
        
        if ($token) {
            $params['token'] = $token;
        } elseif ($this->getAuthToken()) {
            $params['token'] = $this->getAuthToken();
        }
        
        if ($lastId) {
            $params['last_id'] = $lastId;
        }

        $queryString = !empty($params) ? '?' . http_build_query($params) : '';
        
        return $this->baseURL . $this->notificationsEndpoint . '/live_notifications' . $queryString;
    }

    /**
     * Helper method to validate notification types
     * 
     * @param string $type Notification type to validate
     * @return bool True if valid type
     */
    public function isValidNotificationType($type) {
        $validTypes = ['like', 'comment', 'friend_request', 'friend_accept', 'mention', 'tag'];
        return in_array($type, $validTypes);
    }

    /**
     * Helper method to validate content types
     * 
     * @param string $contentType Content type to validate
     * @return bool True if valid content type
     */
    public function isValidContentType($contentType) {
        $validTypes = ['post', 'comment', 'user', 'message'];
        return in_array($contentType, $validTypes);
    }

    /**
     * Helper method to validate filter values
     * 
     * @param string $filter Filter to validate
     * @return bool True if valid filter
     */
    public function isValidFilter($filter) {
        return in_array($filter, ['all', 'unread']);
    }

    /**
     * Convenience method to create a like notification
     * 
     * @param int $recipientId User who will receive the notification
     * @param int $postId ID of the liked post
     * @return array API response
     */
    public function createLikeNotification($recipientId, $postId) {
        return $this->createNotification($recipientId, 'like', 'post', $postId);
    }

    /**
     * Convenience method to create a comment notification
     * 
     * @param int $recipientId User who will receive the notification
     * @param int $postId ID of the commented post
     * @return array API response
     */
    public function createCommentNotification($recipientId, $postId) {
        return $this->createNotification($recipientId, 'comment', 'post', $postId);
    }

    /**
     * Convenience method to create a friend request notification
     * 
     * @param int $recipientId User who will receive the notification
     * @param int $senderId ID of the user sending the friend request
     * @return array API response
     */
    public function createFriendRequestNotification($recipientId, $senderId) {
        return $this->createNotification($recipientId, 'friend_request', 'user', $senderId);
    }

    /**
     * Convenience method to create a friend accept notification
     * 
     * @param int $recipientId User who will receive the notification
     * @param int $accepterId ID of the user who accepted the friend request
     * @return array API response
     */
    public function createFriendAcceptNotification($recipientId, $accepterId) {
        return $this->createNotification($recipientId, 'friend_accept', 'user', $accepterId);
    }

    /**
     * Convenience method to create a mention notification
     * 
     * @param int $recipientId User who was mentioned
     * @param int $postId ID of the post containing the mention
     * @return array API response
     */
    public function createMentionNotification($recipientId, $postId) {
        return $this->createNotification($recipientId, 'mention', 'post', $postId);
    }

    /**
     * Convenience method to create a tag notification
     * 
     * @param int $recipientId User who was tagged
     * @param int $postId ID of the post containing the tag
     * @return array API response
     */
    public function createTagNotification($recipientId, $postId) {
        return $this->createNotification($recipientId, 'tag', 'post', $postId);
    }

    /**
     * Get only unread notifications
     * 
     * @param int $page Page number
     * @param int $limit Notifications per page
     * @return array API response
     */
    public function getUnreadNotifications($page = 1, $limit = 20) {
        return $this->getNotificationsWithParams($page, $limit, 'unread');
    }

    /**
     * Get all notifications (read and unread)
     * 
     * @param int $page Page number
     * @param int $limit Notifications per page
     * @return array API response
     */
    public function getAllNotifications($page = 1, $limit = 20) {
        return $this->getNotificationsWithParams($page, $limit, 'all');
    }

    /**
     * Batch mark multiple notifications as read
     * Note: This would require multiple API calls since the API doesn't support batch operations
     * 
     * @param array $notificationIds Array of notification IDs to mark as read
     * @return array Results of each operation
     */
    public function markMultipleNotificationsAsRead($notificationIds) {
        $results = [];
        
        foreach ($notificationIds as $id) {
            try {
                $results[$id] = $this->markNotificationAsRead($id);
            } catch (Exception $e) {
                $results[$id] = ['success' => false, 'error' => $e->getMessage()];
            }
        }
        
        return $results;
    }

    /**
     * Batch delete multiple notifications
     * Note: This would require multiple API calls since the API doesn't support batch operations
     * 
     * @param array $notificationIds Array of notification IDs to delete
     * @return array Results of each operation
     */
    public function deleteMultipleNotifications($notificationIds) {
        $results = [];
        
        foreach ($notificationIds as $id) {
            try {
                $results[$id] = $this->deleteNotification($id);
            } catch (Exception $e) {
                $results[$id] = ['success' => false, 'error' => $e->getMessage()];
            }
        }
        
        return $results;
    }
}