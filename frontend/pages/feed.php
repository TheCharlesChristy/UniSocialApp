<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SocialConnect - Feed</title>
</head>
<body>    
    <?php
    // Require authentication - this will handle all JWT token logic
    require_once '../php/auth-guard.php';
    
    // Include the FeedPost builder
    require_once '../php/builders/feed-post.php';
    
    // Create an instance of the FeedPost builder
    $feedPost = new FeedPost();
    
    // Example: Build a feed post with ID 1 (replace with actual post ID)
    $feedPost->build(1);
    ?>
</body>
</html>
