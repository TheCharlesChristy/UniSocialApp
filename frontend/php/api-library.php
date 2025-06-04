<?php

// Include all API handlers
require_once 'admin-api.php';
require_once 'auth-api.php';
require_once 'conversations-api.php';
require_once 'friends-api.php';
require_once 'media-api.php';
require_once 'messages-api.php';
require_once 'notifications-api.php';
require_once 'posts-api.php';
require_once 'privacy-api.php';
require_once 'reports-api.php';
require_once 'search-api.php';
require_once 'users-api.php';

/**
 * API Library Class
 * 
 * Simple class that provides public attributes for each API handler.
 * All API handlers are instantiated once during construction.
 */
class APILibrary {
    
    // Public API handler instances
    public $adminAPI;
    public $authAPI;
    public $conversationsAPI;
    public $friendsAPI;
    public $mediaAPI;
    public $messagesAPI;
    public $notificationsAPI;
    public $postsAPI;
    public $privacyAPI;
    public $reportsAPI;
    public $searchAPI;    public $usersAPI;

    /**
     * Constructor - initializes all API handlers
     */
    public function __construct() {
        $this->adminAPI = new AdminAPI();
        $this->authAPI = new AuthAPI();
        $this->conversationsAPI = new ConversationsAPI();
        $this->friendsAPI = new FriendsAPI();
        $this->mediaAPI = new MediaAPI();
        $this->messagesAPI = new MessagesAPI();
        $this->notificationsAPI = new NotificationsAPI();
        $this->postsAPI = new PostsAPI();
        $this->privacyAPI = new PrivacyAPI();
        $this->reportsAPI = new ReportsAPI();
        $this->searchAPI = new SearchAPI();
        $this->usersAPI = new UsersAPI();
    }
}