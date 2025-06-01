<?php
require_once '../php/component-loader.php';

// Initialize the component loader
$loader = new ComponentLoader('../components/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - SocialConnect</title>
    <meta name="description" content="Reset your SocialConnect password by entering your email address">
    <meta name="robots" content="noindex, nofollow">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
</head>
<body>
    <?php 
        $loader->renderComponent('home_header');
        $loader->renderComponent('forgot_password_form');
    ?>
</body>
</html>
