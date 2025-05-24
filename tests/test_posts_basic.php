<?php
/**
 * Posts and Media API Basic Test Script
 * 
 * Simple test to verify database connectivity and basic operations
 */

// Include database connection
$Database = require_once dirname(dirname(__FILE__)) . '/backend/src/db_handler/connection.php';

echo "<!DOCTYPE html>\n<html>\n<head>\n<title>Posts API Basic Test</title>\n<style>
.back-to-tests-btn {
    position: fixed;
    top: 10px;
    left: 10px;
    background-color: #007bff;
    color: white;
    padding: 8px 12px;
    text-decoration: none;
    border-radius: 4px;
    font-size: 14px;
    z-index: 1000;
    transition: background-color 0.3s;
}
.back-to-tests-btn:hover {
    background-color: #0056b3;
    color: white;
    text-decoration: none;
}
</style>\n</head>\n<body>\n";
echo "<a href='http://localhost/webdev/tests' class='back-to-tests-btn'>← Back to Tests</a>\n";
echo "<h1>Posts and Media API Basic Test</h1>\n";

// Check database connection
if ($Database->isConnected()) {
    echo "<p style='color: green;'>✓ Database connection successful</p>\n";
    
    // Test basic queries
    try {
        // Check if posts table exists and has data
        $postsCount = $Database->query("SELECT COUNT(*) as count FROM posts");
        if ($postsCount !== false) {
            echo "<p style='color: green;'>✓ Posts table accessible - " . $postsCount[0]['count'] . " posts found</p>\n";
        }
        
        // Check if users table exists
        $usersCount = $Database->query("SELECT COUNT(*) as count FROM users WHERE account_status = 'active'");
        if ($usersCount !== false) {
            echo "<p style='color: green;'>✓ Users table accessible - " . $usersCount[0]['count'] . " active users found</p>\n";
        }
        
        // Check if likes table exists
        $likesCount = $Database->query("SELECT COUNT(*) as count FROM likes");
        if ($likesCount !== false) {
            echo "<p style='color: green;'>✓ Likes table accessible - " . $likesCount[0]['count'] . " likes found</p>\n";
        }
        
        // Check if comments table exists
        $commentsCount = $Database->query("SELECT COUNT(*) as count FROM comments");
        if ($commentsCount !== false) {
            echo "<p style='color: green;'>✓ Comments table accessible - " . $commentsCount[0]['count'] . " comments found</p>\n";
        }
        
        // Check if friendships table exists
        $friendshipsCount = $Database->query("SELECT COUNT(*) as count FROM friendships");
        if ($friendshipsCount !== false) {
            echo "<p style='color: green;'>✓ Friendships table accessible - " . $friendshipsCount[0]['count'] . " friendships found</p>\n";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Database query error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
    }
    
} else {
    echo "<p style='color: red;'>✗ Database connection failed</p>\n";
    $errors = $Database->getErrors();
    if (!empty($errors)) {
        echo "<p style='color: red;'>Errors: " . htmlspecialchars(implode(', ', $errors)) . "</p>\n";
    }
}

// Check if media directories exist
$mediaDir = dirname(dirname(__FILE__)) . '/backend/media/images/posts/';
if (is_dir($mediaDir)) {
    echo "<p style='color: green;'>✓ Media upload directory exists: " . htmlspecialchars($mediaDir) . "</p>\n";
    if (is_writable($mediaDir)) {
        echo "<p style='color: green;'>✓ Media upload directory is writable</p>\n";
    } else {
        echo "<p style='color: red;'>✗ Media upload directory is not writable</p>\n";
    }
} else {
    echo "<p style='color: red;'>✗ Media upload directory does not exist: " . htmlspecialchars($mediaDir) . "</p>\n";
}

// List available endpoints
echo "<h2>Available Endpoints</h2>\n";
echo "<h3>Posts Endpoints:</h3>\n";
echo "<ul>\n";
echo "<li>GET /api/posts/get_feed.php - Get user's feed posts</li>\n";
echo "<li>GET /api/posts/get_post.php - Get specific post</li>\n";
echo "<li>POST /api/posts/create_post.php - Create new post</li>\n";
echo "<li>PUT /api/posts/update_post.php - Update existing post</li>\n";
echo "<li>DELETE /api/posts/delete_post.php - Delete post</li>\n";
echo "<li>GET /api/posts/search_posts.php - Search posts</li>\n";
echo "</ul>\n";

echo "<h3>Likes Endpoints:</h3>\n";
echo "<ul>\n";
echo "<li>POST /api/posts/like_post.php - Like a post</li>\n";
echo "<li>DELETE /api/posts/unlike_post.php - Unlike a post</li>\n";
echo "<li>GET /api/posts/get_post_likes.php - Get post likes</li>\n";
echo "</ul>\n";

echo "<h3>Comments Endpoints:</h3>\n";
echo "<ul>\n";
echo "<li>GET /api/posts/get_comments.php - Get post comments</li>\n";
echo "<li>POST /api/posts/add_comment.php - Add comment</li>\n";
echo "<li>PUT /api/posts/update_comment.php - Update comment</li>\n";
echo "<li>DELETE /api/posts/delete_comment.php - Delete comment</li>\n";
echo "</ul>\n";

echo "<h3>Media Endpoints:</h3>\n";
echo "<ul>\n";
echo "<li>POST /api/media/upload.php - Upload media files</li>\n";
echo "</ul>\n";

echo "<h2>Testing</h2>\n";
echo "<p>Use the <a href='test_posts_media.html'>Interactive Test Page</a> to test all endpoints manually.</p>\n";

echo "</body>\n</html>";
?>
