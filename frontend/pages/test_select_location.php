<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SocialConnect - LOCATION TEST</title>
</head>
<body>
    <?php
    // Require authentication - this will handle all JWT token logic
    require_once '../php/auth-guard.php';

    // Include the API library
    require_once '../php/api-library.php';

    // Include the component loader
    require_once '../php/component-loader.php';

    // Load the API library
    $apiLibrary = new ApiLibrary();
    
    // Create component loader instance
    $componentLoader = new ComponentLoader();
    
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
    echo $componentLoader->renderComponentWithVars('logged_in_header', $userData);

    // Prepare location component variables
    $locationData = [
        'component_id' => 'location_test', // Unique ID for this instance
        'default_latitude' => '',
        'default_longitude' => '',
        'default_location_text' => 'Choose a location for your post',
        'default_location_name' => ''
    ];
    
    // Render the select location component with variables
    echo $componentLoader->renderComponentWithVars('select_location', $locationData);
?>
</body>
</html>