<?php
/**
 * Webpage Navigator
 * 
 * This script automatically generates buttons linking to all web pages in the current directory
 */

// Get all files in the current directory
$directory = __DIR__;
$files = scandir($directory);
$webPages = [];

// Web file extensions to look for
$webExtensions = [
    'php', 'html', 'htm', 'xhtml'
];

// Filter for web pages only
foreach ($files as $file) {
    // Skip the current file
    if ($file === basename($_SERVER['PHP_SELF'])) {
        continue;
    }
    
    // Skip directories and special entries
    if (is_dir($directory . '/' . $file) || $file === '.' || $file === '..') {
        continue;
    }
    
    // Check if the file has a web page extension
    $extension = pathinfo($file, PATHINFO_EXTENSION);
    if (in_array(strtolower($extension), $webExtensions)) {
        $webPages[] = $file;
    }
}

// Sort alphabetically
sort($webPages);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Page Navigator</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        .button-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 20px;
        }
        .page-button {
            background-color: #4CAF50;
            border: none;
            color: white;
            padding: 10px 15px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .page-button:hover {
            background-color: #45a049;
        }
        .no-pages {
            color: #888;
            font-style: italic;
        }
    </style>
</head>
<body>
    <h1>Web Page Navigator</h1>
    <p>Click on a button to navigate to the corresponding web page:</p>
    
    <div class="button-container">
        <?php if (count($webPages) > 0): ?>
            <?php foreach ($webPages as $page): ?>
                <a href="<?php echo htmlspecialchars($page); ?>" class="page-button">
                    <?php echo htmlspecialchars($page); ?>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-pages">No web pages found in this directory.</p>
        <?php endif; ?>
    </div>
</body>
</html>
