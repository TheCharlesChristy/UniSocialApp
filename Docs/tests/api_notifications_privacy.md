# Notifications and Privacy API Test Plan

## Overview
This test plan covers all notification management and privacy settings endpoints available in the `test_notifications_privacy_endpoints.php` test page.

## Prerequisites
- Two user accounts created and verified
- Valid auth tokens for both users
- Access to `http://localhost/webdev/tests/test_notifications_privacy_endpoints.php`
- Some existing posts/comments for notification testing

## Test Data Setup
For this test plan, we'll assume:
- **User A**: Your primary test account (Token A)
- **User B**: Secondary test account (Token B, User ID: 7)
- **Post ID**: 1 (existing post for notification testing)
- **Comment ID**: 1 (existing comment for notification testing)

---

## Test Execution Steps

### Phase 1: Initial Setup and Authentication

#### Step 1.1: Access Test Page
1. Open browser and navigate to `http://localhost/webdev/tests/test_notifications_privacy_endpoints.php`
2. Verify the page loads with two tabs: "Notifications", "Privacy Settings"

#### Step 1.2: Authentication Setup
1. Enter **Token A** (your primary user's token) in the "Access Token" field
2. Verify token is saved to localStorage for persistence
3. Keep this token ready for switching between users during tests

---

### Phase 2: Privacy Settings Management

#### Step 2.1: Test Get Privacy Settings (First Time)
**Tab:** Privacy Settings
**Section:** Get Privacy Settings

1. **Action:** Click "Get Privacy Settings"
2. **Expected Success Result (Default Settings):**
   ```json
   {
     "success": true,
     "data": {
       "user_id": 1,
       "profile_visibility": "public",
       "posts_visibility": "public",
       "friends_visibility": "public",
       "allow_friend_requests": true,
       "show_online_status": true,
       "allow_messages_from": "everyone",
       "created_at": "2025-05-25 ...",
       "updated_at": "2025-05-25 ..."
     }
   }
   ```

#### Step 2.2: Test Update Privacy Settings
**Tab:** Privacy Settings
**Section:** Update Privacy Settings

1. **Input Configuration:**
   - Profile Visibility: `friends`
   - Posts Visibility: `private`
   - Friends List Visibility: `friends`
   - Allow Friend Requests: `No`
   - Show Online Status: `No`
   - Allow Messages From: `friends`

2. **Action:** Click "Update Privacy Settings"
3. **Expected Success Result:**
   ```json
   {
     "success": true,
     "message": "Privacy settings updated successfully",
     "data": {
       "user_id": 1,
       "profile_visibility": "friends",
       "posts_visibility": "private",
       "friends_visibility": "friends",
       "allow_friend_requests": false,
       "show_online_status": false,
       "allow_messages_from": "friends",
       "updated_at": "2025-05-25 ..."
     }
   }
   ```

#### Step 2.3: Verify Privacy Settings Update
1. **Action:** Click "Get Privacy Settings" again
2. **Expected Result:** Should reflect all the changes made in Step 2.2

#### Step 2.4: Test Privacy Settings Validation
**Test Invalid Values:**

1. **Profile Visibility:** Try invalid value (manually edit browser dev tools)
2. **Expected Error:**
   ```json
   {
     "success": false,
     "message": "Invalid profile_visibility. Valid options: public, friends, private"
   }
   ```

---

### Phase 3: Notification Creation and Management (Admin Functions)

#### Step 3.1: Test Create Notification (User A to User B)
**Tab:** Notifications
**Section:** Create Notification (Admin Only)

1. **Input Configuration:**
   - Recipient User ID: `7` (User B)
   - Notification Type: `like`
   - Related Content Type: `post`
   - Related Content ID: `1`

2. **Action:** Click "Create Notification"
3. **Expected Success Result:**
   ```json
   {
     "success": true,
     "message": "Notification created successfully",
     "notification_id": 1
   }
   ```

#### Step 3.2: Test Create Multiple Notification Types
**Repeat Step 3.1 with different configurations:**

**Configuration 1 - Comment Notification:**
- Type: `comment`, Content Type: `post`, Content ID: `1`

**Configuration 2 - Friend Request Notification:**
- Type: `friend_request`, Content Type: `user`, Content ID: User A's ID

**Configuration 3 - Mention Notification:**
- Type: `mention`, Content Type: `comment`, Content ID: `1`

#### Step 3.3: Test Duplicate Notification Prevention
1. **Repeat Step 3.1:** Use exact same parameters
2. **Expected Error Result:**
   ```json
   {
     "success": false,
     "message": "Duplicate notification already exists"
   }
   ```

#### Step 3.4: Test Invalid Create Notification Parameters
**Test Missing Required Fields:**
1. **Leave Recipient ID empty:** Should return field required error
2. **Use Invalid User ID (999):** Should return recipient not found error
3. **Use Invalid Type:** Should return invalid notification type error
4. **Try to send to yourself:** Should return cannot send to yourself error

---

### Phase 4: Notification Retrieval and Reading

#### Step 4.1: Switch to User B and Test Get Notifications
1. **Change Token:** Replace auth token with **Token B**
2. **Tab:** Notifications
3. **Section:** Get Notifications

**Test Basic Retrieval:**
1. **Input:** Page: `1`, Limit: `10`, Filter: `All notifications`
2. **Action:** Click "Get Notifications"
3. **Expected Success Result:**
   ```json
   {
     "success": true,
     "data": {
       "notifications": [
         {
           "notification_id": 1,
           "type": "like",
           "related_content_type": "post",
           "related_content_id": 1,
           "is_read": false,
           "created_at": "2025-05-25 ...",
           "sender": {
             "user_id": 1,
             "username": "user_a_username",
             "first_name": "User",
             "last_name": "A",
             "profile_picture": null
           }
         }
       ],
       "pagination": {
         "current_page": 1,
         "total_pages": 1,
         "total_notifications": 3,
         "per_page": 10
       }
     }
   }
   ```

#### Step 4.2: Test Get Unread Count
**Section:** Get Unread Count

1. **Action:** Click "Get Unread Count"
2. **Expected Success Result:**
   ```json
   {
     "success": true,
     "data": {
       "unread_count": 3
     }
   }
   ```

#### Step 4.3: Test Filter Unread Notifications
**Section:** Get Notifications

1. **Input:** Page: `1`, Limit: `10`, Filter: `Unread only`
2. **Action:** Click "Get Notifications"
3. **Expected Result:** Should show only unread notifications (all of them initially)

---

### Phase 5: Marking Notifications as Read

#### Step 5.1: Test Mark Single Notification as Read
**Section:** Mark Notification as Read

1. **Input:** Notification ID: `1` (from previous results)
2. **Action:** Click "Mark as Read"
3. **Expected Success Result:**
   ```json
   {
     "success": true,
     "message": "Notification marked as read successfully"
   }
   ```

#### Step 5.2: Verify Single Read Status
1. **Action:** Click "Get Unread Count"
2. **Expected Result:** Count should decrease by 1
3. **Action:** Get notifications with unread filter
4. **Expected Result:** Should not show the marked notification

#### Step 5.3: Test Mark All Notifications as Read
**Section:** Mark All Notifications as Read

1. **Action:** Click "Mark All as Read"
2. **Expected Success Result:**
   ```json
   {
     "success": true,
     "message": "All notifications marked as read successfully",
     "updated_count": 2
   }
   ```

#### Step 5.4: Verify All Read Status
1. **Action:** Click "Get Unread Count"
2. **Expected Result:**
   ```json
   {
     "success": true,
     "data": {
       "unread_count": 0
     }
   }
   ```

---

### Phase 6: Notification Deletion

#### Step 6.1: Test Delete Specific Notification
**Section:** Delete Notification

1. **Input:** Notification ID: `1`
2. **Action:** Click "Delete Notification"
3. **Confirm:** Click "OK" on confirmation dialog
4. **Expected Success Result:**
   ```json
   {
     "success": true,
     "message": "Notification deleted successfully"
   }
   ```

#### Step 6.2: Verify Deletion
1. **Action:** Get notifications list
2. **Expected Result:** Should not contain the deleted notification
3. **Total count should decrease accordingly**

#### Step 6.3: Test Delete Non-existent Notification
1. **Input:** Notification ID: `999`
2. **Action:** Click "Delete Notification"
3. **Expected Error Result:**
   ```json
   {
     "success": false,
     "message": "Notification not found or access denied"
   }
   ```

---

### Phase 7: Cross-User Permission Testing

#### Step 7.1: Test Unauthorized Notification Access
**Switch back to User A (Token A):**

1. **Try to delete User B's notification:** Use notification ID that belongs to User B
2. **Expected Error:**
   ```json
   {
     "success": false,
     "message": "Notification not found or access denied"
   }
   ```

#### Step 7.2: Test Privacy Settings Cross-User Access
**User A trying to access User B's privacy:**

1. **Note:** Privacy endpoints are user-specific and should only return current user's settings
2. **Action:** Get privacy settings with User A's token
3. **Expected Result:** Should only return User A's privacy settings, not User B's

---

### Phase 8: Pagination Testing

#### Step 8.1: Test Notification Pagination
**Create enough notifications for pagination testing (if needed):**

1. **Create 15+ notifications** using the create notification endpoint
2. **Test pagination parameters:**
   - Page: `1`, Limit: `5`
   - Page: `2`, Limit: `5`
   - Page: `3`, Limit: `5`

3. **Verify pagination response:**
   ```json
   {
     "pagination": {
       "current_page": 2,
       "total_pages": 3,
       "total_notifications": 15,
       "per_page": 5
     }
   }
   ```

#### Step 8.2: Test Pagination Limits
1. **Test maximum limit:** Try Limit: `999`
2. **Expected Result:** Should be capped at 50
3. **Test invalid page:** Try Page: `0` or negative numbers
4. **Expected Result:** Should default to page 1

---

### Phase 9: Error Handling and Edge Cases

#### Step 9.1: Test Invalid Authentication
1. **Use invalid token:** Enter expired/fake token
2. **Try any endpoint:** Attempt any API call
3. **Expected Error:**
   ```json
   {
     "success": false,
     "message": "Invalid or expired token"
   }
   ```

#### Step 9.2: Test Missing Authentication
1. **Clear token field:** Remove auth token
2. **Try protected endpoint:** Attempt API call
3. **Expected Error:** Should return authentication required error

#### Step 9.3: Test Malformed Requests
1. **Invalid JSON in request body**
2. **Missing required fields**
3. **Invalid data types**
4. **Expected Result:** Proper error messages for each case

#### Step 9.4: Test SQL Injection Prevention
1. **Try malicious input:** Use SQL injection strings in notification ID fields
2. **Expected Result:** Should be properly sanitized and return appropriate errors

---

### Phase 10: Performance and Load Testing

#### Step 10.1: Test Large Dataset Handling
1. **Create 100+ notifications**
2. **Test retrieval performance:**
   - Large page sizes
   - Multiple page requests
   - Filter operations

#### Step 10.2: Test Concurrent Operations
1. **Multiple browser tabs:** Open same test page in multiple tabs
2. **Simultaneous requests:** Make concurrent API calls
3. **Expected Result:** No data corruption or conflicts

---

## Success Criteria

### All Tests Pass When:
- Privacy settings can be retrieved and updated successfully
- Default privacy settings are created automatically
- All notification types can be created with proper validation
- Notifications are properly retrieved with correct pagination
- Unread count is accurate and updates correctly
- Individual and bulk mark-as-read operations work
- Notification deletion works with proper permissions
- Cross-user access is properly restricted
- All error cases return appropriate HTTP status codes
- Pagination works correctly with all parameters
- Authentication is enforced on all protected endpoints

### Error Scenarios Work Correctly:
- Invalid tokens return 401 Unauthorized
- Missing required fields return 400 Bad Request
- Non-existent resources return 404 Not Found
- Permission violations return 403 Forbidden
- Duplicate operations are handled gracefully
- Malformed requests are rejected safely
- SQL injection attempts are prevented

---

## Test Results Template

**Date:** ___________  
**Tester:** ___________  
**Test Environment:** localhost/webdev

| Test Phase | Test Case | Status | Notes |
|------------|-----------|--------|-------|
| Phase 2.1 | Get Privacy Settings (Default) | PASS/FAIL | |
| Phase 2.2 | Update Privacy Settings | PASS/FAIL | |
| Phase 2.3 | Verify Privacy Update | PASS/FAIL | |
| Phase 2.4 | Privacy Validation | PASS/FAIL | |
| Phase 3.1 | Create Notification | PASS/FAIL | |
| Phase 3.2 | Create Multiple Types | PASS/FAIL | |
| Phase 3.3 | Duplicate Prevention | PASS/FAIL | |
| Phase 3.4 | Invalid Parameters | PASS/FAIL | |
| Phase 4.1 | Get Notifications | PASS/FAIL | |
| Phase 4.2 | Get Unread Count | PASS/FAIL | |
| Phase 4.3 | Filter Unread | PASS/FAIL | |
| Phase 5.1 | Mark Single as Read | PASS/FAIL | |
| Phase 5.2 | Verify Single Read | PASS/FAIL | |
| Phase 5.3 | Mark All as Read | PASS/FAIL | |
| Phase 5.4 | Verify All Read | PASS/FAIL | |
| Phase 6.1 | Delete Notification | PASS/FAIL | |
| Phase 6.2 | Verify Deletion | PASS/FAIL | |
| Phase 6.3 | Delete Non-existent | PASS/FAIL | |
| Phase 7.1 | Unauthorized Access | PASS/FAIL | |
| Phase 7.2 | Cross-User Privacy | PASS/FAIL | |
| Phase 8.1 | Pagination Testing | PASS/FAIL | |
| Phase 8.2 | Pagination Limits | PASS/FAIL | |
| Phase 9.x | Error Handling | PASS/FAIL | |
| Phase 10.x | Performance Testing | PASS/FAIL | |

**Overall Result:** PASS / FAIL

**Performance Notes:**
- Average response time for get notifications: _____ ms
- Average response time for privacy operations: _____ ms
- Maximum concurrent users tested: _____

**Additional Notes:**
_________________________________
_________________________________
_________________________________

**Security Test Results:**
- SQL injection prevention: PASS / FAIL
- Authentication bypass attempts: PASS / FAIL
- Cross-user data access prevention: PASS / FAIL
- Token validation: PASS / FAIL

**Recommendations:**
_________________________________
_________________________________
_________________________________
