<?php

require_once 'api-handler.php';

class PostsAPI extends APIHandler {
    
    public function __construct() {
        // Initialize with posts-specific base URL
        parent::__construct('/webdev/backend/src/api/posts');
    }

    /**
     * Create a new text post
     * 
     * @param string $postType Type of post: text, photo, or video
     * @param string $privacyLevel Privacy setting: public, friends, or private
     * @param string $caption Post caption (required for text posts, optional for media)
     * @param string|null $locationName Location name (optional)
     * @param float|null $locationLat Latitude (-90 to 90) (optional)
     * @param float|null $locationLng Longitude (-180 to 180) (optional)
     * @return array Response data
     * @throws Exception If creation fails
     */
    public function createTextPost($postType, $privacyLevel, $caption, $locationName = null, $locationLat = null, $locationLng = null) {
        $data = [
            'post_type' => $postType,
            'privacy_level' => $privacyLevel,
            'caption' => $caption
        ];

        if ($locationName !== null) {
            $data['location_name'] = $locationName;
        }
        if ($locationLat !== null) {
            $data['location_lat'] = $locationLat;
        }
        if ($locationLng !== null) {
            $data['location_lng'] = $locationLng;
        }

        return $this->authenticatedRequest('/create_post', [
            'method' => 'POST',
            'body' => json_encode($data)
        ]);
    }

    /**
     * Create a new media post (photo or video)
     * 
     * @param string $postType Type of post: photo or video
     * @param string $privacyLevel Privacy setting: public, friends, or private
     * @param array $mediaFile File data array with 'name', 'type', 'tmp_name', 'size'
     * @param string|null $caption Post caption (optional for media posts)
     * @param string|null $locationName Location name (optional)
     * @param float|null $locationLat Latitude (-90 to 90) (optional)
     * @param float|null $locationLng Longitude (-180 to 180) (optional)
     * @return array Response data
     * @throws Exception If creation fails
     */
    public function createMediaPost($postType, $privacyLevel, $mediaFile, $caption = null, $locationName = null, $locationLat = null, $locationLng = null) {
        // Prepare multipart form data
        $formData = [
            'post_type' => $postType,
            'privacy_level' => $privacyLevel,
            'media' => new CURLFile($mediaFile['tmp_name'], $mediaFile['type'], $mediaFile['name'])
        ];

        if ($caption !== null) {
            $formData['caption'] = $caption;
        }
        if ($locationName !== null) {
            $formData['location_name'] = $locationName;
        }
        if ($locationLat !== null) {
            $formData['location_lat'] = $locationLat;
        }
        if ($locationLng !== null) {
            $formData['location_lng'] = $locationLng;
        }

        $token = $this->getAuthToken();
        if (!$token) {
            throw new Exception('No authentication token found');
        }

        return $this->request('/create_post', [
            'method' => 'POST',
            'headers' => [
                'Authorization' => "Bearer $token"
            ],
            'body' => $formData
        ]);
    }

    /**
     * Get a specific post by ID
     * 
     * @param int $postId Post ID
     * @return array Response data
     * @throws Exception If request fails
     */
    public function getPost($postId) {
        return $this->authenticatedRequest("/get_post?id=$postId", [
            'method' => 'GET'
        ]);
    }

    /**
     * Update an existing post
     * 
     * @param int $postId Post ID to update
     * @param string|null $caption New caption (optional)
     * @param string|null $privacyLevel New privacy level (optional)
     * @param string|null $locationName New location name (optional)
     * @param float|null $locationLat New latitude (optional)
     * @param float|null $locationLng New longitude (optional)
     * @return array Response data
     * @throws Exception If update fails
     */
    public function updatePost($postId, $caption = null, $privacyLevel = null, $locationName = null, $locationLat = null, $locationLng = null) {
        $data = [
            'post_id' => $postId
        ];

        if ($caption !== null) {
            $data['caption'] = $caption;
        }
        if ($privacyLevel !== null) {
            $data['privacy_level'] = $privacyLevel;
        }
        if ($locationName !== null) {
            $data['location_name'] = $locationName;
        }
        if ($locationLat !== null) {
            $data['location_lat'] = $locationLat;
        }
        if ($locationLng !== null) {
            $data['location_lng'] = $locationLng;
        }

        return $this->authenticatedRequest('/update_post', [
            'method' => 'PUT',
            'body' => json_encode($data)
        ]);
    }

