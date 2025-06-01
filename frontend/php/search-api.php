<?php

require_once 'api-handler.php';

/**
 * Search API Handler
 * 
 * Provides interface for all search-related API endpoints
 * Extends the generic APIHandler class
 */
class SearchAPI extends APIHandler {
    
    /**
     * Perform global search across users and posts
     * 
     * @param string $query Search query string
     * @param string $type Search type: 'all', 'users', or 'posts' (default: 'all')
     * @param int $page Page number for pagination (default: 1)
     * @param int $limit Results per page, max 50 (default: 10)
     * @return array API response
     * @throws Exception if request fails
     */
    public function globalSearch($query, $type = 'all', $page = 1, $limit = 10) {
        if (empty($query)) {
            throw new Exception('Search query is required');
        }

        $params = [
            'q' => $query,
            'type' => $type,
            'page' => $page,
            'limit' => min($limit, 50) // Enforce max limit
        ];

        return $this->authenticatedRequest('/search/index', [
            'method' => 'GET',
            'headers' => ['Authorization' => 'Bearer ' . $this->getAuthToken()]
        ]);
    }

    /**
     * Search for users with advanced filtering
     * 
     * @param string $query Search query (name, username, or email)
     * @param array $options Optional parameters:
     *   - page: Page number (default: 1)
     *   - limit: Results per page, max 50 (default: 10)
     *   - role: Filter by user role ('user', 'admin', 'moderator')
     *   - status: Account status ('active', 'suspended', 'all') (default: 'active')
     * @return array API response
     * @throws Exception if request fails
     */
    public function searchUsers($query, $options = []) {
        if (empty($query)) {
            throw new Exception('Search query is required');
        }

        $params = [
            'q' => $query,
            'page' => $options['page'] ?? 1,
            'limit' => min($options['limit'] ?? 10, 50),
            'status' => $options['status'] ?? 'active'
        ];

        // Add optional filters
        if (!empty($options['role'])) {
            $params['role'] = $options['role'];
        }

        return $this->get('/search/users', $params);
    }

    /**
     * Search for posts with comprehensive filtering
     * 
     * @param array $searchParams Search parameters (at least one of q, location, or author required):
     *   - q: Search query for post content
     *   - location: Search query for location names
     *   - author: Filter by author username
     * @param array $options Optional parameters:
     *   - page: Page number (default: 1)
     *   - limit: Results per page, max 50 (default: 10)
     *   - post_type: Filter by type ('photo', 'video', 'text')
     *   - privacy: Filter by privacy ('public', 'friends')
     *   - date_from: Start date filter (YYYY-MM-DD)
     *   - date_to: End date filter (YYYY-MM-DD)
     *   - sort_by: Sort order ('relevance', 'date', 'likes', 'comments') (default: 'relevance')
     * @return array API response
     * @throws Exception if request fails
     */
    public function searchPosts($searchParams = [], $options = []) {
        // Validate that at least one search parameter is provided
        if (empty($searchParams['q']) && empty($searchParams['location']) && empty($searchParams['author'])) {
            throw new Exception('At least one of q, location, or author is required');
        }

        $params = [
            'page' => $options['page'] ?? 1,
            'limit' => min($options['limit'] ?? 10, 50),
            'sort_by' => $options['sort_by'] ?? 'relevance'
        ];

        // Add search parameters
        if (!empty($searchParams['q'])) {
            $params['q'] = $searchParams['q'];
        }
        if (!empty($searchParams['location'])) {
            $params['location'] = $searchParams['location'];
        }
        if (!empty($searchParams['author'])) {
            $params['author'] = $searchParams['author'];
        }

        // Add optional filters
        if (!empty($options['post_type'])) {
            $params['post_type'] = $options['post_type'];
        }
        if (!empty($options['privacy'])) {
            $params['privacy'] = $options['privacy'];
        }
        if (!empty($options['date_from'])) {
            $params['date_from'] = $options['date_from'];
        }
        if (!empty($options['date_to'])) {
            $params['date_to'] = $options['date_to'];
        }

        return $this->get('/search/posts', $params);
    }

    /**
     * Quick search for users only
     * 
     * @param string $query Search query
     * @param int $limit Number of results (default: 5)
     * @return array API response
     */
    public function quickUserSearch($query, $limit = 5) {
        return $this->globalSearch($query, 'users', 1, $limit);
    }

    /**
     * Quick search for posts only
     * 
     * @param string $query Search query
     * @param int $limit Number of results (default: 5)
     * @return array API response
     */
    public function quickPostSearch($query, $limit = 5) {
        return $this->globalSearch($query, 'posts', 1, $limit);
    }

