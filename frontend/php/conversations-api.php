<?php

require_once 'api-handler.php';

class ConversationsAPI extends APIHandler {
    
    public function __construct($baseURL = '/webdev/backend/src/api') {
        parent::__construct($baseURL);
    }

    /**
     * Create a new conversation
     * 
     * @param array $participants Array of user IDs to include in conversation
     * @param bool $isGroupChat Force group chat mode (optional)
     * @param string $groupName Required for group chats, max 100 characters
     * @return array API response
     * @throws Exception
     */
    public function createConversation($participants, $isGroupChat = null, $groupName = null) {
        $data = [
            'participants' => $participants
        ];
        
        if ($isGroupChat !== null) {
            $data['is_group_chat'] = $isGroupChat;
        }
        
        if ($groupName !== null) {
            $data['group_name'] = $groupName;
        }

        return $this->authenticatedRequest('/conversations', [
            'method' => 'POST',
            'body' => json_encode($data)
        ]);
    }

    /**
     * Get all conversations for the authenticated user
     * 
     * @param int $page Page number for pagination (default: 1)
     * @param int $limit Number of conversations per page (default: 20, max: 100)
     * @return array API response
     * @throws Exception
     */
    public function getUserConversations($page = 1, $limit = 20) {
        $params = [
            'page' => $page,
            'limit' => min($limit, 100) // Enforce max limit
        ];

        return $this->authenticatedRequest('/conversations?' . http_build_query($params), [
            'method' => 'GET'
        ]);
    }

    /**
     * Get detailed information about a specific conversation
     * 
     * @param int $conversationId ID of the conversation
     * @return array API response
     * @throws Exception
     */
    public function getConversationDetails($conversationId) {
        return $this->authenticatedRequest("/conversations/$conversationId", [
            'method' => 'GET'
        ]);
    }

    /**
     * Get messages from a conversation
     * 
     * @param int $conversationId ID of the conversation
     * @param int $page Page number for pagination (default: 1)
     * @param int $limit Number of messages per page (default: 50, max: 100)
     * @param int $beforeMessageId Get messages before this message ID (optional)
     * @return array API response
     * @throws Exception
     */
    public function getConversationMessages($conversationId, $page = 1, $limit = 50, $beforeMessageId = null) {
        $params = [
            'page' => $page,
            'limit' => min($limit, 100) // Enforce max limit
        ];
        
        if ($beforeMessageId !== null) {
            $params['before_message_id'] = $beforeMessageId;
        }

        return $this->authenticatedRequest("/conversations/$conversationId/messages?" . http_build_query($params), [
            'method' => 'GET'
        ]);
    }

    /**
     * Send a message to a conversation
     * 
     * @param int $conversationId ID of the conversation
     * @param string $content Message content (max 5000 characters)
     * @return array API response
     * @throws Exception
     */
    public function sendMessage($conversationId, $content) {
        if (strlen($content) > 5000) {
            throw new Exception('Message content cannot exceed 5000 characters');
        }

        $data = [
            'content' => $content
        ];

        return $this->authenticatedRequest("/conversations/$conversationId/messages", [
            'method' => 'POST',
            'body' => json_encode($data)
        ]);
    }

    /**
     * Leave a group conversation
     * 
     * @param int $conversationId ID of the conversation to leave
     * @return array API response
     * @throws Exception
     */
    public function leaveConversation($conversationId) {
        $data = [
            'conversation_id' => $conversationId
        ];

        return $this->authenticatedRequest('/conversations/leave_conversation', [
            'method' => 'POST',
            'body' => json_encode($data)
        ]);
    }

    /**
     * Delete a conversation (only owners can delete group conversations)
     * 
     * @param int $conversationId ID of the conversation to delete
     * @return array API response
     * @throws Exception
     */
    public function deleteConversation($conversationId) {
        $data = [
            'conversation_id' => $conversationId
        ];

        return $this->authenticatedRequest('/conversations/delete_conversation', [
            'method' => 'DELETE',
            'body' => json_encode($data)
        ]);
    }

    // Convenience methods for common use cases

    /**
     * Create a private conversation between two users
     * 
     * @param int $otherUserId ID of the other user
     * @return array API response
     * @throws Exception
     */
    public function createPrivateConversation($otherUserId) {
        return $this->createConversation([$otherUserId]);
    }

