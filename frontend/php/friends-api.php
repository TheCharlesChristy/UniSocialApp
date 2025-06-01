<?php

require_once 'api-handler.php';

/**
 * Friends API Interface Class
 * 
 * Provides a comprehensive interface for all Friends API endpoints
 * Inherits from the generic APIHandler class
 * 
 * @author Your Name
 * @version 1.0
 */
class FriendsAPI extends APIHandler {
    
    /**
     * Get the current user's friends list with pagination
     * 
     * @param int $page Page number (default: 1)
     * @param int $limit Number of friends per page (default: 20, max: 50)
     * @return array API response with friends list
     * @throws Exception on API error
     */
    public function getFriends($page = 1, $limit = 20) {
        $params = [
            'page' => max(1, (int)$page),
            'limit' => min(50, max(1, (int)$limit))
        ];
        
        return $this->authenticatedRequest('/friends/get_friends', [
            'method' => 'GET',
            'headers' => ['Content-Type' => 'application/json']
        ]);
    }
    
    /**
     * Send a friend request to another user
     * 
     * @param int $userId Target user ID to send friend request to
     * @return array API response
     * @throws Exception on API error
     */
    public function sendFriendRequest($userId) {
        if (!is_numeric($userId) || $userId <= 0) {
            throw new Exception('Invalid user ID provided');
        }
        
        $data = ['user_id' => (int)$userId];
        
        return $this->authenticatedRequest('/friends/send_request', [
            'method' => 'POST',
            'body' => json_encode($data),
            'headers' => ['Content-Type' => 'application/json']
        ]);
    }
    
    /**
     * Send a friend request using URL parameter method
     * 
     * @param int $userId Target user ID to send friend request to
     * @return array API response
     * @throws Exception on API error
     */
    public function sendFriendRequestById($userId) {
        if (!is_numeric($userId) || $userId <= 0) {
            throw new Exception('Invalid user ID provided');
        }
        
        return $this->authenticatedRequest("/friends/send_request/$userId", [
            'method' => 'POST',
            'headers' => ['Content-Type' => 'application/json']
        ]);
    }
    
    /**
     * Get friend requests (received or sent)
     * 
     * @param string $type Type of requests: 'received' or 'sent' (default: 'received')
     * @param int $page Page number (default: 1)
     * @param int $limit Number of requests per page (default: 20, max: 50)
     * @return array API response with friend requests
     * @throws Exception on API error
     */
    public function getFriendRequests($type = 'received', $page = 1, $limit = 20) {
        if (!in_array($type, ['received', 'sent'])) {
            throw new Exception('Invalid type parameter. Must be "received" or "sent"');
        }
        
        $params = [
            'type' => $type,
            'page' => max(1, (int)$page),
            'limit' => min(50, max(1, (int)$limit))
        ];
        
        return $this->authenticatedRequest('/friends/get_requests?' . http_build_query($params), [
            'method' => 'GET',
            'headers' => ['Content-Type' => 'application/json']
        ]);
    }
    
    /**
     * Get received friend requests (convenience method)
     * 
     * @param int $page Page number (default: 1)
     * @param int $limit Number of requests per page (default: 20, max: 50)
     * @return array API response with received friend requests
     * @throws Exception on API error
     */
    public function getReceivedRequests($page = 1, $limit = 20) {
        return $this->getFriendRequests('received', $page, $limit);
    }
    
    /**
     * Get sent friend requests (convenience method)
     * 
     * @param int $page Page number (default: 1)
     * @param int $limit Number of requests per page (default: 20, max: 50)
     * @return array API response with sent friend requests
     * @throws Exception on API error
     */
    public function getSentRequests($page = 1, $limit = 20) {
        return $this->getFriendRequests('sent', $page, $limit);
    }
    
    /**
     * Get detailed outgoing friend requests with enhanced user data
     * 
     * @param int $page Page number (default: 1)
     * @param int $limit Number of requests per page (default: 10, max: 50)
     * @return array API response with detailed outgoing requests
     * @throws Exception on API error
     */
    public function getOutgoingRequests($page = 1, $limit = 10) {
        $params = [
            'page' => max(1, (int)$page),
            'limit' => min(50, max(1, (int)$limit))
        ];
        
        return $this->authenticatedRequest('/friends/get_outgoing_requests?' . http_build_query($params), [
            'method' => 'GET',
            'headers' => ['Content-Type' => 'application/json']
        ]);
    }
    
