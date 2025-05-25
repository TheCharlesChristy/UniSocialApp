# Test Reports API Endpoints

All tests are done in the [test_reports_endpoints](../../tests/test_reports_endpoints.php) file.

## Prerequisites

Before testing reports endpoints:

1. **Authentication Required**: Get a valid JWT token by logging in through the auth endpoints
2. **Database Setup**: Ensure reports, users, posts, and comments tables are properly initialized
3. **Test Data**: Have existing posts and comments in the database to create reports against
4. **User Accounts**: Have multiple user accounts for testing access controls

---

## Test Create Report

In the create report tab of the above. Enter the auth token, content type, content ID, reason, and optional description.

### Test Create Report with Valid Data

Input test data for creating a report with valid token, existing content (post/comment), valid reason, and optional description.

**Test Data:**
- Auth Token: (valid JWT token from login)
- Content Type: "post" or "comment"
- Content ID: (existing post_id or comment_id)
- Reason: "spam", "harassment", "inappropriate", "violence", or "other"
- Description: (optional detailed explanation)

**Expected Result:** Report created successfully with status 201 and report details returned.

### Test Create Report with Non-Existing Content

Input test data for creating a report with valid token but non-existing content ID.

**Test Data:**
- Auth Token: (valid JWT token)
- Content Type: "post"
- Content ID: 99999 (non-existing ID)
- Reason: "spam"

**Expected Result:** Error 404 with message about content not found.

### Test Create Report with Missing Required Fields

Input test data for creating a report with missing required fields.

**Test Data:**
- Auth Token: (valid JWT token)
- Content Type: "" (empty)
- Content ID: ""
- Reason: ""

**Expected Result:** Error 400 with message about missing required fields.


### Test Create Duplicate Report

Input test data for creating a report on the same content by the same user twice.

**Test Data:**
1. First create a report with valid data
2. Attempt to create another report on the same content with same user

**Expected Result:** Error 409 with message about duplicate report.

### Test Create Report on Own Content

Input test data for creating a report on content created by the same user.

**Test Data:**
- Auth Token: (valid JWT token from user who created the content)
- Content Type: "post"
- Content ID: (post_id created by same user)
- Reason: "spam"

**Expected Result:** Error 403 with message about not being able to report own content.

### Test Create Report on Comment

Input test data for creating a report specifically on a comment.

**Test Data:**
- Auth Token: (valid JWT token)
- Content Type: "comment"
- Content ID: (existing comment_id)
- Reason: "harassment"
- Description: "This comment contains offensive language"

**Expected Result:** Report created successfully with comment-specific validation.

---

## Test Get User Reports

In the get user reports tab of the above. Enter the auth token, user ID, and optional pagination parameters.

### Test Get Reports with Valid User ID

Input test data for getting reports with valid token and user ID (own reports).

**Test Data:**
- Auth Token: (valid JWT token)
- User ID: (same as authenticated user's ID)
- Page: 1
- Limit: 10

**Expected Result:** List of user's reports with pagination info and status 200.

### Test Get Reports with Different User ID

Input test data for getting reports with valid token but different user ID.

**Test Data:**
- Auth Token: (valid JWT token)
- User ID: (different user's ID)

**Expected Result:** Error 403 with message about access denied.

### Test Get Reports with Invalid User ID

Input test data for getting reports with valid token but non-existing user ID.

**Test Data:**
- Auth Token: (valid JWT token)
- User ID: 99999 (non-existing)

**Expected Result:** Error 404 with message about user not found.

### Test Get Reports with No Authentication

Input test data for getting reports without authentication token.

**Test Data:**
- Auth Token: (empty or invalid)
- User ID: 1

**Expected Result:** Error 401 with message about authentication required.

### Test Get Reports Pagination

Input test data for getting reports with valid token and test pagination parameters.

**Test Data:**
- Auth Token: (valid JWT token)
- User ID: (same as authenticated user)
- Page: 2
- Limit: 5

**Expected Result:** Second page of results with correct pagination metadata.

### Test Get Reports with Invalid Pagination

Input test data for getting reports with invalid pagination parameters.

**Test Data:**
- Auth Token: (valid JWT token)
- User ID: (same as authenticated user)
- Page: 0 (invalid)
- Limit: 100 (exceeds maximum)

**Expected Result:** Error 400 with message about invalid pagination parameters.

### Test Get Reports Empty Results

Input test data for getting reports for a user with no reports.

**Test Data:**
- Auth Token: (valid JWT token from user with no reports)
- User ID: (same as authenticated user with no reports)

**Expected Result:** Empty array with pagination info and status 200.

### Test Get Reports Default Pagination

Input test data for getting reports without specifying pagination parameters.

**Test Data:**
- Auth Token: (valid JWT token)
- User ID: (same as authenticated user)
- (no page or limit specified)

**Expected Result:** First page with default limit (10) and correct pagination.

### Test Get Reports with Status Filter

Input test data for getting reports and verify status information is included.

**Test Data:**
- Auth Token: (valid JWT token)
- User ID: (same as authenticated user)

**Expected Result:** Reports returned with status field (pending, reviewed, resolved).