    /**
     * Search posts by location
     * 
     * @param string $location Location query
     * @param array $options Optional parameters (same as searchPosts)
     * @return array API response
     */
    public function searchPostsByLocation($location, $options = []) {
        return $this->searchPosts(['location' => $location], $options);
    }

    /**
     * Search posts by author
     * 
     * @param string $author Author username
     * @param array $options Optional parameters (same as searchPosts)
     * @return array API response
     */
    public function searchPostsByAuthor($author, $options = []) {
        return $this->searchPosts(['author' => $author], $options);
    }

    /**
     * Search for active users only
     * 
     * @param string $query Search query
     * @param array $options Optional parameters (same as searchUsers)
     * @return array API response
     */
    public function searchActiveUsers($query, $options = []) {
        $options['status'] = 'active';
        return $this->searchUsers($query, $options);
    }

    /**
     * Search for users by role
     * 
     * @param string $query Search query
     * @param string $role User role ('user', 'admin', 'moderator')
     * @param array $options Optional parameters (same as searchUsers)
     * @return array API response
     */
    public function searchUsersByRole($query, $role, $options = []) {
        $options['role'] = $role;
        return $this->searchUsers($query, $options);
    }

    /**
     * Search for recent posts
     * 
     * @param string $query Search query
     * @param int $days Number of days back to search (default: 7)
     * @param array $options Optional parameters (same as searchPosts)
     * @return array API response
     */
    public function searchRecentPosts($query, $days = 7, $options = []) {
        $dateFrom = date('Y-m-d', strtotime("-{$days} days"));
        $options['date_from'] = $dateFrom;
        $options['sort_by'] = 'date';
        
        return $this->searchPosts(['q' => $query], $options);
    }

    /**
     * Search for popular posts (sorted by likes)
     * 
     * @param string $query Search query
     * @param array $options Optional parameters (same as searchPosts)
     * @return array API response
     */
    public function searchPopularPosts($query, $options = []) {
        $options['sort_by'] = 'likes';
        return $this->searchPosts(['q' => $query], $options);
    }

    /**
     * Search for public posts only
     * 
     * @param string $query Search query
     * @param array $options Optional parameters (same as searchPosts)
     * @return array API response
     */
    public function searchPublicPosts($query, $options = []) {
        $options['privacy'] = 'public';
        return $this->searchPosts(['q' => $query], $options);
    }

    /**
     * Search for photo posts only
     * 
     * @param string $query Search query
     * @param array $options Optional parameters (same as searchPosts)
     * @return array API response
     */
    public function searchPhotos($query, $options = []) {
        $options['post_type'] = 'photo';
        return $this->searchPosts(['q' => $query], $options);
    }

    /**
     * Search for video posts only
     * 
     * @param string $query Search query
     * @param array $options Optional parameters (same as searchPosts)
     * @return array API response
     */
    public function searchVideos($query, $options = []) {
        $options['post_type'] = 'video';
        return $this->searchPosts(['q' => $query], $options);
    }

    /**
     * Advanced search with multiple criteria
     * 
     * @param array $criteria Search criteria:
     *   - query: Text query
     *   - type: Search type ('all', 'users', 'posts')
     *   - user_filters: Array of user-specific filters
     *   - post_filters: Array of post-specific filters
     * @param array $options Pagination and other options
     * @return array API response
     */
    public function advancedSearch($criteria, $options = []) {
        $type = $criteria['type'] ?? 'all';
        $query = $criteria['query'] ?? '';

        switch ($type) {
            case 'users':
                if (empty($query)) {
                    throw new Exception('Query is required for user search');
                }
                $userOptions = array_merge($options, $criteria['user_filters'] ?? []);
                return $this->searchUsers($query, $userOptions);

            case 'posts':
                $searchParams = [];
                if (!empty($query)) {
                    $searchParams['q'] = $query;
                }
                if (!empty($criteria['location'])) {
                    $searchParams['location'] = $criteria['location'];
                }
                if (!empty($criteria['author'])) {
                    $searchParams['author'] = $criteria['author'];
                }
                
                $postOptions = array_merge($options, $criteria['post_filters'] ?? []);
                return $this->searchPosts($searchParams, $postOptions);

            case 'all':
            default:
                if (empty($query)) {
                    throw new Exception('Query is required for global search');
                }
                return $this->globalSearch(
                    $query, 
                    'all',
                    $options['page'] ?? 1,
                    $options['limit'] ?? 10
                );
        }
    }
}
