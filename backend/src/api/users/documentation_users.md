# User Management API Documentation

## Overview

The User Management API provides endpoints for managing user accounts, profiles, relationships, and user-related content in a social media platform. The API implements user authentication, profile management, user search, blocking functionality, and administrative features.

**Base URL:** `/api/users/`  
**Authentication:** Bearer token required for all endpoints  
**Content Type:** `application/json`  
**CORS:** Enabled for all origins

## Authentication

All endpoints require authentication via Bearer token in the Authorization header:
```
Authorization: Bearer <your_token>
```

## Common Response Format

All endpoints return JSON responses with a consistent structure:

**Success Response:**
```json
{
  "success": true,
  "message": "Operation completed successfully",
  "data": {}
}
```

**Error Response:**
```json
{
  "success": false,
  "message": "Error description"
}
```

## Endpoints

### User Profile Management

#### GET /api/users/me
Retrieves the current authenticated user's complete profile.

**Authentication:** Required

**Response:**
- **200 OK** - Profile retrieved successfully
- **404 Not Found** - User profile not found

**Example Response:**
```json
{
  "success": true,
  "message": "User profile retrieved successfully",
  "user": {
    "user_id": 123,
    "username": "johndoe",
    "email": "john@example.com",
    "first_name": "John",
    "last_name": "Doe",
    "profile_picture": "media/images/profile/123_1640995200.jpg",
    "bio": "Software developer and coffee enthusiast",
    "date_of_birth": "1990-05-15",
    "registration_date": "2024-01-15T10:30:00Z",
    "account_status": "active",
    "role": "user"
  }
}
```

#### PUT /api/users/me
Updates the current user's profile information.

**Authentication:** Required

**Content Type:** `application/json` or `multipart/form-data` (for file uploads)

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| first_name | string | No | User's first name |
| last_name | string | No | User's last name |
| bio | string | No | User biography (can be empty to clear) |
| profile_picture | file | No | Profile image (JPEG, JPG, PNG, GIF, max 5MB) |

**Request Body (JSON):**
```json
{
  "first_name": "John",
  "last_name": "Doe",
  "bio": "Updated bio text"
}
```

**Response:**
- **200 OK** - Profile updated successfully
- **400 Bad Request** - No updates provided or invalid file format
- **500 Internal Server Error** - Failed to update profile

**Example Response:**
```json
{
  "success": true,
  "message": "Profile updated successfully",
  "updated_fields": ["first_name", "last_name", "bio"],
  "profile_picture": "media/images/profile/123_1640995200.jpg"
}
```

#### PUT /api/users/me/password
Updates the current user's password.

**Authentication:** Required

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| current_password | string | Yes | Current password for verification |
| new_password | string | Yes | New password (minimum 8 characters) |

**Request Body:**
```json
{
  "current_password": "current_password_here",
  "new_password": "new_secure_password"
}
```

**Response:**
- **200 OK** - Password updated successfully
- **400 Bad Request** - Invalid current password or new password requirements not met
- **404 Not Found** - User not found

### User Discovery

#### GET /api/users/:userId
Retrieves a specific user's profile information.

**Authentication:** Required

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| userId | integer | Yes | Target user's ID |

**Response:**
- **200 OK** - User profile retrieved
- **400 Bad Request** - Invalid user ID format
- **404 Not Found** - User not found

**Example Response (Regular User):**
```json
{
  "success": true,
  "user": {
    "user_id": 456,
    "username": "janedoe",
    "first_name": "Jane",
    "last_name": "Doe",
    "profile_picture": "media/images/profile/456_1640995300.jpg",
    "bio": "Graphic designer",
    "friendship_status": "accepted"
  }
}
```

**Example Response (Admin View):**
```json
{
  "success": true,
  "user": {
    "user_id": 456,
    "username": "janedoe",
    "email": "jane@example.com",
    "first_name": "Jane",
    "last_name": "Doe",
    "profile_picture": "media/images/profile/456_1640995300.jpg",
    "bio": "Graphic designer",
    "date_of_birth": "1992-08-20",
    "registration_date": "2024-01-20T14:15:00Z",
    "last_login": "2024-05-30T09:00:00Z",
    "account_status": "active",
    "role": "user"
  }
}
```

#### GET /api/users/search
Searches for users by name or username.

**Authentication:** Required

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| query | string | Yes | Search term (name or username) |
| page | integer | No | Page number (default: 1) |
| limit | integer | No | Results per page (1-50, default: 10) |

**Response:**
- **200 OK** - Search results returned
- **400 Bad Request** - Search query is required

**Example Request:**
```
GET /api/users/search?query=john&page=1&limit=10
```

