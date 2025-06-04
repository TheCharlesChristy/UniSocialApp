# Notifications API Documentation

## Overview

The Notifications API provides a comprehensive system for managing user notifications in a social platform or application. This API enables creating, retrieving, managing, and real-time streaming of notifications between users. The system supports various notification types including likes, comments, friend requests, mentions, and tags.

**Base URL:** `/api/notifications`

## Authentication

All endpoints require authentication via Bearer token or session-based authentication. The authenticated user is automatically identified from the session/token and used as the context for all operations.

**Authentication Methods:**
- Bearer token in Authorization header
- Session-based authentication
- For SSE endpoint: Token can be passed as URL parameter

**Headers:**
```
Content-Type: application/json
Authorization: Bearer <token>
```

## Endpoints

### POST /api/notifications
Creates a new notification for a specified recipient.

**Authentication:** Required

**Request Body:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| recipient_id | integer | Yes | ID of the user who will receive the notification |
| type | string | Yes | Type of notification (see valid types below) |
| related_content_type | string | Yes | Type of content the notification relates to |
| related_content_id | integer | Yes | ID of the related content |

**Valid Notification Types:**
- `like` - User liked content
- `comment` - User commented on content
- `friend_request` - User sent friend request
- `friend_accept` - User accepted friend request
- `mention` - User was mentioned
- `tag` - User was tagged

**Valid Content Types:**
- `post` - Social media post
- `comment` - Comment on content
- `user` - User profile
- `message` - Direct message

**Example Request:**
```json
{
  "recipient_id": 123,
  "type": "like",
  "related_content_type": "post",
  "related_content_id": 456
}
```

**Response:**
- **200 OK** - Notification created successfully
- **400 Bad Request** - Invalid input data or validation error
- **404 Not Found** - Recipient user not found
- **409 Conflict** - Duplicate notification (prevents spam)
- **500 Internal Server Error** - Server error

**Example Response:**
```json
{
  "success": true,
  "message": "Notification created successfully",
  "notification_id": 789
}
```

### GET /api/notifications
Retrieves notifications for the authenticated user with pagination and filtering options.

**Authentication:** Required

**Query Parameters:**
| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| page | integer | No | 1 | Page number for pagination |
| limit | integer | No | 20 | Number of notifications per page (max 50) |
| filter | string | No | "all" | Filter notifications ("all" or "unread") |

**Response:**
- **200 OK** - Notifications retrieved successfully
- **400 Bad Request** - Invalid filter parameter
- **500 Internal Server Error** - Server error

**Example Response:**
```json
{
  "success": true,
  "notifications": [
    {
      "notification_id": 789,
      "sender_id": 456,
      "type": "like",
      "related_content_type": "post",
      "related_content_id": 123,
      "created_at": "2024-01-15T10:30:00Z",
      "is_read": false,
      "read_at": null,
      "sender_username": "john_doe",
      "sender_first_name": "John",
      "sender_last_name": "Doe",
      "sender_profile_picture": "profile.jpg",
      "content_preview": "liked your post",
      "sender_name": "John Doe"
    }
  ],
  "total_notifications": 25,
  "unread_count": 5,
  "current_page": 1,
  "total_pages": 2
}
```

### DELETE /api/notifications/:notificationId
Deletes a specific notification. Users can delete notifications they sent or received.

**Authentication:** Required

**URL Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| notificationId | integer | Yes | ID of the notification to delete |

**Alternative Methods:**
- Query parameter: `?notificationId=123`
- Request body: `{"notificationId": 123}`

**Response:**
- **200 OK** - Notification deleted successfully
- **400 Bad Request** - Invalid notification ID
- **403 Forbidden** - Access denied
- **404 Not Found** - Notification not found
- **500 Internal Server Error** - Server error

**Example Response:**
```json
{
  "success": true,
  "message": "Notification deleted successfully"
}
```

### PUT /api/notifications/:notificationId/read
Marks a specific notification as read for the authenticated user.

**Authentication:** Required

**URL Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| notificationId | integer | Yes | ID of the notification to mark as read |

**Alternative Methods:**
- Query parameter: `?notificationId=123`
- Request body: `{"notificationId": 123}`
- Supports GET, POST, and PUT methods

**Response:**
- **200 OK** - Notification marked as read (or already read)
- **400 Bad Request** - Invalid notification ID
- **404 Not Found** - Notification not found
- **500 Internal Server Error** - Server error

**Example Response:**
```json
{
  "success": true,
  "message": "Notification marked as read"
}
```

### PUT /api/notifications/read-all
Marks all unread notifications as read for the authenticated user.

**Authentication:** Required

**HTTP Methods:** GET, POST, PUT

**Response:**
- **200 OK** - All notifications marked as read
- **500 Internal Server Error** - Server error

