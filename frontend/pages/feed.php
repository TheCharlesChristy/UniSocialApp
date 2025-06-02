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

    // Include the API library
    require_once '../php/api-library.php';

    // Include the component loader
    require_once '../php/component-loader.php';
    
    // Include the FeedPost builder
    require_once '../php/builders/feed-post.php';

    // Initialize the API library
    $apiLibrary = new APILibrary();

    // Initialize the component loader
    $componentLoader = new ComponentLoader();
    
    // Create an instance of the FeedPost builder
    $feedPost = new FeedPost();

    // Get the user data from the API
    $userResponse = $apiLibrary->usersAPI->getMyProfile();
    if (!$userResponse['success']) {
        echo "Error: " . $userResponse['message'];
        // redirect to login or show an error page
        header('Location: ../pages/login.php?error=' . urlencode($userResponse['message']));
        exit();
    }
    $userData = $userResponse['user'];

    // Prepare user data for the header component
    $userData = [
        'user_name' => $userData['username'],
        'profile_picture' => $userResponse['profile_picture'] ?? '../assets/images/default-profile.svg',
        'notification_count' => $userResponse['notification_count'] ?? 0
    ];


    // Add the header component
    echo $componentLoader->getComponentWithVars('logged_in_header', $userData);

    // Get the page number from the query parameters, default to 1
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $page = max($page, 1); // Ensure page is at least 1
    $pageSize = 10; // Number of posts per page
    
    
    // Fetch the latest posts from the API
    $postsResponse = $apiLibrary->postsAPI->getFeed($page, $pageSize);

    if (!$postsResponse['success']) {
        echo "Error: " . $postsResponse['message'];
        exit();
    }

    $posts = $postsResponse['posts'];
    if (empty($posts)) {
        echo "<p>No posts available.</p>";
    } else {
        foreach ($posts as $post) {
            // Build each post using the FeedPost builder
            $feedPost->build($post['post_id']);
        }
    }
    ?>
</body>
</html>
