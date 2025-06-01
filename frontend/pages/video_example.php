<?php
require_once '../php/component-loader.php';

// Create an instance of the ComponentLoader
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
        $videoData = [
        'video_url' => '../../backend/media/images/posts/6_1748099593_6831e209a6095.mp4',
        ];
        echo $loader->getComponentWithVars('video_display', $videoData);
    ?>
</body>
</html>
