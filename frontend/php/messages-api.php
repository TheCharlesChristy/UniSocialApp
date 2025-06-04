<?php

require_once 'api-handler.php';

class MessagesAPI extends APIHandler {
    
    public function __construct() {
        // Initialize with messages-specific base URL
        parent::__construct('/backend/src/api/messages');
    }

    /**
     * Delete a message from a conversation
     * Only the sender of the message can delete their own messages.
     * User must be an active participant in the conversation.
     * 
     * @param int $messageId Unique identifier of the message to delete
     * @return array Response data including success status and message details
     * @throws Exception If deletion fails, user is not the sender, or not authorized
     */
    public function deleteMessage($messageId) {
        if (!$this->isAuthenticated()) {
            throw new Exception('Authentication required to delete messages');
        }

        // Validate message ID
        if (!is_numeric($messageId) || $messageId <= 0) {
            throw new Exception('Invalid message ID provided');
        }

        // Use authenticated request with message ID as query parameter
        $endpoint = "/delete_message?messageId=" . $messageId;
        
        return $this->authenticatedRequest($endpoint, [
            'method' => 'DELETE'
        ]);
    }

    /**
     * Mark a message as read by the current user
     * Users cannot mark their own messages as read.
     * User must be a participant in the conversation.
     * 
     * @param int $messageId Unique identifier of the message to mark as read
     * @return array Response data including success status and read timestamp
     * @throws Exception If marking fails, user tries to mark own message, or not authorized
     */
    public function markMessageAsRead($messageId) {
        if (!$this->isAuthenticated()) {
            throw new Exception('Authentication required to mark messages as read');
        }

        // Validate message ID
        if (!is_numeric($messageId) || $messageId <= 0) {
            throw new Exception('Invalid message ID provided');
        }

        // Use authenticated request with message ID as query parameter
        $endpoint = "/mark_message_read?messageId=" . $messageId;
        
        return $this->authenticatedRequest($endpoint, [
            'method' => 'PUT'
        ]);
    }

    /**
     * Batch mark multiple messages as read
     * Convenience method to mark multiple messages as read in sequence
     * 
     * @param array $messageIds Array of message IDs to mark as read
     * @return array Array of results for each message ID
     */
    public function markMultipleMessagesAsRead($messageIds) {
        if (!$this->isAuthenticated()) {
            throw new Exception('Authentication required to mark messages as read');
        }

        if (!is_array($messageIds) || empty($messageIds)) {
            throw new Exception('Invalid message IDs array provided');
        }

        $results = [];
        $errors = [];

        foreach ($messageIds as $messageId) {
            try {
                $results[$messageId] = $this->markMessageAsRead($messageId);
            } catch (Exception $e) {
                $errors[$messageId] = $e->getMessage();
            }
        }

        return [
            'successful_reads' => $results,
            'errors' => $errors,
            'total_processed' => count($messageIds),
            'successful_count' => count($results),
            'error_count' => count($errors)
        ];
    }

    /**
     * Batch delete multiple messages
     * Convenience method to delete multiple messages in sequence
     * Note: Only messages owned by the authenticated user can be deleted
     * 
     * @param array $messageIds Array of message IDs to delete
     * @return array Array of results for each message ID
     */
    public function deleteMultipleMessages($messageIds) {
        if (!$this->isAuthenticated()) {
            throw new Exception('Authentication required to delete messages');
        }

        if (!is_array($messageIds) || empty($messageIds)) {
            throw new Exception('Invalid message IDs array provided');
        }

        $results = [];
        $errors = [];

        foreach ($messageIds as $messageId) {
            try {
                $results[$messageId] = $this->deleteMessage($messageId);
            } catch (Exception $e) {
                $errors[$messageId] = $e->getMessage();
            }
        }

        return [
            'successful_deletions' => $results,
            'errors' => $errors,
            'total_processed' => count($messageIds),
            'successful_count' => count($results),
            'error_count' => count($errors)
        ];
    }

    /**
     * Check if a specific message can be deleted by the current user
     * This is a helper method that doesn't actually delete but validates permissions
     * 
     * @param int $messageId Message ID to check
     * @return bool True if message can be deleted, false otherwise
     */
    public function canDeleteMessage($messageId) {
        try {
            // We can't actually check without attempting deletion
            // This would require a separate check endpoint in the backend
            // For now, we validate basic requirements
            return $this->isAuthenticated() && is_numeric($messageId) && $messageId > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Check if a specific message can be marked as read by the current user
     * This is a helper method that validates basic requirements
     * 
     * @param int $messageId Message ID to check
     * @return bool True if message can be marked as read, false otherwise
     */
    public function canMarkMessageAsRead($messageId) {
        try {
            // Basic validation - actual permissions checked on server side
            return $this->isAuthenticated() && is_numeric($messageId) && $messageId > 0;
        } catch (Exception $e) {
            return false;
        }
    }
}