    /**
     * Delete a post
     * 
     * @param int $postId Post ID to delete
     * @return array Response data
     * @throws Exception If deletion fails
     */
    public function deletePost($postId) {
        $data = [
            'post_id' => $postId
        ];

        return $this->authenticatedRequest('/delete_post', [
            'method' => 'DELETE',
            'body' => json_encode($data)
        ]);
    }

    /**
     * Add a comment to a post
     * 
     * @param int $postId Target post ID
     * @param string $content Comment text (max 1000 chars)
     * @param int|null $parentCommentId Parent comment ID for replies (optional)
     * @return array Response data
     * @throws Exception If comment fails
     */
    public function addComment($postId, $content, $parentCommentId = null) {
        $data = [
            'post_id' => $postId,
            'content' => $content
        ];

        if ($parentCommentId !== null) {
            $data['parent_comment_id'] = $parentCommentId;
        }

        return $this->authenticatedRequest('/add_comment', [
            'method' => 'POST',
            'body' => json_encode($data)
        ]);
    }

    /**
     * Get comments for a post
     * 
     * @param int $postId Post ID to get comments for
     * @param int $page Page number (default: 1)
     * @param int $limit Comments per page (1-50, default: 20)
     * @return array Response data
     * @throws Exception If request fails
     */
    public function getComments($postId, $page = 1, $limit = 20) {
        $params = [
            'post_id' => $postId,
            'page' => $page,
            'limit' => $limit
        ];

        $queryString = http_build_query($params);
        return $this->authenticatedRequest("/get_comments?$queryString", [
            'method' => 'GET'
        ]);
    }

    /**
     * Update a comment
     * 
     * @param int $commentId Comment ID to update
     * @param string $content New comment content (max 1000 chars)
     * @return array Response data
     * @throws Exception If update fails
     */
    public function updateComment($commentId, $content) {
        $data = [
            'comment_id' => $commentId,
            'content' => $content
        ];

        return $this->authenticatedRequest('/update_comment', [
            'method' => 'PUT',
            'body' => json_encode($data)
        ]);
    }

    /**
     * Delete a comment
     * 
     * @param int $commentId Comment ID to delete
     * @return array Response data
     * @throws Exception If deletion fails
     */
    public function deleteComment($commentId) {
        $data = [
            'comment_id' => $commentId
        ];

        return $this->authenticatedRequest('/delete_comment', [
            'method' => 'DELETE',
            'body' => json_encode($data)
        ]);
    }

    /**
     * Like a post
     * 
     * @param int $postId Post ID to like
     * @return array Response data
     * @throws Exception If like fails
     */
    public function likePost($postId) {
        $data = [
            'post_id' => $postId
        ];

        return $this->authenticatedRequest('/like_post', [
            'method' => 'POST',
            'body' => json_encode($data)
        ]);
    }

    /**
     * Unlike a post
     * 
     * @param int $postId Post ID to unlike
     * @return array Response data
     * @throws Exception If unlike fails
     */
    public function unlikePost($postId) {
        $data = [
            'post_id' => $postId
        ];

        return $this->authenticatedRequest('/unlike_post', [
            'method' => 'DELETE',
            'body' => json_encode($data)
        ]);
    }

    /**
     * Like a comment
     * 
     * @param int $commentId Comment ID to like
     * @return array Response data
     * @throws Exception If like fails
     */
    public function likeComment($commentId) {
        $data = [
            'comment_id' => $commentId
        ];

        return $this->authenticatedRequest('/like_comment', [
            'method' => 'POST',
            'body' => json_encode($data)
        ]);
    }

    /**
     * Unlike a comment
     * 
     * @param int $commentId Comment ID to unlike
     * @return array Response data
     * @throws Exception If unlike fails
     */
    public function unlikeComment($commentId) {
        $data = [
            'comment_id' => $commentId
        ];

        return $this->authenticatedRequest('/unlike_comment', [
            'method' => 'DELETE',
            'body' => json_encode($data)
        ]);
    }

    /**
     * Get users who liked a post
     * 
     * @param int $postId Post ID
     * @param int $page Page number (default: 1)
     * @param int $limit Users per page (1-50, default: 20)
     * @return array Response data
     * @throws Exception If request fails
     */
    public function getPostLikes($postId, $page = 1, $limit = 20) {
        $params = [
            'post_id' => $postId,
            'page' => $page,
            'limit' => $limit
        ];

        $queryString = http_build_query($params);
        return $this->authenticatedRequest("/get_post_likes?$queryString", [
            'method' => 'GET'
        ]);
    }

