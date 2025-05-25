<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin & Search API Test Suite</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/themes/prism.min.css" rel="stylesheet">
    <style>
        .response-container {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 1rem;
            margin-top: 1rem;
        }
        .error { color: #dc3545; }
        .success { color: #198754; }
        .token-field { font-family: 'Courier New', monospace; font-size: 0.875rem; }
        .endpoint-header { margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid #dee2e6; }
        .status-badge { display: inline-block; padding: 0.25rem 0.5rem; border-radius: 0.375rem; font-size: 0.75rem; font-weight: 600; }
        .status-200 { background-color: #d1e7dd; color: #0f5132; }
        .status-400 { background-color: #f8d7da; color: #721c24; }
        .status-401 { background-color: #f8d7da; color: #721c24; }
        .status-403 { background-color: #f8d7da; color: #721c24; }
        .status-500 { background-color: #f8d7da; color: #721c24; }
        .nav-section { font-weight: 600; color: #6c757d; background-color: #f8f9fa; }
        pre { background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 0.375rem; padding: 0.75rem; }
    </style>
</head>
<body>
    <div class="container-fluid mt-3">
        <h1 class="mb-4">Admin & Search API Test Suite</h1>
        
        <?php
        // Session management for storing tokens
        session_start();
        
        // Base URLs
        define('BASE_API_URL', 'http://localhost/webdev/backend/src/api/');
        define('ADMIN_API_URL', BASE_API_URL . 'admin/');
        define('SEARCH_API_URL', BASE_API_URL . 'search/');
        
        // Helper function to make API requests
        function makeApiRequest($url, $method = 'GET', $headers = [], $data = null) {
            $ch = curl_init();
            
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false
            ]);
            
            if ($data !== null) {
                if ($method === 'GET') {
                    // For GET requests, append data as query string
                    $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($data);
                    curl_setopt($ch, CURLOPT_URL, $url);
                } else {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
            }
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            return [
                'response' => $response,
                'http_code' => $httpCode,
                'error' => $error,
                'url' => $url
            ];
        }
        
        // Handle form submissions
        $response = null;
        $testResult = null;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $endpoint = $_POST['endpoint'] ?? '';
            $method = $_POST['method'] ?? 'GET';
            $token = $_POST['token'] ?? '';
            $data = json_decode($_POST['data'] ?? '{}', true);
            
            // Store token in session
            if (!empty($token)) {
                $_SESSION['admin_search_token'] = $token;
            }
            
            // Prepare headers
            $headers = ['Content-Type: application/json'];
            if (!empty($token)) {
                $headers[] = 'Authorization: Bearer ' . $token;
            }
            
            // Make API request
            $url = '';
            if (strpos($endpoint, 'admin/') === 0) {
                $url = ADMIN_API_URL . substr($endpoint, 6);
            } elseif (strpos($endpoint, 'search/') === 0) {
                $url = SEARCH_API_URL . substr($endpoint, 7);
            } else {
                $url = BASE_API_URL . $endpoint;
            }
            
            $startTime = microtime(true);
            $testResult = makeApiRequest($url, $method, $headers, $data);
            $endTime = microtime(true);
            $testResult['duration'] = round(($endTime - $startTime) * 1000, 2);
            
            // Parse response
            $response = json_decode($testResult['response'], true);
        }
        
        $activeTab = $_GET['tab'] ?? 'admin-dashboard';
        ?>
        
        <ul class="nav nav-tabs" id="apiTabs" role="tablist">
            <!-- Admin Section -->
            <li class="nav-item">
                <span class="nav-link nav-section">Admin Endpoints</span>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'admin-dashboard' ? 'active' : '' ?>" 
                        id="admin-dashboard-tab" data-bs-toggle="tab" data-bs-target="#admin-dashboard" 
                        type="button" role="tab">Dashboard</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'admin-users' ? 'active' : '' ?>" 
                        id="admin-users-tab" data-bs-toggle="tab" data-bs-target="#admin-users" 
                        type="button" role="tab">Users</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'admin-reports' ? 'active' : '' ?>" 
                        id="admin-reports-tab" data-bs-toggle="tab" data-bs-target="#admin-reports" 
                        type="button" role="tab">Reports</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'admin-analytics' ? 'active' : '' ?>" 
                        id="admin-analytics-tab" data-bs-toggle="tab" data-bs-target="#admin-analytics" 
                        type="button" role="tab">Analytics</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'admin-user-actions' ? 'active' : '' ?>" 
                        id="admin-user-actions-tab" data-bs-toggle="tab" data-bs-target="#admin-user-actions" 
                        type="button" role="tab">User Actions</button>
            </li>
            
            <!-- Search Section -->
            <li class="nav-item">
                <span class="nav-link nav-section">Search Endpoints</span>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'search-global' ? 'active' : '' ?>" 
                        id="search-global-tab" data-bs-toggle="tab" data-bs-target="#search-global" 
                        type="button" role="tab">Global Search</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'search-users' ? 'active' : '' ?>" 
                        id="search-users-tab" data-bs-toggle="tab" data-bs-target="#search-users" 
                        type="button" role="tab">Search Users</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'search-posts' ? 'active' : '' ?>" 
                        id="search-posts-tab" data-bs-toggle="tab" data-bs-target="#search-posts" 
                        type="button" role="tab">Search Posts</button>
            </li>
        </ul>
        
        <div class="tab-content" id="apiTabsContent">
            
            <!-- Admin Dashboard Tab -->
            <div class="tab-pane fade <?= $activeTab === 'admin-dashboard' ? 'show active' : '' ?>" 
                 id="admin-dashboard" role="tabpanel">
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="endpoint-header">
                            <h3>Admin Dashboard</h3>
                            <p class="text-muted">Endpoint: GET /api/admin/dashboard</p>
                        </div>
                        <form method="post" action="">
                            <input type="hidden" name="endpoint" value="admin/dashboard.php">
                            <input type="hidden" name="method" value="GET">
                            
                            <div class="form-group mb-3">
                                <label for="admin-dashboard-token">Admin Authentication Token:</label>
                                <textarea class="form-control token-field" id="admin-dashboard-token" 
                                         name="token" rows="3"><?= htmlspecialchars($_SESSION['admin_search_token'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="admin-dashboard-data">Request Parameters:</label>
                                <textarea class="form-control" id="admin-dashboard-data" 
                                         name="data" rows="2">{}</textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Get Dashboard Stats</button>
                        </form>
                    </div>
                    <div class="col-md-6">
                        <h4>Expected Response:</h4>
                        <pre><code class="language-json">{
  "success": true,
  "message": "Dashboard data retrieved successfully",
  "dashboard": {
    "overview": {
      "total_users": 150,
      "active_users": 142,
      "suspended_users": 8,
      "total_posts": 1250,
      "total_comments": 3400,
      "total_likes": 8900,
      "pending_reports": 5
    },
    "recent_activity": {
      "new_users_today": 3,
      "new_posts_today": 25,
      "new_reports_today": 2
    }
  }
}</code></pre>
                    </div>
                </div>
            </div>
            
            <!-- Admin Users Tab -->
            <div class="tab-pane fade <?= $activeTab === 'admin-users' ? 'show active' : '' ?>" 
                 id="admin-users" role="tabpanel">
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="endpoint-header">
                            <h3>Admin Users Management</h3>
                            <p class="text-muted">Endpoint: GET /api/admin/users</p>
                        </div>
                        <form method="post" action="">
                            <input type="hidden" name="endpoint" value="admin/users.php">
                            <input type="hidden" name="method" value="GET">
                            
                            <div class="form-group mb-3">
                                <label for="admin-users-token">Admin Authentication Token:</label>
                                <textarea class="form-control token-field" id="admin-users-token" 
                                         name="token" rows="3"><?= htmlspecialchars($_SESSION['admin_search_token'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="admin-users-data">Request Parameters:</label>
                                <textarea class="form-control" id="admin-users-data" 
                                         name="data" rows="8">{
  "page": 1,
  "limit": 10,
  "status": "active",
  "role": "",
  "search": ""
}</textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Get Users List</button>
                        </form>
                    </div>
                    <div class="col-md-6">
                        <h4>Parameters:</h4>
                        <ul>
                            <li><code>page</code>: Page number (default: 1)</li>
                            <li><code>limit</code>: Users per page (default: 10, max: 50)</li>
                            <li><code>status</code>: active, suspended, deleted, all</li>
                            <li><code>role</code>: user, admin, moderator (optional)</li>
                            <li><code>search</code>: Search term for username/name</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Admin Reports Tab -->
            <div class="tab-pane fade <?= $activeTab === 'admin-reports' ? 'show active' : '' ?>" 
                 id="admin-reports" role="tabpanel">
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="endpoint-header">
                            <h3>Admin Reports Management</h3>
                            <p class="text-muted">Endpoint: GET /api/admin/reports</p>
                        </div>
                        <form method="post" action="">
                            <input type="hidden" name="endpoint" value="admin/reports.php">
                            <input type="hidden" name="method" value="GET">
                            
                            <div class="form-group mb-3">
                                <label for="admin-reports-token">Admin Authentication Token:</label>
                                <textarea class="form-control token-field" id="admin-reports-token" 
                                         name="token" rows="3"><?= htmlspecialchars($_SESSION['admin_search_token'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="admin-reports-data">Request Parameters:</label>
                                <textarea class="form-control" id="admin-reports-data" 
                                         name="data" rows="8">{
  "page": 1,
  "limit": 10,
  "status": "pending",
  "content_type": ""
}</textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Get Reports List</button>
                        </form>
                    </div>
                    <div class="col-md-6">
                        <h4>Parameters:</h4>
                        <ul>
                            <li><code>status</code>: pending, reviewed, action_taken, dismissed</li>
                            <li><code>content_type</code>: post, comment, user (optional)</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Admin Analytics Tab -->
            <div class="tab-pane fade <?= $activeTab === 'admin-analytics' ? 'show active' : '' ?>" 
                 id="admin-analytics" role="tabpanel">
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="endpoint-header">
                            <h3>Admin Analytics</h3>
                            <p class="text-muted">Endpoints: GET /api/admin/analytics_posts & analytics_users</p>
                        </div>
                        
                        <!-- Posts Analytics -->
                        <form method="post" action="" class="mb-4">
                            <input type="hidden" name="endpoint" value="admin/analytics_posts.php">
                            <input type="hidden" name="method" value="GET">
                            
                            <h5>Posts Analytics</h5>
                            <div class="form-group mb-3">
                                <label for="analytics-posts-token">Admin Token:</label>
                                <textarea class="form-control token-field" id="analytics-posts-token" 
                                         name="token" rows="2"><?= htmlspecialchars($_SESSION['admin_search_token'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="analytics-posts-data">Date Range:</label>
                                <textarea class="form-control" id="analytics-posts-data" 
                                         name="data" rows="5">{
  "start_date": "2024-01-01",
  "end_date": "2024-12-31"
}</textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-info">Get Posts Analytics</button>
                        </form>
                        
                        <!-- Users Analytics -->
                        <form method="post" action="">
                            <input type="hidden" name="endpoint" value="admin/analytics_users.php">
                            <input type="hidden" name="method" value="GET">
                            
                            <h5>Users Analytics</h5>
                            <div class="form-group mb-3">
                                <label for="analytics-users-token">Admin Token:</label>
                                <textarea class="form-control token-field" id="analytics-users-token" 
                                         name="token" rows="2"><?= htmlspecialchars($_SESSION['admin_search_token'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="analytics-users-data">Date Range:</label>
                                <textarea class="form-control" id="analytics-users-data" 
                                         name="data" rows="5">{
  "start_date": "2024-01-01",
  "end_date": "2024-12-31"
}</textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-info">Get Users Analytics</button>
                        </form>
                    </div>
                    <div class="col-md-6">
                        <h4>Analytics Features:</h4>
                        <ul>
                            <li>Post creation trends</li>
                            <li>User registration patterns</li>
                            <li>Engagement statistics</li>
                            <li>Top performers</li>
                            <li>Content type distribution</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Admin User Actions Tab -->
            <div class="tab-pane fade <?= $activeTab === 'admin-user-actions' ? 'show active' : '' ?>" 
                 id="admin-user-actions" role="tabpanel">
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="endpoint-header">
                            <h3>Admin User Actions</h3>
                            <p class="text-muted">Update, Suspend, Activate, Delete Users</p>
                        </div>
                        
                        <!-- Update User -->
                        <form method="post" action="" class="mb-4">
                            <input type="hidden" name="endpoint" value="admin/update_user.php">
                            <input type="hidden" name="method" value="PUT">
                            
                            <h5>Update User</h5>
                            <div class="form-group mb-3">
                                <label for="update-user-token">Admin Token:</label>
                                <textarea class="form-control token-field" id="update-user-token" 
                                         name="token" rows="2"><?= htmlspecialchars($_SESSION['admin_search_token'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="update-user-data">User Data:</label>
                                <textarea class="form-control" id="update-user-data" 
                                         name="data" rows="8">{
  "user_id": 1,
  "first_name": "Updated Name",
  "last_name": "Updated Last",
  "email": "newemail@example.com",
  "role": "user"
}</textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-warning">Update User</button>
                        </form>
                        
                        <!-- Suspend User -->
                        <form method="post" action="" class="mb-4">
                            <input type="hidden" name="endpoint" value="admin/suspend_user.php">
                            <input type="hidden" name="method" value="PUT">
                            
                            <h5>Suspend User</h5>
                            <div class="form-group mb-3">
                                <label for="suspend-user-token">Admin Token:</label>
                                <textarea class="form-control token-field" id="suspend-user-token" 
                                         name="token" rows="2"><?= htmlspecialchars($_SESSION['admin_search_token'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="suspend-user-data">Suspend Data:</label>
                                <textarea class="form-control" id="suspend-user-data" 
                                         name="data" rows="5">{
  "user_id": 1,
  "reason": "Policy violation"
}</textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-danger">Suspend User</button>
                        </form>
                    </div>
                    <div class="col-md-6">
                        <h4>Available Actions:</h4>
                        <ul>
                            <li><strong>Update User:</strong> Modify user profile data</li>
                            <li><strong>Suspend User:</strong> Temporarily disable account</li>
                            <li><strong>Activate User:</strong> Re-enable suspended account</li>
                            <li><strong>Delete User:</strong> Soft delete user account</li>
                        </ul>
                        
                        <div class="alert alert-warning mt-3">
                            <strong>Safety Note:</strong> Admin accounts cannot perform destructive actions on other admin accounts for security.
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Search Global Tab -->
            <div class="tab-pane fade <?= $activeTab === 'search-global' ? 'show active' : '' ?>" 
                 id="search-global" role="tabpanel">
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="endpoint-header">
                            <h3>Global Search</h3>
                            <p class="text-muted">Endpoint: GET /api/search/index</p>
                        </div>
                        <form method="post" action="">
                            <input type="hidden" name="endpoint" value="search/index.php">
                            <input type="hidden" name="method" value="GET">
                            
                            <div class="form-group mb-3">
                                <label for="search-global-token">Authentication Token:</label>
                                <textarea class="form-control token-field" id="search-global-token" 
                                         name="token" rows="3"><?= htmlspecialchars($_SESSION['admin_search_token'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="search-global-data">Search Parameters:</label>
                                <textarea class="form-control" id="search-global-data" 
                                         name="data" rows="8">{
  "q": "test search",
  "type": "all",
  "page": 1,
  "limit": 10
}</textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Perform Global Search</button>
                        </form>
                    </div>
                    <div class="col-md-6">
                        <h4>Parameters:</h4>
                        <ul>
                            <li><code>q</code>: Search query (required)</li>
                            <li><code>type</code>: all, users, posts (default: all)</li>
                            <li><code>page</code>: Page number</li>
                            <li><code>limit</code>: Results per page (max: 50)</li>
                        </ul>
                        
                        <h4>Features:</h4>
                        <ul>
                            <li>Searches across users and posts</li>
                            <li>Privacy-aware results</li>
                            <li>Relevance scoring</li>
                            <li>Friendship status included</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Search Users Tab -->
            <div class="tab-pane fade <?= $activeTab === 'search-users' ? 'show active' : '' ?>" 
                 id="search-users" role="tabpanel">
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="endpoint-header">
                            <h3>Search Users</h3>
                            <p class="text-muted">Endpoint: GET /api/search/users</p>
                        </div>
                        <form method="post" action="">
                            <input type="hidden" name="endpoint" value="search/users.php">
                            <input type="hidden" name="method" value="GET">
                            
                            <div class="form-group mb-3">
                                <label for="search-users-token">Authentication Token:</label>
                                <textarea class="form-control token-field" id="search-users-token" 
                                         name="token" rows="3"><?= htmlspecialchars($_SESSION['admin_search_token'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="search-users-data">Search Parameters:</label>
                                <textarea class="form-control" id="search-users-data" 
                                         name="data" rows="10">{
  "q": "john",
  "page": 1,
  "limit": 10,
  "role": "",
  "status": "active"
}</textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Search Users</button>
                        </form>
                    </div>
                    <div class="col-md-6">
                        <h4>Advanced Filters:</h4>
                        <ul>
                            <li><code>role</code>: user, admin, moderator (optional)</li>
                            <li><code>status</code>: active, suspended, all</li>
                        </ul>
                        
                        <h4>Search Features:</h4>
                        <ul>
                            <li>Name and username search</li>
                            <li>Multi-word support</li>
                            <li>Friendship status</li>
                            <li>User statistics (posts, friends)</li>
                            <li>Relevance ranking</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Search Posts Tab -->
            <div class="tab-pane fade <?= $activeTab === 'search-posts' ? 'show active' : '' ?>" 
                 id="search-posts" role="tabpanel">
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="endpoint-header">
                            <h3>Search Posts</h3>
                            <p class="text-muted">Endpoint: GET /api/search/posts</p>
                        </div>
                        <form method="post" action="">
                            <input type="hidden" name="endpoint" value="search/posts.php">
                            <input type="hidden" name="method" value="GET">
                            
                            <div class="form-group mb-3">
                                <label for="search-posts-token">Authentication Token:</label>
                                <textarea class="form-control token-field" id="search-posts-token" 
                                         name="token" rows="3"><?= htmlspecialchars($_SESSION['admin_search_token'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="search-posts-data">Search Parameters:</label>
                                <textarea class="form-control" id="search-posts-data" 
                                         name="data" rows="12">{
  "q": "vacation",
  "location": "",
  "author": "",
  "post_type": "",
  "privacy": "",
  "date_from": "",
  "date_to": "",
  "sort_by": "relevance",
  "page": 1,
  "limit": 10
}</textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Search Posts</button>
                        </form>
                    </div>
                    <div class="col-md-6">
                        <h4>Advanced Filters:</h4>
                        <ul>
                            <li><code>location</code>: Location name search</li>
                            <li><code>author</code>: Username filter</li>
                            <li><code>post_type</code>: photo, video, text</li>
                            <li><code>privacy</code>: public, friends</li>
                            <li><code>date_from/date_to</code>: Date range (YYYY-MM-DD)</li>
                            <li><code>sort_by</code>: relevance, date, likes, comments</li>
                        </ul>
                        
                        <h4>Privacy Features:</h4>
                        <ul>
                            <li>Respects post privacy settings</li>
                            <li>Friend relationship awareness</li>
                            <li>User's own posts always visible</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Response Display -->
        <?php if ($testResult): ?>
        <div class="response-container mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>API Response</h4>
                <div>
                    <span class="status-badge status-<?= $testResult['http_code'] ?>">
                        HTTP <?= $testResult['http_code'] ?>
                    </span>
                    <span class="badge bg-secondary"><?= $testResult['duration'] ?>ms</span>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <h5>Request Details</h5>
                    <p><strong>URL:</strong> <code><?= htmlspecialchars($testResult['url']) ?></code></p>
                    <p><strong>Method:</strong> <?= htmlspecialchars($_POST['method'] ?? 'GET') ?></p>
                    
                    <?php if (!empty($_POST['data']) && $_POST['data'] !== '{}'): ?>
                    <h6>Request Data:</h6>
                    <pre><code class="language-json"><?= htmlspecialchars($_POST['data']) ?></code></pre>
                    <?php endif; ?>
                </div>
                
                <div class="col-md-6">
                    <h5>Response 
                        <?= $response && isset($response['success']) && $response['success'] ? 
                            '<span class="success">✓</span>' : '<span class="error">✗</span>' ?>
                    </h5>
                    
                    <?php if ($testResult['error']): ?>
                        <div class="alert alert-danger">
                            <strong>cURL Error:</strong> <?= htmlspecialchars($testResult['error']) ?>
                        </div>
                    <?php else: ?>
                        <pre><code class="language-json"><?= htmlspecialchars(json_encode($response, JSON_PRETTY_PRINT)) ?></code></pre>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- PowerShell Commands Section -->
        <div class="mt-5">
            <h3>PowerShell Commands for Testing</h3>
            <div class="row">
                <div class="col-md-6">
                    <h5>Admin Dashboard</h5>
                    <pre><code class="language-powershell">Invoke-RestMethod -Uri "http://localhost/webdev/backend/src/api/admin/dashboard.php" `
  -Method GET `
  -Headers @{"Authorization"="Bearer YOUR_ADMIN_TOKEN"}</code></pre>
                </div>
                <div class="col-md-6">
                    <h5>Global Search</h5>
                    <pre><code class="language-powershell">Invoke-RestMethod -Uri "http://localhost/webdev/backend/src/api/search/index.php?q=test&type=all" `
  -Method GET `
  -Headers @{"Authorization"="Bearer YOUR_TOKEN"}</code></pre>
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-md-6">
                    <h5>Search Users</h5>
                    <pre><code class="language-powershell">Invoke-RestMethod -Uri "http://localhost/webdev/backend/src/api/search/users.php?q=john&status=active" `
  -Method GET `
  -Headers @{"Authorization"="Bearer YOUR_TOKEN"}</code></pre>
                </div>
                <div class="col-md-6">
                    <h5>Admin User Update</h5>
                    <pre><code class="language-powershell">$body = @{
  user_id = 1
  first_name = "Updated"
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost/webdev/backend/src/api/admin/update_user.php" `
  -Method PUT `
  -Headers @{"Authorization"="Bearer YOUR_ADMIN_TOKEN"; "Content-Type"="application/json"} `
  -Body $body</code></pre>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/components/prism-core.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/plugins/autoloader/prism-autoloader.min.js"></script>
    
    <script>
        // Auto-sync token across all forms
        document.addEventListener('DOMContentLoaded', function() {
            const tokenFields = document.querySelectorAll('.token-field');
            
            tokenFields.forEach(field => {
                field.addEventListener('input', function() {
                    const token = this.value;
                    tokenFields.forEach(otherField => {
                        if (otherField !== this) {
                            otherField.value = token;
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>
