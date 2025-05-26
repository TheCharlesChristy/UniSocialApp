<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - SocialConnect Admin</title>
    <link rel="stylesheet" href="../css/generic.css">
    <link rel="stylesheet" href="../css/admin-users.css">
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
                    <a href="admin-users.php" class="admin-nav-link active">Users</a>
                    <a href="admin-analytics.php" class="admin-nav-link">Analytics</a>
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
                        <img src="/webdev/backend/src/api/media/get_media.php?file=placeholder.png" alt="Admin" class="admin-avatar" id="userAvatar">
                        <span id="userName">Admin User</span>
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
                <h1>User Management</h1>
                <p>Search, view, and manage user accounts</p>
            </div>

            <!-- User Management Controls -->
            <section class="user-controls">
                <div class="search-section">
                    <div class="search-input-group">
                        <input type="text" id="userSearch" placeholder="Search users by name, username, or email..." class="search-input">
                        <button id="searchBtn" class="btn-primary">
                            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                            </svg>
                            Search
                        </button>
                    </div>
                </div>

                <div class="filter-section">
                    <select id="statusFilter" class="filter-select">
                        <option value="">All Status</option>
                        <option value="active">Active Users</option>
                        <option value="suspended">Suspended Users</option>
                    </select>
                    
                    <select id="roleFilter" class="filter-select">
                        <option value="">All Roles</option>
                        <option value="user">Regular Users</option>
                        <option value="admin">Administrators</option>
                    </select>

                    <button id="clearFilters" class="btn-secondary">Clear Filters</button>
                </div>
            </section>

            <!-- User Statistics -->
            <section class="user-stats-grid">
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M16 4c0-1.11.89-2 2-2s2 .89 2 2-.89 2-2 2-2-.89-2-2zM4 18v-4h3v4H4zM10 18v-7h3v7h-3zM16 18v-4h3v4h-3z"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h3 id="totalUsersCount">0</h3>
                        <p>Total Users</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon success">
                        <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h3 id="activeUsersCount">0</h3>
                        <p>Active Users</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon warning">
                        <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h3 id="suspendedUsersCount">0</h3>
                        <p>Suspended Users</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon danger">
                        <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h3 id="reportedUsersCount">0</h3>
                        <p>Reported Users</p>
                    </div>
                </div>
            </section>

            <!-- Users Table -->
            <section class="admin-card">
                <div class="admin-card-header">
                    <h2>User Accounts</h2>
                    <div class="pagination-info">
                        <span id="paginationInfo">Loading...</span>
                    </div>
                </div>
                <div class="admin-card-content">
                    <div class="users-table-container">
                        <div id="loadingIndicator" class="loading-indicator">
                            <div class="spinner"></div>
                            <span>Loading users...</span>
                        </div>
                        
                        <table class="users-table" id="usersTable" style="display: none;">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Joined</th>
                                    <th>Last Login</th>
                                    <th>Posts</th>
                                    <th>Reports</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="usersTableBody">
                                <!-- Users will be populated here -->
                            </tbody>
                        </table>

                        <div id="noUsersFound" class="no-results" style="display: none;">
                            <svg width="64" height="64" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                            </svg>
                            <h3>No users found</h3>
                            <p>Try adjusting your search criteria or filters.</p>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="pagination-container" id="paginationContainer" style="display: none;">
                        <button id="prevPage" class="btn-secondary" disabled>Previous</button>
                        <div class="pagination-pages" id="paginationPages">
                            <!-- Page numbers will be generated here -->
                        </div>
                        <button id="nextPage" class="btn-secondary" disabled>Next</button>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- User Action Modal -->
    <div id="userActionModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">User Action</h3>
                <button class="modal-close" onclick="closeUserActionModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div id="modalUserInfo" class="user-info-section">
                    <!-- User info will be populated here -->
                </div>
                <div id="modalActionContent" class="action-content">
                    <!-- Action specific content will be populated here -->
                </div>
            </div>
            <div class="modal-footer">
                <button id="modalCancelBtn" class="btn-secondary" onclick="closeUserActionModal()">Cancel</button>
                <button id="modalConfirmBtn" class="btn-primary">Confirm</button>
            </div>
        </div>
    </div>

    <!-- Toast Notifications -->
    <div id="toastContainer" class="toast-container">
        <!-- Toast notifications will be added here -->
    </div>

    <!-- Scripts -->
    <script>
        class AdminUserManager {
            constructor() {
                this.apiBaseUrl = '/webdev/backend/src/api';
                this.authToken = this.getAuthToken();
                this.currentPage = 1;
                this.currentFilters = {
                    search: '',
                    status: '',
                    role: ''
                };
                this.usersPerPage = 20;
                this.totalUsers = 0;
                this.totalPages = 0;
                
                this.initializeEventListeners();
                this.loadCurrentUser();
                this.loadNotificationCount();
                this.loadUserStats();
                this.loadUsers();
                this.startLiveUpdates();
            }

            getAuthToken() {
                return localStorage.getItem('authToken') || sessionStorage.getItem('authToken');
            }            initializeEventListeners() {
                try {
                    // Search functionality
                    const searchInput = document.getElementById('userSearch');
                    const searchBtn = document.getElementById('searchBtn');
                    
                    if (searchBtn) {
                        searchBtn.addEventListener('click', () => this.performSearch());
                    }
                    if (searchInput) {
                        searchInput.addEventListener('keypress', (e) => {
                            if (e.key === 'Enter') {
                                this.performSearch();
                            }
                        });
                    }

                    // Filter functionality
                    const statusFilter = document.getElementById('statusFilter');
                    const roleFilter = document.getElementById('roleFilter');
                    const clearFiltersBtn = document.getElementById('clearFilters');
                    
                    if (statusFilter) {
                        statusFilter.addEventListener('change', () => this.applyFilters());
                    }
                    if (roleFilter) {
                        roleFilter.addEventListener('change', () => this.applyFilters());
                    }
                    if (clearFiltersBtn) {
                        clearFiltersBtn.addEventListener('click', () => this.clearFilters());
                    }

                    // Pagination
                    const prevPageBtn = document.getElementById('prevPage');
                    const nextPageBtn = document.getElementById('nextPage');
                    
                    if (prevPageBtn) {
                        prevPageBtn.addEventListener('click', () => this.goToPreviousPage());
                    }
                    if (nextPageBtn) {
                        nextPageBtn.addEventListener('click', () => this.goToNextPage());
                    }

                    // User dropdown toggle
                    this.initializeUserDropdown();
                    
                } catch (error) {
                    console.error('Error initializing event listeners:', error);
                }
            }

            initializeUserDropdown() {
                const userBtn = document.querySelector('.admin-user-btn');
                const dropdown = document.querySelector('.admin-user-dropdown');
                
                if (userBtn && dropdown) {
                    userBtn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        dropdown.classList.toggle('show');
                    });

                    document.addEventListener('click', () => {
                        dropdown.classList.remove('show');
                    });
                }
            }            async loadCurrentUser() {
                try {
                    const response = await this.apiRequest('/users/me.php');
                    if (response.success) {
                        const user = response.user;
                        document.getElementById('userName').textContent = user.first_name + ' ' + user.last_name;
                        
                        const avatar = document.getElementById('userAvatar');
                        if (user.profile_picture) {
                            avatar.src = `/webdev/backend/src/api/media/get_media.php?file=${encodeURIComponent(user.profile_picture)}`;
                        }
                    }
                } catch (error) {
                    console.error('Failed to load current user:', error);
                }
            }            async loadNotificationCount() {
                try {
                    const response = await this.apiRequest('/notifications/unread_count.php');
                    if (response.success) {
                        document.getElementById('notificationBadge').textContent = response.count || 0;
                    }
                } catch (error) {
                    console.error('Failed to load notification count:', error);
                }
            }

            async loadUserStats() {
                try {
                    const response = await this.apiRequest('/admin/users.php?page=1&limit=1');
                    if (response.success) {
                        document.getElementById('totalUsersCount').textContent = response.pagination.total_users;
                    }

                    // Load additional stats
                    const [activeStats, suspendedStats] = await Promise.all([
                        this.apiRequest('/admin/users.php?status=active&page=1&limit=1'),
                        this.apiRequest('/admin/users.php?status=suspended&page=1&limit=1')
                    ]);

                    if (activeStats.success) {
                        document.getElementById('activeUsersCount').textContent = activeStats.pagination.total_users;
                    }

                    if (suspendedStats.success) {
                        document.getElementById('suspendedUsersCount').textContent = suspendedStats.pagination.total_users;
                    }

                    // TODO: Load reported users count from reports API
                    document.getElementById('reportedUsersCount').textContent = '0';

                } catch (error) {
                    console.error('Failed to load user stats:', error);
                }
            }

            async loadUsers() {
                this.showLoading();
                
                try {
                    const params = new URLSearchParams({
                        page: this.currentPage,
                        limit: this.usersPerPage
                    });

                    if (this.currentFilters.search) {
                        params.append('search', this.currentFilters.search);
                    }
                    if (this.currentFilters.status) {
                        params.append('status', this.currentFilters.status);
                    }
                    if (this.currentFilters.role) {
                        params.append('role', this.currentFilters.role);
                    }

                    const response = await this.apiRequest(`/admin/users.php?${params.toString()}`);
                    
                    if (response.success) {
                        this.displayUsers(response.users);
                        this.updatePagination(response.pagination);
                        this.totalUsers = response.pagination.total_users;
                        this.totalPages = response.pagination.total_pages;
                    } else {
                        this.showError('Failed to load users: ' + response.message);
                    }
                } catch (error) {
                    console.error('Failed to load users:', error);
                    this.showError('Failed to load users. Please try again.');
                } finally {
                    this.hideLoading();
                }
            }

            displayUsers(users) {
                const tbody = document.getElementById('usersTableBody');
                const table = document.getElementById('usersTable');
                const noResults = document.getElementById('noUsersFound');

                if (!users || users.length === 0) {
                    table.style.display = 'none';
                    noResults.style.display = 'block';
                    return;
                }

                table.style.display = 'table';
                noResults.style.display = 'none';

                tbody.innerHTML = users.map(user => this.createUserRow(user)).join('');
            }            
            
            createUserRow(user) {
                const statusClass = user.account_status === 'active' ? 'success' : 
                                   user.account_status === 'suspended' ? 'warning' : 'danger';
                
                // Use the get_media.php endpoint for profile pictures
                const avatar = user.profile_picture ? 
                    `/webdev/backend/src/api/media/get_media.php?file=${encodeURIComponent(user.profile_picture)}` : 
                    '/webdev/backend/src/api/media/get_media.php?file=placeholder.png';

                const joinDate = new Date(user.registration_date).toLocaleDateString();
                const lastLogin = user.last_login ? new Date(user.last_login).toLocaleDateString() : 'Never';

                return `
                    <tr data-user-id="${user.user_id}">
                        <td>
                            <div class="user-cell">
                                <img src="${avatar}" alt="${user.username}" class="user-avatar-small" 
                                     onerror="this.src='/webdev/backend/src/api/media/get_media.php?file=placeholder.png'">
                                <div class="user-info">
                                    <div class="user-name">${user.first_name} ${user.last_name}</div>
                                    <div class="user-username">@${user.username}</div>
                                </div>
                            </div>
                        </td>
                        <td>${user.email}</td>
                        <td>
                            <span class="role-badge ${user.role}">${user.role}</span>
                        </td>
                        <td>
                            <span class="status-badge ${statusClass}">${user.account_status}</span>
                        </td>
                        <td>${joinDate}</td>
                        <td>${lastLogin}</td>
                        <td>${user.posts_count || 0}</td>
                        <td>
                            <span class="reports-count ${user.reports_count > 0 ? 'has-reports' : ''}">${user.reports_count || 0}</span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-icon" onclick="window.adminUserManager.viewUser('${user.user_id}')" title="View Details">
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                                    </svg>
                                </button>
                                ${user.account_status === 'active' ? 
                                    `<button class="btn-icon warning" onclick="window.adminUserManager.suspendUser('${user.user_id}')" title="Suspend User">
                                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                        </svg>
                                    </button>` :
                                    `<button class="btn-icon success" onclick="window.adminUserManager.activateUser('${user.user_id}')" title="Activate User">
                                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </button>`
                                }
                                ${user.role !== 'admin' ? 
                                    `<button class="btn-icon danger" onclick="window.adminUserManager.deleteUser('${user.user_id}')" title="Delete User">
                                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>` : ''
                                }
                            </div>
                        </td>
                    </tr>
                `;
            }

            updatePagination(pagination) {
                const info = document.getElementById('paginationInfo');
                const container = document.getElementById('paginationContainer');
                const prevBtn = document.getElementById('prevPage');
                const nextBtn = document.getElementById('nextPage');
                const pagesContainer = document.getElementById('paginationPages');

                info.textContent = `Showing ${((pagination.current_page - 1) * pagination.users_per_page) + 1}-${Math.min(pagination.current_page * pagination.users_per_page, pagination.total_users)} of ${pagination.total_users} users`;

                if (pagination.total_pages > 1) {
                    container.style.display = 'flex';
                    
                    prevBtn.disabled = pagination.current_page <= 1;
                    nextBtn.disabled = pagination.current_page >= pagination.total_pages;

                    // Generate page numbers
                    pagesContainer.innerHTML = this.generatePageNumbers(pagination.current_page, pagination.total_pages);
                } else {
                    container.style.display = 'none';
                }
            }

            generatePageNumbers(currentPage, totalPages) {
                let pages = '';
                const maxVisible = 5;
                let start = Math.max(1, currentPage - Math.floor(maxVisible / 2));
                let end = Math.min(totalPages, start + maxVisible - 1);

                if (end - start < maxVisible - 1) {
                    start = Math.max(1, end - maxVisible + 1);
                }

                for (let i = start; i <= end; i++) {
                    pages += `<button class="page-btn ${i === currentPage ? 'active' : ''}" onclick="window.adminUserManager.goToPage(${i})">${i}</button>`;
                }

                return pages;
            }

            performSearch() {
                const searchTerm = document.getElementById('userSearch').value.trim();
                this.currentFilters.search = searchTerm;
                this.currentPage = 1;
                this.loadUsers();
            }            applyFilters() {
                this.currentFilters.status = document.getElementById('statusFilter').value;
                this.currentFilters.role = document.getElementById('roleFilter').value;
                this.currentPage = 1;
                this.loadUsers();
            }

            clearFilters() {
                document.getElementById('userSearch').value = '';
                document.getElementById('statusFilter').value = '';
                document.getElementById('roleFilter').value = '';
                
                this.currentFilters = { search: '', status: '', role: '' };
                this.currentPage = 1;
                this.loadUsers();
            }

            goToPage(page) {
                this.currentPage = page;
                this.loadUsers();
            }

            goToPreviousPage() {
                if (this.currentPage > 1) {
                    this.currentPage--;
                    this.loadUsers();
                }
            }

            goToNextPage() {
                if (this.currentPage < this.totalPages) {
                    this.currentPage++;
                    this.loadUsers();
                }
            }            async viewUser(userId) {
                try {
                    const user = await this.getUserData(userId);
                    if (user) {
                        this.showUserModal('View User Details', user, 'view');
                    } else {
                        this.showError('Failed to load user details');
                    }
                } catch (error) {
                    console.error('Failed to view user:', error);
                    this.showError('Failed to load user details');
                }
            }

            async suspendUser(userId) {
                const user = await this.getUserData(userId);
                if (user) {
                    this.showUserModal('Suspend User', user, 'suspend');
                }
            }

            async activateUser(userId) {
                const user = await this.getUserData(userId);
                if (user) {
                    this.showUserModal('Activate User', user, 'activate');
                }
            }

            async deleteUser(userId) {
                const user = await this.getUserData(userId);
                if (user) {
                    this.showUserModal('Delete User', user, 'delete');
                }
            }            async getUserData(userId) {
                try {
                    const response = await this.apiRequest(`/users/get_user.php?userId=${userId}`);
                    return response.success ? response.user : null;
                } catch (error) {
                    console.error('Failed to get user data:', error);
                    return null;
                }
            }

            showUserModal(title, user, action) {
                const modal = document.getElementById('userActionModal');
                const modalTitle = document.getElementById('modalTitle');
                const userInfo = document.getElementById('modalUserInfo');
                const actionContent = document.getElementById('modalActionContent');
                const confirmBtn = document.getElementById('modalConfirmBtn');

                modalTitle.textContent = title;                // Display user info
                userInfo.innerHTML = `
                    <div class="user-details">
                        <img src="${user.profile_picture ? `/webdev/backend/src/api/media/get_media.php?file=${encodeURIComponent(user.profile_picture)}` : '/webdev/backend/src/api/media/get_media.php?file=placeholder.png'}" 
                             alt="${user.username}" class="user-avatar-large">
                        <div class="user-details-text">
                            <h4>${user.first_name} ${user.last_name}</h4>
                            <p>@${user.username}</p>
                            <p>${user.email}</p>
                        </div>
                    </div>
                `;

                // Configure action content and button
                this.configureModalAction(action, user, actionContent, confirmBtn);

                modal.style.display = 'flex';
            }

            configureModalAction(action, user, actionContent, confirmBtn) {
                switch (action) {
                    case 'view':
                        actionContent.innerHTML = `
                            <div class="user-full-details">
                                <p><strong>Bio:</strong> ${user.bio || 'No bio provided'}</p>
                                <p><strong>Date of Birth:</strong> ${user.date_of_birth || 'Not provided'}</p>
                                <p><strong>Registration Date:</strong> ${new Date(user.registration_date).toLocaleDateString()}</p>
                                <p><strong>Last Login:</strong> ${user.last_login ? new Date(user.last_login).toLocaleDateString() : 'Never'}</p>
                                <p><strong>Account Status:</strong> ${user.account_status}</p>
                                <p><strong>Role:</strong> ${user.role}</p>
                            </div>
                        `;
                        confirmBtn.textContent = 'Close';
                        confirmBtn.onclick = () => this.closeUserActionModal();
                        break;

                    case 'suspend':
                        actionContent.innerHTML = `
                            <div class="warning-message">
                                <p>Are you sure you want to suspend this user? They will not be able to log in or access their account.</p>
                                <textarea id="suspendReason" placeholder="Reason for suspension (optional)" rows="3"></textarea>
                            </div>
                        `;
                        confirmBtn.textContent = 'Suspend User';
                        confirmBtn.className = 'btn-warning';
                        confirmBtn.onclick = () => this.confirmSuspendUser(user.user_id);
                        break;

                    case 'activate':
                        actionContent.innerHTML = `
                            <div class="success-message">
                                <p>Are you sure you want to activate this user? They will regain access to their account.</p>
                            </div>
                        `;
                        confirmBtn.textContent = 'Activate User';
                        confirmBtn.className = 'btn-success';
                        confirmBtn.onclick = () => this.confirmActivateUser(user.user_id);
                        break;

                    case 'delete':
                        actionContent.innerHTML = `
                            <div class="danger-message">
                                <p><strong>Warning:</strong> This action cannot be undone. The user's account and all associated data will be permanently deleted.</p>
                                <label>
                                    <input type="checkbox" id="confirmDelete"> I understand that this action is irreversible
                                </label>
                            </div>
                        `;
                        confirmBtn.textContent = 'Delete User';
                        confirmBtn.className = 'btn-danger';
                        confirmBtn.onclick = () => this.confirmDeleteUser(user.user_id);
                        break;
                }
            }

            async confirmSuspendUser(userId) {
                const reason = document.getElementById('suspendReason').value;
                
                try {
                    const response = await this.apiRequest('/admin/suspend_user.php', 'POST', {
                        user_id: userId,
                        reason: reason
                    });

                    if (response.success) {
                        this.showSuccess('User suspended successfully');
                        this.closeUserActionModal();
                        this.loadUsers();
                        this.loadUserStats();
                    } else {
                        this.showError('Failed to suspend user: ' + response.message);
                    }
                } catch (error) {
                    console.error('Failed to suspend user:', error);
                    this.showError('Failed to suspend user');
                }
            }

            async confirmActivateUser(userId) {
                try {
                    const response = await this.apiRequest('/admin/activate_user.php', 'POST', {
                        user_id: userId
                    });

                    if (response.success) {
                        this.showSuccess('User activated successfully');
                        this.closeUserActionModal();
                        this.loadUsers();
                        this.loadUserStats();
                    } else {
                        this.showError('Failed to activate user: ' + response.message);
                    }
                } catch (error) {
                    console.error('Failed to activate user:', error);
                    this.showError('Failed to activate user');
                }
            }

            async confirmDeleteUser(userId) {
                const checkbox = document.getElementById('confirmDelete');
                
                if (!checkbox.checked) {
                    this.showError('Please confirm that you understand this action is irreversible');
                    return;
                }

                try {
                    const response = await this.apiRequest('/admin/delete_user.php', 'POST', {
                        user_id: userId
                    });

                    if (response.success) {
                        this.showSuccess('User deleted successfully');
                        this.closeUserActionModal();
                        this.loadUsers();
                        this.loadUserStats();
                    } else {
                        this.showError('Failed to delete user: ' + response.message);
                    }
                } catch (error) {
                    console.error('Failed to delete user:', error);
                    this.showError('Failed to delete user');
                }
            }

            closeUserActionModal() {
                document.getElementById('userActionModal').style.display = 'none';
            }

            startLiveUpdates() {
                this.connectToNotificationsSSE();
                this.connectToUsersSSE();
            }

            connectToNotificationsSSE() {
                try {
                    const authToken = this.getAuthToken();
                    let sseUrl = `${this.apiBaseUrl}/notifications/live_notifications.php`;
                    
                    if (authToken) {
                        sseUrl += `?token=${encodeURIComponent(authToken)}`;
                    }
                    
                    this.notificationEventSource = new EventSource(sseUrl);

                    this.notificationEventSource.onopen = () => {
                        console.log('Notifications SSE connection opened');
                        this.updateLiveIndicator(true);
                    };

                    this.notificationEventSource.addEventListener('notification_count_update', (event) => {
                        const data = JSON.parse(event.data);
                        document.getElementById('notificationBadge').textContent = data.count;
                    });

                    this.notificationEventSource.onerror = (error) => {
                        console.error('Notifications SSE connection error:', error);
                        this.updateLiveIndicator(false);
                    };

                } catch (error) {
                    console.error('Failed to connect to notifications SSE:', error);
                }
            }

            connectToUsersSSE() {
                try {
                    const authToken = this.getAuthToken();
                    let sseUrl = `${this.apiBaseUrl}/admin/live_users.php`;
                    
                    if (authToken) {
                        sseUrl += `?token=${encodeURIComponent(authToken)}`;
                    }
                    
                    this.usersEventSource = new EventSource(sseUrl);

                    this.usersEventSource.addEventListener('user_stats_update', (event) => {
                        const stats = JSON.parse(event.data);
                        this.updateUserStats(stats);
                    });

                    this.usersEventSource.addEventListener('user_status_change', (event) => {
                        const data = JSON.parse(event.data);
                        this.handleUserStatusChange(data);
                    });

                    this.usersEventSource.onerror = (error) => {
                        console.error('Users SSE connection error:', error);
                    };

                } catch (error) {
                    console.error('Failed to connect to users SSE:', error);
                }
            }

            updateUserStats(stats) {
                if (stats.total_users !== undefined) {
                    document.getElementById('totalUsersCount').textContent = stats.total_users;
                }
                if (stats.active_users !== undefined) {
                    document.getElementById('activeUsersCount').textContent = stats.active_users;
                }
                if (stats.suspended_users !== undefined) {
                    document.getElementById('suspendedUsersCount').textContent = stats.suspended_users;
                }
                if (stats.reported_users !== undefined) {
                    document.getElementById('reportedUsersCount').textContent = stats.reported_users;
                }
            }

            handleUserStatusChange(data) {
                // Update the specific user row if it's currently visible
                const userRow = document.querySelector(`tr[data-user-id="${data.user_id}"]`);
                if (userRow) {
                    // Reload the current page to reflect changes
                    this.loadUsers();
                }
                
                // Show notification
                this.showInfo(`User ${data.username} status changed to ${data.new_status}`);
            }

            updateLiveIndicator(connected) {
                const indicator = document.getElementById('liveIndicator');
                if (indicator) {
                    indicator.classList.toggle('connected', connected);
                }
            }

            showLoading() {
                document.getElementById('loadingIndicator').style.display = 'flex';
                document.getElementById('usersTable').style.display = 'none';
                document.getElementById('noUsersFound').style.display = 'none';
            }

            hideLoading() {
                document.getElementById('loadingIndicator').style.display = 'none';
            }

            showSuccess(message) {
                this.showToast(message, 'success');
            }

            showError(message) {
                this.showToast(message, 'error');
            }

            showInfo(message) {
                this.showToast(message, 'info');
            }

            showToast(message, type = 'info') {
                const toast = document.createElement('div');
                toast.className = `toast toast-${type}`;
                toast.innerHTML = `
                    <div class="toast-content">
                        <span>${message}</span>
                        <button class="toast-close" onclick="this.parentElement.parentElement.remove()">&times;</button>
                    </div>
                `;

                document.getElementById('toastContainer').appendChild(toast);

                // Auto remove after 5 seconds
                setTimeout(() => {
                    if (toast.parentElement) {
                        toast.remove();
                    }
                }, 5000);
            }

            async apiRequest(endpoint, method = 'GET', data = null) {
                const token = this.getAuthToken();
                const headers = {
                    'Content-Type': 'application/json'
                };

                if (token) {
                    headers['Authorization'] = `Bearer ${token}`;
                }

                const options = {
                    method,
                    headers
                };

                if (data && method !== 'GET') {
                    options.body = JSON.stringify(data);
                }

                const response = await fetch(this.apiBaseUrl + endpoint, options);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                return await response.json();
            }

            cleanup() {
                if (this.notificationEventSource) {
                    this.notificationEventSource.close();
                }
                if (this.usersEventSource) {
                    this.usersEventSource.close();
                }
            }
        }

        // Global functions for onclick handlers
        function closeUserActionModal() {
            window.adminUserManager.closeUserActionModal();
        }

        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', () => {
            window.adminUserManager = new AdminUserManager();
        });

        // Cleanup when page is unloaded
        window.addEventListener('beforeunload', () => {
            if (window.adminUserManager) {
                window.adminUserManager.cleanup();
            }
        });

        // Close modal when clicking outside
        document.addEventListener('click', (e) => {
            const modal = document.getElementById('userActionModal');
            if (e.target === modal) {
                closeUserActionModal();
            }
        });
    </script>
</body>
</html>
