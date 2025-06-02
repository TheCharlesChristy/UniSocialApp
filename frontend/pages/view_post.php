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
    require_once '../php/builders/view_post.php';

    // Initialize the API library
    $apiLibrary = new APILibrary();

    // Initialize the component loader
    $componentLoader = new ComponentLoader();
    
    // Create an instance of the ViewPost builder
    $viewPost = new ViewPost();

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
    

    // Get the post id from the query parameters
    $postId = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;
    if ($postId <= 0) {
        echo "<p>Invalid post ID.</p>";
        exit();
    }

    $viewPost->build($postId);
    ?>
</body>
</html>
