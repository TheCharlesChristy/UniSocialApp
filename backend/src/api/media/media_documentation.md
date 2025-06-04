# Media API Documentation

## Overview
The Media API provides endpoints for uploading and serving media files (images and videos) for user profiles and posts. It handles secure file uploads with validation, automatic file organization, and optimized media delivery with proper caching headers.

**Base URL:** `/api/media/`

## Authentication
- **Upload endpoint** requires authentication via Bearer token
- **Media serving endpoint** is publicly accessible (no authentication required)

## Table of Contents
- [Upload Media](#upload-media)
- [Get Media](#get-media)
- [Error Handling](#error-handling)
- [File Organization](#file-organization)

---

## Upload Media

### POST /api/media/upload

Uploads media files for profile pictures or post content with automatic validation and processing.

**Authentication:** Required (Bearer token)

**Content-Type:** `multipart/form-data`

**Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| file | file | Yes | The media file to upload |
| type | string | Yes | Upload type: `profile_picture` or `post_media` |

**File Restrictions:**

| Upload Type | Allowed Formats | Max Size | MIME Types |
|-------------|----------------|----------|------------|
| profile_picture | jpg, jpeg, png, gif | 5MB | image/jpeg, image/png, image/gif |
| post_media | jpg, jpeg, png, gif, mp4, avi, mov | 50MB | image/jpeg, image/png, image/gif, video/mp4, video/avi, video/quicktime |

**Request Example:**
```bash
curl -X POST https://example.com/api/media/upload \
  -H "Authorization: Bearer your_token_here" \
  -F "file=@profile_image.jpg" \
  -F "type=profile_picture"
```

**Response Codes:**
- **200 OK** - File uploaded successfully
- **400 Bad Request** - Invalid file type, missing parameters, or validation error
- **405 Method Not Allowed** - Non-POST request
- **413 Payload Too Large** - File size exceeds limits
- **500 Internal Server Error** - Server-side upload failure

**Success Response:**
```json
{
  "success": true,
  "message": "File uploaded successfully",
  "file_path": "media/images/123_1640995200_abc123.jpg",
  "file_size": 2048576,
  "file_type": "image/jpeg"
}
```

**Error Response Examples:**
```json
{
  "success": false,
  "message": "Invalid file type. Allowed: jpg, jpeg, png, gif"
}
```

```json
{
  "success": false,
  "message": "File size too large. Maximum allowed size is 5.00 MB",
  "uploaded_size": "8.50 MB",
  "max_size": "5.00 MB"
}
```

---

## Get Media

### GET /api/media/get_media

Serves media files with proper content types and caching headers. Automatically detects file location across multiple subdirectories.

**Authentication:** Not required (public endpoint)

**Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| file | string | Yes | Filename or relative path to the media file |

**Request Examples:**
```bash
# Basic filename
GET /api/media/get_media?file=profile_123.jpg

# With path (path components are automatically extracted)
GET /api/media/get_media?file=media/images/posts/post_456.jpg
```

**File Location Logic:**
The API automatically searches for files in the following order:
1. `/media/images/` or `/media/videos/` (main directory)
2. `/media/images/posts/` or `/media/videos/posts/` (posts subdirectory)
3. `/media/images/profile/` or `/media/videos/profile/` (profile subdirectory)

**File Type Detection:**
- Files containing `_video_` or with extensions `.mp4`, `.avi`, `.mov`, `.webm` are served from the videos directory
- All other files are served from the images directory

**Response Headers:**
```
Content-Type: image/jpeg (or appropriate MIME type)
Content-Length: 2048576
Cache-Control: public, max-age=31536000
Expires: Thu, 01 Jan 2026 00:00:00 GMT
Accept-Ranges: bytes (for images)
```

**Supported MIME Types:**

| Extension | MIME Type |
|-----------|-----------|
| jpg, jpeg | image/jpeg |
| png | image/png |
| gif | image/gif |
| webp | image/webp |
| svg | image/svg+xml |
| mp4 | video/mp4 |
| avi | video/x-msvideo |
| mov | video/quicktime |
| webm | video/webm |

**Response Codes:**
- **200 OK** - File served successfully
- **400 Bad Request** - Missing file parameter or invalid filename
- **404 Not Found** - File does not exist
- **405 Method Not Allowed** - Non-GET request

**Error Response Examples:**
```json
{
  "success": false,
  "message": "File parameter is required"
}
```

```json
{
  "success": false,
  "message": "File not found",
  "debug": {
    "original_file_param": "nonexistent.jpg",
    "extracted_filename": "nonexistent.jpg",
    "base_path": "/var/www/media/images/",
    "possible_paths": [
      "/var/www/media/images/nonexistent.jpg",
      "/var/www/media/images/posts/nonexistent.jpg",
      "/var/www/media/images/profile/nonexistent.jpg"
    ],
    "file_exists_checks": [
      {"path": "/var/www/media/images/nonexistent.jpg", "exists": false, "is_readable": false}
    ]
  }
}
```

---

## Error Handling

### Common Error Codes

| Code | Status | Description |
|------|--------|-------------|
| 400 | Bad Request | Invalid parameters, file type, or malformed request |
| 401 | Unauthorized | Missing or invalid authentication token (upload only) |
| 404 | Not Found | Requested media file does not exist |
| 405 | Method Not Allowed | Incorrect HTTP method used |
| 413 | Payload Too Large | File size exceeds maximum allowed size |
| 500 | Internal Server Error | Server-side processing error |

### Upload-Specific Errors

| Error Code | Description |
|------------|-------------|
| UPLOAD_ERR_INI_SIZE | File size exceeds server limit |
| UPLOAD_ERR_FORM_SIZE | File size exceeds form limit |
| UPLOAD_ERR_PARTIAL | File was only partially uploaded |
| UPLOAD_ERR_NO_FILE | No file was uploaded |
| UPLOAD_ERR_NO_TMP_DIR | Missing temporary folder |
| UPLOAD_ERR_CANT_WRITE | Failed to write file to disk |
| UPLOAD_ERR_EXTENSION | File upload stopped by extension |

### Security Features

**Upload Security:**
- Authentication required for uploads
- File type validation (both extension and MIME type)
- Filename sanitization to prevent directory traversal
- Unique filename generation to prevent conflicts
- File size limits enforced

**Download Security:**
- Directory traversal protection using `basename()`
- Path separator validation
- Secure file path resolution

---

## File Organization

### Directory Structure
```
media/
├── images/
│   ├── [user_files]           # Direct profile pictures
│   ├── posts/                 # Post-related images
│   └── profile/               # Profile-specific images
└── videos/
    ├── [user_files]           # Direct video files
    ├── posts/                 # Post-related videos
    └── profile/               # Profile-specific videos
```

### Filename Convention
Uploaded files are automatically renamed using the pattern:
```
{user_id}_{timestamp}_{unique_id}.{extension}
```

Example: `123_1640995200_abc123def456.jpg`

### Profile Picture Updates
When uploading a profile picture (`type=profile_picture`), the user's profile is automatically updated in the database with the new file path.

---

## Implementation Notes

1. **Debug Information**: The get_media endpoint includes debug information in error responses during development (should be removed in production)

2. **Caching**: Media files are served with long-term caching headers (1 year) for optimal performance

3. **Rate Limiting**: Upload endpoint includes placeholder for rate limiting implementation

4. **CORS**: Upload endpoint includes CORS headers for cross-origin requests

5. **Error Suppression**: PHP errors are suppressed in upload endpoint to maintain clean JSON responses