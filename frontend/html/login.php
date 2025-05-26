<?php
session_start();

// Redirect if user is already logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['jwt_token'])) {
    header('Location: main-feed.php');
    exit();
}

// Handle form submission
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Basic validation
    if (empty($email) || empty($password)) {
        $error_message = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } else {
        // Prepare data for API call
        $login_data = [
            'email' => $email,
            'password' => $password
        ];
        
        // Make API call to login endpoint
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        echo "API URL: $protocol://$host\n"; // Debugging line
        $api_url = $protocol . '://' . $host . '/webdev/backend/src/api/auth/login.php';

        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($login_data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen(json_encode($login_data))
        ]);
        
        $response = curl_exec($ch);
        $curl_error = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($response === false || !empty($curl_error)) {
            $error_message = 'Unable to connect to authentication service. Please try again. Error: ' . $curl_error;
        } else {
            $result = json_decode($response, true);
            
            if ($http_code === 200 && $result['success']) {
                // Login successful - store JWT and user info in session
                $_SESSION['jwt_token'] = $result['token'];
                $_SESSION['user_id'] = $result['user_id'];
                $_SESSION['token_expiration'] = $result['expiration'];
                
                // Set JWT in HTTP-only cookie for security
                $cookie_expiry = strtotime($result['expiration']);
                setcookie('jwt_token', $result['token'], $cookie_expiry, '/', '', true, true);
                
                // Redirect to main feed or dashboard
                header('Location: main-feed.php');
                exit();
            } else {
                // Login failed
                $error_message = $result['message'] ?? 'Login failed. Please check your credentials.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In to SocialConnect - Access Your Account</title>
    <meta name="description" content="Sign in to your SocialConnect account to connect with friends, share content, and explore communities.">
    <link rel="stylesheet" href="../css/generic.css">
    <link rel="stylesheet" href="../css/login_page.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .alert {
            padding: 12px 16px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-size: 14px;
            line-height: 1.4;
        }
        .alert-error {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
        }
        .alert-success {
            background-color: #f0f9ff;
            border: 1px solid #bae6fd;
            color: #0369a1;
        }
        .form-input.error {
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }
    </style>
</head>
<body>
    <header class="header">
        <nav class="navbar container">
            <a href="welcome.php" class="logo">SocialConnect</a>
            <ul class="nav-menu">
                <li><a href="welcome.php" class="nav-link">Home</a></li>
                <li><a href="login.php" class="nav-link active">Sign In</a></li>
                <li><a href="register.php" class="btn btn-primary">Register</a></li>
            </ul>
            <button class="nav-toggle" aria-label="Toggle navigation">☰</button>
        </nav>
    </header>

    <main class="auth-main">
        <div class="container">
            <div class="auth-container">
                <div class="auth-form-section">
                    <div class="auth-header">
                        <h1>Welcome back</h1>
                        <p>Sign in to your SocialConnect account</p>
                    </div>

                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-error" role="alert">
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success" role="alert">
                            <?php echo htmlspecialchars($success_message); ?>
                        </div>
                    <?php endif; ?>

                    <div class="divider">
                        <span>or sign in with email</span>
                    </div>

                    <form class="auth-form" id="loginForm" method="POST" novalidate>
                        <div class="form-group">
                            <label for="email" class="form-label">Email address</label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                class="form-input <?php echo (!empty($error_message) && empty($_POST['email'])) ? 'error' : ''; ?>" 
                                placeholder="Enter your email"
                                value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                required
                                aria-describedby="email-error"
                            >
                            <div class="form-error" id="email-error" role="alert"></div>
                        </div>

                        <div class="form-group">
                            <label for="password" class="form-label">Password</label>
                            <div class="password-input-wrapper">
                                <input 
                                    type="password" 
                                    id="password" 
                                    name="password" 
                                    class="form-input <?php echo (!empty($error_message) && empty($_POST['password'])) ? 'error' : ''; ?>" 
                                    placeholder="Enter your password"
                                    required
                                    aria-describedby="password-error"
                                >
                                <button type="button" class="password-toggle" aria-label="Toggle password visibility">
                                    👁️
                                </button>
                            </div>
                            <div class="form-error" id="password-error" role="alert"></div>
                        </div>

                        <div class="form-options">
                            <label class="checkbox-wrapper">
                                <input type="checkbox" id="remember" name="remember" <?php echo isset($_POST['remember']) ? 'checked' : ''; ?>>
                                <span class="checkmark"></span>
                                <span class="checkbox-label">Remember me</span>
                            </label>
                            <a href="forgot-password.php" class="forgot-link">Forgot password?</a>
                        </div>

                        <button type="submit" class="btn btn-primary btn-full btn-lg">
                            <span class="btn-text">Sign In</span>
                            <span class="spinner hidden"></span>
                        </button>

                        <div class="auth-footer">
                            <p>Don't have an account? <a href="register.php">Sign up for free</a></p>
                        </div>
                    </form>
                </div>

                <div class="auth-visual-section">
                    <div class="visual-content">
                        <div class="visual-header">
                            <h2>Connect with your community</h2>
                            <p>Join conversations, share moments, and discover new friends on SocialConnect.</p>
                        </div>
                        
                        <div class="features-preview">
                            <div class="feature-item">
                                <div class="feature-icon">💬</div>
                                <div class="feature-text">
                                    <h4>Real-time Messaging</h4>
                                    <p>Instant conversations with friends and groups</p>
                                </div>
                            </div>
                            <div class="feature-item">
                                <div class="feature-icon">📸</div>
                                <div class="feature-text">
                                    <h4>Rich Media Sharing</h4>
                                    <p>Share photos, videos, and stories beautifully</p>
                                </div>
                            </div>
                            <div class="feature-item">
                                <div class="feature-icon">🔒</div>
                                <div class="feature-text">
                                    <h4>Privacy Controls</h4>
                                    <p>Complete control over your data and privacy</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Enhanced form validation and UX
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const passwordToggle = document.querySelector('.password-toggle');
            const submitButton = form.querySelector('button[type="submit"]');
            const spinner = submitButton.querySelector('.spinner');
            const buttonText = submitButton.querySelector('.btn-text');

            // Password visibility toggle
            passwordToggle.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.textContent = type === 'password' ? '👁️' : '🙈';
            });

            // Form submission handling
            form.addEventListener('submit', function(e) {
                // Clear previous errors
                clearErrors();
                
                let isValid = true;

                // Email validation
                if (!emailInput.value.trim()) {
                    showError('email', 'Email is required');
                    isValid = false;
                } else if (!isValidEmail(emailInput.value)) {
                    showError('email', 'Please enter a valid email address');
                    isValid = false;
                }

                // Password validation
                if (!passwordInput.value) {
                    showError('password', 'Password is required');
                    isValid = false;
                }

                if (!isValid) {
                    e.preventDefault();
                    return;
                }

                // Show loading state
                submitButton.disabled = true;
                spinner.classList.remove('hidden');
                buttonText.textContent = 'Signing in...';
            });

            // Real-time validation
            emailInput.addEventListener('blur', function() {
                if (this.value.trim() && !isValidEmail(this.value)) {
                    showError('email', 'Please enter a valid email address');
                } else {
                    clearError('email');
                }
            });

            passwordInput.addEventListener('input', function() {
                if (this.value) {
                    clearError('password');
                }
            });

            function isValidEmail(email) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(email);
            }

            function showError(fieldName, message) {
                const input = document.getElementById(fieldName);
                const errorDiv = document.getElementById(fieldName + '-error');
                input.classList.add('error');
                errorDiv.textContent = message;
            }

            function clearError(fieldName) {
                const input = document.getElementById(fieldName);
                const errorDiv = document.getElementById(fieldName + '-error');
                input.classList.remove('error');
                errorDiv.textContent = '';
            }

            function clearErrors() {
                clearError('email');
                clearError('password');
            }
        });

        // Mobile navigation toggle
        document.querySelector('.nav-toggle').addEventListener('click', function() {
            document.querySelector('.nav-menu').classList.toggle('show');
        });
    </script>
</body>
</html>