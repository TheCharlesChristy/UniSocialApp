<?php

require_once 'api-handler.php';

/**
 * Media API Interface Class
 * 
 * Provides a comprehensive interface for all Media API endpoints
 * Inherits from the generic APIHandler class
 * 
 * Handles media file uploads and serving for profile pictures and post content
 * with automatic validation, file organization, and optimized delivery.
 * 
 * @author GitHub Copilot
 * @version 1.0
 */
class MediaAPI extends APIHandler {
    
    /**
     * Initialize Media API with media-specific base URL
     */
    public function __construct() {
        parent::__construct('/webdev/backend/src/api');
    }

    /**
     * Upload a media file (profile picture or post media)
     * 
     * Supports secure file uploads with validation, automatic file organization,
     * and database integration for profile pictures.
     * 
     * @param array $fileData The $_FILES array data for the uploaded file
     * @param string $type Upload type: 'profile_picture' or 'post_media'
     * @return array API response with file details
     * @throws Exception If upload fails or validation errors occur
     * 
     * File Restrictions:
     * - profile_picture: jpg, jpeg, png, gif (max 5MB)
     * - post_media: jpg, jpeg, png, gif, mp4, avi, mov (max 50MB)
     */
    public function uploadFile($fileData, $type) {
        // Validate upload type
        if (!in_array($type, ['profile_picture', 'post_media'])) {
            throw new Exception('Invalid upload type. Must be profile_picture or post_media');
        }

        // Prepare multipart form data
        $formData = [
            'type' => $type
        ];

        // Handle file data - convert PHP $_FILES format to cURL format
        if (isset($fileData['tmp_name']) && is_uploaded_file($fileData['tmp_name'])) {
            $formData['file'] = new CURLFile(
                $fileData['tmp_name'],
                $fileData['type'],
                $fileData['name']
            );
        } else {
            throw new Exception('No valid file uploaded');
        }

        return $this->authenticatedUpload('/media/upload', $formData);
    }

    /**
     * Upload a profile picture for the authenticated user
     * 
     * Convenience method that automatically sets the upload type to 'profile_picture'
     * and updates the user's profile in the database.
     * 
     * @param array $fileData The $_FILES array data for the uploaded image
     * @return array API response with file details
     * @throws Exception If upload fails or validation errors occur
     */
    public function uploadProfilePicture($fileData) {
        return $this->uploadFile($fileData, 'profile_picture');
    }

    /**
     * Upload media content for a post
     * 
     * Convenience method that automatically sets the upload type to 'post_media'
     * for images and videos to be used in posts.
     * 
     * @param array $fileData The $_FILES array data for the uploaded media
     * @return array API response with file details
     * @throws Exception If upload fails or validation errors occur
     */
    public function uploadPostMedia($fileData) {
        return $this->uploadFile($fileData, 'post_media');
    }

    /**
     * Get/serve a media file
     * 
     * Retrieves media files with proper content types and caching headers.
     * Automatically detects file location across multiple subdirectories.
     * 
     * @param string $filename Filename or relative path to the media file
     * @return mixed Raw file content with appropriate headers, or error response
     * @throws Exception If file not found or access denied
     * 
     * File Location Logic:
     * - Searches in /media/images/ or /media/videos/ (main directory)
     * - Falls back to /media/images/posts/ or /media/videos/posts/ (posts subdirectory)
     * - Falls back to /media/images/profile/ or /media/videos/profile/ (profile subdirectory)
     */
    public function getMedia($filename) {
        if (empty($filename)) {
            throw new Exception('Filename parameter is required');
        }

        $params = ['file' => $filename];
        
        return $this->get('/media/get_media', $params);
    }

    /**
     * Get a profile picture
     * 
     * Convenience method for retrieving profile pictures.
     * 
     * @param string $filename The profile picture filename
     * @return mixed Raw image content with appropriate headers
     * @throws Exception If file not found
     */
    public function getProfilePicture($filename) {
        return $this->getMedia($filename);
    }

    /**
     * Get post media content
     * 
     * Convenience method for retrieving post media files.
     * 
     * @param string $filename The media filename
     * @return mixed Raw media content with appropriate headers
     * @throws Exception If file not found
     */
    public function getPostMedia($filename) {
        return $this->getMedia($filename);
    }

