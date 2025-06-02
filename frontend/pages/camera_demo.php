<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SocialConnect - Camera Capture</title>
</head>
<body>
    <?php
    // Include the component loader
    require_once '../php/component-loader.php';
    
    // Create component loader instance
    $loader = new ComponentLoader();
    
    // Render the camera capture component
    echo $loader->getComponent('camera_capture');
    ?>
</body>
</html>
