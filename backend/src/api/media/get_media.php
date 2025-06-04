<?php
/**
 * Get Media API Endpoint
 * 
 * Serves media files (images/videos) with proper headers
 * Endpoint: GET /api/media/get_media?file={filename}
 */

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get the file parameter
$filename = $_GET['file'] ?? null;

if (!$filename) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'File parameter is required']);
    exit();
}

// Extract just the filename from a path if a full path is provided
// This handles cases where the database stores full paths like "media/images/filename.jpg"
$filename = basename($filename);

// After basename(), validate that there are no remaining path separators
if (strpos($filename, '..') !== false) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid file parameter - directory traversal detected']);
    exit();
}

// Determine file type and location based on the filename pattern
$fileType = 'images'; // default to images
if (strpos($filename, '_video_') !== false || 
    preg_match('/\.(mp4|avi|mov|webm)$/i', $filename)) {
    $fileType = 'videos';
}

// Construct possible file paths - check main directory first, then posts subdirectory
$basePath = dirname(dirname(dirname(dirname(__FILE__)))) . "/media/{$fileType}/";
$possiblePaths = [
    $basePath . $filename,                    // Main directory (e.g., /media/images/filename.jpg)
    $basePath . "posts/" . $filename,         // Posts subdirectory (e.g., /media/images/posts/filename.jpg)
    $basePath . "profile/" . $filename        // Profile subdirectory (e.g., /media/images/profile/filename.jpg)
];

// Debug information (remove in production)
$debugInfo = [
    'original_file_param' => $_GET['file'] ?? 'not set',
    'extracted_filename' => $filename,
    'base_path' => $basePath,
    'possible_paths' => $possiblePaths,
    'file_exists_checks' => []
];

$filePath = null;
foreach ($possiblePaths as $path) {
    $exists = file_exists($path);
    $debugInfo['file_exists_checks'][] = [
        'path' => $path,
        'exists' => $exists,
        'is_readable' => $exists ? is_readable($path) : false
    ];
    
    if ($exists) {
        $filePath = $path;
        break;
    }
}

// Check if file exists
if (!$filePath) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false, 
        'message' => 'File not found',
        'debug' => $debugInfo  // Include debug info to help troubleshoot
    ]);
    exit();
}

// Get file info
$fileInfo = pathinfo($filePath);
$fileExtension = strtolower($fileInfo['extension']);

// Set appropriate content type
$contentTypes = [
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'webp' => 'image/webp',
    'svg' => 'image/svg+xml',
    'mp4' => 'video/mp4',
    'avi' => 'video/x-msvideo',
    'mov' => 'video/quicktime',
    'webm' => 'video/webm'
];

$contentType = $contentTypes[$fileExtension] ?? 'application/octet-stream';

// Set headers
header('Content-Type: ' . $contentType);
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: public, max-age=31536000'); // Cache for 1 year
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');

// For images, add additional headers
if (strpos($contentType, 'image/') === 0) {
    header('Accept-Ranges: bytes');
}

// Output the file
readfile($filePath);
exit();
