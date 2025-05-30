# Privacy Settings API Documentation

## Overview

The Privacy Settings API allows users to manage their privacy preferences within the application. Users can retrieve their current privacy settings and update them as needed. All endpoints require authentication and automatically create default privacy settings if none exist.

**Base URL:** `/api/privacy`

## Authentication

All endpoints require user authentication via Bearer token or session-based authentication. The API uses middleware to verify user identity before processing requests.

## Endpoints

### GET /api/privacy

Retrieves the current user's privacy settings. If no privacy settings exist for the user, default settings are automatically created.

**Authentication:** Required

**Parameters:** None

**Default Privacy Settings:**
- `post_default_privacy`: "public"
- `profile_visibility`: "public" 
- `friend_list_visibility`: "friends"
- `who_can_send_requests`: "everyone"

**Response:**

**Success Response (200 OK):**
```json
{
  "success": true,
  "privacy_settings": {
    "post_default_privacy": "public",
    "profile_visibility": "public",
    "friend_list_visibility": "friends",
    "who_can_send_requests": "everyone",
    "created_at": "2024-01-15 10:30:00",
    "updated_at": "2024-01-15 10:30:00"
  }
}
```

**Error Responses:**
- **405 Method Not Allowed** - Invalid HTTP method used
- **500 Internal Server Error** - Database connection error or server issue

**Example Request:**
```bash
curl -X GET \
  https://api.example.com/api/privacy \
  -H "Authorization: Bearer your-auth-token" \
  -H "Content-Type: application/json"
```

---

### PUT /api/privacy

Updates the current user's privacy settings. Only provided fields will be updated, and all values are validated against allowed options.

**Authentication:** Required

**Request Body Parameters:**

| Parameter | Type | Required | Description | Valid Values |
|-----------|------|----------|-------------|--------------|
| post_default_privacy | string | No | Default privacy level for new posts | `public`, `friends`, `private` |
| profile_visibility | string | No | Who can view the user's profile | `public`, `friends`, `private` |
| friend_list_visibility | string | No | Who can see the user's friend list | `public`, `friends`, `private` |
| who_can_send_requests | string | No | Who can send friend requests | `everyone`, `friends_of_friends`, `nobody` |

**Request Body:**
```json
{
  "post_default_privacy": "friends",
  "profile_visibility": "private",
  "friend_list_visibility": "private",
  "who_can_send_requests": "friends_of_friends"
}
```

**Response:**

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Privacy settings updated successfully",
  "updated_settings": [
    "post_default_privacy",
    "profile_visibility", 
    "friend_list_visibility",
    "who_can_send_requests"
  ]
}
```

**Error Responses:**

**400 Bad Request - Invalid Field Value:**
```json
{
  "success": false,
  "message": "Invalid post_default_privacy value. Must be: public, friends, or private"
}
```

**400 Bad Request - No Fields Provided:**
```json
{
  "success": false,
  "message": "No valid fields provided for update"
}
```

**405 Method Not Allowed:**
```json
{
  "success": false,
  "message": "Method not allowed"
}
```

**500 Internal Server Error:**
```json
{
  "success": false,
  "message": "Failed to update privacy settings"
}
```

**Example Request:**
```bash
curl -X PUT \
  https://api.example.com/api/privacy \
  -H "Authorization: Bearer your-auth-token" \
  -H "Content-Type: application/json" \
  -d '{
    "post_default_privacy": "friends",
    "profile_visibility": "private"
  }'
```

## Data Models

### Privacy Settings Object

| Field | Type | Description |
|-------|------|-------------|
| post_default_privacy | string | Default privacy level for new posts (`public`, `friends`, `private`) |
| profile_visibility | string | Profile visibility setting (`public`, `friends`, `private`) |
| friend_list_visibility | string | Friend list visibility (`public`, `friends`, `private`) |
| who_can_send_requests | string | Friend request permissions (`everyone`, `friends_of_friends`, `nobody`) |
| created_at | datetime | Timestamp when settings were created |
| updated_at | datetime | Timestamp when settings were last modified |

## Error Handling

The API uses standard HTTP status codes and returns JSON error responses:

- **400 Bad Request** - Invalid input data or missing required fields
- **405 Method Not Allowed** - Incorrect HTTP method used
- **500 Internal Server Error** - Database errors or server issues

All error responses follow this format:
```json
{
  "success": false,
  "message": "Error description"
}
```

## Implementation Notes

- **Automatic Defaults**: If a user has no privacy settings, the GET endpoint will automatically create default settings
- **Partial Updates**: The PUT endpoint supports partial updates - only provided fields will be modified
- **Transaction Safety**: Updates use database transactions to ensure data consistency
- **Input Validation**: All privacy setting values are validated against predefined allowed values
- **CORS Support**: API includes CORS headers for cross-origin requests
- **Preflight Handling**: OPTIONS requests are handled for CORS preflight

## Common Use Cases

1. **Initial Setup**: When a user first accesses privacy settings, defaults are automatically created
2. **Bulk Update**: Update multiple privacy settings in a single request
3. **Selective Update**: Update only specific privacy settings while leaving others unchanged
4. **Settings Retrieval**: Get current privacy configuration for display in user interface