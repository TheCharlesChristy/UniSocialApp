<?php

require_once __DIR__ . '/../api-library.php';
require_once __DIR__ . '/../component-loader.php';

/**
 * FeedPost Builder Class
 * 
 * This class handles building feed post components by fetching post and user data
 * through the API library and processing them for display.
 */
class FeedPost {
    
    /**
     * API Library instance for accessing posts and users APIs
     * 
     * @var APILibrary
     * @var ComponentLoader
     */
    private $apiLibrary;
    private $componentLoader;

    /**
     * Constructor - initializes the API library
     */
    public function __construct() {
        $this->apiLibrary = new APILibrary();
        $this->componentLoader = new ComponentLoader();
    }

    /**
     * Public method to build a feed post component
     * 
     * @param int $postId The ID of the post to build
     * @return void
     */
    public function build($postId) {
        try {
            // Get post details from the posts API
            $postResponse = $this->apiLibrary->postsAPI->getPost($postId);
            
            if (!$postResponse['success']) {
                echo "Error: Failed to fetch post details - " . $postResponse['message'];
                return;
            }
            
            $postData = $postResponse['post'];
            
            // Get user details from the users API using the post's user_id
            $userResponse = $this->apiLibrary->usersAPI->getUser($postData['user_id']);
            
            if (!$userResponse['success']) {
                echo "Error: Failed to fetch user details - " . $userResponse['message'];
                return;
            }
            
            $userData = $userResponse['user'];
            
            // Pass both post and user data to the private build method
            $this->buildPost($postData, $userData);
            
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    /**
     * Private method that processes and displays the post and user details
     * 
     * @param array $postData Post information from the API
     * @param array $userData User information from the API
     * @return void
     */
    private function buildPost($postData, $userData) {
        echo "=== FEED POST DETAILS ===\n\n";
        
        echo "POST INFORMATION:\n";
        echo "- Post ID: " . $postData['post_id'] . "\n";
        echo "- Caption: " . $postData['caption'] . "\n";
        echo "- Post Type: " . $postData['post_type'] . "\n";
        echo "- Privacy Level: " . $postData['privacy_level'] . "\n";
        echo "- Created At: " . $postData['created_at'] . "\n";
        echo "- Likes Count: " . $postData['likes_count'] . "\n";
        echo "- Comments Count: " . $postData['comments_count'] . "\n";
        
        if (!empty($postData['media_url'])) {
            echo "- Media URL: " . $postData['media_url'] . "\n";
        }
        
        if (!empty($postData['location_name'])) {
            echo "- Location: " . $postData['location_name'] . "\n";
        }
        
        echo "\nUSER INFORMATION:\n";
        echo "- User ID: " . $userData['user_id'] . "\n";
        echo "- Username: " . $userData['username'] . "\n";
        echo "- Full Name: " . $userData['first_name'] . " " . $userData['last_name'] . "\n";
        
        if (!empty($userData['profile_picture'])) {
            echo "- Profile Picture: " . $userData['profile_picture'] . "\n";
        }
        
        if (!empty($userData['bio'])) {
            echo "- Bio: " . $userData['bio'] . "\n";
        }
        
        echo "\n=== END FEED POST ===\n";
        // Create the user banner component
        $this->createUserBanner($userData);
    }    
    
    private function createUserBanner($userData) {
        // Use the component loader to create a user banner component
        $profile_picture = $userData['profile_picture'] ?? 'default_profile_picture.png';
        
        // Extract the path from 'media/' onwards
        $media_pos = strpos($profile_picture, 'media/');
        if ($media_pos !== false) {
            $profile_picture = substr($profile_picture, $media_pos);
        }

        // Add ../../backend/ to the path
        $profile_picture = '../../backend/' . $profile_picture;

        $html = $this->componentLoader->getComponentWithVars('user_banner', [
            'user_id' => $userData['user_id'],
            'user_name' => $userData['username'],
            'profile_picture' => $profile_picture,
            'full_name' => $userData['first_name'] . ' ' . $userData['last_name'],
            'bio' => $userData['bio']
        ]);
        if ($html === null) {
            echo "Error: Failed to load user banner component.";
            return;
        }
        echo $html;
    }
}