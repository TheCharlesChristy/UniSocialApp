# Search API Documentation

## Overview

The Search API provides comprehensive search functionality across users and posts within the social media platform. The API supports global search, user-specific search, and post-specific search with advanced filtering options. All endpoints require user authentication and respect privacy settings and friendship relationships.

**Base URL:** `/api/search`

## Authentication

All search endpoints require authentication via Bearer token or session-based authentication. The authenticated user's ID is used to:
- Filter results based on privacy settings
- Determine friendship relationships
- Exclude the user's own profile from user searches

**Headers:**
- `Content-Type: application/json`
- `Authorization: Bearer <token>` (if using token-based auth)

## Endpoints

### 1. Global Search

Performs a unified search across both users and posts.

**Endpoint:** `GET /api/search/index`

**Description:** Searches across users and posts simultaneously, returning a combined result set with configurable result types.

**Parameters:**

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `q` | string | Yes | - | Search query string |
| `type` | string | No | `all` | Search type: `all`, `users`, or `posts` |
| `page` | integer | No | `1` | Page number for pagination |
| `limit` | integer | No | `10` | Results per page (max 50) |

**Example Request:**
```bash
curl -X GET "/api/search/index?q=john&type=all&page=1&limit=10" \
  -H "Authorization: Bearer <token>"
```

