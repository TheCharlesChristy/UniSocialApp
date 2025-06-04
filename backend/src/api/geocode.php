<?php
// filepath: c:\xampp\htdocs\webdev\backend\src\api\geocode.php
/**
 * Geocoding API Proxy
 * This file acts as a secure proxy for Google Maps API requests
 */

header('Content-Type: application/json');

// Define secure access constant to allow loading the API keys file
define('SECURE_ACCESS', true);

// Load API keys
$api_keys = include '../config/api_keys.php';

// Check if latitude and longitude are provided
if (!isset($_GET['lat']) || !isset($_GET['lng'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing latitude or longitude parameters']);
    exit;
}

// Validate coordinates
$latitude = filter_var($_GET['lat'], FILTER_VALIDATE_FLOAT);
$longitude = filter_var($_GET['lng'], FILTER_VALIDATE_FLOAT);

if ($latitude === false || $longitude === false || 
    $latitude < -90 || $latitude > 90 || 
    $longitude < -180 || $longitude > 180) {
    
    http_response_code(400);
    echo json_encode(['error' => 'Invalid coordinates']);
    exit;
}

// Get Google Maps API key
$api_key = $api_keys['google_maps'];

if (empty($api_key) || $api_key === 'YOUR_GOOGLE_MAPS_API_KEY') {
    http_response_code(500);
    echo json_encode(['error' => 'API key not configured']);
    exit;
}

// Build the Google Maps Geocoding API URL
$url = "https://maps.googleapis.com/maps/api/geocode/json?latlng={$latitude},{$longitude}&key={$api_key}";

// Make the request to Google Maps API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Forward the response from Google
if ($status === 200) {
    echo $response;
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to get location data']);
}
?>