**Example Response:**
```json
{
  "success": true,
  "users": [
    {
      "user_id": 123,
      "username": "johndoe",
      "first_name": "John",
      "last_name": "Doe",
      "profile_picture": "media/images/profile/123_1640995200.jpg",
      "bio": "Software developer",
      "friendship_status": "pending"
    }
  ],
  "total_results": 15,
  "current_page": 1,
  "total_pages": 2
}
```

#### GET /api/users/suggestions
Gets suggested users for friendship based on mutual connections.

**Authentication:** Required

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| limit | integer | No | Number of suggestions (1-50, default: 10) |

**Response:**
- **200 OK** - User suggestions returned

**Example Response:**
```json
{
  "success": true,
  "users": [
    {
      "user_id": 789,
      "username": "mikejones",
      "first_name": "Mike",
      "last_name": "Jones",
      "profile_picture": "media/images/profile/789_1640995400.jpg",
      "bio": "Photographer",
      "mutual_friends_count": 3
    }
  ],
  "count": 5
}
```

### User Content

#### GET /api/users/:userId/posts
Retrieves posts from a specific user with privacy filtering.

**Authentication:** Required

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| userId | integer | Yes | Target user's ID |

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| page | integer | No | Page number (default: 1) |
| limit | integer | No | Posts per page (1-50, default: 10) |

**Response:**
- **200 OK** - Posts retrieved successfully
- **400 Bad Request** - Invalid user ID format
- **404 Not Found** - User not found

**Example Response:**
```json
{
  "success": true,
  "posts": [
    {
      "post_id": 101,
      "user_id": 456,
      "caption": "Beautiful sunset today!",
      "post_type": "image",
      "media_url": "media/posts/sunset.jpg",
      "created_at": "2024-05-30T18:30:00Z",
      "updated_at": "2024-05-30T18:30:00Z",
      "privacy_level": "public",
      "location_name": "Central Park",
      "username": "janedoe",
      "first_name": "Jane",
      "last_name": "Doe",
      "profile_picture": "media/images/profile/456_1640995300.jpg",
      "likes_count": 25,
      "comments_count": 8,
      "user_has_liked": false
    }
  ],
  "total_posts": 45,
  "current_page": 1,
  "total_pages": 5
}
```

### User Blocking

#### POST /api/users/:userId/block
Blocks a specific user, preventing interaction and removing existing friendships.

**Authentication:** Required

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| userId | integer | Yes | User ID to block |

**Response:**
- **200 OK** - User blocked successfully
- **400 Bad Request** - Invalid user ID, cannot block yourself, or user already blocked
- **404 Not Found** - User not found
- **500 Internal Server Error** - Database error

**Example Response:**
```json
{
  "success": true,
  "message": "User blocked successfully"
}
```

#### DELETE /api/users/:userId/block
Unblocks a previously blocked user.

**Authentication:** Required

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| userId | integer | Yes | User ID to unblock |

**Response:**
- **200 OK** - User unblocked successfully
- **400 Bad Request** - Invalid user ID
- **404 Not Found** - User is not blocked
- **500 Internal Server Error** - Database error

**Example Response:**
```json
{
  "success": true,
  "message": "User unblocked successfully"
}
```

#### GET /api/users/blocked
Retrieves the current user's list of blocked users.

**Authentication:** Required

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| page | integer | No | Page number (default: 1) |
| limit | integer | No | Results per page (1-50, default: 20) |

**Response:**
- **200 OK** - Blocked users list retrieved

**Example Response:**
```json
{
  "success": true,
  "data": [
    {
      "user_id": 999,
      "username": "blockeduser",
      "full_name": "Blocked User",
      "profile_picture": "media/images/profile/999_1640995500.jpg",
      "blocked_at": "2024-05-25T10:15:00Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "total_pages": 1,
    "total_count": 3,
    "per_page": 20
  }
}
```

### Administrative Features

#### DELETE /api/users/delete_user.php
Soft deletes a user account. Users can delete their own account, or admins can delete any account.

**Authentication:** Required (Admin role for deleting other users)

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| userId | integer | No | User ID to delete (admin only, defaults to current user) |

**Request Body (Alternative):**
```json
{
  "userId": 456
}
```

**Response:**
- **200 OK** - User account deleted successfully
- **400 Bad Request** - User account already deleted
- **403 Forbidden** - Access denied (non-admin trying to delete other user)
- **404 Not Found** - User not found
- **500 Internal Server Error** - Failed to delete account

**Example Response:**
```json
{
  "success": true,
  "message": "User account deleted successfully",
  "deleted_user_id": 456,
  "deleted_username": "janedoe"
}
```

#### GET /api/users/:userId/reports
Retrieves reports filed by a specific user (admin only).

