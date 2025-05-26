<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics & Reports - SocialConnect Admin</title>
    <link rel="stylesheet" href="../css/generic.css">
    <link rel="stylesheet" href="../css/admin-analytics.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="admin-body">
    <!-- Admin Header -->
    <header class="admin-header">
        <div class="admin-header-content">
            <div class="admin-header-left">
                <div class="admin-logo">
                    <h1>SocialConnect Admin</h1>
                </div>
                <nav class="admin-nav">
                    <a href="admin-dashboard.php" class="admin-nav-link">Dashboard</a>
                    <a href="admin-users.php" class="admin-nav-link">Users</a>
                    <a href="admin-analytics.php" class="admin-nav-link active">Analytics</a>
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
                <div class="admin-user-menu">
                    <button class="admin-user-btn" aria-label="User menu">
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
            <!-- Page Header -->
            <div class="admin-page-header">
                <h1>Analytics & Reports</h1>
                <p>Comprehensive insights and platform analytics</p>
            </div>

            <!-- Loading Indicator -->
            <div id="loadingIndicator" class="loading-indicator" style="display: none;">
                <div class="spinner"></div>
                <p>Loading analytics data...</p>
            </div>

            <!-- Error Message -->
            <div id="errorMessage" class="error-message" style="display: none;">
                <p>Failed to load analytics data. Please check your authentication and try again.</p>
                <button onclick="window.analytics.loadInitialData()">Retry</button>
            </div>

            <!-- Analytics Controls -->
            <section class="analytics-controls">
                <div class="analytics-filters">
                    <select class="filter-select" id="timeRangeFilter">
                        <option value="7d">Last 7 Days</option>
                        <option value="30d" selected>Last 30 Days</option>
                        <option value="90d">Last 3 Months</option>
                        <option value="1y">Last Year</option>
                        <option value="custom">Custom Range</option>
                    </select>
                    
                    <div id="customDateRange" style="display: none;">
                        <input type="date" id="startDate" class="filter-select">
                        <input type="date" id="endDate" class="filter-select">
                        <button class="btn-secondary" onclick="window.analytics.applyCustomRange()">Apply</button>
                    </div>
                    
                    <select class="filter-select" id="metricFilter">
                        <option value="all">All Metrics</option>
                        <option value="users">User Metrics</option>
                        <option value="content">Content Metrics</option>
                        <option value="reports">Reports</option>
                    </select>
                    
                    <button class="btn-primary" id="exportReportBtn">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z"/>
                            <polyline points="14,2 14,8 20,8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                        </svg>
                        Export Report
                    </button>
                    
                    <button class="btn-secondary" id="refreshDataBtn">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Refresh
                    </button>
                </div>
            </section>

            <!-- Key Metrics Overview -->
            <section class="metrics-overview">
                <div class="metric-card" data-metric="users">
                    <div class="metric-header">
                        <h3>Total Users</h3>
                        <div class="metric-trend neutral" id="totalUsersTrend">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="metric-value" id="totalUsersValue">Loading...</div>
                    <div class="metric-subtitle">Registered users</div>
                </div>

                <div class="metric-card" data-metric="users">
                    <div class="metric-header">
                        <h3>Active Users</h3>
                        <div class="metric-trend neutral" id="activeUsersTrend">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M15 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm-9-2V7H4v3H1v2h3v3h2v-3h3v-2H6zm9 4c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="metric-value" id="activeUsersValue">Loading...</div>
                    <div class="metric-subtitle">Active users</div>
                </div>

                <div class="metric-card" data-metric="content">
                    <div class="metric-header">
                        <h3>Total Posts</h3>
                        <div class="metric-trend neutral" id="totalPostsTrend">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="metric-value" id="totalPostsValue">Loading...</div>
                    <div class="metric-subtitle">Published posts</div>
                </div>

                <div class="metric-card" data-metric="reports">
                    <div class="metric-header">
                        <h3>Pending Reports</h3>
                        <div class="metric-trend neutral" id="pendingReportsTrend">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="metric-value" id="pendingReportsValue">Loading...</div>
                    <div class="metric-subtitle">Awaiting review</div>
                </div>

                <div class="metric-card" data-metric="users">
                    <div class="metric-header">
                        <h3>New Users This Week</h3>
                        <div class="metric-trend positive" id="newUsersWeekTrend">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M7 14l5-5 5 5z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="metric-value" id="newUsersWeekValue">Loading...</div>
                    <div class="metric-subtitle">New registrations</div>
                </div>
            </section>

            <!-- Charts Grid -->
            <div class="charts-grid">
                <!-- User Growth Chart -->
                <section class="chart-card" data-metric="users">
                    <div class="chart-header">
                        <h3>User Growth Trend</h3>
                        <div class="chart-controls">
                            <button class="chart-toggle active" data-chart="userGrowth" data-type="line">Line</button>
                            <button class="chart-toggle" data-chart="userGrowth" data-type="bar">Bar</button>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="userGrowthChart"></canvas>
                    </div>
                </section>

                <!-- Content Distribution -->
                <section class="chart-card" data-metric="content">
                    <div class="chart-header">
                        <h3>Content Distribution</h3>
                        <div class="chart-controls">
                            <button class="chart-toggle active" data-chart="content" data-type="doughnut">Doughnut</button>
                            <button class="chart-toggle" data-chart="content" data-type="pie">Pie</button>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="contentChart"></canvas>
                    </div>
                </section>

                <!-- Reports Overview -->
                <section class="chart-card" data-metric="reports">
                    <div class="chart-header">
                        <h3>Reports Overview</h3>
                        <div class="chart-controls">
                            <button class="chart-toggle active" data-chart="reports" data-type="bar">Bar</button>
                            <button class="chart-toggle" data-chart="reports" data-type="line">Line</button>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="reportsChart"></canvas>
                    </div>
                </section>

                <!-- User Activity Heatmap -->
                <section class="chart-card" data-metric="users">
                    <div class="chart-header">
                        <h3>User Activity by Hour</h3>
                    </div>
                    <div class="chart-container">
                        <canvas id="activityChart"></canvas>
                    </div>
                </section>

                <!-- Top Performers -->
                <section class="chart-card" data-metric="content">
                    <div class="chart-header">
                        <h3>Top Content Creators</h3>
                    </div>
                    <div class="chart-container">
                        <canvas id="topCreatorsChart"></canvas>
                    </div>
                </section>
            </div>

            <!-- Reports Summary Table -->
            <section class="reports-summary" data-metric="reports">
                <div class="section-header">
                    <h3>Recent Reports Summary</h3>
                    <button class="btn-secondary" onclick="window.analytics.loadReportsData()">Refresh Reports</button>
                </div>
                <div class="table-container">
                    <table id="reportsTable">
                        <thead>
                            <tr>
                                <th>Report ID</th>
                                <th>Type</th>
                                <th>Reason</th>
                                <th>Reporter</th>
                                <th>Reported User</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="reportsTableBody">
                            <tr>
                                <td colspan="8" class="loading-cell">Loading reports...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>

    <!-- Scripts -->
    <script>
        class AnalyticsDashboard {            constructor() {
                this.charts = {};
                this.apiBaseUrl = '/webdev/backend/src/api';
                this.authToken = this.getAuthToken();
                this.currentData = {
                    dashboard: null,
                    users: null,
                    posts: null,
                    reports: null,
                    dateRange: null
                };
                this.eventSource = null;
                
                this.initializeEventListeners();
                this.loadInitialData();
                
                // Initialize live notifications after a short delay
                setTimeout(() => {
                    this.requestNotificationPermission();
                    this.initializeLiveNotifications();
                }, 1000);
            }

            getAuthToken() {
                // Try multiple sources for the auth token
                return localStorage.getItem('authToken') || 
                       sessionStorage.getItem('authToken') || 
                       localStorage.getItem('adminToken') ||
                       sessionStorage.getItem('adminToken');
            }

            async apiRequest(endpoint, method = 'GET', data = null) {
                const config = {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json'
                    }
                };

                // Add auth token if available
                if (this.authToken) {
                    config.headers['Authorization'] = `Bearer ${this.authToken}`;
                }

                if (data && (method === 'POST' || method === 'PUT')) {
                    config.body = JSON.stringify(data);
                }

                try {
                    const response = await fetch(`${this.apiBaseUrl}${endpoint}`, config);
                    const result = await response.json();
                    
                    if (!response.ok) {
                        throw new Error(result.message || `HTTP ${response.status}: ${response.statusText}`);
                    }
                    
                    return result;
                } catch (error) {
                    console.error('API Error:', error);
                    this.showError('API Error: ' + error.message);
                    throw error;
                }
            }            async loadInitialData() {
                try {
                    this.showLoading(true);
                    this.hideError();
                      // Load user and notification data first
                    console.log('Loading notification count and current user data...');
                    await Promise.allSettled([
                        this.loadCurrentUser(),
                        this.loadNotificationCount()
                    ]);
                    console.log('User and notification data loading completed.');
                    
                    // Get date range from filter
                    const timeRange = document.getElementById('timeRangeFilter').value;
                    const dateRange = this.getDateRange(timeRange);
                    this.currentData.dateRange = dateRange;
                    
                    // Load all analytics data in parallel
                    const promises = [
                        this.loadDashboardData(),
                        this.loadAnalyticsData(dateRange),
                        this.loadReportsData()
                    ];
                    
                    await Promise.allSettled(promises);
                    
                    // Initialize charts after data is loaded
                    this.initializeCharts();
                    
                    this.showNotification('Analytics data loaded successfully', 'success');
                    
                } catch (error) {
                    console.error('Failed to load initial data:', error);
                    this.showError('Failed to load analytics data. Please check your authentication and try again.');
                } finally {
                    this.showLoading(false);                }
            }            async loadNotificationCount() {
                try {
                    const response = await this.apiRequest('/notifications/unread_count.php');
                    
                    if (response && response.success && typeof response.count !== 'undefined') {
                        this.updateNotificationBadge(response.count);
                    } else {
                        console.warn('Invalid notification count response:', response);
                        this.updateNotificationBadge(0);
                    }
                } catch (error) {
                    console.error('Failed to load notification count:', error);
                    // Set default count of 0 on error
                    this.updateNotificationBadge(0);
                }
            }

            async loadCurrentUser() {
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
            }updateNotificationBadge(count) {
                const badge = document.getElementById('notificationBadge');
                if (badge) {
                    const numericCount = parseInt(count) || 0;
                    badge.textContent = numericCount.toString();
                    // Hide badge if count is 0
                    if (numericCount > 0) {
                        badge.style.display = 'inline';
                        badge.setAttribute('aria-label', `${numericCount} unread notifications`);
                    } else {
                        badge.style.display = 'none';
                        badge.removeAttribute('aria-label');
                    }
                }
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
                }                // Update user avatar
                const userAvatar = document.getElementById('userAvatar');
                const fileUrl = userData.profile_picture || 'media/images/placeholder.png';
                
                if (userAvatar) {
                    // Ensure the URL is absolute
                    let profilePicUrl = fileUrl;
                    if (!profilePicUrl.startsWith('http') && !profilePicUrl.startsWith('/')) {
                        profilePicUrl = `/webdev/backend/${fileUrl}`;
                    }
                    
                    userAvatar.src = profilePicUrl;
                    userAvatar.alt = `${userData.first_name || userData.username || 'Admin'} Avatar`;
                    
                    // Add error handling for broken images with infinite loop prevention
                    userAvatar.onerror = function() {
                        if (!this.src.includes('placeholder.png')) {
                            this.src = '/webdev/backend/media/images/placeholder.png';
                            this.alt = 'Default Admin Avatar';
                        }
                    };
                }
            }

            async loadDashboardData() {
                try {
                    const response = await this.apiRequest('/admin/dashboard.php');
                    
                    if (response.success) {
                        this.currentData.dashboard = response.dashboard;
                        this.updateDashboardMetrics(response.dashboard.statistics);
                    }
                } catch (error) {
                    console.error('Failed to load dashboard data:', error);
                }
            }

            async loadAnalyticsData(dateRange) {
                try {
                    // Load user analytics
                    const userAnalyticsPromise = this.apiRequest(
                        `/admin/analytics_users.php?start_date=${dateRange.start}&end_date=${dateRange.end}`
                    );
                    
                    // Load post analytics
                    const postAnalyticsPromise = this.apiRequest(
                        `/admin/analytics_posts.php?start_date=${dateRange.start}&end_date=${dateRange.end}`
                    );
                    
                    const [userAnalytics, postAnalytics] = await Promise.allSettled([
                        userAnalyticsPromise, 
                        postAnalyticsPromise
                    ]);
                    
                    // Store successful results
                    if (userAnalytics.status === 'fulfilled' && userAnalytics.value.success) {
                        this.currentData.users = userAnalytics.value.analytics;
                    }
                    
                    if (postAnalytics.status === 'fulfilled' && postAnalytics.value.success) {
                        this.currentData.posts = postAnalytics.value.analytics;
                    }
                    
                } catch (error) {
                    console.error('Failed to load analytics data:', error);
                }
            }

            async loadReportsData() {
                try {
                    const response = await this.apiRequest('/admin/reports.php?limit=10&status=pending');
                    
                    if (response.success) {
                        this.currentData.reports = response;
                        this.updateReportsTable(response.reports);
                    }
                } catch (error) {
                    console.error('Failed to load reports data:', error);
                }
            }

            updateDashboardMetrics(stats) {
                console.log('Updating dashboard metrics with stats:', stats);
                // Update metric cards with real data
                document.getElementById('totalUsersValue').textContent = stats.total_users || 0;
                document.getElementById('activeUsersValue').textContent = stats.active_users || 0;
                document.getElementById('totalPostsValue').textContent = stats.total_posts || 0;
                document.getElementById('pendingReportsValue').textContent = stats.pending_reports || 0;
                document.getElementById('newUsersWeekValue').textContent = stats.new_users_this_week || 0;
            }

            updateReportsTable(reports) {
                const tbody = document.getElementById('reportsTableBody');
                
                if (!reports || reports.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="8" class="no-data">No reports found</td></tr>';
                    return;
                }
                
                tbody.innerHTML = reports.map(report => `
                    <tr>
                        <td>#${report.report_id}</td>
                        <td>${report.content_type}</td>
                        <td>${report.reason}</td>
                        <td>${report.reporter_username || 'N/A'}</td>
                        <td>${report.reported_username || 'N/A'}</td>
                        <td><span class="status-badge status-${report.status}">${report.status}</span></td>
                        <td>${new Date(report.created_at).toLocaleDateString()}</td>
                        <td>
                            <button class="btn-sm btn-primary" onclick="window.analytics.viewReport(${report.report_id})">View</button>
                        </td>
                    </tr>
                `).join('');
            }

            getDateRange(timeRange) {
                const end = new Date();
                let start = new Date();
                
                switch (timeRange) {
                    case '7d':
                        start.setDate(end.getDate() - 7);
                        break;
                    case '30d':
                        start.setDate(end.getDate() - 30);
                        break;
                    case '90d':
                        start.setDate(end.getDate() - 90);
                        break;
                    case '1y':
                        start.setFullYear(end.getFullYear() - 1);
                        break;
                    case 'custom':
                        const startInput = document.getElementById('startDate');
                        const endInput = document.getElementById('endDate');
                        if (startInput.value && endInput.value) {
                            return {
                                start: startInput.value,
                                end: endInput.value
                            };
                        }
                        // Fallback to 30 days if custom dates not set
                        start.setDate(end.getDate() - 30);
                        break;
                    default:
                        start.setDate(end.getDate() - 30);
                }
                
                return {
                    start: start.toISOString().split('T')[0],
                    end: end.toISOString().split('T')[0]
                };
            }

            filterMetrics(metricType) {
                const cards = document.querySelectorAll('.metric-card, .chart-card, .reports-summary');
                
                cards.forEach(card => {
                    if (metricType === 'all' || card.dataset.metric === metricType) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });
            }

            showLoading(show) {
                const indicator = document.getElementById('loadingIndicator');
                indicator.style.display = show ? 'block' : 'none';
            }

            showError(message) {
                const errorDiv = document.getElementById('errorMessage');
                errorDiv.querySelector('p').textContent = message;
                errorDiv.style.display = 'block';
            }

            hideError() {
                document.getElementById('errorMessage').style.display = 'none';
            }

            applyCustomRange() {
                const timeRangeFilter = document.getElementById('timeRangeFilter');
                if (timeRangeFilter.value === 'custom') {
                    this.updateChartsData('custom');
                }
            }

            initializeEventListeners() {
                // Time range filter
                document.getElementById('timeRangeFilter').addEventListener('change', async (e) => {
                    const customRange = document.getElementById('customDateRange');
                    if (e.target.value === 'custom') {
                        customRange.style.display = 'block';
                    } else {
                        customRange.style.display = 'none';
                        await this.updateChartsData(e.target.value);
                    }
                });

                // Metric filter
                document.getElementById('metricFilter').addEventListener('change', (e) => {
                    this.filterMetrics(e.target.value);
                });

                // Export report
                document.getElementById('exportReportBtn').addEventListener('click', () => {
                    this.exportReport();
                });

                // Refresh data
                document.getElementById('refreshDataBtn').addEventListener('click', () => {
                    this.loadInitialData();
                });

                // Chart toggle buttons
                document.querySelectorAll('.chart-toggle').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        const chartName = e.target.dataset.chart;
                        const chartType = e.target.dataset.type;
                        this.toggleChartType(chartName, chartType, e.target);
                    });
                });

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
            }

            async updateChartsData(timeRange) {
                try {
                    this.showLoading(true);
                    
                    const dateRange = this.getDateRange(timeRange);
                    this.currentData.dateRange = dateRange;
                    
                    await this.loadAnalyticsData(dateRange);
                    
                    // Update charts with new data
                    this.updateCharts();
                    
                    this.showNotification(`Analytics updated for ${timeRange}`, 'info');
                } catch (error) {
                    this.showError('Failed to update charts data');
                } finally {
                    this.showLoading(false);
                }
            }            initializeCharts() {
                // Always destroy existing charts first to prevent canvas reuse errors
                this.destroyCharts();
                
                this.createUserGrowthChart();
                this.createContentChart();
                this.createReportsChart();
                this.createActivityChart();
                this.createTopCreatorsChart();
            }            destroyCharts() {
                // Destroy existing charts to prevent canvas reuse errors
                Object.keys(this.charts).forEach(chartName => {
                    if (this.charts[chartName]) {
                        try {
                            this.charts[chartName].destroy();
                        } catch (error) {
                            console.warn(`Error destroying chart ${chartName}:`, error);
                        }
                        this.charts[chartName] = null;
                    }
                });
                
                // Clear the charts object completely
                this.charts = {};
                
                // Additional cleanup for Chart.js internal cache
                Chart.helpers.each(Chart.instances, function(instance) {
                    try {
                        instance.destroy();
                    } catch (error) {
                        console.warn('Error destroying Chart instance:', error);
                    }
                });
            }            createUserGrowthChart() {
                // Get the canvas element
                const canvas = document.getElementById('userGrowthChart');
                
                // Clear any Chart instance attached to this canvas
                const chartInstance = Chart.getChart(canvas);
                if (chartInstance) {
                    try {
                        chartInstance.destroy();
                    } catch (error) {
                        console.warn('Error destroying chart instance on canvas:', error);
                    }
                }
                
                // Then get the context for drawing
                const ctx = canvas.getContext('2d');
                
                // Also check our internal reference
                if (this.charts.userGrowth) {
                    try {
                        this.charts.userGrowth.destroy();
                    } catch (error) {
                        console.warn('Error destroying existing userGrowth chart:', error);
                    }
                }
                
                // Use real data if available
                let labels = [];
                let newUsersData = [];
                let totalUsersData = [];
                
                if (this.currentData.users && this.currentData.users.daily_registrations) {
                    const dailyData = this.currentData.users.daily_registrations;
                    labels = dailyData.map(d => new Date(d.date).toLocaleDateString());
                    newUsersData = dailyData.map(d => d.count);
                    
                    // Calculate cumulative total
                    let total = 0;
                    totalUsersData = newUsersData.map(count => total += count);
                } else {
                    // Fallback dummy data
                    labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
                    newUsersData = [5, 8, 12, 6, 15, 10];
                    totalUsersData = [5, 13, 25, 31, 46, 56];
                }
                
                this.charts.userGrowth = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'New Users',
                            data: newUsersData,
                            borderColor: '#2563eb',
                            backgroundColor: 'rgba(37, 99, 235, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4
                        }, {
                            label: 'Total Users',
                            data: totalUsersData,
                            borderColor: '#16a34a',
                            backgroundColor: 'rgba(22, 163, 74, 0.1)',
                            borderWidth: 2,
                            fill: false,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            },
                            x: {
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            }
                        }
                    }
                });
            }

            createContentChart() {
                const ctx = document.getElementById('contentChart').getContext('2d');
                
                // Use real data if available
                let labels = [];
                let data = [];
                
                if (this.currentData.posts && this.currentData.posts.posts_by_type) {
                    const typeData = this.currentData.posts.posts_by_type;
                    labels = typeData.map(t => t.post_type);
                    data = typeData.map(t => t.count);
                } else {
                    // Fallback dummy data
                    labels = ['Text Posts', 'Images', 'Videos', 'Links'];
                    data = [35, 45, 15, 5];
                }
                
                this.charts.content = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: data,
                            backgroundColor: [
                                '#2563eb',
                                '#16a34a',
                                '#dc2626',
                                '#ca8a04',
                                '#9333ea'
                            ],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                            }
                        }
                    }
                });
            }

            createReportsChart() {
                const ctx = document.getElementById('reportsChart').getContext('2d');
                
                this.charts.reports = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['Spam', 'Harassment', 'Inappropriate', 'Violence', 'Other'],
                        datasets: [{
                            label: 'Reports',
                            data: [12, 5, 8, 2, 3],
                            backgroundColor: [
                                '#dc2626',
                                '#ea580c',
                                '#ca8a04',
                                '#16a34a',
                                '#6b7280'
                            ],
                            borderRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }

            createActivityChart() {
                const ctx = document.getElementById('activityChart').getContext('2d');
                
                // Generate hourly activity data
                const hours = Array.from({length: 24}, (_, i) => `${i}:00`);
                const activityData = hours.map(() => Math.floor(Math.random() * 100));
                
                this.charts.activity = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: hours,
                        datasets: [{
                            label: 'Active Users',
                            data: activityData,
                            backgroundColor: '#2563eb',
                            borderRadius: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }

            createTopCreatorsChart() {
                const ctx = document.getElementById('topCreatorsChart').getContext('2d');
                
                // Use real data if available
                let labels = [];
                let data = [];
                
                if (this.currentData.posts && this.currentData.posts.top_posters) {
                    const topPosters = this.currentData.posts.top_posters;
                    labels = topPosters.map(p => p.username || `${p.first_name} ${p.last_name}`);
                    data = topPosters.map(p => p.posts_count);
                } else {
                    // Fallback dummy data
                    labels = ['User1', 'User2', 'User3', 'User4', 'User5'];
                    data = [25, 18, 15, 12, 8];
                }
                
                this.charts.topCreators = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Posts',
                            data: data,
                            backgroundColor: '#16a34a',
                            borderRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }            
            
            updateCharts() {
                // Call the destroyCharts method first to properly clean up all charts
                this.destroyCharts();
                
                // Recreate all charts with new data
                this.createUserGrowthChart();
                this.createContentChart();
                this.createReportsChart();
                this.createActivityChart();
                this.createTopCreatorsChart();
                this.initializeCharts();
            }

            toggleChartType(chartName, chartType, button) {
                // Update active button
                button.parentElement.querySelectorAll('.chart-toggle').forEach(btn => {
                    btn.classList.remove('active');
                });
                button.classList.add('active');

                // Update chart type
                if (this.charts[chartName]) {
                    this.charts[chartName].config.type = chartType;
                    this.charts[chartName].update();
                }
            }            async viewReport(reportId) {
                // Navigate to the detailed report review page
                window.location.href = `admin-review-report.php?id=${reportId}`;
            }

            exportReport() {
                try {
                    // Create comprehensive CSV content from current data
                    const headers = ['Metric', 'Value', 'Period', 'Date'];
                    const data = [];
                    
                    // Add dashboard statistics
                    if (this.currentData.dashboard) {
                        const stats = this.currentData.dashboard.statistics;
                        data.push(['Total Users', stats.total_users, 'Current', new Date().toISOString().split('T')[0]]);
                        data.push(['Active Users', stats.active_users, 'Current', new Date().toISOString().split('T')[0]]);
                        data.push(['Total Posts', stats.total_posts, 'Current', new Date().toISOString().split('T')[0]]);
                        data.push(['Pending Reports', stats.pending_reports, 'Current', new Date().toISOString().split('T')[0]]);
                        data.push(['New Users This Week', stats.new_users_this_week, 'This Week', new Date().toISOString().split('T')[0]]);
                    }
                    
                    // Add analytics data
                    if (this.currentData.users && this.currentData.users.overview) {
                        const userStats = this.currentData.users.overview;
                        data.push(['New Users in Period', userStats.new_users_in_period, `${this.currentData.dateRange.start} to ${this.currentData.dateRange.end}`, new Date().toISOString().split('T')[0]]);
                    }
                    
                    if (this.currentData.posts && this.currentData.posts.overview) {
                        const postStats = this.currentData.posts.overview;
                        data.push(['Total Posts in Period', postStats.total_posts, `${this.currentData.dateRange.start} to ${this.currentData.dateRange.end}`, new Date().toISOString().split('T')[0]]);
                    }

                    const csvContent = [
                        headers.join(','),
                        ...data.map(row => row.join(','))
                    ].join('\n');

                    // Download CSV
                    const blob = new Blob([csvContent], { type: 'text/csv' });
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `analytics_report_${new Date().toISOString().split('T')[0]}.csv`;
                    a.click();
                    window.URL.revokeObjectURL(url);

                    this.showNotification('Analytics report exported successfully!', 'success');
                } catch (error) {
                    console.error('Export failed:', error);
                    this.showNotification('Failed to export report', 'error');
                }
            }

            showNotification(message, type = 'info') {
                // Create notification element
                const notification = document.createElement('div');
                notification.className = `notification notification-${type}`;
                notification.innerHTML = `
                    <span>${message}</span>
                    <button onclick="this.parentElement.remove()">&times;</button>
                `;
                
                // Add to page
                document.body.appendChild(notification);
                
                // Show notification
                setTimeout(() => {
                    notification.classList.add('show');
                }, 100);
                
                // Auto-hide after 5 seconds
                setTimeout(() => {
                    if (notification.parentElement) {
                        notification.classList.remove('show');
                        setTimeout(() => {
                            if (notification.parentElement) {
                                notification.remove();
                            }
                        }, 300);
                    }
                }, 5000);
            }

            async testApiIntegration() {
                console.log('Testing API integration...');
                
                // Test notification count endpoint
                try {
                    console.log('Testing notification count endpoint...');
                    const notificationResponse = await this.apiRequest('/notifications/unread_count.php');
                    console.log('Notification count response:', notificationResponse);
                } catch (error) {
                    console.error('Notification count test failed:', error);
                }
                
                // Test current user endpoint
                try {
                    console.log('Testing current user endpoint...');
                    const userResponse = await this.apiRequest('/users/me.php');
                    console.log('Current user response:', userResponse);
                } catch (error) {
                    console.error('Current user test failed:', error);
                }
            }

            initializeLiveNotifications() {
                // Check if browser supports Server-Sent Events
                if (typeof(EventSource) === "undefined") {
                    console.warn('Server-Sent Events not supported by this browser');
                    return;
                }

                // Create EventSource connection
                const eventSource = new EventSource(`${this.apiBaseUrl}/notifications/live_notifications.php?last_id=0`);
                  eventSource.onopen = () => {
                    console.log('Live notifications connected');
                    const indicator = document.getElementById('liveIndicator');
                    if (indicator) {
                        indicator.classList.add('connected');
                        indicator.classList.remove('disconnected');
                    }
                };

                eventSource.addEventListener('connected', (event) => {
                    const data = JSON.parse(event.data);
                    console.log('Live notifications:', data.message);
                });

                eventSource.addEventListener('new_notification', (event) => {
                    const notification = JSON.parse(event.data);
                    console.log('New notification received:', notification);
                    
                    // Show browser notification if permission granted
                    this.showBrowserNotification(notification);
                    
                    // Update UI immediately
                    this.handleNewNotification(notification);
                });

                eventSource.addEventListener('count_update', (event) => {
                    const data = JSON.parse(event.data);
                    console.log('Notification count updated:', data.count);
                    this.updateNotificationBadge(data.count);
                });

                eventSource.addEventListener('error', (event) => {
                    const data = JSON.parse(event.data);
                    console.error('Live notifications error:', data.message);
                });                eventSource.onerror = (error) => {
                    console.error('EventSource failed:', error);
                    const indicator = document.getElementById('liveIndicator');
                    if (indicator) {
                        indicator.classList.add('disconnected');
                        indicator.classList.remove('connected');
                    }
                    // Attempt to reconnect after 5 seconds
                    setTimeout(() => {
                        console.log('Attempting to reconnect live notifications...');
                        this.initializeLiveNotifications();
                    }, 5000);
                };

                // Store reference for cleanup
                this.eventSource = eventSource;
            }

            async requestNotificationPermission() {
                if ('Notification' in window) {
                    const permission = await Notification.requestPermission();
                    return permission === 'granted';
                }
                return false;
            }

            showBrowserNotification(notification) {
                if ('Notification' in window && Notification.permission === 'granted') {                const options = {
                        body: notification.message,
                        icon: notification.sender.profile_picture || '/webdev/backend/media/images/placeholder.png',
                        badge: '../assets/images/notification-icon.png',
                        tag: `notification-${notification.notification_id}`,
                        requireInteraction: false,
                        silent: false
                    };

                    const browserNotification = new Notification(notification.title, options);
                    
                    // Auto-close after 5 seconds
                    setTimeout(() => {
                        browserNotification.close();
                    }, 5000);

                    // Handle click to focus window
                    browserNotification.onclick = () => {
                        window.focus();
                        browserNotification.close();
                        // You could also navigate to the relevant page here
                    };
                }
            }

            handleNewNotification(notification) {
                // Add visual notification in the admin interface
                this.showNotification(
                    `${notification.title}: ${notification.message}`, 
                    'info'
                );

                // Add pulse animation to notification button
                const notificationBtn = document.querySelector('.admin-notifications .icon-btn');
                if (notificationBtn) {
                    notificationBtn.classList.add('pulse-animation');
                    setTimeout(() => {
                        notificationBtn.classList.remove('pulse-animation');
                    }, 2000);
                }
            }

        }

        // Initialize analytics dashboard when DOM is loaded
        document.addEventListener('DOMContentLoaded', () => {
            window.analytics = new AnalyticsDashboard();
        });
    </script>

    <!-- Additional CSS for new features -->
    <style>
        .loading-indicator {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            margin: 1rem 0;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f4f6;
            border-top: 4px solid #2563eb;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .error-message {
            background: #fee2e2;
            border: 1px solid #fca5a5;
            color: #dc2626;
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
            text-align: center;
        }

        .error-message button {
            background: #dc2626;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 0.5rem;
        }

        .reports-summary {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-top: 2rem;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .table-container {
            overflow-x: auto;
        }

        #reportsTable {
            width: 100%;
            border-collapse: collapse;
        }

        #reportsTable th,
        #reportsTable td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        #reportsTable th {
            background: #f9fafb;
            font-weight: 600;
        }

        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-reviewed {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-action_taken {
            background: #dcfce7;
            color: #166534;
        }

        .status-dismissed {
            background: #f3f4f6;
            color: #374151;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            border-radius: 0.25rem;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: #2563eb;
            color: white;
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            color: white;
            z-index: 1000;
            transform: translateX(100%);
            transition: transform 0.3s ease;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .notification.show {
            transform: translateX(0);
        }

        .notification-success {
            background: #16a34a;
        }

        .notification-error {
            background: #dc2626;
        }

        .notification-info {
            background: #2563eb;
        }

        .notification button {
            background: none;
            border: none;
            color: white;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 0;
            line-height: 1;
        }

        #customDateRange {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }        .loading-cell,
        .no-data {
            text-align: center;
            color: #6b7280;
            font-style: italic;
        }

        /* Dynamic notification badge styling */
        .notification-badge {
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .notification-badge[style*="display: none"] {
            opacity: 0;
            transform: scale(0.8);
        }

        /* User avatar loading state */
        .admin-avatar {
            transition: opacity 0.3s ease;
        }

        .admin-avatar[src*="placeholder"] {
            opacity: 0.7;
        }        /* Pulse animation for notification button */
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.7);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(37, 99, 235, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(37, 99, 235, 0);
            }
        }

        .pulse-animation {
            animation: pulse 2s infinite;
        }

        /* Notification badge styles */
        .notification-badge {
            position: absolute;
            top: -6px;
            right: -6px;
            background: #ef4444;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.75rem;
            font-weight: 600;
            min-width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .notification-badge:empty,
        .notification-badge[data-count="0"] {
            display: none;
        }

        .notification-badge.pulse {
            animation: pulse-badge 2s infinite;
        }

        @keyframes pulse-badge {
            0% {
                transform: scale(1);
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }
            50% {
                transform: scale(1.1);
                box-shadow: 0 4px 8px rgba(239, 68, 68, 0.4);
            }
            100% {
                transform: scale(1);
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }
        }

        /* Live indicator connection status */
        .live-indicator.connecting {
            background: #f59e0b;
            animation: pulse 1s infinite;
        }
    </style>
</body>
</html>