    /**
     * Check if a media file exists
     * 
     * Utility method to verify if a media file exists without downloading it.
     * 
     * @param string $filename The filename to check
     * @return bool True if file exists and is accessible, false otherwise
     */
    public function mediaExists($filename) {
        try {
            $this->getMedia($filename);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get media file information
     * 
     * Retrieves metadata about a media file without downloading the content.
     * This method makes a HEAD request to get file information.
     * 
     * @param string $filename The filename to get information about
     * @return array File information including size, type, and headers
     * @throws Exception If file not found or access denied
     */
    public function getMediaInfo($filename) {
        if (empty($filename)) {
            throw new Exception('Filename parameter is required');
        }

        $params = ['file' => $filename];
        
        // Make a HEAD request to get file info without downloading content
        return $this->request('/media/get_media', [
            'method' => 'HEAD',
            'headers' => ['Content-Type' => 'application/json']
        ] + ['file' => $filename]);
    }

    /**
     * Upload file with authentication and proper error handling
     * 
     * Private method that handles authenticated file uploads with proper
     * multipart form data handling for the media upload endpoint.
     * 
     * @param string $endpoint The API endpoint path
     * @param array $formData Multipart form data including file
     * @return array API response
     * @throws Exception If authentication fails or upload error occurs
     */
    private function authenticatedUpload($endpoint, $formData) {
        $token = $this->getAuthToken();
        if (!$token) {
            throw new Exception('No authentication token found');
        }

        // For file uploads, we need to handle headers differently
        $headers = [
            'Authorization' => "Bearer $token"
            // Note: Content-Type will be set automatically by cURL for multipart data
        ];

        return $this->request($endpoint, [
            'method' => 'POST',
            'headers' => $headers,
            'body' => $formData
        ]);
    }

    /**
     * Validate file before upload
     * 
     * Client-side validation helper to check file constraints before upload.
     * This can help prevent unnecessary API calls for invalid files.
     * 
     * @param array $fileData The $_FILES array data
     * @param string $type Upload type ('profile_picture' or 'post_media')
     * @return array Validation result with success status and message
     */
    public function validateFile($fileData, $type) {
        $constraints = [
            'profile_picture' => [
                'extensions' => ['jpg', 'jpeg', 'png', 'gif'],
                'mime_types' => ['image/jpeg', 'image/png', 'image/gif'],
                'max_size' => 5 * 1024 * 1024, // 5MB
            ],
            'post_media' => [
                'extensions' => ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'avi', 'mov'],
                'mime_types' => ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/avi', 'video/quicktime'],
                'max_size' => 50 * 1024 * 1024, // 50MB
            ]
        ];

        if (!isset($constraints[$type])) {
            return ['success' => false, 'message' => 'Invalid upload type'];
        }

        $config = $constraints[$type];

        // Check if file exists
        if (!isset($fileData['tmp_name']) || !is_uploaded_file($fileData['tmp_name'])) {
            return ['success' => false, 'message' => 'No file uploaded'];
        }

        // Check file extension
        $extension = strtolower(pathinfo($fileData['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $config['extensions'])) {
            return [
                'success' => false, 
                'message' => 'Invalid file type. Allowed: ' . implode(', ', $config['extensions'])
            ];
        }

        // Check file size
        if ($fileData['size'] > $config['max_size']) {
            return [
                'success' => false,
                'message' => 'File size too large. Maximum allowed: ' . $this->formatBytes($config['max_size']),
                'uploaded_size' => $this->formatBytes($fileData['size']),
                'max_size' => $this->formatBytes($config['max_size'])
            ];
        }

        return ['success' => true, 'message' => 'File validation passed'];
    }

    /**
     * Format bytes to human readable format
     * 
     * Utility method to convert file sizes to readable format.
     * 
     * @param int $bytes File size in bytes
     * @param int $precision Number of decimal places
     * @return string Formatted file size (e.g., "2.5 MB")
     */
    private function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    public function formatMediaUrl($filename) {
        if (empty($filename)) {
            return '';
        }

        // Extract the path from 'media/' onwards
        $media_pos = strpos($filename, 'media/');
        if ($media_pos !== false) {
            $filename = substr($filename, $media_pos);
        }

        // Add ../../backend/ to the path
        $filename = '../../backend/' . $filename;

        return $filename;

    }
}
