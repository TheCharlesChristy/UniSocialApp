<?php
/**
 * Database Configuration Manager
 * 
 * Reads and parses database configuration from file
 * Validates required configuration parameters
 * Provides standardized access to configuration values
 */

class DatabaseConfig {
    private $configPath;
    private $configData = [];
    private $required = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS'];
    private $errorMessages = [];

    /**
     * Constructor for DatabaseConfig
     * 
     * @param string $configPath Path to configuration file
     */
    public function __construct($configPath) {
        $this->configPath = $configPath;
        $this->loadConfig();
    }
    
    /**
     * Load configuration from file
     * 
     * @return bool True if configuration loaded successfully, false otherwise
     */
    public function loadConfig() {
        // Check if config file exists and is readable
        if (!file_exists($this->configPath) || !is_readable($this->configPath)) {
            $this->errorMessages[] = "Configuration file not found or not readable: {$this->configPath}";
            return false;
        }
        
        // Read configuration file line by line
        $lines = file($this->configPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Skip comments (lines starting with #)
            if (empty($line) || strpos($line, '#') === 0) {
                continue;
            }
            
            // Parse KEY=VALUE format
            $parts = explode('=', $line, 2);
            
            if (count($parts) === 2) {
                $key = trim($parts[0]);
                $value = trim($parts[1]);
                
                // Convert "TRUE" and "FALSE" strings to boolean values
                if (strtoupper($value) === 'TRUE') {
                    $value = true;
                } elseif (strtoupper($value) === 'FALSE') {
                    $value = false;
                }
                
                $this->configData[$key] = $value;
            }
        }
        
        // Validate that all required parameters are present
        $this->validateConfig();
        
        return empty($this->errorMessages);
    }
    
    /**
     * Validate required configuration parameters
     * 
     * @return bool True if all required parameters present, false otherwise
     */
    private function validateConfig() {
        foreach ($this->required as $param) {
            if (!isset($this->configData[$param])) {
                $this->errorMessages[] = "Required configuration parameter missing: {$param}";
            }
        }
        
        return empty($this->errorMessages);
    }
    
    /**
     * Get configuration value by key
     * 
     * @param string $key Configuration key
     * @param mixed $default Default value if key not found
     * @return mixed Configuration value or default
     */
    public function get($key, $default = null) {
        return isset($this->configData[$key]) ? $this->configData[$key] : $default;
    }
    
    /**
     * Parse database host and port
     * 
     * @return array Array with 'host' and 'port' keys
     */
    public function getHostData() {
        $hostData = ['host' => 'localhost', 'port' => 3306];
        
        if (isset($this->configData['DB_HOST'])) {
            $parts = explode(':', $this->configData['DB_HOST']);
            $hostData['host'] = $parts[0];
            if (isset($parts[1]) && is_numeric($parts[1])) {
                $hostData['port'] = (int)$parts[1];
            }
        }
        
        return $hostData;
    }
    
    /**
     * Check if configuration has errors
     * 
     * @return bool True if errors exist, false otherwise
     */
    public function hasErrors() {
        return !empty($this->errorMessages);
    }
    
    /**
     * Get all error messages
     * 
     * @return array Array of error messages
     */
    public function getErrors() {
        return $this->errorMessages;
    }
}
