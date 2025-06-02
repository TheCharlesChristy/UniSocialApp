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
        // Load Components for the post
        $userbanner = $this->createUserBanner($userData);

        $post_media = $postData['post_type'] !== 'text' ? $this->createPostMedia($postData) : '';
        
        $post_stats = $this->createPostStats($postData);

        $post_caption = $this->createPostCaption($postData, $userData);

        $post_view_comments = $this->createPostViewComments($postData);        // Wrap the stats and caption in a container
        $post_stats_and_caption = $this->componentLoader->getComponentInsertHtml('post_stats_and_caption', [
            'post_stats' => $post_stats,
            'post_caption' => $post_caption,
            'post_view_comments' => $post_view_comments
        ]);

        // Wrap everything in a post container
        $post_container = $this->componentLoader->getComponentInsertHtml('post_container', [
            'user_banner' => $userbanner,
            'post_media' => $post_media,
            'post_stats_and_caption' => $post_stats_and_caption,
        ]);

        // Output the final post HTML
        if ($post_container === null) {
            echo "Error: Failed to load post container component.";
            return;
        }
        echo $post_container;
    }
    
    private function createUserBanner($userData) {
        // Use the component loader to create a user banner component
        $profile_picture = $userData['profile_picture'] ?? 'default_profile_picture.png';

        $profile_picture = $this->apiLibrary->mediaAPI->formatMediaUrl($profile_picture);

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
        return $html;
    }

    private function createPostMedia($postData) {
        // Use the component loader to create a post media component
        $media_url = $postData['media_url'] ?? 'default_media.png';
        $media_url = $this->apiLibrary->mediaAPI->formatMediaUrl($media_url);

        $html = $this->componentLoader->getComponentWithVars('post_media', [
            'media_src_url' => $media_url,
        ]);
        
        if ($html === null) {
            echo "Error: Failed to load post media component.";
            return;
        }
        return $html;
    }

    private function createPostStats($postData) {
        // Use the component loader to create a post stats component
        $html = $this->componentLoader->getComponentWithVars('post_stats', [
            'post_id' => $postData['post_id'],
            'like_count' => $postData['likes_count'],
            'comment_count' => $postData['comments_count'],
            'created_at' => $postData['created_at'],
            'user_has_liked' => $this->apiLibrary->postsAPI->hasLiked($postData['post_id'])
        ]);
        
        if ($html === null) {
            echo "Error: Failed to load post stats component.";
            return;
        }
        return $html;
    }

    private function createPostCaption($postData, $userData) {
        // Use the component loader to create a post caption component
        $html = $this->componentLoader->getComponentWithVars('post_caption', [
            'caption' => $postData['caption'],
            'user_name' => $userData['username'],
        ]);
        
        if ($html === null) {
            echo "Error: Failed to load post caption component.";
            return;
        }
        return $html;
    }

    private function createPostViewComments($postData) {
        // Use the component loader to create a post view comments component
        $html = $this->componentLoader->getComponentWithVars('view_post_comments', [
            'post_id' => $postData['post_id'],
        ]);
        
        if ($html === null) {
            echo "Error: Failed to load post view comments component.";
            return;
        }
        return $html;
    }
}