<?php
/**
 * Get User Suggestions API Endpoint
 * 
 * Gets suggested users for friendship based on mutual connections
 * Endpoint: GET /api/users/suggestions
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

// Require authentication
$requireAuth = true;

// Get database connection
$Database = require_once dirname(dirname(dirname(__FILE__))) . '/db_handler/connection.php';

// Include authentication middleware (this will exit if not authorized)
require_once dirname(dirname(__FILE__)) . '/auth/auth_middleware.php';

// If code execution reaches here, user is authenticated
// $authUser contains the authenticated user data

// Get limit parameter (default 10)
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

// Validate limit parameter
if ($limit < 1) $limit = 10;
if ($limit > 50) $limit = 50; // Maximum limit to prevent overload

// Get current user's existing friendships (both accepted and pending)
$existingFriendships = $Database->query(
    "SELECT user_id_1, user_id_2 FROM friendships 
     WHERE user_id_1 = ? OR user_id_2 = ?",
    [$authUser['user_id'], $authUser['user_id']]
);

// Build array of users the current user is already connected to
$connectedUsers = [$authUser['user_id']]; // Start with own ID
if ($existingFriendships && !empty($existingFriendships)) {
    foreach ($existingFriendships as $friendship) {
        if ($friendship['user_id_1'] == $authUser['user_id']) {
            $connectedUsers[] = $friendship['user_id_2'];
        } else {
            $connectedUsers[] = $friendship['user_id_1'];
        }
    }
}

// Build SQL to exclude the connected users
$excludeUsersSql = implode(',', array_fill(0, count($connectedUsers), '?'));

// First priority: Get users with mutual friends
// This query finds users who are friends with the current user's friends but not with the current user
$mutualFriendsQuery = "
    -- Get users who aren't already connected to the current user
    SELECT DISTINCT u.user_id, u.username, u.first_name, u.last_name, u.profile_picture, u.bio,
           COUNT(DISTINCT f1.user_id_1) + COUNT(DISTINCT f2.user_id_2) AS mutual_friends_count
    FROM users u
    -- Find mutual connections where the potential suggestion is friends with current user's friends
    LEFT JOIN friendships f1 ON 
        u.user_id = f1.user_id_2 AND 
        f1.user_id_1 IN (
            -- Current user's friends (where current user is user_id_1)
            SELECT user_id_2 FROM friendships 
            WHERE user_id_1 = ? AND status = 'accepted'
        ) AND 
        f1.status = 'accepted'
    LEFT JOIN friendships f2 ON 
        u.user_id = f2.user_id_1 AND 
        f2.user_id_2 IN (
            -- Current user's friends (where current user is user_id_2)
            SELECT user_id_1 FROM friendships 
            WHERE user_id_2 = ? AND status = 'accepted'
        ) AND
        f2.status = 'accepted'
    WHERE u.user_id NOT IN ({$excludeUsersSql})
      AND u.account_status = 'active'
    GROUP BY u.user_id
    HAVING COUNT(DISTINCT f1.user_id_1) + COUNT(DISTINCT f2.user_id_2) > 0
    ORDER BY mutual_friends_count DESC, u.registration_date DESC
    LIMIT ?
";

// Prepare parameters
$queryParams = array_merge(
    [$authUser['user_id'], $authUser['user_id']], // For mutual friends subqueries
    $connectedUsers, // For the NOT IN clause
    [$limit] // For the LIMIT clause
);

// Execute query
$suggestedUsers = $Database->query($mutualFriendsQuery, $queryParams);

// If we don't have enough suggestions with mutual friends, get newer users as additional suggestions
if (!$suggestedUsers || count($suggestedUsers) < $limit) {
    $neededMore = $limit - (count($suggestedUsers) ?? 0);
    
    // Update the exclude list to include the users we already have in suggestions
    if ($suggestedUsers && !empty($suggestedUsers)) {
        foreach ($suggestedUsers as $user) {
            $connectedUsers[] = $user['user_id'];
        }
    }
    
    // Rebuild the exclude SQL
    $excludeUsersSql = implode(',', array_fill(0, count($connectedUsers), '?'));
    
    // Query for getting newer users
    $newerUsersQuery = "
        SELECT user_id, username, first_name, last_name, profile_picture, bio, 0 AS mutual_friends_count
        FROM users
        WHERE user_id NOT IN ({$excludeUsersSql})
          AND account_status = 'active'
        ORDER BY registration_date DESC
        LIMIT ?
    ";
    
    // Prepare parameters
    $newUsersParams = array_merge(
        $connectedUsers,
        [$neededMore]
    );
    
    // Execute query
    $newerUsers = $Database->query($newerUsersQuery, $newUsersParams);
    
    // Merge the results
    if ($newerUsers && !empty($newerUsers)) {
        if (!$suggestedUsers) {
            $suggestedUsers = $newerUsers;
        } else {
            $suggestedUsers = array_merge($suggestedUsers, $newerUsers);
        }
    }
}

// Format the results
$formattedSuggestions = [];
if ($suggestedUsers && !empty($suggestedUsers)) {
    foreach ($suggestedUsers as $user) {
        $formattedSuggestions[] = [
            'user_id' => $user['user_id'],
            'username' => $user['username'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'profile_picture' => $user['profile_picture'],
            'bio' => $user['bio'],
            'mutual_friends_count' => $user['mutual_friends_count']
        ];
    }
}

// Return user suggestions
echo json_encode([
    'success' => true,
    'users' => $formattedSuggestions,
    'count' => count($formattedSuggestions)
]);