    /**
     * Get users who liked a comment
     * 
     * @param int $commentId Comment ID
     * @param int $page Page number (default: 1)
     * @param int $limit Users per page (1-50, default: 20)
     * @return array Response data
     * @throws Exception If request fails
     */
    public function getCommentLikes($commentId, $page = 1, $limit = 20) {
        $params = [
            'comment_id' => $commentId,
            'page' => $page,
            'limit' => $limit
        ];

        $queryString = http_build_query($params);
        return $this->authenticatedRequest("/get_comment_likes?$queryString", [
            'method' => 'GET'
        ]);
    }

    /**
     * Get personalized feed
     * 
     * @param int $page Page number (default: 1)
     * @param int $limit Posts per page (1-50, default: 10)
     * @param string|null $filter Search filter for caption/location (optional)
     * @return array Response data
     * @throws Exception If request fails
     */
    public function getFeed($page = 1, $limit = 10, $filter = null) {
        $params = [
            'page' => $page,
            'limit' => $limit
        ];

        if ($filter !== null) {
            $params['filter'] = $filter;
        }

        $queryString = http_build_query($params);
        return $this->authenticatedRequest("/get_feed?$queryString", [
            'method' => 'GET'
        ]);
    }

    /**
     * Search posts by caption and location content
     * 
     * @param string $query Search query
     * @param int $page Page number (default: 1)
     * @param int $limit Posts per page (1-50, default: 10)
     * @return array Response data
     * @throws Exception If request fails
     */
    public function searchPosts($query, $page = 1, $limit = 10) {
        $params = [
            'q' => $query,
            'page' => $page,
            'limit' => $limit
        ];

        $queryString = http_build_query($params);
        return $this->authenticatedRequest("/search_posts?$queryString", [
            'method' => 'GET'
        ]);
    }

    // Convenience methods for common operations

    /**
     * Create a simple text post with public privacy
     * 
     * @param string $caption Post caption
     * @return array Response data
     * @throws Exception If creation fails
     */
    public function createSimpleTextPost($caption) {
        return $this->createTextPost('text', 'public', $caption);
    }

    /**
     * Create a simple photo post with public privacy
     * 
     * @param array $mediaFile File data array
     * @param string|null $caption Post caption (optional)
     * @return array Response data
     * @throws Exception If creation fails
     */
    public function createSimplePhotoPost($mediaFile, $caption = null) {
        return $this->createMediaPost('photo', 'public', $mediaFile, $caption);
    }

    /**
     * Create a simple video post with public privacy
     * 
     * @param array $mediaFile File data array
     * @param string|null $caption Post caption (optional)
     * @return array Response data
     * @throws Exception If creation fails
     */
    public function createSimpleVideoPost($mediaFile, $caption = null) {
        return $this->createMediaPost('video', 'public', $mediaFile, $caption);
    }

    /**
     * Toggle like on a post (like if not liked, unlike if already liked)
     * 
     * @param int $postId Post ID
     * @param bool $currentlyLiked Whether the post is currently liked by the user
     * @return array Response data
     * @throws Exception If toggle fails
     */
    public function togglePostLike($postId, $currentlyLiked) {
        if ($currentlyLiked) {
            return $this->unlikePost($postId);
        } else {
            return $this->likePost($postId);
        }
    }

    /**
     * Toggle like on a comment (like if not liked, unlike if already liked)
     * 
     * @param int $commentId Comment ID
     * @param bool $currentlyLiked Whether the comment is currently liked by the user
     * @return array Response data
     * @throws Exception If toggle fails
     */
    public function toggleCommentLike($commentId, $currentlyLiked) {
        if ($currentlyLiked) {
            return $this->unlikeComment($commentId);
        } else {
            return $this->likeComment($commentId);
        }
    }

    /**
     * Reply to a comment
     * 
     * @param int $parentCommentId Parent comment ID
     * @param int $postId Post ID where the comment belongs
     * @param string $content Reply content
     * @return array Response data
     * @throws Exception If reply fails
     */
    public function replyToComment($parentCommentId, $postId, $content) {
        return $this->addComment($postId, $content, $parentCommentId);
    }
}
