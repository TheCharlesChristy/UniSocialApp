<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SocialConnect - Create Post</title>
    <link rel="stylesheet" href="../css/globals.css">
    <link rel="stylesheet" href="../css/confirm_post.css">
</head>
<body>
    <?php
    // Require authentication
    require_once '../php/auth-guard.php';
    
    // Include the component loader
    require_once '../php/component-loader.php';
    
    // Create component loader instance
    $loader = new ComponentLoader();
    
    // Sample user data
    $userData = [
        'user_name' => 'John Doe',
        'profile_picture' => '../assets/images/default-profile.svg',
        'notification_count' => '1'
    ];
    
    // Render the logged-in header
    echo $loader->getComponentWithVars('logged_in_header', $userData);
    ?>
    
    <main class="confirm-post-main" role="main">
        <div class="confirm-post-container">
            <div class="confirm-post-card">
                <div class="confirm-post-header">
                    <h1 class="confirm-post-title">Create Post</h1>
                    <p class="confirm-post-subtitle">Share your moment with the world</p>
                </div>
                
                <form class="post-form" id="postForm">
                    <!-- Media Preview Section -->
                    <div class="media-preview-section" id="mediaPreviewSection">
                        <div class="media-preview-container">
                            <img id="mediaPreviewImage" class="media-preview" style="display: none;" alt="Media preview">
                            <video id="mediaPreviewVideo" class="media-preview" controls style="display: none;">
                                Your browser does not support video playback.
                            </video>
                            
                            <div class="media-info" id="mediaInfo" style="display: none;">
                                <div class="media-details">
                                    <span class="media-type" id="mediaType"></span>
                                    <span class="media-size" id="mediaSize"></span>
                                </div>
                                <button type="button" class="change-media-btn" id="changeMediaBtn">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                                        <circle cx="12" cy="13" r="4"></circle>
                                    </svg>
                                    Change Media
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Post Content Section -->
                    <div class="post-content-section">
                        <div class="form-group">
                            <label for="postCaption" class="form-label">Caption</label>
                            <textarea 
                                id="postCaption" 
                                name="caption" 
                                class="form-textarea" 
                                placeholder="What's on your mind?"
                                rows="4"
                                maxlength="500"
                                aria-describedby="caption-help"
                            ></textarea>
                            <div class="form-help" id="caption-help">
                                <span class="character-count" id="characterCount">0/500</span>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="postLocation" class="form-label">Location (Optional)</label>
                            <div class="location-input-wrapper">
                                <input 
                                    type="text" 
                                    id="postLocation" 
                                    name="location" 
                                    class="form-input" 
                                    placeholder="Add location..."
                                    aria-describedby="location-help"
                                >
                                <button type="button" class="location-btn" id="locationBtn" aria-label="Get current location">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="10" r="3"></circle>
                                        <path d="m12 21.7-6.3-6.3a9 9 0 1 1 12.6 0L12 21.7z"></path>
                                    </svg>
                                </button>
                            </div>
                            <div class="form-help" id="location-help">Your location helps friends discover your posts</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="postVisibility" class="form-label">Visibility</label>
                            <select id="postVisibility" name="visibility" class="form-select">
                                <option value="public">Public - Anyone can see this post</option>
                                <option value="friends">Friends - Only your friends can see this post</option>
                                <option value="private" selected>Private - Only you can see this post</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" id="backBtn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="15,18 9,12 15,6"></polyline>
                            </svg>
                            Back to Camera
                        </button>
                        <button type="submit" class="btn btn-primary" id="shareBtn" disabled>
                            <span class="share-text">Share Post</span>
                            <svg class="share-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="18" cy="5" r="3"></circle>
                                <circle cx="6" cy="12" r="3"></circle>
                                <circle cx="18" cy="19" r="3"></circle>
                                <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line>
                                <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>
                            </svg>
                            <div class="loading-spinner" style="display: none;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 12a9 9 0 11-6.219-8.56"/>
                                </svg>
                            </div>
                        </button>
                    </div>
                </form>
                
                <!-- No Media State -->
                <div class="no-media-state" id="noMediaState">
                    <div class="no-media-icon">📷</div>
                    <h3 class="no-media-title">No Media Selected</h3>
                    <p class="no-media-text">You need to capture or select media before creating a post.</p>
                    <button type="button" class="btn btn-primary" id="addMediaBtn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                            <circle cx="12" cy="13" r="4"></circle>
                        </svg>
                        Add Media
                    </button>
                </div>
            </div>
        </div>
    </main>
    
    <script src="../js/confirm_post.js"></script>
</body>
</html>