**Authentication:** Required (Admin role)

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| userId | integer | Yes | ID of user whose reports to retrieve |

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| page | integer | No | Page number (default: 1) |
| limit | integer | No | Results per page (1-50, default: 20) |
| content_type | string | No | Filter by content type: user, post, comment |
| reported_user_id | integer | No | Filter by reported user ID |
| reason | string | No | Filter by reason: spam, harassment, inappropriate, violence, other |
| status | string | No | Filter by status: pending, reviewed, action_taken, dismissed |

**Response:**
- **200 OK** - Reports retrieved successfully
- **400 Bad Request** - Invalid parameters
- **403 Forbidden** - Admin access required

**Example Response:**
```json
{
  "success": true,
  "reports": [
    {
      "report_id": 15,
      "reported_user": {
        "user_id": 456,
        "username": "reporteduser",
        "first_name": "Reported",
        "last_name": "User",
        "profile_picture": "media/images/profile/456_1640995300.jpg"
      },
      "content_type": "post",
      "content_id": 789,
      "reason": "inappropriate",
      "description": "Contains offensive language",
      "status": "pending",
      "created_at": "2024-05-30T14:20:00Z",
      "reviewed_at": null,
      "admin_notes": null,
      "content_preview": "This post contains inappropriate content that violates..."
    }
  ],
  "total_reports": 12,
  "current_page": 1,
  "total_pages": 1,
  "filters_applied": {
    "content_type": null,
    "reported_user_id": null,
    "reporter_id": null,
    "reason": null,
    "status": null
  }
}
```

## Data Models

### User Object
```json
{
  "user_id": "integer",
  "username": "string",
  "email": "string (admin only)",
  "first_name": "string",
  "last_name": "string", 
  "profile_picture": "string (relative path)",
  "bio": "string",
  "date_of_birth": "string (admin only)",
  "registration_date": "string (admin only)",
  "last_login": "string (admin only)",
  "account_status": "string (admin only)",
  "role": "string (admin only)",
  "friendship_status": "string (pending|accepted|null)"
}
```

### Post Object
```json
{
  "post_id": "integer",
  "user_id": "integer",
  "caption": "string",
  "post_type": "string",
  "media_url": "string",
  "created_at": "string (ISO 8601)",
  "updated_at": "string (ISO 8601)",
  "privacy_level": "string (public|friends|private)",
  "location_name": "string",
  "username": "string",
  "first_name": "string",
  "last_name": "string",
  "profile_picture": "string",
  "likes_count": "integer",
  "comments_count": "integer",
  "user_has_liked": "boolean"
}
```

### Report Object
```json
{
  "report_id": "integer",
  "reported_user": "User Object",
  "content_type": "string (user|post|comment)",
  "content_id": "integer",
  "reason": "string (spam|harassment|inappropriate|violence|other)",
  "description": "string",
  "status": "string (pending|reviewed|action_taken|dismissed)",
  "created_at": "string (ISO 8601)",
  "reviewed_at": "string (ISO 8601)|null",
  "admin_notes": "string|null",
  "content_preview": "string|null"
}
```

## Error Handling

### Common Error Codes

| Code | Description |
|------|-------------|
| 400 | Bad Request - Invalid parameters or request format |
| 401 | Unauthorized - Authentication required |
| 403 | Forbidden - Insufficient permissions |
| 404 | Not Found - Resource does not exist |
| 405 | Method Not Allowed - HTTP method not supported |
| 500 | Internal Server Error - Server-side error |

### Error Response Format
```json
{
  "success": false,
  "message": "Descriptive error message"
}
```

## Privacy and Permissions

### Post Visibility Rules
- **Public posts**: Visible to all authenticated users
- **Friends posts**: Visible only to accepted friends and the post author
- **Private posts**: Visible only to the post author

### Admin Permissions
- View detailed user information including email, registration date, and account status
- Delete any user account
- View user reports and administrative data

### User Permissions
- View and update their own profile
- Search for other users
- Block/unblock other users
- View posts based on friendship status and privacy settings

## Rate Limiting
- Search results are limited to 50 items per request
- File uploads are limited to 5MB for profile pictures
- Pagination is enforced on all list endpoints

## File Upload Requirements
- **Supported formats**: JPEG, JPG, PNG, GIF
- **Maximum size**: 5MB
- **Storage location**: `media/images/profile/`
- **Naming convention**: `{user_id}_{timestamp}.{extension}`

## Notes
- All endpoints use soft delete for user accounts, preserving data integrity
- Blocking a user automatically removes any existing friendship
- The API implements comprehensive privacy filtering based on user relationships
- Search functionality supports both exact matches and partial matches across multiple fields
- Pagination is consistently implemented across all list endpoints