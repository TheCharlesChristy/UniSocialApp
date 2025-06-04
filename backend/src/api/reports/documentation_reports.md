# Reports API Documentation

## Overview

The Reports API provides endpoints for creating and managing user-generated reports for content moderation. Users can report inappropriate posts, comments, or other users, while administrators can retrieve and review these reports. The API is built with PHP and includes comprehensive validation, authentication, and content verification.

## Authentication

All endpoints require authentication via Bearer token. The token should be included in the request headers:

```
Authorization: Bearer <your_token>
```

## Base URL

```
/api/reports
```

## Endpoints

### POST /api/reports

Creates a new report for a post, comment, or user. Authenticated users can report content that violates community guidelines.

**Authentication:** Required (Bearer token)

**Request Body:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| reported_id | integer | Yes | ID of the user being reported |
| content_type | string | Yes | Type of content being reported. Must be one of: `user`, `post`, `comment` |
| content_id | integer | Yes | ID of the specific content being reported |
| reason | string | Yes | Reason for the report (max 100 characters) |
| description | string | No | Additional details about the report |

**Validation Rules:**
- Users cannot report themselves
- `content_type` must be exactly one of: `user`, `post`, `comment`
- For user reports, `content_id` must match `reported_id`
- The reported content must exist and belong to the reported user
- Users cannot submit duplicate reports for the same content and reason

**Request Example:**
```json
{
  "reported_id": 123,
  "content_type": "post",
  "content_id": 456,
  "reason": "Inappropriate content",
  "description": "This post contains offensive language"
}
```

**Response Codes:**
- **200 OK** - Report created successfully
- **400 Bad Request** - Invalid input data or validation error
- **401 Unauthorized** - Authentication required
- **404 Not Found** - Reported user or content not found
- **409 Conflict** - Duplicate report exists
- **500 Internal Server Error** - Server error

**Success Response Example:**
```json
{
  "success": true,
  "report_id": 789,
  "message": "Report created successfully"
}
```

**Error Response Examples:**
```json
{
  "success": false,
  "message": "content_type must be one of: user, post, comment"
}
```

```json
{
  "success": false,
  "message": "You have already reported this content for this reason"
}
```

### GET /api/reports/{reportId}

Retrieves detailed information about a specific report by its ID. This endpoint is restricted to administrators only.

**Authentication:** Required (Admin role)

**Path Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| reportId | integer | Yes | Unique identifier of the report |

**Query Parameters (Fallback):**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Optional | Report ID (used as fallback if not in path) |

**Response Codes:**
- **200 OK** - Report retrieved successfully
- **400 Bad Request** - Invalid or missing report ID
- **401 Unauthorized** - Authentication required
- **403 Forbidden** - Admin access required
- **404 Not Found** - Report not found
- **500 Internal Server Error** - Server error

**Success Response Example:**
```json
{
  "success": true,
  "message": "Report retrieved successfully",
  "report": {
    "report_id": 789,
    "reporter": {
      "user_id": 100,
      "username": "john_doe",
      "first_name": "John",
      "last_name": "Doe",
      "email": "john@example.com",
      "profile_picture_url": "https://example.com/profile.jpg"
    },
    "reported_user": {
      "user_id": 123,
      "username": "reported_user",
      "first_name": "Jane",
      "last_name": "Smith",
      "email": "jane@example.com",
      "profile_picture_url": "https://example.com/jane.jpg"
    },
    "content_type": "post",
    "content_id": 456,
    "content_details": {
      "post_id": 456,
      "caption": "This is the reported post caption",
      "post_type": "image",
      "privacy_level": "public",
      "location_name": "New York, NY",
      "created_at": "2024-01-15T10:30:00Z"
    },
    "reason": "Inappropriate content",
    "description": "This post contains offensive language",
    "status": "pending",
    "created_at": "2024-01-16T14:20:00Z",
    "admin_notes": null,
    "reviewed_by": null,
    "reviewed_at": null
  }
}
```

**Content Details by Type:**

**For Post Reports:**
```json
"content_details": {
  "post_id": 456,
  "caption": "Post caption text",
  "post_type": "image",
  "privacy_level": "public",
  "location_name": "Location name",
  "created_at": "2024-01-15T10:30:00Z"
}
```

**For Comment Reports:**
```json
"content_details": {
  "comment_id": 789,
  "comment_text": "The comment text",
  "post_id": 456,
  "created_at": "2024-01-15T11:45:00Z"
}
```

**For User Reports:**
```json
"content_details": {
  "user_id": 123,
  "username": "reported_user",
  "name": "Jane Smith",
  "email": "jane@example.com"
}
```

## Data Models

### Report Object

| Field | Type | Description |
|-------|------|-------------|
| report_id | integer | Unique identifier for the report |
| reporter | User Object | Information about the user who made the report |
| reported_user | User Object | Information about the user being reported |
| content_type | string | Type of content: `user`, `post`, or `comment` |
| content_id | integer | ID of the reported content |
| content_details | Object | Detailed information about the reported content |
| reason | string | Reason for the report (max 100 characters) |
| description | string | Additional description (optional) |
| status | string | Current status: `pending`, `reviewed`, `resolved` |
| created_at | string | ISO 8601 timestamp when report was created |
| admin_notes | string | Notes added by administrators (nullable) |
| reviewed_by | User Object | Administrator who reviewed the report (nullable) |
| reviewed_at | string | ISO 8601 timestamp when report was reviewed (nullable) |

### User Object

| Field | Type | Description |
|-------|------|-------------|
| user_id | integer | Unique user identifier |
| username | string | User's username |
| first_name | string | User's first name |
| last_name | string | User's last name |
| email | string | User's email address |
| profile_picture_url | string | URL to user's profile picture |

## Error Handling

The API uses standard HTTP status codes and returns error responses in JSON format:

```json
{
  "success": false,
  "message": "Error description"
}
```

### Common Error Codes

| Status Code | Description |
|-------------|-------------|
| 400 | Bad Request - Invalid input data or validation error |
| 401 | Unauthorized - Authentication token missing or invalid |
| 403 | Forbidden - Insufficient permissions (admin required) |
| 404 | Not Found - Requested resource doesn't exist |
| 405 | Method Not Allowed - HTTP method not supported |
| 409 | Conflict - Duplicate report or resource conflict |
| 500 | Internal Server Error - Unexpected server error |

## Business Logic Notes

1. **Self-Reporting Prevention**: Users cannot report themselves
2. **Content Verification**: The system verifies that reported content exists and belongs to the reported user
3. **Duplicate Prevention**: Users cannot submit multiple reports for the same content with the same reason
4. **Content Type Validation**: For user reports, the `content_id` must match the `reported_id`
5. **Admin-Only Access**: Report retrieval is restricted to administrators only
6. **Transaction Safety**: Report creation uses database transactions to ensure data integrity

## Usage Examples

### Creating a Post Report

```bash
curl -X POST /api/reports \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer your_token_here" \
  -d '{
    "reported_id": 123,
    "content_type": "post",
    "content_id": 456,
    "reason": "Spam content",
    "description": "This post is clearly spam and violates community guidelines"
  }'
```

### Retrieving a Report (Admin Only)

```bash
curl -X GET /api/reports/789 \
  -H "Authorization: Bearer admin_token_here"
```

## Implementation Notes

- Built with PHP using procedural approach
- Uses prepared statements for SQL injection prevention
- Implements CORS headers for cross-origin requests
- Includes comprehensive input validation and sanitization
- Uses database transactions for data consistency
- Follows RESTful API design principles