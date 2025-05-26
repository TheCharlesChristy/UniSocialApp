<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Report - SocialConnect Admin</title>
    <link rel="stylesheet" href="../css/generic.css">
    <link rel="stylesheet" href="../css/admin-review-report.css">
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
                </div>
                <nav class="admin-nav">
                    <a href="admin-dashboard.php" class="admin-nav-link">Dashboard</a>
                    <a href="admin-users.php" class="admin-nav-link">Users</a>
                    <a href="admin-analytics.php" class="admin-nav-link active">Analytics</a>
                </nav>
            </div>
            <div class="admin-header-right">
                <div class="admin-notifications">
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
                        <span id="userName">Admin</span>
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
                <div class="page-header-content">
                    <div class="page-header-left">
                        <h1 class="page-title">Review Report</h1>
                        <p class="page-subtitle">Review and take action on user reports</p>
                    </div>
                    <div class="page-header-actions">
                        <button onclick="history.back()" class="btn btn-secondary">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/>
                            </svg>
                            Back
                        </button>
                    </div>
                </div>
            </div>

            <!-- Loading Indicator -->
            <div id="loadingIndicator" class="loading-indicator" style="display: none;">
                <div class="spinner"></div>
                <p>Loading report details...</p>
            </div>

            <!-- Error Message -->
            <div id="errorMessage" class="error-message" style="display: none;">
                <p id="errorText">An error occurred while loading the report.</p>
                <button onclick="loadReport()" class="btn btn-primary">Retry</button>
            </div>

            <!-- Report Content -->
            <div id="reportContent" class="report-content" style="display: none;">
                <!-- Report Overview Card -->
                <div class="report-card">
                    <div class="card-header">
                        <h2>Report Overview</h2>
                        <div class="report-status">
                            <span id="reportStatus" class="status-badge status-pending">Pending</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="report-details-grid">
                            <div class="detail-item">
                                <label>Report ID</label>
                                <span id="reportId">#-</span>
                            </div>
                            <div class="detail-item">
                                <label>Reported Date</label>
                                <span id="reportDate">-</span>
                            </div>
                            <div class="detail-item">
                                <label>Content Type</label>
                                <span id="contentType">-</span>
                            </div>
                            <div class="detail-item">
                                <label>Reason</label>
                                <span id="reportReason">-</span>
                            </div>
                        </div>
                        <div class="report-description">
                            <label>Description</label>
                            <p id="reportDescription">-</p>
                        </div>
                    </div>
                </div>

                <!-- Reporter Information Card -->
                <div class="report-card">
                    <div class="card-header">
                        <h2>Reporter Information</h2>
                    </div>
                    <div class="card-body">
                        <div class="user-info">
                            <div class="user-avatar">
                                <img src="/webdev/backend/media/images/placeholder.png" alt="Reporter" id="reporterAvatar">
                            </div>
                            <div class="user-details">
                                <h3 id="reporterName">-</h3>
                                <p id="reporterUsername">@-</p>
                                <p id="reporterEmail">-</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reported User Information Card -->
                <div class="report-card">
                    <div class="card-header">
                        <h2>Reported User</h2>
                    </div>
                    <div class="card-body">
                        <div class="user-info">
                            <div class="user-avatar">
                                <img src="/webdev/backend/media/images/placeholder.png" alt="Reported User" id="reportedAvatar">
                            </div>
                            <div class="user-details">
                                <h3 id="reportedName">-</h3>
                                <p id="reportedUsername">@-</p>
                                <p id="reportedEmail">-</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Content Details Card -->
                <div class="report-card" id="contentDetailsCard" style="display: none;">
                    <div class="card-header">
                        <h2>Reported Content</h2>
                    </div>
                    <div class="card-body">
                        <div id="contentDetails">
                            <!-- Content will be populated based on content type -->
                        </div>
                    </div>
                </div>

                <!-- Review History Card -->                <div class="report-card" id="reviewHistoryCard" style="display: none;">
                    <div class="card-header">
                        <h2>Review History</h2>
                    </div>
                    <div class="card-body">
                        <div class="user-info">
                            <div class="user-avatar">
                                <img src="/webdev/backend/media/images/placeholder.png" alt="Reviewer" id="reviewerAvatar">
                            </div>
                            <div class="user-details">
                                <h3 id="reviewerName">-</h3>
                                <p id="reviewDate">-</p>
                            </div>
                        </div>
                        <div class="admin-notes-display">
                            <label>Previous Admin Notes</label>
                            <p id="existingAdminNotes">No previous notes</p>
                        </div>
                    </div>
                </div>

                <!-- Admin Actions Card -->
                <div class="report-card">
                    <div class="card-header">
                        <h2>Admin Actions</h2>
                    </div>
                    <div class="card-body">
                        <form id="adminActionForm">
                            <div class="form-group">
                                <label for="statusSelect">Status</label>
                                <select id="statusSelect" name="status" class="form-control">
                                    <option value="pending">Pending</option>
                                    <option value="reviewed">Reviewed</option>
                                    <option value="action_taken">Action Taken</option>
                                    <option value="dismissed">Dismissed</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="adminNotes">Admin Notes</label>
                                <textarea id="adminNotes" name="admin_notes" class="form-control" rows="4" 
                                          placeholder="Add your notes about this report..."></textarea>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Update Report</button>
                                <button type="button" id="dismissBtn" class="btn btn-secondary">Quick Dismiss</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Notification Popup -->
    <div id="notification" class="notification" style="display: none;">
        <p id="notificationText"></p>
        <button onclick="hideNotification()">&times;</button>
    </div>

    <!-- Scripts -->
    <script>        class ReportReviewer {
            constructor() {
                this.apiBaseUrl = '/webdev/backend/src/api';
                this.authToken = this.getAuthToken();
                this.reportId = this.getReportIdFromUrl();
                
                this.initializeEventListeners();
                this.loadReport();
            }

            getAuthToken() {
                return localStorage.getItem('authToken') || sessionStorage.getItem('authToken');
            }

            getReportIdFromUrl() {
                const urlParams = new URLSearchParams(window.location.search);
                return urlParams.get('id');
            }

            async apiRequest(endpoint, method = 'GET', data = null) {
                const config = {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                    }
                };

                if (this.authToken) {
                    config.headers['Authorization'] = `Bearer ${this.authToken}`;
                }

                if (data) {
                    config.body = JSON.stringify(data);
                }

                try {
                    const response = await fetch(`${this.apiBaseUrl}${endpoint}`, config);
                    const result = await response.json();
                    
                    if (!response.ok) {
                        throw new Error(result.message || `HTTP error! status: ${response.status}`);
                    }
                    
                    return result;
                } catch (error) {
                    console.error('API request failed:', error);
                    throw error;
                }
            }            async loadReport() {
                if (!this.reportId) {
                    this.showError('No report ID provided in URL');
                    return;
                }

                this.showLoading(true);
                this.hideError();

                try {
                    const response = await this.apiRequest(`/reports/get_report.php?id=${this.reportId}`);
                    
                    if (response.success && response.report) {
                        this.displayReport(response.report);
                        this.showLoading(false);
                        document.getElementById('reportContent').style.display = 'block';
                    } else {
                        throw new Error(response.message || 'Failed to load report');
                    }
                } catch (error) {
                    console.error('Failed to load report:', error);
                    this.showLoading(false);
                    this.showError(error.message);
                }
            }

            displayReport(report) {
                // Update report overview
                document.getElementById('reportId').textContent = `#${report.report_id}`;
                document.getElementById('reportDate').textContent = this.formatDate(report.created_at);
                document.getElementById('contentType').textContent = this.formatContentType(report.content_type);
                document.getElementById('reportReason').textContent = report.reason;
                document.getElementById('reportDescription').textContent = report.description || 'No description provided';
                
                // Update status
                const statusElement = document.getElementById('reportStatus');
                statusElement.textContent = this.formatStatus(report.status);
                statusElement.className = `status-badge status-${report.status}`;
                  // Update reporter info
                if (report.reporter) {
                    document.getElementById('reporterName').textContent = 
                        `${report.reporter.first_name} ${report.reporter.last_name}`;
                    document.getElementById('reporterUsername').textContent = `@${report.reporter.username}`;
                    document.getElementById('reporterEmail').textContent = report.reporter.email;
                      // Update reporter avatar
                    const reporterAvatar = document.getElementById('reporterAvatar');
                    reporterAvatar.src = this.getProfilePictureUrl(report.reporter.profile_picture_url);
                    reporterAvatar.onerror = () => {
                        // Prevent infinite loop by checking if we're already showing placeholder
                        if (!reporterAvatar.src.includes('placeholder.png')) {
                            reporterAvatar.src = '/webdev/backend/media/images/placeholder.png';
                        }
                    };
                }
                
                // Update reported user info
                if (report.reported_user) {
                    document.getElementById('reportedName').textContent = 
                        `${report.reported_user.first_name || ''} ${report.reported_user.last_name || ''}`.trim() || 'Unknown User';
                    document.getElementById('reportedUsername').textContent = 
                        report.reported_user.username ? `@${report.reported_user.username}` : '@unknown';
                    document.getElementById('reportedEmail').textContent = report.reported_user.email || 'No email';
                      // Update reported user avatar
                    const reportedAvatar = document.getElementById('reportedAvatar');
                    reportedAvatar.src = this.getProfilePictureUrl(report.reported_user.profile_picture_url);
                    reportedAvatar.onerror = () => {
                        // Prevent infinite loop by checking if we're already showing placeholder
                        if (!reportedAvatar.src.includes('placeholder.png')) {
                            reportedAvatar.src = '/webdev/backend/media/images/placeholder.png';
                        }
                    };
                }
                
                // Display content details if available
                if (report.content_details) {
                    this.displayContentDetails(report.content_type, report.content_details);
                }
                
                // Display review history if reviewed
                if (report.reviewed_by) {
                    this.displayReviewHistory(report);
                }
                
                // Set current status in form
                document.getElementById('statusSelect').value = report.status;
                document.getElementById('adminNotes').value = report.admin_notes || '';
            }

            displayContentDetails(contentType, details) {
                const contentDetailsCard = document.getElementById('contentDetailsCard');
                const contentDetailsDiv = document.getElementById('contentDetails');
                
                if (!details) return;
                
                let html = '';
                
                switch (contentType) {
                    case 'post':
                        html = `
                            <div class="content-item">
                                <label>Post Caption</label>
                                <p>${details.caption || 'No caption'}</p>
                            </div>
                            <div class="content-grid">
                                <div class="content-detail">
                                    <label>Post Type</label>
                                    <span>${details.post_type || 'Unknown'}</span>
                                </div>
                                <div class="content-detail">
                                    <label>Privacy</label>
                                    <span>${details.privacy_level || 'Unknown'}</span>
                                </div>
                                <div class="content-detail">
                                    <label>Created</label>
                                    <span>${this.formatDate(details.created_at)}</span>
                                </div>
                            </div>
                        `;
                        break;
                    case 'comment':
                        html = `
                            <div class="content-item">
                                <label>Comment Text</label>
                                <p>${details.comment_text || 'No text'}</p>
                            </div>
                            <div class="content-detail">
                                <label>Posted</label>
                                <span>${this.formatDate(details.created_at)}</span>
                            </div>
                        `;
                        break;
                    case 'user':
                        html = `
                            <div class="content-item">
                                <label>User Profile Report</label>
                                <p>This report is about the user's profile or general behavior.</p>
                            </div>
                        `;
                        break;
                }
                
                contentDetailsDiv.innerHTML = html;
                contentDetailsCard.style.display = 'block';
            }            displayReviewHistory(report) {
                if (report.reviewed_by) {
                    document.getElementById('reviewerName').textContent = 
                        `${report.reviewed_by.first_name || ''} ${report.reviewed_by.last_name || ''}`.trim() || 'Unknown Admin';
                    document.getElementById('reviewDate').textContent = this.formatDate(report.reviewed_at);
                    document.getElementById('existingAdminNotes').textContent = report.admin_notes || 'No previous notes';
                      // Update reviewer avatar if available
                    const reviewerAvatar = document.getElementById('reviewerAvatar');
                    if (reviewerAvatar) {
                        reviewerAvatar.src = this.getProfilePictureUrl(report.reviewed_by.profile_picture_url);
                        reviewerAvatar.onerror = () => {
                            // Prevent infinite loop by checking if we're already showing placeholder
                            if (!reviewerAvatar.src.includes('placeholder.png')) {
                                reviewerAvatar.src = '/webdev/backend/media/images/placeholder.png';
                            }
                        };
                    }
                    
                    document.getElementById('reviewHistoryCard').style.display = 'block';
                }
            }async updateReport(formData) {
                try {
                    const response = await this.apiRequest('/admin/update_report.php', 'PUT', {
                        report_id: parseInt(this.reportId),
                        ...formData
                    });
                    
                    if (response.success) {
                        this.showNotification('Report updated successfully', 'success');
                        // Reload the report to show updated data
                        setTimeout(() => {
                            this.loadReport();
                        }, 1000);
                    } else {
                        throw new Error(response.message || 'Failed to update report');
                    }
                } catch (error) {
                    console.error('Failed to update report:', error);
                    this.showNotification(error.message, 'error');
                }
            }

            initializeEventListeners() {
                // Admin action form
                document.getElementById('adminActionForm').addEventListener('submit', (e) => {
                    e.preventDefault();
                    
                    const formData = new FormData(e.target);
                    const data = {
                        status: formData.get('status'),
                        admin_notes: formData.get('admin_notes')
                    };
                    
                    this.updateReport(data);
                });
                
                // Quick dismiss button
                document.getElementById('dismissBtn').addEventListener('click', () => {
                    this.updateReport({
                        status: 'dismissed',
                        admin_notes: document.getElementById('adminNotes').value || 'Report dismissed by admin'
                    });
                });
            }

            formatDate(dateString) {
                if (!dateString) return '-';
                const date = new Date(dateString);
                return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
            }

            formatContentType(type) {
                const types = {
                    'post': 'Post',
                    'comment': 'Comment',
                    'user': 'User Profile'
                };
                return types[type] || type;
            }            formatStatus(status) {
                const statuses = {
                    'pending': 'Pending',
                    'reviewed': 'Reviewed',
                    'action_taken': 'Action Taken',
                    'dismissed': 'Dismissed'
                };
                return statuses[status] || status;
            }            getProfilePictureUrl(profilePictureUrl) {
                if (!profilePictureUrl) {
                    return '/webdev/backend/media/images/placeholder.png';
                }
                
                // If it's already a full URL, return as is
                if (profilePictureUrl.startsWith('http')) {
                    return profilePictureUrl;
                }
                
                // If it's a relative path, construct the media endpoint URL
                return `/webdev/backend/src/api/media/get_media.php?file=${encodeURIComponent(profilePictureUrl)}`;
            }

            showLoading(show) {
                document.getElementById('loadingIndicator').style.display = show ? 'flex' : 'none';
            }

            showError(message) {
                const errorDiv = document.getElementById('errorMessage');
                const errorText = document.getElementById('errorText');
                errorText.textContent = message;
                errorDiv.style.display = 'block';
            }

            hideError() {
                document.getElementById('errorMessage').style.display = 'none';
            }

            showNotification(message, type = 'info') {
                const notification = document.getElementById('notification');
                const notificationText = document.getElementById('notificationText');
                
                notificationText.textContent = message;
                notification.className = `notification notification-${type} show`;
                notification.style.display = 'block';
                
                // Auto hide after 5 seconds
                setTimeout(() => {
                    this.hideNotification();
                }, 5000);
            }

            hideNotification() {
                const notification = document.getElementById('notification');
                notification.classList.remove('show');
                setTimeout(() => {
                    notification.style.display = 'none';
                }, 300);
            }
        }

        // Global functions for template compatibility
        function loadReport() {
            if (window.reportReviewer) {
                window.reportReviewer.loadReport();
            }
        }

        function hideNotification() {
            if (window.reportReviewer) {
                window.reportReviewer.hideNotification();
            }
        }

        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', () => {
            window.reportReviewer = new ReportReviewer();
        });
    </script>
</body>
</html>