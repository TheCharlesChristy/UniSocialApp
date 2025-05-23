# User API Documentation

This documentation covers the available API endpoints for user management in the application. All endpoints require authentication unless otherwise specified.

## Authentication

All endpoints in this document require authentication. Include an `Authorization` header with a valid Bearer token:

```
Authorization: Bearer YOUR_ACCESS_TOKEN
```

If authentication is missing or invalid, the API will respond with a `401 Unauthorized` status code.

## General Response Format

Most responses follow this format:

```json
{
  "success": true|false,
  "message": "Description of the result",
  "data": { ... }
}
```

## Error Handling

Errors are returned with appropriate HTTP status codes and follow this format:

```json
{
  "success": false,
  "message": "Error description"
}
```

Common error status codes:

- `400 Bad Request`: Missing or invalid parameters
- `401 Unauthorized`: Authentication required or failed
- `403 Forbidden`: Insufficient permissions
- `404 Not Found`: Resource not found
- `500 Internal Server Error`: Server-side error

## User Endpoints

### Get Current User Profile

Retrieves the authenticated user's profile data.

- **URL**: `/api/users/me`
- **Method**: `GET`
- **Auth Required**: Yes

#### Response

```json
{
  "success": true,
  "message": "Authentication successful",
  "user": {
    "id": 1,
    "username": "johndoe",
    "email": "johndoe@example.com",
    "role": "user"
  }
}
```

### Get Specific User Profile

Retrieves a specific user's profile data.

- **URL**: `/api/users/:userId`
- **Method**: `GET`
- **Auth Required**: Yes
- **URL Parameters**:
  - `userId`: The ID of the user to retrieve

#### Response

```json
{
  "success": true,
  "user": {
    "user_id": 1,
    "username": "johndoe",
    "first_name": "John",
    "last_name": "Doe",
    "profile_picture": "media/images/profile/1_1620000000.jpg",
    "bio": "Software developer and hiking enthusiast",
    "friendship_status": "accepted"
  }
}
```

The `friendship_status` field will be one of:

- `null`: No relationship
- `pending`: Friend request sent but not accepted
- `accepted`: Users are friends

### Update User Profile

Updates the authenticated user's profile information.

- **URL**: `/api/users/me`
- **Method**: `PUT`
- **Auth Required**: Yes
- **Content-Type**: `multipart/form-data` (for file uploads) or `application/json`

#### Request Body

All fields are optional. Only provided fields will be updated.

```json
{
  "first_name": "Updated First Name",
  "last_name": "Updated Last Name",
  "bio": "Updated bio information"
}
```

For profile picture uploads, use `multipart/form-data` and include the image in the `profile_picture` field.

#### Response

```json
{
  "success": true,
  "message": "Profile updated successfully",
  "updated_fields": ["first_name", "last_name", "bio"],
  "profile_picture": "media/images/profile/1_1620000000.jpg"
}
```

### Update User Password

Updates the authenticated user's password.

- **URL**: `/api/users/me/password`
- **Method**: `PUT`
- **Auth Required**: Yes
- **Content-Type**: `application/json`

#### Request Body

```json
{
  "current_password": "existing-password",
  "new_password": "new-secure-password"
}
```

Requirements:

- `current_password`: Valid current password
- `new_password`: At least 8 characters

#### Response

```json
{
  "success": true,
  "message": "Password updated successfully"
}
```

### Get User Posts

Retrieves posts from a specific user.

- **URL**: `/api/users/:userId/posts`
- **Method**: `GET`
- **Auth Required**: Yes
- **URL Parameters**:
  - `userId`: The ID of the user whose posts to retrieve
- **Query Parameters**:
  - `page`: Page number (default: 1)
  - `limit`: Posts per page (default: 10, max: 50)

#### Response

```json
{
  "success": true,
  "posts": [
    {
      "post_id": 123,
      "user_id": 1,
      "username": "johndoe",
      "first_name": "John",
      "last_name": "Doe",
      "profile_picture": "media/images/profile/1_1620000000.jpg",
      "caption": "Example post caption",
      "post_type": "photo",
      "media_url": "media/images/posts/123_1620000000.jpg",
      "created_at": "2025-05-01 14:30:00",
      "updated_at": "2025-05-01 14:30:00",
      "privacy_level": "public",
      "location_name": "Mountain View, CA",
      "likes_count": 15,
      "comments_count": 3,
      "user_has_liked": true
    }
  ],
  "total_posts": 45,
  "current_page": 1,
  "total_pages": 5
}
```

Notes:

- Privacy filtering applies: users can see their own private posts, friends can see "friends" posts, and public posts are visible to all users
- `user_has_liked` indicates whether the authenticated user has liked the post

### Search Users

Searches for users by name or username.

- **URL**: `/api/users/search`
- **Method**: `GET`
- **Auth Required**: Yes
- **Query Parameters**:
  - `query`: Search term (required)
  - `page`: Page number (default: 1)
  - `limit`: Results per page (default: 10, max: 50)

#### Response

```json
{
  "success": true,
  "users": [
    {
      "user_id": 2,
      "username": "janedoe",
      "first_name": "Jane",
      "last_name": "Doe",
      "profile_picture": "media/images/profile/2_1620000000.jpg",
      "bio": "Photographer and traveler",
      "friendship_status": null
    }
  ],
  "total_results": 25,
  "current_page": 1,
  "total_pages": 3
}
```

### Get User Suggestions

Gets suggested users for friendship based on mutual connections.

- **URL**: `/api/users/suggestions`
- **Method**: `GET`
- **Auth Required**: Yes
- **Query Parameters**:
  - `limit`: Maximum number of suggestions (default: 10, max: 50)

#### Response

```json
{
  "success": true,
  "users": [
    {
      "user_id": 3,
      "username": "bobsmith",
      "first_name": "Bob",
      "last_name": "Smith",
      "profile_picture": "media/images/profile/3_1620000000.jpg",
      "bio": "Tech enthusiast",
      "mutual_friends_count": 5
    }
  ],
  "count": 10
}
```

Notes:

- Results are prioritized by number of mutual connections
- If there are insufficient mutual connections, newly registered users will be suggested
- Users who are already friends or have pending friend requests are excluded

## Implementation Notes

- All endpoints include CORS headers to allow cross-origin requests
- Validation is performed on all input data
- File uploads are restricted by type (.jpg, .jpeg, .png, .gif) and size (5MB max)
- Pagination is implemented where appropriate
- API supports both `PUT` and `POST` methods for update operations for better compatibility with clients