    /**
     * Create a group conversation
     * 
     * @param array $participants Array of user IDs to include
     * @param string $groupName Name for the group chat
     * @return array API response
     * @throws Exception
     */
    public function createGroupConversation($participants, $groupName) {
        if (empty($groupName)) {
            throw new Exception('Group name is required for group conversations');
        }
        
        return $this->createConversation($participants, true, $groupName);
    }

    /**
     * Get the latest messages from a conversation
     * 
     * @param int $conversationId ID of the conversation
     * @param int $limit Number of messages to retrieve (default: 20)
     * @return array API response
     * @throws Exception
     */
    public function getLatestMessages($conversationId, $limit = 20) {
        return $this->getConversationMessages($conversationId, 1, $limit);
    }

    /**
     * Get all conversations with unread messages
     * 
     * @return array Filtered conversations with unread messages
     * @throws Exception
     */
    public function getConversationsWithUnreadMessages() {
        $response = $this->getUserConversations(1, 100); // Get larger batch
        
        if (!$response['success']) {
            return $response;
        }

        // Filter conversations with unread messages
        $unreadConversations = array_filter($response['conversations'], function($conversation) {
            return isset($conversation['unread_count']) && $conversation['unread_count'] > 0;
        });

        return [
            'success' => true,
            'message' => 'Conversations with unread messages retrieved successfully',
            'conversations' => array_values($unreadConversations),
            'total_unread_conversations' => count($unreadConversations)
        ];
    }

    /**
     * Search conversations by name or participant
     * 
     * @param string $searchTerm Search term to filter conversations
     * @return array Filtered conversations
     * @throws Exception
     */
    public function searchConversations($searchTerm) {
        $response = $this->getUserConversations(1, 100); // Get larger batch
        
        if (!$response['success']) {
            return $response;
        }

        $searchTerm = strtolower(trim($searchTerm));
        
        // Filter conversations by search term
        $filteredConversations = array_filter($response['conversations'], function($conversation) use ($searchTerm) {
            // Search in group name
            if (isset($conversation['group_name']) && stripos($conversation['group_name'], $searchTerm) !== false) {
                return true;
            }
            
            // Search in display name
            if (isset($conversation['display_name']) && stripos($conversation['display_name'], $searchTerm) !== false) {
                return true;
            }
            
            // Search in participants
            if (isset($conversation['participants'])) {
                foreach ($conversation['participants'] as $participant) {
                    $fullName = ($participant['first_name'] ?? '') . ' ' . ($participant['last_name'] ?? '');
                    $username = $participant['username'] ?? '';
                    
                    if (stripos($fullName, $searchTerm) !== false || stripos($username, $searchTerm) !== false) {
                        return true;
                    }
                }
            }
            
            return false;
        });

        return [
            'success' => true,
            'message' => 'Conversations search completed successfully',
            'conversations' => array_values($filteredConversations),
            'search_term' => $searchTerm,
            'total_found' => count($filteredConversations)
        ];
    }

    /**
     * Get conversation statistics
     * 
     * @param int $conversationId ID of the conversation
     * @return array Conversation statistics
     * @throws Exception
     */
    public function getConversationStats($conversationId) {
        $details = $this->getConversationDetails($conversationId);
        
        if (!$details['success']) {
            return $details;
        }

        $conversation = $details['conversation'];
        $participants = $details['participants'] ?? [];
        $leftParticipants = $details['left_participants'] ?? [];

        return [
            'success' => true,
            'message' => 'Conversation statistics retrieved successfully',
            'stats' => [
                'conversation_id' => $conversationId,
                'total_messages' => $conversation['total_messages'] ?? 0,
                'active_participants' => count($participants),
                'left_participants' => count($leftParticipants),
                'total_participants_ever' => count($participants) + count($leftParticipants),
                'is_group_chat' => $conversation['is_group_chat'] ?? false,
                'created_at' => $conversation['created_at'] ?? null,
                'last_activity' => $conversation['last_message_time'] ?? null,
                'unread_count' => $conversation['unread_count'] ?? 0
            ]
        ];
    }
}
