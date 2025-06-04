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
    <title>Welcome to SocialConnect</title>
    <link rel="stylesheet" href="../css/globals.css">
</head>
<body>
    <?php 
    // Load components server-side
    $loader->renderComponent('home_header');
    $loader->renderComponent('welcome_hero');
    ?>
</body>
</html>