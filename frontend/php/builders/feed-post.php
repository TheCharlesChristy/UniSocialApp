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
            
            // Pass both post and user data to the private build method
            $this->buildPost($postData);
            
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
    private function buildPost($postData) {
        // Get user data from the API
        $userData = $this->apiLibrary->usersAPI->getUser($postData['user_id']);
        if (!$userData['success']) {
            echo "Error: Failed to fetch user details - " . $userData['message'];
            return;
        }
        $userData = $userData['user'];

        // Load Components for the post
        $userbanner = $this->createUserBanner($userData);

        $post_location = $this->createLocationBanner($postData);

        $post_media = $postData['post_type'] !== 'text' ? $this->createPostMedia($postData) : '';
        
        $post_stats = $this->createPostStats($postData);

        $post_caption = $this->createPostCaption($postData, $userData);

        $post_view_comments = $this->createPostViewComments($postData);

        $add_comment = $this->createAddComment($postData);
        if ($add_comment === null) {
            echo "Error: Failed to load add comment component.";
            return;
        }
        
        // Wrap the stats and caption in a container
        $post_stats_and_caption = $this->componentLoader->getComponentInsertHtml('post_stats_and_caption', [
            'post_stats' => $post_stats,
            'post_caption' => $post_caption,
            'post_view_comments' => $post_view_comments
        ]);

        // Wrap everything in a post container
        $post_container = $this->componentLoader->getComponentInsertHtml('post_container', [
            'user_banner' => $userbanner,
            'post_location' => $post_location,
            'post_media' => $post_media,
            'post_stats_and_caption' => $post_stats_and_caption,
            'add_comment' => $add_comment,
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
        $profile_picture = $userData['profile_picture'] ?? 'default-profile.svg';

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
        return $html;    }    private function createPostViewComments($postData) {
        // Get the comments for the post
        $commentsResponse = $this->apiLibrary->postsAPI->getComments($postData['post_id']);
        if (!$commentsResponse['success']) {
            return "Error: Failed to fetch comments - " . $commentsResponse['message'];
        }        $comments = $commentsResponse['comments'];

        // Use the component loader to create a post view comments component
        $commentsHtml = '';
        foreach ($comments as $comment) {
            $commentsHtml .= $this->renderSingleComment($comment, $postData['post_id']);
        }
        
        // Wrap comments in a container with proper classes for dynamic insertion
        return '<div class="comments-list" data-post-id="' . $postData['post_id'] . '">' . $commentsHtml . '</div>';
    }    /**
     * Recursively render a single comment and its children
     * 
     * @param array $comment Comment data from API
     * @param int $postId The ID of the post this comment belongs to
     * @return string HTML for the comment and its children
     */
    private function renderSingleComment($comment, $postId = null) {
        // Validate input parameters
        if (empty($comment) || !is_array($comment)) {
            error_log("Invalid comment data provided to renderSingleComment");
            return "<!-- Invalid comment data -->";
        }
        
        // If postId is not provided, try to get it from the comment
        if ($postId === null) {
            $postId = $comment['post_id'] ?? null;
        }
        
        // Validate that we have a valid post ID
        if (empty($postId)) {
            error_log("No valid post_id found for comment: " . ($comment['comment_id'] ?? 'unknown'));
            return "<!-- Missing post ID for comment -->";
        }
        
        // Format the comment data
        $comment['profile_picture'] = $this->apiLibrary->mediaAPI->formatMediaUrl($comment['profile_picture']);
          // Process children comments recursively
        $childrenHtml = '';
        if (!empty($comment['replies']) && is_array($comment['replies'])) {
            foreach ($comment['replies'] as $childComment) {
                $childrenHtml .= $this->renderSingleComment($childComment, $postId);
            }
        }
        
        // Generate "Load more replies" button if needed
        $loadMoreButton = '';
        $hasMoreReplies = $comment['has_more_replies'] ?? false;
        $replyCount = $comment['reply_count'] ?? 0;
        $loadedReplies = $comment['loaded_replies'] ?? 0;
        
        if ($hasMoreReplies && $replyCount > $loadedReplies) {
            $remainingReplies = $replyCount - $loadedReplies;
            $loadMoreButton = $this->generateLoadMoreButton($comment['comment_id'], $remainingReplies, $postId);
        }
        
        // Create the comment HTML with children
        try {            $commentHtml = $this->componentLoader->getComponentInsertHtml('comment_view', [
                'comment_id' => $comment['comment_id'] ?? 0,
                'user_name' => $comment['username'] ?? 'Unknown User',
                'user_id' => $comment['user_id'] ?? 0,
                'user_profile_picture_url' => $comment['profile_picture'] ?? '../assets/images/default-profile.svg',
                'comment_content' => $comment['content'] ?? '',
                'created_at' => $comment['created_at'] ?? '',
                'comment_likes' => $comment['likes_count'] ?? 0,
                'has_liked' => $comment['user_has_liked'] ?? 0,
                'children_comments' => $childrenHtml,
                'load_more_replies_button' => $loadMoreButton,
                'post_id' => $postId
            ]);
            
            return $commentHtml;
        } catch (Exception $e) {
            error_log("Error rendering comment component: " . $e->getMessage());
            return "<!-- Error rendering comment -->";
        }
    }
      /**
     * Generate the "Load more replies" button HTML
     * 
     * @param int $commentId The ID of the parent comment
     * @param int $remainingReplies Number of remaining replies to load
     * @param int $postId The ID of the post
     * @return string HTML for the load more button
     */
    private function generateLoadMoreButton($commentId, $remainingReplies, $postId) {
        return '
        <button class="load-more-replies" 
                onclick="loadMoreReplies(' . $commentId . ', ' . $postId . ')"
                data-comment-id="' . $commentId . '"
                data-post-id="' . $postId . '"
                data-page="1"
                aria-label="Load more replies for this comment">
            <svg class="loading-spinner" width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                <path d="M12 2v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <span class="button-text">Load more replies</span>
        </button>';
    }

    private function createAddComment($postData) {
        // Get the current user data
        $currentUser = $this->apiLibrary->usersAPI->getMyProfile();
        if (!$currentUser['success']) {
            return "Error: Failed to fetch current user data - " . $currentUser['message'];
        }

        $currentUser = $currentUser['user'];

        $profile_picture = $currentUser['profile_picture'] ?? 'default-profile.svg';
        $profile_picture = $this->apiLibrary->mediaAPI->formatMediaUrl($profile_picture);

        // Use the component loader to create an add comment component
        $html = $this->componentLoader->getComponentWithVars('add_comment', [
            'post_id' => $postData['post_id'],
            'current_user_profile_picture' => $profile_picture
        ]);

        return $html;
    }

    private function createLocationBanner($postData) {
        // Use the component loader to create a location component
        if (empty($postData['location_lat']) || empty($postData['location_lng'])) {
            return '';
        }

        $html = $this->componentLoader->getComponentWithVars('post_location', [
            'post_id' => $postData['post_id'],
            'latitude' => $postData['location_lat'] ?? '',
            'longitude' => $postData['location_lng'] ?? ''
        ]);

        if ($html === null) {
            echo "Error: Failed to load post location component.";
            return '';
        }

        return $html;
    }
}