    /**
     * Accept a pending friend request
     * 
     * @param int $userId User ID who sent the friend request
     * @return array API response
     * @throws Exception on API error
     */
    public function acceptFriendRequest($userId) {
        if (!is_numeric($userId) || $userId <= 0) {
            throw new Exception('Invalid user ID provided');
        }
        
        return $this->authenticatedRequest("/friends/accept_request/$userId", [
            'method' => 'PUT',
            'headers' => ['Content-Type' => 'application/json']
        ]);
    }
    
    /**
     * Reject a pending friend request
     * 
     * @param int $userId User ID who sent the friend request
     * @return array API response
     * @throws Exception on API error
     */
    public function rejectFriendRequest($userId) {
        if (!is_numeric($userId) || $userId <= 0) {
            throw new Exception('Invalid user ID provided');
        }
        
        return $this->authenticatedRequest("/friends/reject_request/$userId", [
            'method' => 'PUT',
            'headers' => ['Content-Type' => 'application/json']
        ]);
    }
    
    /**
     * Remove an existing friendship
     * 
     * @param int $userId Friend's user ID to remove
     * @return array API response
     * @throws Exception on API error
     */
    public function removeFriend($userId) {
        if (!is_numeric($userId) || $userId <= 0) {
            throw new Exception('Invalid user ID provided');
        }
        
        return $this->authenticatedRequest("/friends/remove_friend/$userId", [
            'method' => 'DELETE',
            'headers' => ['Content-Type' => 'application/json']
        ]);
    }
    
    /**
     * Remove/cancel a sent friend request
     * 
     * @param int $userId User ID to whom the request was sent
     * @return array API response
     * @throws Exception on API error
     */
    public function removeFriendRequest($userId) {
        if (!is_numeric($userId) || $userId <= 0) {
            throw new Exception('Invalid user ID provided');
        }
        
        return $this->authenticatedRequest("/friends/remove_friend_request/$userId", [
            'method' => 'DELETE',
            'headers' => ['Content-Type' => 'application/json']
        ]);
    }
    
    /**
     * Cancel a sent friend request (alias for removeFriendRequest)
     * 
     * @param int $userId User ID to whom the request was sent
     * @return array API response
     * @throws Exception on API error
     */
    public function cancelFriendRequest($userId) {
        return $this->removeFriendRequest($userId);
    }
    
    /**
     * Get friends count for the current user
     * Helper method that extracts total_friends from getFriends response
     * 
     * @return int Total number of friends
     * @throws Exception on API error
     */
    public function getFriendsCount() {
        $response = $this->getFriends(1, 1); // Get minimal data just for count
        
        if (isset($response['total_friends'])) {
            return (int)$response['total_friends'];
        }
        
        return 0;
    }
    
    /**
     * Get pending friend requests count (received)
     * Helper method that extracts total from getReceivedRequests response
     * 
     * @return int Total number of pending received requests
     * @throws Exception on API error
     */
    public function getPendingRequestsCount() {
        $response = $this->getReceivedRequests(1, 1); // Get minimal data just for count
        
        if (isset($response['total_requests'])) {
            return (int)$response['total_requests'];
        }
        
        return 0;
    }
    
    /**
     * Get sent friend requests count
     * Helper method that extracts total from getSentRequests response
     * 
     * @return int Total number of sent requests
     * @throws Exception on API error
     */
    public function getSentRequestsCount() {
        $response = $this->getSentRequests(1, 1); // Get minimal data just for count
        
        if (isset($response['total_requests'])) {
            return (int)$response['total_requests'];
        }
        
        return 0;
    }
    
    /**
     * Check if a specific user is a friend
     * 
     * @param int $userId User ID to check friendship status
     * @return bool True if user is a friend, false otherwise
     * @throws Exception on API error
     */
    public function isFriend($userId) {
        if (!is_numeric($userId) || $userId <= 0) {
            throw new Exception('Invalid user ID provided');
        }
        
        try {
            // Get all friends and check if the user is in the list
            $allFriends = [];
            $page = 1;
            $limit = 50; // Maximum allowed
            
            do {
                $response = $this->getFriends($page, $limit);
                
                if (isset($response['friends']) && is_array($response['friends'])) {
                    foreach ($response['friends'] as $friend) {
                        if (isset($friend['user_id']) && $friend['user_id'] == $userId) {
                            return true;
                        }
                    }
                    
                    // Check if there are more pages
                    if (isset($response['current_page'], $response['total_pages'])) {
                        if ($response['current_page'] >= $response['total_pages']) {
                            break;
                        }
                        $page++;
                    } else {
                        break;
                    }
                } else {
                    break;
                }
            } while (true);
            
            return false;
            
        } catch (Exception $e) {
            // Re-throw the exception to maintain error handling
            throw $e;
        }
    }
    
