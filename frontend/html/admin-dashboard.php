<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SocialConnect</title>
    <link rel="stylesheet" href="../css/generic.css">
    <link rel="stylesheet" href="../css/admin-dashboard.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="admin-body">
    <!-- Admin Header -->
    <header class="admin-header">
        <div class="admin-header-content">
            <div class="admin-header-left">
                <div class="admin-logo">
                    <h1>SocialConnect Admin</h1>
                </div>                <nav class="admin-nav">
                    <a href="admin-dashboard.php" class="admin-nav-link active">Dashboard</a>
                    <a href="admin-users.php" class="admin-nav-link">Users</a>
                    <a href="admin-analytics.php" class="admin-nav-link">Analytics</a>
                </nav>
            </div>
            <div class="admin-header-right">                <div class="admin-notifications">
                    <button class="icon-btn" aria-label="Notifications" style="position: relative;" onclick="window.location.href='notifications.php'">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>
                        </svg>
                        <span class="notification-badge" id="notificationBadge">0</span>
                        <span class="live-indicator" id="liveIndicator"></span>
                    </button>
                </div>
                <div class="admin-user-menu">                    <button class="admin-user-btn" aria-label="User menu">
                        <img src="/webdev/backend/media/images/placeholder.png" alt="Admin" class="admin-avatar" id="userAvatar">
                        <span id="userName">John Admin</span>
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M7 10l5 5 5-5z"/>
                        </svg>
                    </button>
                    <div class="admin-user-dropdown">
                        <a href="edit-profile.php">Profile Settings</a>
                        <a href="privacy-settings.php">Privacy</a>
                        <hr>
                        <a href="logout.php">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="admin-main">
        <div class="admin-container">
            <!-- Dashboard Header -->
            <div class="admin-page-header">
                <h1>Dashboard Overview</h1>
                <p>Monitor platform activity and key metrics</p>
            </div>

            <!-- Quick Stats -->
            <section class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M16 4c0-1.11.89-2 2-2s2 .89 2 2-.89 2-2 2-2-.89-2-2zM4 18v-4h3v4H4zM10 18v-7h3v7h-3zM16 18v-4h3v4h-3z"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h3>15,847</h3>
                        <p>Total Users</p>
                        <span class="stat-change positive">+12.5%</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon success">
                        <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.11 0 2-.9 2-2V5c0-1.1-.89-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h3>2,341</h3>
                        <p>Posts Today</p>
                        <span class="stat-change positive">+8.2%</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon danger">
                        <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h3>23</h3>
                        <p>Pending Reports</p>
                        <span class="stat-change negative">+5 new</span>
                    </div>
                </div>
            </section>

            <!-- Dashboard Content Grid -->
            <div class="admin-content-grid">

                <!-- Recent Reports -->
                <section class="admin-card">
                    <div class="admin-card-header">
                        <h2>Pending Reports</h2>
                        <button class="btn-secondary btn-sm">View All</button>
                    </div>
                    <div class="admin-card-content">
                        <div class="reports-list">
                            <div class="report-item urgent">
                                <div class="report-header">
                                    <span class="report-type">Harassment</span>
                                    <span class="report-priority high">High Priority</span>
                                </div>
                                <p class="report-content">User @toxicuser reported for harassment in comments</p>
                                <div class="report-meta">
                                    <span>Reported by: @safeuser</span>
                                    <span>5 min ago</span>
                                </div>
                                <div class="report-actions">
                                    <button class="btn-danger btn-sm">Suspend User</button>
                                    <button class="btn-secondary btn-sm">Review</button>
                                </div>
                            </div>

                            <div class="report-item">
                                <div class="report-header">
                                    <span class="report-type">Spam</span>
                                    <span class="report-priority medium">Medium Priority</span>
                                </div>
                                <p class="report-content">Multiple spam posts detected from @spambot2024</p>
                                <div class="report-meta">
                                    <span>Auto-detected</span>
                                    <span>1 hour ago</span>
                                </div>
                                <div class="report-actions">
                                    <button class="btn-warning btn-sm">Remove Posts</button>
                                    <button class="btn-secondary btn-sm">Review</button>
                                </div>
                            </div>

                            <div class="report-item">
                                <div class="report-header">
                                    <span class="report-type">Copyright</span>
                                    <span class="report-priority low">Low Priority</span>
                                </div>
                                <p class="report-content">Possible copyright violation in post #1234</p>
                                <div class="report-meta">
                                    <span>Reported by: @photographer</span>
                                    <span>3 hours ago</span>
                                </div>
                                <div class="report-actions">
                                    <button class="btn-warning btn-sm">Remove Post</button>
                                    <button class="btn-secondary btn-sm">Review</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </main>

    <!-- Scripts -->
    <script>
        // Admin Dashboard Functionality
        class AdminDashboard {
            constructor() {
                this.apiBaseUrl = '/webdev/backend/src/api';
                this.authToken = this.getAuthToken();
                this.isLoading = false;
                this.loadingIndicators = new Set();
                
                this.initializeEventListeners();
                this.showLoadingState();
                this.loadDashboardData();
                this.loadCurrentUser();
                this.loadNotificationCount();
                this.loadPendingReports();
                this.loadSystemMetrics();
                this.loadAnalyticsData();
                this.startRealTimeUpdates();
            }

            showLoadingState() {
                // Show loading indicators for major sections
                this.showSectionLoading('stats-grid', 'Loading statistics...');
                this.showSectionLoading('reports-list', 'Loading reports...');
                this.showSectionLoading('metrics-list', 'Loading system metrics...');
            }

            showSectionLoading(sectionClass, message) {
                const section = document.querySelector(`.${sectionClass}`);
                if (section) {
                    const loadingDiv = document.createElement('div');
                    loadingDiv.className = 'loading-indicator';
                    loadingDiv.innerHTML = `
                        <div class="loading-spinner"></div>
                        <span>${message}</span>
                    `;
                    section.appendChild(loadingDiv);
                    this.loadingIndicators.add(sectionClass);
                }
            }

            hideSectionLoading(sectionClass) {
                if (this.loadingIndicators.has(sectionClass)) {
                    const section = document.querySelector(`.${sectionClass}`);
                    const loading = section?.querySelector('.loading-indicator');
                    if (loading) {
                        loading.remove();
                        this.loadingIndicators.delete(sectionClass);
                    }
                }
            }

            getAuthToken() {
                // Try multiple sources for the auth token
                return localStorage.getItem('authToken') || 
                       sessionStorage.getItem('authToken') || 
                       localStorage.getItem('adminToken') ||
                       sessionStorage.getItem('adminToken');
            }

            async apiRequest(endpoint, method = 'GET', data = null) {
                const url = `${this.apiBaseUrl}${endpoint}`;
                const config = {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    credentials: 'include'
                };

                if (this.authToken) {
                    config.headers['Authorization'] = `Bearer ${this.authToken}`;
                }

                if (data && method !== 'GET') {
                    config.body = JSON.stringify(data);
                }

                const response = await fetch(url, config);
                
                if (!response.ok) {
                    if (response.status === 401) {
                        // Unauthorized - redirect to login
                        window.location.href = 'login.html';
                        throw new Error('Authentication required');
                    }
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                return await response.json();
            }            async loadCurrentUser() {
                try {
                    const response = await this.apiRequest('/users/me.php');
                    
                    if (response && response.success && response.user) {
                        this.updateUserDisplay(response.user);
                    } else {
                        console.warn('Invalid user data response:', response);
                    }
                } catch (error) {
                    console.error('Failed to load current user:', error);
                    // Keep default display on error
                }
            }

            async loadDashboardData() {
                try {
                    const dashboardResponse = await this.apiRequest('/admin/dashboard.php');
                    
                    if (dashboardResponse && dashboardResponse.success) {
                        this.updateDashboardStats(dashboardResponse.dashboard.statistics);
                        this.hideSectionLoading('stats-grid');
                    } else {
                        throw new Error(dashboardResponse?.message || 'Failed to load dashboard data');
                    }
                } catch (error) {
                    console.error('Failed to load dashboard data:', error);
                    this.hideSectionLoading('stats-grid');
                    this.showNotification('Failed to load dashboard data. Please refresh the page.', 'error');
                }
            }

            async loadNotificationCount() {
                try {
                    const response = await this.apiRequest('/notifications/unread_count.php');
                    
                    if (response && response.success) {
                        this.updateNotificationBadge(response.count);
                    }
                } catch (error) {
                    console.error('Failed to load notification count:', error);
                    // Don't show error notification for this as it's not critical
                }
            }

            async loadPendingReports() {
                try {
                    const response = await this.apiRequest('/admin/reports.php?status=pending&limit=5');
                    
                    if (response && response.success) {
                        this.updatePendingReports(response.reports);
                        this.hideSectionLoading('reports-list');
                    } else {
                        throw new Error(response?.message || 'Failed to load reports');
                    }
                } catch (error) {
                    console.error('Failed to load pending reports:', error);
                    this.hideSectionLoading('reports-list');
                    this.showNotification('Failed to load pending reports', 'warning');
                }
            }

            async loadSystemMetrics() {
                try {
                    // Load system performance metrics
                    const [userAnalytics, postAnalytics] = await Promise.all([
                        this.apiRequest('/admin/analytics_users.php', 'GET'),
                        this.apiRequest('/admin/analytics_posts.php', 'GET')
                    ]);

                    if (userAnalytics.success && postAnalytics.success) {
                        this.hideSectionLoading('metrics-list');
                    } else {
                        throw new Error('Failed to load analytics data');
                    }
                } catch (error) {
                    console.error('Failed to load system metrics:', error);
                    this.hideSectionLoading('metrics-list');
                    // Fall back to default metrics display
                }
            }

            async loadAnalyticsData() {
                try {
                    const [userAnalytics, postAnalytics] = await Promise.all([
                        this.apiRequest('/admin/analytics_users.php', 'GET'),
                        this.apiRequest('/admin/analytics_posts.php', 'GET')
                    ]);

                    if (userAnalytics.success && postAnalytics.success) {
                        this.updateAnalyticsDisplay(userAnalytics.data, postAnalytics.data);
                    }
                } catch (error) {
                    console.error('Failed to load analytics data:', error);
                }
            }

            updateDashboardStats(stats) {
                // Update Total Users
                const totalUsersElement = document.querySelector('.stat-card:nth-child(1) .stat-content h3');
                if (totalUsersElement) {
                    totalUsersElement.textContent = stats.total_users.toLocaleString();
                }

                // Update Posts Today (using new posts this week as proxy)
                const postsElement = document.querySelector('.stat-card:nth-child(2) .stat-content h3');
                if (postsElement) {
                    postsElement.textContent = stats.new_posts_this_week.toLocaleString();
                }

                // Update Pending Reports
                const reportsElement = document.querySelector('.stat-card:nth-child(3) .stat-content h3');
                if (reportsElement) {
                    reportsElement.textContent = stats.pending_reports.toLocaleString();
                }

                // Update the stat changes based on weekly data
                const userChange = document.querySelector('.stat-card:nth-child(1) .stat-change');
                if (userChange && stats.new_users_this_week > 0) {
                    userChange.textContent = `+${stats.new_users_this_week} this week`;
                    userChange.className = 'stat-change positive';
                }

                const postsChange = document.querySelector('.stat-card:nth-child(2) .stat-change');
                if (postsChange) {
                    postsChange.textContent = `${stats.new_posts_this_week} this week`;
                    postsChange.className = 'stat-change positive';
                }

                const reportsChange = document.querySelector('.stat-card:nth-child(3) .stat-change');
                if (reportsChange && stats.pending_reports > 0) {
                    reportsChange.textContent = `${stats.pending_reports} pending`;
                    reportsChange.className = 'stat-change negative';
                }
            }

            updateNotificationBadge(count) {
                const badge = document.getElementById('notificationBadge');
                if (badge) {
                    badge.textContent = count;
                    badge.style.display = count > 0 ? 'block' : 'none';
                }
            }

            updatePendingReports(reports) {
                const reportsContainer = document.querySelector('.reports-list');
                if (!reportsContainer || !reports || reports.length === 0) {
                    return;
                }

                // Clear existing placeholder reports
                reportsContainer.innerHTML = '';

                reports.forEach(report => {
                    const reportElement = this.createReportElement(report);
                    reportsContainer.appendChild(reportElement);
                });
            }

            createReportElement(report) {
                const reportDiv = document.createElement('div');
                reportDiv.className = 'report-item';
                
                const priorityClass = this.getReportPriorityClass(report.reason);
                const timeAgo = this.formatTimeAgo(report.created_at);

                reportDiv.innerHTML = `
                    <div class="report-header">
                        <span class="report-type">${this.formatReportType(report.reason)}</span>
                        <span class="report-priority ${priorityClass}">${this.formatPriority(priorityClass)} Priority</span>
                    </div>
                    <p class="report-content">${this.formatReportContent(report)}</p>
                    <div class="report-meta">
                        <span>Reported by: @${report.reporter_username}</span>
                        <span>${timeAgo}</span>
                    </div>
                    <div class="report-actions">
                        <button class="btn-danger btn-sm" data-action="suspend" data-report-id="${report.report_id}">Suspend User</button>
                        <button class="btn-warning btn-sm" data-action="remove" data-report-id="${report.report_id}">Remove ${report.content_type}</button>
                        <button class="btn-secondary btn-sm" data-action="review" data-report-id="${report.report_id}">Review</button>
                    </div>
                `;

                // Add event listeners to action buttons
                const actionButtons = reportDiv.querySelectorAll('[data-action]');
                actionButtons.forEach(button => {
                    button.addEventListener('click', (e) => {
                        e.preventDefault();
                        const action = button.dataset.action;
                        const reportId = button.dataset.reportId;
                        this.handleReportAction(action, reportId, reportDiv);
                    });
                });

                return reportDiv;
            }

            getReportPriorityClass(reason) {
                const highPriority = ['harassment', 'hate_speech', 'violence'];
                const mediumPriority = ['spam', 'inappropriate_content'];
                
                if (highPriority.includes(reason)) return 'high';
                if (mediumPriority.includes(reason)) return 'medium';
                return 'low';
            }

            formatReportType(reason) {
                const typeMap = {
                    'spam': 'Spam',
                    'harassment': 'Harassment',
                    'hate_speech': 'Hate Speech',
                    'inappropriate_content': 'Inappropriate',
                    'violence': 'Violence',
                    'copyright': 'Copyright',
                    'other': 'Other'
                };
                return typeMap[reason] || 'Report';
            }

            formatPriority(priorityClass) {
                return priorityClass.charAt(0).toUpperCase() + priorityClass.slice(1);
            }

            formatReportContent(report) {
                const contentType = report.content_type;
                const reportedUser = report.reported_username;
                const reason = this.formatReportType(report.reason);
                
                if (contentType === 'user') {
                    return `${reason} reported for user @${reportedUser}`;
                } else if (contentType === 'post') {
                    return `${reason} reported in post by @${reportedUser}`;
                } else if (contentType === 'comment') {
                    return `${reason} reported in comment by @${reportedUser}`;
                }
                
                return `${reason} reported by @${report.reporter_username}`;
            }

            formatTimeAgo(dateString) {
                const date = new Date(dateString);
                const now = new Date();
                const diffMs = now - date;
                const diffMinutes = Math.floor(diffMs / 60000);
                const diffHours = Math.floor(diffMinutes / 60);
                const diffDays = Math.floor(diffHours / 24);

                if (diffMinutes < 60) {
                    return `${diffMinutes} minute${diffMinutes !== 1 ? 's' : ''} ago`;
                } else if (diffHours < 24) {
                    return `${diffHours} hour${diffHours !== 1 ? 's' : ''} ago`;
                } else {
                    return `${diffDays} day${diffDays !== 1 ? 's' : ''} ago`;
                }
            }

            async handleReportAction(action, reportId, reportElement) {
                try {
                    switch (action) {
                        case 'suspend':
                            await this.suspendUser(reportId, reportElement);
                            break;
                        case 'remove':
                            await this.removeContent(reportId, reportElement);
                            break;
                        case 'review':
                            this.reviewReport(reportId);
                            break;
                    }
                } catch (error) {
                    console.error('Failed to handle report action:', error);
                    this.showNotification('Failed to process action', 'error');
                }
            }

            async suspendUser(reportId, reportElement) {
                if (!confirm('Suspend this user? They will be unable to access the platform.')) {
                    return;
                }

                try {
                    // Update report status to action_taken
                    await this.apiRequest('/admin/update_report.php', 'PUT', {
                        report_id: parseInt(reportId),
                        status: 'action_taken',
                        admin_notes: 'User suspended for policy violation'
                    });

                    reportElement.style.opacity = '0.5';
                    this.showNotification('User suspended successfully', 'success');
                    
                    setTimeout(() => {
                        reportElement.remove();
                    }, 1000);
                } catch (error) {
                    this.showNotification('Failed to suspend user', 'error');
                    throw error;
                }
            }

            async removeContent(reportId, reportElement) {
                if (!confirm('Remove this content? This action cannot be undone.')) {
                    return;
                }

                try {
                    // Update report status to action_taken
                    await this.apiRequest('/admin/update_report.php', 'PUT', {
                        report_id: parseInt(reportId),
                        status: 'action_taken',
                        admin_notes: 'Content removed for policy violation'
                    });

                    reportElement.style.opacity = '0.5';
                    this.showNotification('Content removed successfully', 'success');
                    
                    setTimeout(() => {
                        reportElement.remove();
                    }, 1000);
                } catch (error) {
                    this.showNotification('Failed to remove content', 'error');
                    throw error;
                }
            }

            reviewReport(reportId) {
                // Navigate to detailed report view
                window.location.href = `admin-analytics.php#report-${reportId}`;
            }

            updateUserDisplay(userData) {
                // Update user name
                const userNameElement = document.getElementById('userName');
                if (userNameElement) {
                    if (userData.first_name && userData.last_name) {
                        userNameElement.textContent = `${userData.first_name} ${userData.last_name}`;
                    } else if (userData.first_name) {
                        userNameElement.textContent = userData.first_name;
                    } else if (userData.username) {
                        userNameElement.textContent = userData.username;
                    }
                    // Keep "John Admin" as fallback if no name data available
                }

                // Update user avatar
                const userAvatar = document.getElementById('userAvatar');
                if (userAvatar && userData.profile_picture && userData.profile_picture !== '') {
                    // Ensure the profile picture URL is properly formatted
                    let profilePicUrl = userData.profile_picture;
                    if (!profilePicUrl.startsWith('http') && !profilePicUrl.startsWith('/')) {
                        profilePicUrl = `/webdev/backend/${profilePicUrl}`;
                    }
                    
                    userAvatar.src = profilePicUrl;
                    userAvatar.alt = `${userData.first_name || userData.username || 'Admin'} Avatar`;
                    
                    // Add error handling for broken images with infinite loop prevention
                    userAvatar.onerror = function() {
                        if (!this.src.includes('placeholder.png')) {
                            this.src = '/webdev/backend/media/images/placeholder.png';
                            this.alt = 'Default Admin Avatar';
                        }
                    };                }
            }

            async loadAnalyticsData() {
                try {
                    const [userAnalytics, postAnalytics] = await Promise.all([
                        this.apiRequest('/admin/analytics_users.php', 'GET'),
                        this.apiRequest('/admin/analytics_posts.php', 'GET')
                    ]);

                    if (userAnalytics.success && postAnalytics.success) {
                        this.updateAnalyticsDisplay(userAnalytics.data, postAnalytics.data);
                    }
                } catch (error) {
                    console.error('Failed to load analytics data:', error);
                }
            }

            initializeEventListeners() {
                // User menu dropdown
                const userBtn = document.querySelector('.admin-user-btn');
                const userDropdown = document.querySelector('.admin-user-dropdown');
                
                if (userBtn && userDropdown) {
                    userBtn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        userDropdown.classList.toggle('show');
                    });

                    document.addEventListener('click', () => {
                        userDropdown.classList.remove('show');
                    });
                }

                // Quick action buttons
                this.setupQuickActions();

                // Report action buttons
                this.setupReportActions();
            }

            setupQuickActions() {
                const quickActionBtns = document.querySelectorAll('.quick-action-btn');
                
                quickActionBtns.forEach(btn => {
                    btn.addEventListener('click', () => {
                        const actionText = btn.querySelector('span').textContent;
                        
                        switch(actionText) {
                            case 'Create Announcement':
                                this.showAnnouncementModal();
                                break;
                            case 'Review Reports':
                                window.location.href = 'admin-analytics.php#reports';
                                break;
                            case 'System Backup':
                                this.initiateBackup();
                                break;
                            case 'Manage Users':
                                window.location.href = 'admin-users.php';
                                break;
                            case 'View Analytics':
                                window.location.href = 'admin-analytics.php';
                                break;
                            case 'Security Settings':
                                this.showSecuritySettings();
                                break;
                        }
                    });
                });
            }

            setupReportActions() {
                const reportActionBtns = document.querySelectorAll('.report-actions button');
                
                reportActionBtns.forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        e.preventDefault();
                        const action = btn.textContent.trim();
                        const reportItem = btn.closest('.report-item');
                        
                        switch(action) {
                            case 'Suspend User':
                                this.handleSuspendUser(reportItem);
                                break;
                            case 'Remove Posts':
                                this.handleRemovePosts(reportItem);
                                break;
                            case 'Remove Post':
                                this.handleRemovePost(reportItem);
                                break;
                            case 'Review':
                                this.handleReviewReport(reportItem);
                                break;
                        }
                    });
                });
            }

            showAnnouncementModal() {
                // Create and show announcement modal
                const modal = document.createElement('div');
                modal.className = 'modal-overlay';
                modal.innerHTML = `
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3>Create Announcement</h3>
                            <button class="modal-close">&times;</button>
                        </div>
                        <div class="modal-body">
                            <form class="announcement-form">
                                <div class="form-group">
                                    <label>Title</label>
                                    <input type="text" class="form-control" placeholder="Announcement title" required>
                                </div>
                                <div class="form-group">
                                    <label>Message</label>
                                    <textarea class="form-control" rows="4" placeholder="Announcement message" required></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Priority</label>
                                    <select class="form-control">
                                        <option value="low">Low</option>
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                    </select>
                                </div>
                                <div class="form-actions">
                                    <button type="button" class="btn-secondary modal-cancel">Cancel</button>
                                    <button type="submit" class="btn-primary">Send Announcement</button>
                                </div>
                            </form>
                        </div>
                    </div>
                `;
                
                document.body.appendChild(modal);
                
                // Modal event listeners
                modal.querySelector('.modal-close').addEventListener('click', () => {
                    document.body.removeChild(modal);
                });
                
                modal.querySelector('.modal-cancel').addEventListener('click', () => {
                    document.body.removeChild(modal);
                });
                
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) {
                        document.body.removeChild(modal);
                    }
                });
            }

            initiateBackup() {
                if (confirm('Start system backup? This may take several minutes.')) {
                    // Show backup progress
                    this.showNotification('System backup initiated...', 'info');
                    
                    // Simulate backup progress
                    setTimeout(() => {
                        this.showNotification('System backup completed successfully!', 'success');
                    }, 3000);
                }
            }

            handleSuspendUser(reportItem) {
                if (confirm('Suspend this user? They will be unable to access the platform.')) {
                    reportItem.style.opacity = '0.5';
                    this.showNotification('User suspended successfully', 'success');
                    
                    // Remove from pending reports
                    setTimeout(() => {
                        reportItem.remove();
                    }, 1000);
                }
            }

            handleRemovePosts(reportItem) {
                if (confirm('Remove all posts from this user?')) {
                    reportItem.style.opacity = '0.5';
                    this.showNotification('Posts removed successfully', 'success');
                    
                    setTimeout(() => {
                        reportItem.remove();
                    }, 1000);
                }
            }

            handleRemovePost(reportItem) {
                if (confirm('Remove this specific post?')) {
                    reportItem.style.opacity = '0.5';
                    this.showNotification('Post removed successfully', 'success');
                    
                    setTimeout(() => {
                        reportItem.remove();
                    }, 1000);
                }
            }

            handleReviewReport(reportItem) {
                // Navigate to detailed report view
                window.location.href = 'admin-analytics.php#report-detail';
            }

            showNotification(message, type = 'info') {
                const notification = document.createElement('div');
                notification.className = `notification notification-${type}`;
                notification.textContent = message;
                
                document.body.appendChild(notification);
                
                setTimeout(() => {
                    notification.classList.add('show');
                }, 100);
                  setTimeout(() => {
                    notification.classList.remove('show');
                    setTimeout(() => {
                        document.body.removeChild(notification);
                    }, 300);
                }, 3000);
            }            startRealTimeUpdates() {
                // Real-time updates using SSE
                this.updateLiveIndicator(true);
                this.connectToNotificationSSE();
                this.connectToReportsSSE();
                
                // Fallback polling as backup (reduced frequency)
                setInterval(() => {
                    if (!this.notificationSSEConnected || !this.reportsSSEConnected) {
                        console.log('SSE fallback: refreshing data via polling');
                        this.loadDashboardData();
                        this.loadNotificationCount();
                        this.loadPendingReports();
                    }
                }, 60000); // Fallback every 60 seconds
                
                // Show live update indicator
                setInterval(() => {
                    this.pulseIndicator();
                }, 5000); // Pulse every 5 seconds
            }            connectToNotificationSSE() {
                try {
                    const authToken = this.getAuthToken();
                    let sseUrl = `${this.apiBaseUrl}/notifications/live_notifications.php`;
                    
                    // Pass token as query parameter for SSE authentication
                    if (authToken) {
                        sseUrl += `?token=${encodeURIComponent(authToken)}`;
                    }
                    
                    this.notificationEventSource = new EventSource(sseUrl);
                    this.notificationSSEConnected = false;

                    this.notificationEventSource.onopen = () => {
                        console.log('Notification SSE connection opened');
                        this.notificationSSEConnected = true;
                        this.updateLiveIndicator(true);
                    };

                    this.notificationEventSource.addEventListener('connected', (event) => {
                        console.log('Connected to notification SSE:', JSON.parse(event.data));
                        this.notificationSSEConnected = true;
                    });

                    this.notificationEventSource.addEventListener('new_notification', (event) => {
                        const notification = JSON.parse(event.data);
                        console.log('New notification received:', notification);
                        this.handleNewNotification(notification);
                    });

                    this.notificationEventSource.addEventListener('count_update', (event) => {
                        const data = JSON.parse(event.data);
                        this.updateNotificationBadge(data.count);
                    });

                    this.notificationEventSource.addEventListener('error', (event) => {
                        const error = JSON.parse(event.data || '{}');
                        console.error('Notification SSE error event:', error);
                    });

                    this.notificationEventSource.onerror = (error) => {
                        console.error('Notification SSE connection error:', error);
                        this.notificationSSEConnected = false;
                        this.handleSSEError('notifications');
                    };

                } catch (error) {
                    console.error('Failed to connect to notification SSE:', error);
                    this.notificationSSEConnected = false;
                }
            }connectToReportsSSE() {
                try {
                    const authToken = this.getAuthToken();
                    let sseUrl = `${this.apiBaseUrl}/admin/live_reports.php`;
                    
                    // EventSource doesn't support custom headers, so pass token as query parameter
                    if (authToken) {
                        sseUrl += `?token=${encodeURIComponent(authToken)}`;
                    }
                    
                    this.reportsEventSource = new EventSource(sseUrl);
                    this.reportsSSEConnected = false;

                    this.reportsEventSource.onopen = () => {
                        console.log('Reports SSE connection opened');
                        this.reportsSSEConnected = true;
                        this.updateLiveIndicator(true);
                    };

                    this.reportsEventSource.addEventListener('connected', (event) => {
                        console.log('Connected to reports SSE:', JSON.parse(event.data));
                        this.reportsSSEConnected = true;
                    });

                    this.reportsEventSource.addEventListener('new_report', (event) => {
                        const report = JSON.parse(event.data);
                        console.log('New report received:', report);
                        this.handleNewReport(report);
                    });

                    this.reportsEventSource.addEventListener('pending_count_update', (event) => {
                        const data = JSON.parse(event.data);
                        this.updatePendingReportsCount(data.count);
                    });

                    this.reportsEventSource.addEventListener('dashboard_stats_update', (event) => {
                        const stats = JSON.parse(event.data);
                        this.updateDashboardStats(stats);
                    });

                    this.reportsEventSource.addEventListener('report_status_update', (event) => {
                        const update = JSON.parse(event.data);
                        this.handleReportStatusUpdate(update);
                    });

                    this.reportsEventSource.addEventListener('error', (event) => {
                        const error = JSON.parse(event.data || '{}');
                        console.error('Reports SSE error event:', error);
                        if (error.message && error.message.includes('Admin access required')) {
                            this.showNotification('Admin access required for live reports', 'error');
                        }
                    });

                    this.reportsEventSource.onerror = (error) => {
                        console.error('Reports SSE connection error:', error);
                        this.reportsSSEConnected = false;
                        this.handleSSEError('reports');
                    };

                } catch (error) {
                    console.error('Failed to connect to reports SSE:', error);
                    this.reportsSSEConnected = false;
                }
            }

            handleNewNotification(notification) {
                // Update notification badge immediately
                this.loadNotificationCount();
                
                // Show toast notification
                this.showNotification(
                    `New ${notification.type}: ${notification.message}`, 
                    'info'
                );
                
                // Pulse the notification icon
                this.pulseNotificationIcon();
            }

            handleNewReport(report) {
                // Add the new report to the top of the reports list
                this.prependReportToList(report);
                
                // Update statistics
                this.incrementPendingReportsCount();
                
                // Show alert for high priority reports
                if (this.getReportPriorityClass(report.reason) === 'high') {
                    this.showNotification(
                        `HIGH PRIORITY: New ${this.formatReportType(report.reason)} report from @${report.reporter.username}`,
                        'warning'
                    );
                }
                
                // Pulse the live indicator
                this.pulseIndicator();
            }

            handleReportStatusUpdate(update) {
                // Find and update the report in the list
                const reportElements = document.querySelectorAll('[data-report-id]');
                reportElements.forEach(element => {
                    if (element.dataset.reportId === update.report_id.toString()) {
                        if (update.status !== 'pending') {
                            // Remove from pending list with fade effect
                            element.style.opacity = '0.5';
                            setTimeout(() => {
                                element.remove();
                            }, 1000);
                        }
                    }
                });
                
                // Update pending count
                this.loadPendingReports();
            }

            prependReportToList(report) {
                const reportsContainer = document.querySelector('.reports-list');
                if (!reportsContainer) return;

                const reportElement = this.createReportElement(report);
                reportElement.style.opacity = '0';
                reportElement.style.transform = 'translateY(-20px)';
                
                reportsContainer.insertBefore(reportElement, reportsContainer.firstChild);
                
                // Animate in
                setTimeout(() => {
                    reportElement.style.transition = 'all 0.3s ease';
                    reportElement.style.opacity = '1';
                    reportElement.style.transform = 'translateY(0)';
                }, 100);
                
                // Limit to 5 reports in the list
                const reports = reportsContainer.children;
                if (reports.length > 5) {
                    reports[reports.length - 1].remove();
                }
            }

            updatePendingReportsCount(count) {
                const reportsElement = document.querySelector('.stat-card:nth-child(3) .stat-content h3');
                if (reportsElement) {
                    reportsElement.textContent = count.toLocaleString();
                }
                
                const reportsChange = document.querySelector('.stat-card:nth-child(3) .stat-change');
                if (reportsChange) {
                    reportsChange.textContent = `${count} pending`;
                    reportsChange.className = count > 0 ? 'stat-change negative' : 'stat-change neutral';
                }
            }

            incrementPendingReportsCount() {
                const reportsElement = document.querySelector('.stat-card:nth-child(3) .stat-content h3');
                if (reportsElement) {
                    const currentCount = parseInt(reportsElement.textContent.replace(/,/g, '')) || 0;
                    const newCount = currentCount + 1;
                    reportsElement.textContent = newCount.toLocaleString();
                    
                    // Update the change indicator
                    const reportsChange = document.querySelector('.stat-card:nth-child(3) .stat-change');
                    if (reportsChange) {
                        reportsChange.textContent = `${newCount} pending`;
                        reportsChange.className = 'stat-change negative';
                    }
                }
            }

            pulseNotificationIcon() {
                const notificationBtn = document.querySelector('.admin-notifications .icon-btn');
                if (notificationBtn) {
                    notificationBtn.style.animation = 'pulse 0.5s ease-in-out';
                    setTimeout(() => {
                        notificationBtn.style.animation = '';
                    }, 500);
                }
            }

            handleSSEError(type) {
                console.warn(`${type} SSE connection lost. Attempting to reconnect...`);
                this.updateLiveIndicator(false);
                
                // Attempt to reconnect after 5 seconds
                setTimeout(() => {
                    if (type === 'notifications' && (!this.notificationEventSource || this.notificationEventSource.readyState === EventSource.CLOSED)) {
                        this.connectToNotificationSSE();
                    } else if (type === 'reports' && (!this.reportsEventSource || this.reportsEventSource.readyState === EventSource.CLOSED)) {
                        this.connectToReportsSSE();
                    }
                }, 5000);
            }

            // Cleanup method for when page is unloaded
            cleanup() {
                if (this.notificationEventSource) {
                    this.notificationEventSource.close();
                }
                if (this.reportsEventSource) {
                    this.reportsEventSource.close();
                }
            }

            updateLiveIndicator(isLive) {
                const indicator = document.getElementById('liveIndicator');
                if (indicator) {
                    indicator.style.display = isLive ? 'block' : 'none';
                    indicator.title = isLive ? 'Live updates active' : 'Live updates paused';
                }
            }

            pulseIndicator() {
                const indicator = document.getElementById('liveIndicator');
                if (indicator) {
                    indicator.style.animation = 'pulse 0.5s ease-in-out';
                    setTimeout(() => {
                        indicator.style.animation = '';
                    }, 500);
                }
            }

            // Handle connection status and errors
            handleConnectionError() {
                this.updateLiveIndicator(false);
                this.showNotification('Connection lost. Retrying...', 'warning');
                
                // Retry connection after 5 seconds
                setTimeout(() => {
                    this.loadDashboardData().then(() => {
                        this.updateLiveIndicator(true);
                        this.showNotification('Connection restored', 'success');
                    }).catch(() => {
                        this.handleConnectionError();
                    });
                }, 5000);
            }

            // Enhanced error handling with retry logic
            async apiRequestWithRetry(endpoint, method = 'GET', data = null, retries = 2) {
                for (let i = 0; i <= retries; i++) {
                    try {
                        return await this.apiRequest(endpoint, method, data);
                    } catch (error) {
                        if (i === retries) {
                            throw error;
                        }
                        // Wait before retry (exponential backoff)
                        await new Promise(resolve => setTimeout(resolve, Math.pow(2, i) * 1000));
                    }
                }
            }

            updateAnalyticsDisplay(userData, postData) {
                // Update charts and analytics displays if they exist on the page
                const activityChart = document.getElementById('activityChart');
                if (activityChart && window.Chart) {
                    this.updateActivityChart(userData, postData);
                }
            }

        }        // Initialize dashboard when DOM is loaded
        document.addEventListener('DOMContentLoaded', () => {
            window.adminDashboard = new AdminDashboard();
        });

        // Cleanup when page is unloaded
        window.addEventListener('beforeunload', () => {
            if (window.adminDashboard) {
                window.adminDashboard.cleanup();
            }
        });
    </script>
</body>
</html>
