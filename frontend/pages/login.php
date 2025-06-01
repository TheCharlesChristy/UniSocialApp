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
    <title>Login - SocialConnect</title>
    <meta name="description" content="Sign in to your SocialConnect account">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
</head>
<body>
    <?php 
        $loader->renderComponent('home_header');
        $loader->renderComponent('login_form');
    ?>
</body>
</html>
