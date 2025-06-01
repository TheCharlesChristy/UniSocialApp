<?php

class APIHandler {
    protected $baseURL;
    protected $defaultHeaders;
    protected $debugMode = false; // Set to true only for debugging

    public function __construct($baseURL = null) {
        // Auto-detect the base URL if not provided
        if ($baseURL === null) {
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $baseURL = $protocol . '://' . $host . '/webdev/backend/src/api';
        }        $this->baseURL = $baseURL;
        $this->defaultHeaders = [
            'Content-Type' => 'application/json',
        ];
        
        // Only log initialization errors, not normal operation
        if (empty($this->baseURL)) {
            $this->debugLog("Failed to initialize base URL", [], 'error');
        }
    }/**
     * Debug logging method - only logs errors and critical info
     * 
     * @param string $message Debug message to log
     * @param array $context Additional context data
     * @param string $level Log level: 'error', 'warning', 'info'
     */
    private function debugLog($message, $context = [], $level = 'info') {
        if (!$this->debugMode && $level !== 'error') {
            return;
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] API-HANDLER [$level]: $message";
        
        if (!empty($context)) {
            $logMessage .= " | Context: " . json_encode($context);
        }
        
        // Always log to error log
        error_log($logMessage);
        
        // Only show errors and warnings on webpage
        if ($level === 'error' || $level === 'warning') {
            $color = $level === 'error' ? '#f44336' : '#ff9800';
            echo "<div style='background: #ffebee; padding: 5px; margin: 2px; font-family: monospace; font-size: 12px; border-left: 3px solid $color;'>";
            echo htmlspecialchars("[$level] $message");
            echo "</div>\n";
        }
    }    // Generic cURL wrapper with error handling
    public function request($endpoint, $options = []) {
        $url = $this->baseURL . $endpoint . '.php';
        
        // Handle query parameters for GET requests
        if (isset($options['query_params']) && !empty($options['query_params'])) {
            $queryString = http_build_query($options['query_params']);
            $url .= '?' . $queryString;
        }
        
        $ch = curl_init();
        
        // Default cURL options
        $curlOptions = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HEADER => true,
            CURLOPT_NOBODY => false
        ];

        // Set headers
        $headers = array_merge($this->defaultHeaders, $options['headers'] ?? []);
        $headerArray = [];
        foreach ($headers as $key => $value) {
            $headerArray[] = "$key: $value";
        }
        $curlOptions[CURLOPT_HTTPHEADER] = $headerArray;

        // Set HTTP method and body
        $method = $options['method'] ?? 'GET';
        switch (strtoupper($method)) {
            case 'POST':
                $curlOptions[CURLOPT_POST] = true;
                if (isset($options['body'])) {
                    $curlOptions[CURLOPT_POSTFIELDS] = $options['body'];
                }
                break;
            case 'PUT':
                $curlOptions[CURLOPT_CUSTOMREQUEST] = 'PUT';
                if (isset($options['body'])) {
                    $curlOptions[CURLOPT_POSTFIELDS] = $options['body'];
                }
                break;
            case 'DELETE':
                $curlOptions[CURLOPT_CUSTOMREQUEST] = 'DELETE';
                break;
            case 'GET':
            default:
                // GET is default
                break;
        }

