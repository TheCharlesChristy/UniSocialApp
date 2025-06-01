<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SocialConnect - Feed</title>
</head>
<body>
    <?php
    // Include the component loader
    require_once '../php/component-loader.php';
    
    // Create component loader instance
    $loader = new ComponentLoader();
    
    // In a real application, this data would come from the session or database
    $userData = [
        'user_name' => 'John Doe',
        'profile_picture' => '../assets/images/default-profile.svg',
        'notification_count' => '5'
    ];
    
    // Render the logged-in header with user data
    echo $loader->getComponentWithVars('logged_in_header', $userData);
    ?>
</body>
</html>
