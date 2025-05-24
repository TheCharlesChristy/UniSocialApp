<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Posts and Media API Test Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, textarea, select, button {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        button {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            margin-top: 10px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .response {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 10px;
            margin-top: 10px;
            white-space: pre-wrap;
            max-height: 300px;
            overflow-y: auto;
        }
        .error {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        .success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        .tabs {
            display: flex;
            margin-bottom: 20px;
        }
        .tab {
            padding: 10px 20px;
            background: #e9ecef;
            border: 1px solid #ddd;
            border-bottom: none;
            cursor: pointer;
            margin-right: 5px;
            border-radius: 4px 4px 0 0;
        }
        .tab.active {
            background: white;
            border-bottom: 1px solid white;
            margin-bottom: -1px;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .auth-section {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .file-input {
            margin-bottom: 10px;
        }
        .back-to-tests-btn {
            position: fixed;
            top: 10px;
            left: 10px;
            background-color: #007bff;
            color: white;
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            z-index: 1000;
            transition: background-color 0.3s;
        }
        .back-to-tests-btn:hover {
            background-color: #0056b3;
            color: white;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <a href="http://localhost/webdev/tests" class="back-to-tests-btn">← Back to Tests</a>
    <h1>Posts and Media API Test Page</h1>

    <!-- Authentication Section -->
    <div class="auth-section">
        <h3>Authentication</h3>
        <div class="form-group">
            <label for="authToken">Access Token:</label>
            <input type="text" id="authToken" placeholder="Enter your access token here">
            <small>Get your token from the login endpoint first</small>
        </div>
    </div>

    <!-- Tabs -->
    <div class="tabs">
        <div class="tab active" onclick="showTab('posts')">Posts</div>
        <div class="tab" onclick="showTab('likes')">Likes</div>
        <div class="tab" onclick="showTab('comments')">Comments</div>
        <div class="tab" onclick="showTab('media')">Media</div>
    </div>

    <!-- Posts Tab -->
    <div id="posts" class="tab-content active">
        <!-- Get Feed -->
        <div class="container">
            <h3>Get Feed Posts</h3>
            <div class="form-group">
                <label for="feedPage">Page:</label>
                <input type="number" id="feedPage" value="1" min="1">
            </div>
            <div class="form-group">
                <label for="feedLimit">Limit:</label>
                <input type="number" id="feedLimit" value="10" min="1" max="50">
            </div>
            <div class="form-group">
                <label for="feedFilter">Filter:</label>
                <input type="text" id="feedFilter" placeholder="Search caption or location">
            </div>
            <button onclick="getFeed()">Get Feed</button>
            <div id="feedResponse" class="response"></div>
        </div>

        <!-- Get Specific Post -->
        <div class="container">
            <h3>Get Specific Post</h3>
            <div class="form-group">
                <label for="getPostId">Post ID:</label>
                <input type="number" id="getPostId" placeholder="Enter post ID">
            </div>
            <button onclick="getPost()">Get Post</button>
            <div id="getPostResponse" class="response"></div>
        </div>

        <!-- Create Post -->
        <div class="container">
            <h3>Create Post</h3>
            <div class="form-group">
                <label for="postType">Post Type:</label>
                <select id="postType" onchange="toggleMediaUpload()">
                    <option value="text">Text</option>
                    <option value="photo">Photo</option>
                    <option value="video">Video</option>
                </select>
            </div>
            <div class="form-group">
                <label for="postCaption">Caption:</label>
                <textarea id="postCaption" placeholder="Enter post caption"></textarea>
            </div>
            <div class="form-group">
                <label for="privacyLevel">Privacy Level:</label>
                <select id="privacyLevel">
                    <option value="public">Public</option>
                    <option value="friends">Friends</option>
                    <option value="private">Private</option>
                </select>
            </div>
            <div id="mediaUploadSection" class="form-group" style="display: none;">
                <label for="postMedia">Media File:</label>
                <input type="file" id="postMedia" accept="image/*,video/*" class="file-input">
            </div>
            <div class="form-group">
                <label for="locationName">Location Name:</label>
                <input type="text" id="locationName" placeholder="Optional location name">
            </div>
            <div class="form-group">
                <label for="locationLat">Latitude:</label>
                <input type="number" id="locationLat" placeholder="Optional latitude" step="any">
            </div>
            <div class="form-group">
                <label for="locationLng">Longitude:</label>
                <input type="number" id="locationLng" placeholder="Optional longitude" step="any">
            </div>
            <button onclick="createPost()">Create Post</button>
            <div id="createPostResponse" class="response"></div>
        </div>

        <!-- Update Post -->
        <div class="container">
            <h3>Update Post</h3>
            <div class="form-group">
                <label for="updatePostId">Post ID:</label>
                <input type="number" id="updatePostId" placeholder="Enter post ID to update">
            </div>
            <div class="form-group">
                <label for="updateCaption">New Caption:</label>
                <textarea id="updateCaption" placeholder="Enter new caption"></textarea>
            </div>
            <div class="form-group">
                <label for="updatePrivacy">New Privacy Level:</label>
                <select id="updatePrivacy">
                    <option value="">No change</option>
                    <option value="public">Public</option>
                    <option value="friends">Friends</option>
                    <option value="private">Private</option>
                </select>
            </div>
            <button onclick="updatePost()">Update Post</button>
            <div id="updatePostResponse" class="response"></div>
        </div>

        <!-- Delete Post -->
        <div class="container">
            <h3>Delete Post</h3>
            <div class="form-group">
                <label for="deletePostId">Post ID:</label>
                <input type="number" id="deletePostId" placeholder="Enter post ID to delete">
            </div>
            <button onclick="deletePost()" style="background-color: #dc3545;">Delete Post</button>
            <div id="deletePostResponse" class="response"></div>
        </div>

        <!-- Search Posts -->
        <div class="container">
            <h3>Search Posts</h3>
            <div class="form-group">
                <label for="searchQuery">Search Query:</label>
                <input type="text" id="searchQuery" placeholder="Enter search terms">
            </div>
            <div class="form-group">
                <label for="searchPage">Page:</label>
                <input type="number" id="searchPage" value="1" min="1">
            </div>
            <button onclick="searchPosts()">Search Posts</button>
            <div id="searchResponse" class="response"></div>
        </div>
    </div>

    <!-- Likes Tab -->
    <div id="likes" class="tab-content">
        <!-- Like Post -->
        <div class="container">
            <h3>Like Post</h3>
            <div class="form-group">
                <label for="likePostId">Post ID:</label>
                <input type="number" id="likePostId" placeholder="Enter post ID to like">
            </div>
            <button onclick="likePost()">Like Post</button>
            <div id="likeResponse" class="response"></div>
        </div>

        <!-- Unlike Post -->
        <div class="container">
            <h3>Unlike Post</h3>
            <div class="form-group">
                <label for="unlikePostId">Post ID:</label>
                <input type="number" id="unlikePostId" placeholder="Enter post ID to unlike">
            </div>
            <button onclick="unlikePost()">Unlike Post</button>
            <div id="unlikeResponse" class="response"></div>
        </div>

        <!-- Get Post Likes -->
        <div class="container">
            <h3>Get Post Likes</h3>
            <div class="form-group">
                <label for="getLikesPostId">Post ID:</label>
                <input type="number" id="getLikesPostId" placeholder="Enter post ID">
            </div>
            <div class="form-group">
                <label for="likesPage">Page:</label>
                <input type="number" id="likesPage" value="1" min="1">
            </div>
            <button onclick="getPostLikes()">Get Likes</button>
            <div id="getLikesResponse" class="response"></div>
        </div>
    </div>

    <!-- Comments Tab -->
    <div id="comments" class="tab-content">
        <!-- Get Comments -->
        <div class="container">
            <h3>Get Comments</h3>
            <div class="form-group">
                <label for="getCommentsPostId">Post ID:</label>
                <input type="number" id="getCommentsPostId" placeholder="Enter post ID">
            </div>
            <div class="form-group">
                <label for="commentsPage">Page:</label>
                <input type="number" id="commentsPage" value="1" min="1">
            </div>
            <button onclick="getComments()">Get Comments</button>
            <div id="getCommentsResponse" class="response"></div>
        </div>

        <!-- Add Comment -->
        <div class="container">
            <h3>Add Comment</h3>
            <div class="form-group">
                <label for="commentPostId">Post ID:</label>
                <input type="number" id="commentPostId" placeholder="Enter post ID">
            </div>
            <div class="form-group">
                <label for="commentContent">Comment:</label>
                <textarea id="commentContent" placeholder="Enter your comment"></textarea>
            </div>
            <div class="form-group">
                <label for="parentCommentId">Parent Comment ID (for replies):</label>
                <input type="number" id="parentCommentId" placeholder="Optional - leave empty for top-level comment">
            </div>
            <button onclick="addComment()">Add Comment</button>
            <div id="addCommentResponse" class="response"></div>
        </div>

        <!-- Update Comment -->
        <div class="container">
            <h3>Update Comment</h3>
            <div class="form-group">
                <label for="updateCommentId">Comment ID:</label>
                <input type="number" id="updateCommentId" placeholder="Enter comment ID to update">
            </div>
            <div class="form-group">
                <label for="updateCommentContent">New Content:</label>
                <textarea id="updateCommentContent" placeholder="Enter new comment content"></textarea>
            </div>
            <button onclick="updateComment()">Update Comment</button>
            <div id="updateCommentResponse" class="response"></div>
        </div>

        <!-- Delete Comment -->
        <div class="container">
            <h3>Delete Comment</h3>
            <div class="form-group">
                <label for="deleteCommentId">Comment ID:</label>
                <input type="number" id="deleteCommentId" placeholder="Enter comment ID to delete">
            </div>
            <button onclick="deleteComment()" style="background-color: #dc3545;">Delete Comment</button>
            <div id="deleteCommentResponse" class="response"></div>
        </div>
    </div>

    <!-- Media Tab -->
    <div id="media" class="tab-content">
        <!-- Upload Media -->
        <div class="container">
            <h3>Upload Media</h3>
            <div class="form-group">
                <label for="uploadType">Upload Type:</label>
                <select id="uploadType">
                    <option value="profile_picture">Profile Picture</option>
                    <option value="post_media">Post Media</option>
                </select>
            </div>
            <div class="form-group">
                <label for="uploadFile">File:</label>
                <input type="file" id="uploadFile" accept="image/*,video/*" class="file-input">
            </div>
            <button onclick="uploadMedia()">Upload File</button>
            <div id="uploadResponse" class="response"></div>
        </div>
    </div>

    <script>
        const API_BASE_URL = '../backend/src/api';
        // Tab switching
        function showTab(tabName) {
            // Hide all tab contents
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Remove active class from all tabs
            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(tab => tab.classList.remove('active'));
            
            // Show selected tab content
            document.getElementById(tabName).classList.add('active');
            
            // Add active class to clicked tab
            event.target.classList.add('active');
        }

        // Toggle media upload section
        function toggleMediaUpload() {
            const postType = document.getElementById('postType').value;
            const mediaSection = document.getElementById('mediaUploadSection');
            
            if (postType === 'photo' || postType === 'video') {
                mediaSection.style.display = 'block';
            } else {
                mediaSection.style.display = 'none';
            }
        }

        // Helper function to get auth token
        function getAuthToken() {
            return document.getElementById('authToken').value;
        }

        // Helper function to display response
        function displayResponse(elementId, response, isError = false) {
            const element = document.getElementById(elementId);
            element.textContent = JSON.stringify(response, null, 2);
            element.className = 'response ' + (isError ? 'error' : 'success');
        }

        // Helper function to make API requests
        async function makeRequest(url, method = 'GET', data = null, isFormData = false) {
            const headers = {
                'Authorization': 'Bearer ' + getAuthToken()
            };

            if (!isFormData) {
                headers['Content-Type'] = 'application/json';
            }

            const config = {
                method: method,
                headers: headers
            };

            if (data) {
                config.body = isFormData ? data : JSON.stringify(data);
            }

            try {
                const response = await fetch(url, config);
                const result = await response.json();
                return { success: true, data: result, status: response.status };
            } catch (error) {
                return { success: false, error: error.message };
            }
        }

        // Posts API functions
        async function getFeed() {
            const page = document.getElementById('feedPage').value;
            const limit = document.getElementById('feedLimit').value;
            const filter = document.getElementById('feedFilter').value;

            let url = `${API_BASE_URL}/posts/get_feed.php?page=${page}&limit=${limit}`;
            if (filter) {
                url += `&filter=${encodeURIComponent(filter)}`;
            }
            
            const result = await makeRequest(url);
            displayResponse('feedResponse', result.success ? result.data : result.error, !result.success);
        }

        async function getPost() {
            const postId = document.getElementById('getPostId').value;
            if (!postId) {
                displayResponse('getPostResponse', { error: 'Post ID is required' }, true);
                return;
            }

            const result = await makeRequest(`${API_BASE_URL}/posts/get_post.php?id=${postId}`);
            displayResponse('getPostResponse', result.success ? result.data : result.error, !result.success);
        }

        async function createPost() {
            const postType = document.getElementById('postType').value;
            const caption = document.getElementById('postCaption').value;
            const privacyLevel = document.getElementById('privacyLevel').value;
            const locationName = document.getElementById('locationName').value;
            const locationLat = document.getElementById('locationLat').value;
            const locationLng = document.getElementById('locationLng').value;
            
            if (postType === 'text' && !caption) {
                displayResponse('createPostResponse', { error: 'Caption is required for text posts' }, true);
                return;
            }
            
            const formData = new FormData();
            formData.append('post_type', postType);
            formData.append('caption', caption);
            formData.append('privacy_level', privacyLevel);
            
            if (locationName) formData.append('location_name', locationName);
            if (locationLat) formData.append('location_lat', locationLat);
            if (locationLng) formData.append('location_lng', locationLng);
            
            if (postType === 'photo' || postType === 'video') {
                const mediaFile = document.getElementById('postMedia').files[0];
                if (!mediaFile) {
                    displayResponse('createPostResponse', { error: 'Media file is required for photo/video posts' }, true);
                    return;
                }
                formData.append('media', mediaFile);
            }

            const result = await makeRequest(API_BASE_URL + '/posts/create_post.php', 'POST', formData, true);
            displayResponse('createPostResponse', result.success ? result.data : result.error, !result.success);
        }

        async function updatePost() {
            const postId = document.getElementById('updatePostId').value;
            const caption = document.getElementById('updateCaption').value;
            const privacyLevel = document.getElementById('updatePrivacy').value;
            
            if (!postId) {
                displayResponse('updatePostResponse', { error: 'Post ID is required' }, true);
                return;
            }
            
            const data = { post_id: parseInt(postId) };
            data.caption = caption;
            if (privacyLevel) data.privacy_level = privacyLevel;
            
            const result = await makeRequest(API_BASE_URL + '/posts/update_post.php', 'PUT', data);
            displayResponse('updatePostResponse', result.success ? result.data : result.error, !result.success);
        }

        async function deletePost() {
            const postId = document.getElementById('deletePostId').value;
            if (!postId) {
                displayResponse('deletePostResponse', { error: 'Post ID is required' }, true);
                return;
            }
            
            if (!confirm('Are you sure you want to delete this post?')) {
                return;
            }
            
            const result = await makeRequest(API_BASE_URL + '/posts/delete_post.php', 'DELETE', { post_id: parseInt(postId) });
            displayResponse('deletePostResponse', result.success ? result.data : result.error, !result.success);
        }

        async function searchPosts() {
            const query = document.getElementById('searchQuery').value;
            const page = document.getElementById('searchPage').value;
            
            if (!query) {
                displayResponse('searchResponse', { error: 'Search query is required' }, true);
                return;
            }
            
            const result = await makeRequest(`${API_BASE_URL}/posts/search_posts.php?q=${encodeURIComponent(query)}&page=${page}`);
            displayResponse('searchResponse', result.success ? result.data : result.error, !result.success);
        }

        // Likes API functions
        async function likePost() {
            const postId = document.getElementById('likePostId').value;
            if (!postId) {
                displayResponse('likeResponse', { error: 'Post ID is required' }, true);
                return;
            }
            
            const result = await makeRequest(API_BASE_URL + '/posts/like_post.php', 'POST', { post_id: parseInt(postId) });
            displayResponse('likeResponse', result.success ? result.data : result.error, !result.success);
        }

        async function unlikePost() {
            const postId = document.getElementById('unlikePostId').value;
            if (!postId) {
                displayResponse('unlikeResponse', { error: 'Post ID is required' }, true);
                return;
            }
            
            const result = await makeRequest(API_BASE_URL + '/posts/unlike_post.php', 'DELETE', { post_id: parseInt(postId) });
            displayResponse('unlikeResponse', result.success ? result.data : result.error, !result.success);
        }

        async function getPostLikes() {
            const postId = document.getElementById('getLikesPostId').value;
            const page = document.getElementById('likesPage').value;
            
            if (!postId) {
                displayResponse('getLikesResponse', { error: 'Post ID is required' }, true);
                return;
            }
            
            const result = await makeRequest(`${API_BASE_URL}/posts/get_post_likes.php?post_id=${postId}&page=${page}`);
            displayResponse('getLikesResponse', result.success ? result.data : result.error, !result.success);
        }

        // Comments API functions
        async function getComments() {
            const postId = document.getElementById('getCommentsPostId').value;
            const page = document.getElementById('commentsPage').value;
            
            if (!postId) {
                displayResponse('getCommentsResponse', { error: 'Post ID is required' }, true);
                return;
            }
            
            const result = await makeRequest(`${API_BASE_URL}/posts/get_comments.php?post_id=${postId}&page=${page}`);
            displayResponse('getCommentsResponse', result.success ? result.data : result.error, !result.success);
        }

        async function addComment() {
            const postId = document.getElementById('commentPostId').value;
            const content = document.getElementById('commentContent').value;
            const parentCommentId = document.getElementById('parentCommentId').value;
            
            if (!postId || !content) {
                displayResponse('addCommentResponse', { error: 'Post ID and content are required' }, true);
                return;
            }
            
            const data = {
                post_id: parseInt(postId),
                content: content
            };
            
            if (parentCommentId) {
                data.parent_comment_id = parseInt(parentCommentId);
            }
            
            const result = await makeRequest(API_BASE_URL + '/posts/add_comment.php', 'POST', data);
            displayResponse('addCommentResponse', result.success ? result.data : result.error, !result.success);
        }

        async function updateComment() {
            const commentId = document.getElementById('updateCommentId').value;
            const content = document.getElementById('updateCommentContent').value;
            
            if (!commentId || !content) {
                displayResponse('updateCommentResponse', { error: 'Comment ID and content are required' }, true);
                return;
            }
            
            const result = await makeRequest(API_BASE_URL + '/posts/update_comment.php', 'PUT', {
                comment_id: parseInt(commentId),
                content: content
            });
            displayResponse('updateCommentResponse', result.success ? result.data : result.error, !result.success);
        }

        async function deleteComment() {
            const commentId = document.getElementById('deleteCommentId').value;
            if (!commentId) {
                displayResponse('deleteCommentResponse', { error: 'Comment ID is required' }, true);
                return;
            }
            
            if (!confirm('Are you sure you want to delete this comment?')) {
                return;
            }
            
            const result = await makeRequest(API_BASE_URL + '/posts/delete_comment.php', 'DELETE', { comment_id: parseInt(commentId) });
            displayResponse('deleteCommentResponse', result.success ? result.data : result.error, !result.success);
        }

        // Media API functions
        async function uploadMedia() {
            const uploadType = document.getElementById('uploadType').value;
            const fileInput = document.getElementById('uploadFile');
            
            if (!fileInput.files[0]) {
                displayResponse('uploadResponse', { error: 'File is required' }, true);
                return;
            }
            
            const formData = new FormData();
            formData.append('type', uploadType);
            formData.append('file', fileInput.files[0]);
            
            const result = await makeRequest(API_BASE_URL + '/media/upload.php', 'POST', formData, true);
            displayResponse('uploadResponse', result.success ? result.data : result.error, !result.success);
        }
    </script>
</body>
</html>