**Response Format:**

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Search completed successfully",
  "query": "john",
  "type": "all",
  "results": {
    "users": [
      {
        "user_id": 123,
        "username": "johndoe",
        "first_name": "John",
        "last_name": "Doe",
        "profile_picture": "https://example.com/profile.jpg",
        "bio": "Software developer from NYC",
        "friendship_status": "accepted"
      }
    ],
    "posts": [
      {
        "post_id": 456,
        "user_id": 789,
        "caption": "Meeting with John today!",
        "post_type": "photo",
        "media_url": "https://example.com/photo.jpg",
        "created_at": "2024-01-15T10:30:00Z",
        "privacy_level": "public",
        "location_name": "Central Park",
        "username": "alice_smith",
        "first_name": "Alice",
        "last_name": "Smith",
        "profile_picture": "https://example.com/alice.jpg",
        "likes_count": 25,
        "comments_count": 8,
        "user_has_liked": 0
      }
    ]
  },
  "counts": {
    "users": 1,
    "posts": 1
  }
}
```

**Pagination Response (when type != "all"):**
```json
{
  "success": true,
  "message": "Search completed successfully",
  "query": "john",
  "type": "users",
  "results": {
    "users": [...],
    "posts": []
  },
  "pagination": {
    "current_page": 1,
    "total_pages": 5,
    "total_results": 47
  }
}
```

---

### 2. User Search

Advanced search specifically for users with filtering options.

**Endpoint:** `GET /api/search/users`

**Description:** Searches for users by name, username, or email with support for role and status filtering.

**Parameters:**

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `q` | string | Yes | - | Search query (name, username, or email) |
| `page` | integer | No | `1` | Page number for pagination |
| `limit` | integer | No | `10` | Results per page (max 50) |
| `role` | string | No | - | Filter by user role: `user`, `admin`, `moderator` |
| `status` | string | No | `active` | Account status: `active`, `suspended`, `all` |

**Example Request:**
```bash
curl -X GET "/api/search/users?q=john%20doe&status=active&page=1&limit=20" \
  -H "Authorization: Bearer <token>"
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "User search completed successfully",
  "query": "john doe",
  "filters": {
    "status": "active",
    "role": null
  },
  "users": [
    {
      "user_id": 123,
      "username": "johndoe",
      "first_name": "John",
      "last_name": "Doe",
      "profile_picture": "https://example.com/profile.jpg",
      "bio": "Software developer from NYC",
      "role": "user",
      "account_status": "active",
      "registration_date": "2023-06-15T09:00:00Z",
      "friendship_status": "pending",
      "stats": {
        "posts_count": 45,
        "friends_count": 128
      }
    }
  ],
  "pagination": {
    "total_results": 1,
    "current_page": 1,
    "total_pages": 1,
    "per_page": 20
  }
}
```

---

### 3. Post Search

Advanced search for posts with comprehensive filtering options.

**Endpoint:** `GET /api/search/posts`

**Description:** Searches posts by content, location, author, and other criteria with advanced filtering and sorting options.

**Parameters:**

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `q` | string | No* | - | Search query for post content |
| `location` | string | No* | - | Search query for location names |
| `author` | string | No* | - | Filter by author username |
| `page` | integer | No | `1` | Page number for pagination |
| `limit` | integer | No | `10` | Results per page (max 50) |
| `post_type` | string | No | - | Filter by type: `photo`, `video`, `text` |
| `privacy` | string | No | - | Filter by privacy: `public`, `friends` |
| `date_from` | string | No | - | Start date filter (YYYY-MM-DD) |
| `date_to` | string | No | - | End date filter (YYYY-MM-DD) |
| `sort_by` | string | No | `relevance` | Sort order: `relevance`, `date`, `likes`, `comments` |

*At least one of `q`, `location`, or `author` is required.

**Example Request:**
```bash
curl -X GET "/api/search/posts?q=vacation&location=beach&post_type=photo&sort_by=likes&page=1&limit=15" \
  -H "Authorization: Bearer <token>"
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Post search completed successfully",
  "search_params": {
    "query": "vacation",
    "location": "beach",
    "author": "",
    "post_type": "photo",
    "privacy": null,
    "date_from": null,
    "date_to": null,
    "sort_by": "likes"
  },
  "posts": [
    {
      "post_id": 789,
      "user_id": 456,
      "caption": "Amazing vacation at the beach! #summer #vacation",
      "post_type": "photo",
      "media_url": "https://example.com/beach_photo.jpg",
      "created_at": "2024-07-20T15:30:00Z",
      "updated_at": "2024-07-20T15:30:00Z",
      "privacy_level": "public",
      "location_lat": 25.7617,
      "location_lng": -80.1918,
      "location_name": "Miami Beach",
      "username": "travel_lover",
      "first_name": "Sarah",
      "last_name": "Johnson",
      "profile_picture": "https://example.com/sarah.jpg",
      "likes_count": 142,
      "comments_count": 23,
      "user_has_liked": 1
    }
  ],
  "pagination": {
    "total_results": 1,
    "current_page": 1,
    "total_pages": 1,
    "per_page": 15
  }
}
```

## Data Models

### User Object
```json
{
  "user_id": 123,
  "username": "johndoe",
  "first_name": "John",
  "last_name": "Doe",
  "profile_picture": "https://example.com/profile.jpg",
  "bio": "User biography text",
  "role": "user",
  "account_status": "active",
  "registration_date": "2023-06-15T09:00:00Z",
  "friendship_status": "accepted",
  "stats": {
    "posts_count": 45,
    "friends_count": 128
  }
}
```

### Post Object
```json
{
  "post_id": 789,
  "user_id": 456,
  "caption": "Post content text",
  "post_type": "photo",
  "media_url": "https://example.com/media.jpg",
  "created_at": "2024-07-20T15:30:00Z",
  "updated_at": "2024-07-20T15:30:00Z",
  "privacy_level": "public",
  "location_lat": 25.7617,
  "location_lng": -80.1918,
  "location_name": "Location Name",
  "username": "author_username",
  "first_name": "Author",
  "last_name": "Name",
  "profile_picture": "https://example.com/author.jpg",
  "likes_count": 142,
  "comments_count": 23,
  "user_has_liked": 1
}
```

### Pagination Object
```json
{
  "total_results": 150,
  "current_page": 1,
  "total_pages": 15,
  "per_page": 10
}
```

## Error Handling

### Common Error Responses

**400 Bad Request - Missing Required Parameter:**
```json
{
  "success": false,
  "message": "Search query is required"
}
```

**401 Unauthorized - Authentication Required:**
```json
{
  "success": false,
  "message": "Authentication required"
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
  "message": "Search failed: Database connection error"
}
```

## Privacy and Security Features

### Privacy Filtering
- **Public Posts**: Visible to all authenticated users
- **Friends Posts**: Only visible to users with accepted friendship status
- **User's Own Posts**: Always visible to the post author
- **Account Status**: Only active accounts appear in search results (unless specifically filtering for other statuses)

### Search Ranking
Search results are ranked using relevance scoring:

1. **Exact matches** (username, full name) receive highest priority
2. **Partial matches** in primary fields (first name, last name)
3. **Content matches** in descriptions and captions
4. **Location matches** for posts
5. **Chronological ordering** as secondary sort criteria

### Rate Limiting
- Maximum 50 results per page
- Default pagination limits prevent excessive data retrieval
- Query optimization for large datasets

## Usage Examples

### Basic Global Search
```bash
# Search for "john" across all content types
curl -X GET "/api/search/index?q=john" \
  -H "Authorization: Bearer <token>"
```

### Find Users by Role
```bash
# Search for admin users named "smith"
curl -X GET "/api/search/users?q=smith&role=admin" \
  -H "Authorization: Bearer <token>"
```

### Advanced Post Filtering
```bash
# Find recent vacation photos with high engagement
curl -X GET "/api/search/posts?q=vacation&post_type=photo&date_from=2024-06-01&sort_by=likes" \
  -H "Authorization: Bearer <token>"
```

### Location-based Post Search
```bash
# Find all posts from "Central Park"
curl -X GET "/api/search/posts?location=Central%20Park&sort_by=date" \
  -H "Authorization: Bearer <token>"
```

## Implementation Notes

- All search queries use SQL LIKE patterns with wildcards for flexible matching
- Friendship relationships are checked bidirectionally for privacy enforcement
- Search results exclude the authenticated user's own profile from user searches
- Location coordinates are included in post results when available
- User statistics (posts count, friends count) are calculated dynamically
- Multi-word searches support full name matching for improved user discovery