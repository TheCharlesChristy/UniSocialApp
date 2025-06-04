# Admin API Documentation

## Overview

This API provides administrative functionality for managing users, content, and reports in a social media platform. All endpoints require admin-level authentication and return JSON responses.

**Base URL:** `/api/admin/`  
**Authentication:** Bearer token required  
**Content-Type:** `application/json`

## Authentication

All admin endpoints require:
- Valid session token via `Authorization: Bearer <token>` header
- User account with `admin` role
- Active account status

**Error Responses:**
- **401 Unauthorized** - Invalid or missing token
- **403 Forbidden** - Non-admin user or insufficient permissions

---

## Table of Contents

1. [Dashboard & Analytics](#dashboard--analytics)
2. [User Management](#user-management) 
3. [Report Management](#report-management)
4. [Real-time Updates](#real-time-updates)

---

## Dashboard & Analytics

### GET /api/admin/dashboard

Retrieves overview statistics for the admin dashboard.

**Response:**
```json
{
  "success": true,
  "message": "Dashboard data retrieved successfully",
  "dashboard": {
    "statistics": {
      "total_users": 1250,
      "active_users": 1180,
      "suspended_users": 70,
      "total_posts": 8420,
      "pending_reports": 15,
      "new_users_this_week": 42,
      "new_posts_this_week": 156
    },
    "recent_users": [
      {
        "user_id": 123,
        "username": "johndoe",
        "first_name": "John",
        "last_name": "Doe",
        "registration_date": "2024-01-15T10:30:00Z",
        "account_status": "active"
      }
    ]
  }
}
```

### GET /api/admin/analytics/posts

Provides detailed post analytics and statistics.

**Query Parameters:**
| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| start_date | string | No | 30 days ago | Start date (YYYY-MM-DD) |
| end_date | string | No | Today | End date (YYYY-MM-DD) |

**Response:**
```json
{
  "success": true,
  "message": "Post analytics retrieved successfully",
  "analytics": {
    "date_range": {
      "start_date": "2024-01-01",
      "end_date": "2024-01-31"
    },
    "overview": {
      "total_posts": 856,
      "engagement": {
        "total_posts": 856,
        "total_likes": 12450,
        "total_comments": 3420,
        "avg_likes_per_post": 14.54,
        "avg_comments_per_post": 3.99
      }
    },
    "posts_by_type": [
      {"post_type": "image", "count": 450},
      {"post_type": "text", "count": 380},
      {"post_type": "video", "count": 26}
    ],
    "posts_by_privacy": [
      {"privacy_level": "public", "count": 720},
      {"privacy_level": "friends", "count": 136}
    ],
    "daily_posts": [
      {"date": "2024-01-01", "count": 25},
      {"date": "2024-01-02", "count": 31}
    ],
    "top_liked_posts": [],
    "top_commented_posts": [],
    "top_posters": []
  }
}
```

**Error Responses:**
- **400 Bad Request** - Invalid date format

### GET /api/admin/analytics/users

Provides detailed user analytics and statistics.

**Query Parameters:**
| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| start_date | string | No | 30 days ago | Start date (YYYY-MM-DD) |
| end_date | string | No | Today | End date (YYYY-MM-DD) |

**Response Structure:** Similar to posts analytics but focused on user metrics including registration trends, age distribution, and activity patterns.

---

## User Management

### GET /api/admin/users

Retrieves a paginated list of all users with management options.

**Query Parameters:**
| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| page | integer | No | 1 | Page number |
| limit | integer | No | 20 | Users per page (1-100) |
| status | string | No | - | Filter by status: `active`, `suspended` |
| role | string | No | - | Filter by role: `user`, `admin` |
| search | string | No | - | Search username, email, or name |

**Response:**
```json
{
  "success": true,
  "message": "Users retrieved successfully",
  "users": [
    {
      "user_id": 123,
      "username": "johndoe",
      "email": "john@example.com",
      "first_name": "John",
      "last_name": "Doe",
      "profile_picture": "path/to/image.jpg",
      "bio": "Software developer",
      "date_of_birth": "1990-05-15",
      "registration_date": "2024-01-15T10:30:00Z",
      "last_login": "2024-01-30T14:20:00Z",
      "account_status": "active",
      "role": "user",
      "posts_count": 25,
      "reports_count": 0,
      "friends_count": 42
    }
  ],
  "pagination": {
    "current_page": 1,
    "total_pages": 63,
    "total_users": 1250,
    "users_per_page": 20
  }
}
```

### PUT /api/admin/users/:userId

Updates user profile information.

**Request Body:**
```json
{
  "user_id": 123,
  "first_name": "John",
  "last_name": "Doe",
  "email": "john.doe@example.com",
  "username": "johndoe",
  "bio": "Updated bio",
  "account_status": "active",
  "role": "user"
}
```

**Response:**
```json
{
  "success": true,
  "message": "User updated successfully",
  "user": {
    "user_id": 123,
    "username": "johndoe",
    "email": "john.doe@example.com",
    "first_name": "John",
    "last_name": "Doe",
    "profile_picture": "path/to/image.jpg",
    "bio": "Updated bio",
    "date_of_birth": "1990-05-15",
    "registration_date": "2024-01-15T10:30:00Z",
    "last_login": "2024-01-30T14:20:00Z",
    "account_status": "active",
    "role": "user"
  }
}
```

**Error Responses:**
- **400 Bad Request** - Invalid email, username already taken, or no valid fields
- **403 Forbidden** - Cannot modify other admin accounts or remove own admin role
- **404 Not Found** - User not found

### PUT /api/admin/users/:userId/suspend

Suspends a user account.

**Request Body:**
```json
{
  "user_id": 123,
  "reason": "Violation of community guidelines"
}
```

**Response:**
```json
{
  "success": true,
  "message": "User suspended successfully",
  "user": {
    "user_id": 123,
    "username": "johndoe",
    "account_status": "suspended",
    "suspension_reason": "Violation of community guidelines"
  }
}
```

**Error Responses:**
- **400 Bad Request** - User already suspended
- **403 Forbidden** - Cannot suspend own account or other admin accounts
- **404 Not Found** - User not found

### PUT /api/admin/users/:userId/activate

Activates a suspended user account.

**Request Body:**
```json
{
  "user_id": 123
}
```

**Response:**
```json
{
  "success": true,
  "message": "User activated successfully",
  "user": {
    "user_id": 123,
    "username": "johndoe",
    "account_status": "active"
  }
}
```

**Error Responses:**
- **400 Bad Request** - User already active or can only activate suspended users
- **404 Not Found** - User not found

### DELETE /api/admin/users/:userId

Soft deletes a user account (sets status to 'deleted').

**Request Body:**
```json
{
  "user_id": 123
}
```

**Response:**
```json
{
  "success": true,
  "message": "User deleted successfully",
  "deleted_user": {
    "user_id": 123,
    "username": "johndoe"
  }
}
```

**Error Responses:**
- **403 Forbidden** - Cannot delete own account or other admin accounts
- **404 Not Found** - User not found

---

## Report Management

### GET /api/admin/reports

Retrieves a paginated list of all reports for admin review.

**Query Parameters:**
| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| page | integer | No | 1 | Page number |
| limit | integer | No | 20 | Reports per page (1-100) |
| status | string | No | - | Filter by status: `pending`, `reviewed`, `action_taken`, `dismissed` |
| content_type | string | No | - | Filter by type: `user`, `post`, `comment` |

**Response:**
```json
{
  "success": true,
  "message": "Reports retrieved successfully",
  "reports": [
    {
      "report_id": 456,
      "reporter_id": 123,
      "reported_id": 789,
      "content_type": "post",
      "content_id": 101,
      "reason": "spam",
      "description": "This post contains spam content",
      "created_at": "2024-01-15T10:30:00Z",
      "status": "pending",
      "admin_notes": null,
      "reviewed_by": null,
      "reviewed_at": null,
      "reporter_username": "reporter_user",
      "reporter_first_name": "Jane",
      "reporter_last_name": "Smith",
      "reported_username": "reported_user",
      "reported_first_name": "John",
      "reported_last_name": "Doe",
      "reviewer_username": null,
      "reviewer_first_name": null,
      "reviewer_last_name": null,
      "content_details": {
        "id": 101,
        "caption": "Post content here...",
        "type": "image",
        "created_at": "2024-01-14T15:20:00Z"
      }
    }
  ],
  "pagination": {
    "current_page": 1,
    "total_pages": 8,
    "total_reports": 156,
    "reports_per_page": 20
  }
}
```

### PUT /api/admin/reports/:reportId

Updates report status and admin notes.

**Request Body:**
```json
{
  "report_id": 456,
  "status": "reviewed",
  "admin_notes": "Reviewed and found to be valid concern"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Report updated successfully",
  "report": {
    "report_id": 456,
    "reporter_id": 123,
    "reported_id": 789,
    "content_type": "post",
    "content_id": 101,
    "reason": "spam",
    "description": "This post contains spam content",
    "created_at": "2024-01-15T10:30:00Z",
    "status": "reviewed",
    "admin_notes": "Reviewed and found to be valid concern",
    "reviewed_by": 1,
    "reviewed_at": "2024-01-16T09:15:00Z",
    "reporter_username": "reporter_user",
    "reported_username": "reported_user",
    "reviewer_username": "admin_user"
  }
}
```

**Error Responses:**
- **400 Bad Request** - Invalid status or no valid fields provided
- **404 Not Found** - Report not found

---

## Real-time Updates

### GET /api/admin/live_reports

Server-Sent Events (SSE) endpoint for real-time report updates.

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| token | string | Yes | Authentication token (since SSE doesn't support custom headers) |
| last_id | integer | No | Last report ID received to avoid duplicates |

**Event Types:**
- `connected` - Connection established
- `new_report` - New report submitted
- `report_status_update` - Report status changed
- `pending_count_update` - Updated pending reports count
- `dashboard_stats_update` - Updated dashboard statistics
- `error` - Error occurred

**Example Events:**
```
event: connected
data: {"message": "Connected to live reports"}

event: new_report
data: {"report_id": 123, "reporter_id": 456, ...}

event: pending_count_update
data: {"count": 15}
```

### GET /api/admin/live_users

Server-Sent Events (SSE) endpoint for real-time user management updates.

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| token | string | Yes | Authentication token |

**Event Types:**
- `connected` - Connection established
- `user_stats_update` - Updated user statistics (every 30 seconds)
- `user_status_change` - User status changed
- `heartbeat` - Connection heartbeat (every 15 seconds)
- `error` - Error occurred

**Example Events:**
```
event: user_stats_update
data: {"total_users": 1250, "active_users": 1180, "suspended_users": 70, "reported_users": 5}

event: user_status_change
data: {"user_id": 123, "username": "johndoe", "new_status": "suspended"}
```

---

## Common Response Patterns

### Success Response
```json
{
  "success": true,
  "message": "Operation completed successfully",
  "data": {}
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error description"
}
```

### HTTP Status Codes

- **200 OK** - Request successful
- **400 Bad Request** - Invalid request data
- **401 Unauthorized** - Authentication required
- **403 Forbidden** - Insufficient permissions
- **404 Not Found** - Resource not found
- **405 Method Not Allowed** - HTTP method not supported
- **500 Internal Server Error** - Server error

---

## Data Models

### User Object
```json
{
  "user_id": 123,
  "username": "johndoe",
  "email": "john@example.com",
  "first_name": "John",
  "last_name": "Doe",
  "profile_picture": "path/to/image.jpg",
  "bio": "User biography",
  "date_of_birth": "1990-05-15",
  "registration_date": "2024-01-15T10:30:00Z",
  "last_login": "2024-01-30T14:20:00Z",
  "account_status": "active|suspended|deleted",
  "role": "user|admin"
}
```

### Report Object
```json
{
  "report_id": 456,
  "reporter_id": 123,
  "reported_id": 789,
  "content_type": "user|post|comment",
  "content_id": 101,
  "reason": "spam|harassment|inappropriate|other",
  "description": "Detailed description",
  "created_at": "2024-01-15T10:30:00Z",
  "status": "pending|reviewed|action_taken|dismissed",
  "admin_notes": "Admin notes",
  "reviewed_by": 1,
  "reviewed_at": "2024-01-16T09:15:00Z"
}
```

---

## Notes

- All endpoints support CORS with `Access-Control-Allow-Origin: *`
- Timestamps are in ISO 8601 format
- All database operations include proper error handling and transactions where appropriate
- Soft deletion is used for user accounts (status changed to 'deleted')
- Admin users cannot perform destructive actions on other admin accounts
- Real-time endpoints use Server-Sent Events for live updates
- Token authentication is validated against active sessions with expiration checking