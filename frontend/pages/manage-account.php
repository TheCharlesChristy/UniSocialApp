<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SocialConnect - Manage Account</title>
</head>
<body>
    <?php
    // Include the component loader
    require_once '../php/component-loader.php';
    
    // Create component loader instance
    $loader = new ComponentLoader();
    
    // Sample user data
    $userData = [
        'user_name' => 'John Doe',
        'profile_picture' => '../assets/images/default-profile.svg',
        'notification_count' => '0'
    ];
    
    // Render the logged-in header
    echo $loader->getComponentWithVars('logged_in_header', $userData);
    ?>
</body>
</html>