        curl_setopt_array($ch, $curlOptions);        try {
            $response = curl_exec($ch);
            
            if ($response === false) {
                $curlError = curl_error($ch);
                $curlErrno = curl_errno($ch);
                
                // Additional URL validation
                if (filter_var($url, FILTER_VALIDATE_URL) === false) {
                    $this->debugLog("INVALID URL FORMAT: " . $url, [], 'error');
                }
                
                $this->debugLog("cURL failed: " . $curlError . " (Code: " . $curlErrno . ")", [
                    'url' => $url,
                    'errno' => $curlErrno
                ], 'error');
                
                throw new Exception('cURL error: ' . $curlError . ' (Code: ' . $curlErrno . ')');
            }            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $responseHeaders = substr($response, 0, $headerSize);
            $responseBody = substr($response, $headerSize);

            curl_close($ch);

            // Parse content type
            $contentType = '';
            if (preg_match('/Content-Type:\s*([^\r\n]+)/i', $responseHeaders, $matches)) {
                $contentType = trim($matches[1]);
            }

            // Parse response data
            $responseData = $responseBody;
            if (strpos($contentType, 'application/json') !== false) {
                $decodedData = json_decode($responseBody, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $responseData = $decodedData;
                }
            }

            // Debug logging (you might want to use error_log instead)
            error_log('API Response: ' . json_encode([
                'url' => $url,
                'status' => $httpCode,
                'contentType' => $contentType,
                'responseData' => $responseData
            ]));            // Check for HTTP errors
            if ($httpCode >= 400) {
                $this->debugLog("HTTP Error $httpCode for URL: $url", [
                    'response_body' => substr($responseBody, 0, 200)
                ], 'error');
                
                if (is_array($responseData) && isset($responseData['message'])) {
                    throw new Exception($responseData['message']);
                } elseif (is_string($responseData) && trim($responseData)) {
                    throw new Exception($responseData);
                } else {
                    throw new Exception("HTTP error! status: $httpCode");
                }
            }

            return $responseData;        } catch (Exception $e) {
            if (isset($ch)) {
                curl_close($ch);
            }
            
            // Enhanced error logging with more context
            $errorDetails = [
                'url' => $url,
                'error' => $e->getMessage(),
                'base_url' => $this->baseURL,
                'endpoint' => $endpoint,
                'method' => $options['method'] ?? 'GET'
            ];
            
            $this->debugLog('API REQUEST FAILED', $errorDetails, 'error');
            error_log('API request failed: ' . json_encode($errorDetails));
            
            throw $e;
        }
    }

    // GET request
    public function get($endpoint, $params = []) {
        $queryString = http_build_query($params);
        $fullEndpoint = $queryString ? "$endpoint?$queryString" : $endpoint;
        
        return $this->request($fullEndpoint, [
            'method' => 'GET'
        ]);
    }

    // POST request
    public function post($endpoint, $data = []) {
        return $this->request($endpoint, [
            'method' => 'POST',
            'body' => json_encode($data)
        ]);
    }

    // PUT request
    public function put($endpoint, $data = []) {
        return $this->request($endpoint, [
            'method' => 'PUT',
            'body' => json_encode($data)
        ]);
    }

    // DELETE request
    public function delete($endpoint) {
        return $this->request($endpoint, [
            'method' => 'DELETE'
        ]);
    }

    // Upload file
    public function upload($endpoint, $formData) {
        $headers = $this->defaultHeaders;
        unset($headers['Content-Type']); // Let cURL set the boundary for multipart data

        return $this->request($endpoint, [
            'method' => 'POST',
            'headers' => $headers,
            'body' => $formData
        ]);
    }

    // Get with authentication token
    public function authenticatedRequest($endpoint, $options = []) {
        $token = $this->getAuthToken();
        if (!$token) {
            throw new Exception('No authentication token found');
        }

        $authHeaders = array_merge([
            'Authorization' => "Bearer $token"
        ], $options['headers'] ?? []);

        $options['headers'] = $authHeaders;
        return $this->request($endpoint, $options);
    }    public function getAuthToken() {
        // First, check for Authorization header (for AJAX requests)
        $headers = getallheaders();
        if ($headers && isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
            if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                return $matches[1];
            }
        }
        
        // Second, check for token in cookies (this is where JS localStorage tokens should be synced)
        if (isset($_COOKIE['auth_token'])) {
            return $_COOKIE['auth_token'];
        }
        
        // Third, check for token in GET parameters (for URL-based auth)
        if (isset($_GET['auth_token'])) {
            $token = $_GET['auth_token'];
            // Automatically sync to cookie for future requests
            $this->setAuthToken($token, true);
            return $token;
        }
        
        // Fourth, check for token in POST data (for form submissions)
        if (isset($_POST['auth_token'])) {
            $token = $_POST['auth_token'];
            // Automatically sync to cookie for future requests
            $this->setAuthToken($token, true);
            return $token;
        }
        
        // Fifth, handle token synchronization from JavaScript localStorage
        if (isset($_POST['sync_auth_token'])) {
            $token = $_POST['sync_auth_token'];
            $this->setAuthToken($token, true);
            return $token;
        }
        
        // Finally, fallback to session storage
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['auth_token'] ?? null;
    }public function setAuthToken($token, $persistent = true) {
        // Always set in session as fallback
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['auth_token'] = $token;
        
        // Set as HTTP-only cookie for security (prevents XSS but allows server access)
        $expiry = $persistent ? time() + (86400 * 30) : 0; // 30 days or session
        $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'; // Use secure flag if HTTPS
        
        setcookie('auth_token', $token, [
            'expires' => $expiry,
            'path' => '/',
            'domain' => '',
            'secure' => $secure,
            'httponly' => false, // Allow JavaScript access for frontend
            'samesite' => 'Lax'
        ]);
    }

    public function removeAuthToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        unset($_SESSION['auth_token']);
        
        // Also remove cookie if it exists
        if (isset($_COOKIE['auth_token'])) {
            setcookie('auth_token', '', time() - 3600, '/');
        }
    }

    // Helper method to render a script that syncs localStorage token to cookies
    public function renderTokenSyncScript() {
        return '
        <script>
        // Sync auth token from localStorage to cookie for PHP access
        (function() {
            const token = localStorage.getItem("auth_token") || sessionStorage.getItem("auth_token");
            if (token) {
                // Set the token as a cookie that PHP can read
                const secure = location.protocol === "https:";
                document.cookie = `auth_token=${token}; path=/; max-age=${30 * 24 * 60 * 60}; ${secure ? "secure;" : ""} samesite=lax`;
            }
        })();
        </script>';
    }    // Check if user is authenticated
    public function isAuthenticated() {
        $token = $this->getAuthToken();
        if (empty($token)) {
            return false;
        }
        
        // For now, just check if token exists (uncomment below for API validation)
        return true;
        
        /*
        // Validate token by making a test API call
        try {
            // Try to make an authenticated request to verify token validity
            $this->authenticatedRequest('/auth/validate', ['method' => 'GET']);
            return true;
        } catch (Exception $e) {
            // Token is invalid or expired
            error_log('Token validation failed: ' . $e->getMessage());
            return false;
        }
        */
    }
    
    /**
     * Enable or disable debug mode
     * 
     * @param bool $enabled Whether to enable debug mode
     */
    public function setDebugMode($enabled = true) {
        $this->debugMode = $enabled;
        $this->debugLog("Debug mode " . ($enabled ? "enabled" : "disabled"));
    }
    
    /**
     * Get current debug mode status
     * 
     * @return bool True if debug mode is enabled
     */
    public function isDebugMode() {
        return $this->debugMode;
    }
}