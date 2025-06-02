<?php
// filepath: c:\xampp\htdocs\webdev\backend\src\config\api_keys.php
/**
 * Secure storage for API keys
 * This file should not be accessible from the web
 */

// Ensure this file cannot be accessed directly
if (!defined('SECURE_ACCESS')) {
    header('HTTP/1.0 403 Forbidden');
    exit('Direct access to this file is forbidden');
}

// API Keys
return [
    'google_maps' => 'YOUR_GOOGLE_MAPS_API_KEY'
];
?>
