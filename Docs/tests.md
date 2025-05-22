## Test Database Connection

Done by going to [test_db_conn](../tests/test_db_conn.php) on the web.

## Test Authentication API Endpoints

All tests are done in the [test_auth](../tests/test_auth_endpoints.php) file.

### Test Registration

In the registration tab of the above.

#### Test Registration with Valid Data

Input test data for registration with valid username, email, and password.

#### Test Registration with existing username

Input test data for registration with an existing username.

#### Test Registration with existing email

Input test data for registration with an existing email.

### Test Login

In the login tab of the above.

#### Test Login with Valid Credentials

Input test data for login with valid username/email and password.

#### Test Login with Invalid Credentials

Input test data for login with invalid username/email and password.

### Test Logout

In the logout tab of the above.

#### Test Logout with Valid Token

Input test data for logout with a valid token.

#### Test Logout with Invalid Token

Input test data for logout with an invalid token.

### Test Forgot Password

Press send request in the forgot password tab of the above.

### Test Reset Password

Do the Test Forgot Password first to get the token. This should be in email_logs in backend/src/api/auth
Then go to the reset password tab of the above and enter the token and new password.

### Test Verify Email

Upon creating an account a token is put into the email_logs.txt file in the backend/src/api/auth directory.
Get the token from there and put it in the verify email tab of the above.

### Test Me (JWT protected user auth endpoint)

When Registering a user, a JWT token is generated and displayed in the response.
Then go to the me tab of the above and enter the token in the Authorization header.