    /**
     * Check if there's a pending friend request from/to a specific user
     * 
     * @param int $userId User ID to check request status
     * @param string $direction 'sent', 'received', or 'both' (default: 'both')
     * @return array Status information: ['sent' => bool, 'received' => bool]
     * @throws Exception on API error
     */
    public function hasPendingRequest($userId, $direction = 'both') {
        if (!is_numeric($userId) || $userId <= 0) {
            throw new Exception('Invalid user ID provided');
        }
        
        if (!in_array($direction, ['sent', 'received', 'both'])) {
            throw new Exception('Invalid direction parameter. Must be "sent", "received", or "both"');
        }
        
        $result = ['sent' => false, 'received' => false];
        
        try {
            // Check sent requests if needed
            if ($direction === 'sent' || $direction === 'both') {
                $page = 1;
                do {
                    $response = $this->getSentRequests($page, 50);
                    
                    if (isset($response['requests']) && is_array($response['requests'])) {
                        foreach ($response['requests'] as $request) {
                            if (isset($request['user_id']) && $request['user_id'] == $userId) {
                                $result['sent'] = true;
                                break 2; // Break out of both loops
                            }
                        }
                        
                        // Check if there are more pages
                        if (isset($response['current_page'], $response['total_pages'])) {
                            if ($response['current_page'] >= $response['total_pages']) {
                                break;
                            }
                            $page++;
                        } else {
                            break;
                        }
                    } else {
                        break;
                    }
                } while (!$result['sent']);
            }
            
            // Check received requests if needed and not already found
            if (($direction === 'received' || $direction === 'both') && !$result['sent']) {
                $page = 1;
                do {
                    $response = $this->getReceivedRequests($page, 50);
                    
                    if (isset($response['requests']) && is_array($response['requests'])) {
                        foreach ($response['requests'] as $request) {
                            if (isset($request['user_id']) && $request['user_id'] == $userId) {
                                $result['received'] = true;
                                break 2; // Break out of both loops
                            }
                        }
                        
                        // Check if there are more pages
                        if (isset($response['current_page'], $response['total_pages'])) {
                            if ($response['current_page'] >= $response['total_pages']) {
                                break;
                            }
                            $page++;
                        } else {
                            break;
                        }
                    } else {
                        break;
                    }
                } while (!$result['received']);
            }
            
            return $result;
            
        } catch (Exception $e) {
            // Re-throw the exception to maintain error handling
            throw $e;
        }
    }
    
    /**
     * Get friendship status with a specific user
     * Returns comprehensive status information
     * 
     * @param int $userId User ID to check status with
     * @return array Status information with keys: 'is_friend', 'pending_sent', 'pending_received', 'status'
     * @throws Exception on API error
     */
    public function getFriendshipStatus($userId) {
        if (!is_numeric($userId) || $userId <= 0) {
            throw new Exception('Invalid user ID provided');
        }
        
        $isFriend = $this->isFriend($userId);
        
        if ($isFriend) {
            return [
                'is_friend' => true,
                'pending_sent' => false,
                'pending_received' => false,
                'status' => 'friends'
            ];
        }
        
        $pendingRequests = $this->hasPendingRequest($userId, 'both');
        
        $status = 'none';
        if ($pendingRequests['sent']) {
            $status = 'request_sent';
        } elseif ($pendingRequests['received']) {
            $status = 'request_received';
        }
        
        return [
            'is_friend' => false,
            'pending_sent' => $pendingRequests['sent'],
            'pending_received' => $pendingRequests['received'],
            'status' => $status
        ];
    }
    
    /**
     * Batch operation: Get friendship status for multiple users
     * 
     * @param array $userIds Array of user IDs to check
     * @return array Associative array with user_id as key and status as value
     * @throws Exception on API error
     */
    public function getBatchFriendshipStatus($userIds) {
        if (!is_array($userIds) || empty($userIds)) {
            throw new Exception('User IDs array cannot be empty');
        }
        
        $results = [];
        
        foreach ($userIds as $userId) {
            try {
                $results[$userId] = $this->getFriendshipStatus($userId);
            } catch (Exception $e) {
                // Log error but continue with other users
                error_log("Error getting friendship status for user $userId: " . $e->getMessage());
                $results[$userId] = [
                    'is_friend' => false,
                    'pending_sent' => false,
                    'pending_received' => false,
                    'status' => 'error',
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $results;
    }
}