**Example Response:**
```json
{
  "success": true,
  "count": 5,
  "message": "Marked 5 notifications as read"
}
```

### GET /api/notifications/unread-count
Gets the count of unread notifications for the authenticated user.

**Authentication:** Required

**Response:**
- **200 OK** - Unread count retrieved successfully
- **500 Internal Server Error** - Server error

**Example Response:**
```json
{
  "success": true,
  "count": 3
}
```

### GET /api/notifications/live (Server-Sent Events)
Provides real-time notification updates using Server-Sent Events (SSE). Clients can listen to this endpoint to receive live notifications.

**Authentication:** Required (session, token parameter, or fallback methods)

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| token | string | No | Authentication token (if not using session) |
| last_id | integer | No | Last notification ID received (to avoid duplicates) |

**Event Types:**
- `connected` - Connection established
- `new_notification` - New notification received
- `count_update` - Updated unread count
- `error` - Error occurred

**Example Usage (JavaScript):**
```javascript
const eventSource = new EventSource('/api/notifications/live?token=your_token');

eventSource.addEventListener('new_notification', function(event) {
  const notification = JSON.parse(event.data);
  console.log('New notification:', notification);
});

eventSource.addEventListener('count_update', function(event) {
  const data = JSON.parse(event.data);
  console.log('Unread count:', data.count);
});
```

**Example SSE Events:**
```
event: connected
data: {"message":"Connected to live notifications"}

event: new_notification
data: {"notification_id":123,"type":"like","title":"Like Notification","message":"You have a new like notification","sender":{"username":"john_doe","first_name":"John","last_name":"Doe","profile_picture":"profile.jpg"},"related_content_type":"post","related_content_id":456,"created_at":"2024-01-15T10:30:00","is_read":false}

event: count_update
data: {"count":4}
```

## Data Models

### Notification Object
```json
{
  "notification_id": 123,
  "recipient_id": 456,
  "sender_id": 789,
  "type": "like",
  "related_content_type": "post",
  "related_content_id": 101,
  "created_at": "2024-01-15T10:30:00Z",
  "is_read": false,
  "read_at": null,
  "sender_username": "john_doe",
  "sender_first_name": "John",
  "sender_last_name": "Doe",
  "sender_profile_picture": "profile.jpg",
  "content_preview": "liked your post",
  "sender_name": "John Doe"
}
```

### Sender Object (in SSE events)
```json
{
  "username": "john_doe",
  "first_name": "John",
  "last_name": "Doe",
  "profile_picture": "profile.jpg"
}
```

## Error Handling

All endpoints return consistent error responses in the following format:

```json
{
  "success": false,
  "message": "Error description"
}
```

**Common HTTP Status Codes:**
- **200 OK** - Request successful
- **400 Bad Request** - Invalid request parameters or validation error
- **401 Unauthorized** - Authentication required
- **403 Forbidden** - Access denied
- **404 Not Found** - Resource not found
- **405 Method Not Allowed** - HTTP method not supported
- **409 Conflict** - Resource conflict (e.g., duplicate notification)
- **500 Internal Server Error** - Server error

## Features

### Duplicate Prevention
The system automatically prevents duplicate notifications within a 1-hour window to reduce spam.

### Real-time Updates
Server-Sent Events provide real-time notification delivery to connected clients with automatic reconnection handling.

### Flexible ID Resolution
Most endpoints support multiple ways to pass notification IDs:
- URL path parameters (RESTful style)
- Query parameters
- Request body

### Comprehensive Filtering
Notifications can be filtered by read status and support pagination for large datasets.

### User Context
All operations are scoped to the authenticated user, ensuring proper access control and data isolation.

## Code Examples

### Creating a Notification (cURL)
```bash
curl -X POST /api/notifications \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer your_token" \
  -d '{
    "recipient_id": 123,
    "type": "like",
    "related_content_type": "post",
    "related_content_id": 456
  }'
```

### Getting Notifications with Filtering (JavaScript)
```javascript
async function getUnreadNotifications(page = 1) {
  const response = await fetch(`/api/notifications?filter=unread&page=${page}`, {
    headers: {
      'Authorization': 'Bearer ' + token
    }
  });
  return await response.json();
}
```

### Setting up Live Notifications (JavaScript)
```javascript
function setupLiveNotifications(token) {
  const eventSource = new EventSource(`/api/notifications/live?token=${token}`);
  
  eventSource.onopen = () => console.log('Connected to live notifications');
  
  eventSource.addEventListener('new_notification', (event) => {
    const notification = JSON.parse(event.data);
    showNotification(notification);
  });
  
  eventSource.addEventListener('count_update', (event) => {
    const data = JSON.parse(event.data);
    updateNotificationBadge(data.count);
  });
  
  eventSource.onerror = () => console.log('Connection error');
  
  return eventSource;
}
```