<?php

require_once 'api-handler.php';

/**
 * Privacy API Interface Class
 * 
 * Provides a clean interface for interacting with privacy settings API endpoints.
 * Inherits from the generic APIHandler class and implements privacy-specific methods.
 */
class PrivacyAPI extends APIHandler {

    /**
     * Constructor
     * 
     * @param string $baseURL The base URL for the API (defaults to privacy endpoints)
     */
    public function __construct($baseURL = '/webdev/backend/src/api') {
        parent::__construct($baseURL);
    }

    /**
     * Get Privacy Settings
     * 
     * Retrieves the current user's privacy settings. If no privacy settings exist,
     * default settings are automatically created by the API.
     * 
     * @return array Privacy settings data
     * @throws Exception If request fails or user is not authenticated
     */
    public function getPrivacySettings() {
        if (!$this->isAuthenticated()) {
            throw new Exception('Authentication required to access privacy settings');
        }

        try {
            $response = $this->authenticatedRequest('/privacy/get_privacy', [
                'method' => 'GET'
            ]);

            if (!isset($response['success']) || !$response['success']) {
                throw new Exception($response['message'] ?? 'Failed to retrieve privacy settings');
            }

            return $response['privacy_settings'] ?? [];

        } catch (Exception $e) {
            error_log('PrivacyAPI::getPrivacySettings() error: ' . $e->getMessage());
            throw new Exception('Failed to retrieve privacy settings: ' . $e->getMessage());
        }
    }

    /**
     * Update Privacy Settings
     * 
     * Updates the current user's privacy settings. Only provided fields will be updated.
     * All values are validated against allowed options by the API.
     * 
     * @param array $settings Array of privacy settings to update
     *                       Supported keys:
     *                       - post_default_privacy: 'public', 'friends', 'private'
     *                       - profile_visibility: 'public', 'friends', 'private'
     *                       - friend_list_visibility: 'public', 'friends', 'private'
     *                       - who_can_send_requests: 'everyone', 'friends_of_friends', 'nobody'
     * @return array Updated settings information
     * @throws Exception If request fails, user is not authenticated, or invalid values provided
     */
    public function updatePrivacySettings($settings) {
        if (!$this->isAuthenticated()) {
            throw new Exception('Authentication required to update privacy settings');
        }

        if (empty($settings) || !is_array($settings)) {
            throw new Exception('Settings data is required and must be an array');
        }

        // Validate settings before sending to API
        $this->validatePrivacySettings($settings);

        try {
            $response = $this->authenticatedRequest('/privacy/update_privacy', [
                'method' => 'PUT',
                'body' => json_encode($settings)
            ]);

            if (!isset($response['success']) || !$response['success']) {
                throw new Exception($response['message'] ?? 'Failed to update privacy settings');
            }

            return [
                'message' => $response['message'] ?? 'Privacy settings updated successfully',
                'updated_settings' => $response['updated_settings'] ?? []
            ];

        } catch (Exception $e) {
            error_log('PrivacyAPI::updatePrivacySettings() error: ' . $e->getMessage());
            throw new Exception('Failed to update privacy settings: ' . $e->getMessage());
        }
    }

    /**
     * Update Post Default Privacy
     * 
     * Convenience method to update only the default privacy setting for posts.
     * 
     * @param string $privacy Privacy level: 'public', 'friends', or 'private'
     * @return array Updated settings information
     * @throws Exception If invalid value or update fails
     */
    public function updatePostDefaultPrivacy($privacy) {
        return $this->updatePrivacySettings(['post_default_privacy' => $privacy]);
    }

    /**
     * Update Profile Visibility
     * 
     * Convenience method to update only the profile visibility setting.
     * 
     * @param string $visibility Visibility level: 'public', 'friends', or 'private'
     * @return array Updated settings information
     * @throws Exception If invalid value or update fails
     */
    public function updateProfileVisibility($visibility) {
        return $this->updatePrivacySettings(['profile_visibility' => $visibility]);
    }

    /**
     * Update Friend List Visibility
     * 
     * Convenience method to update only the friend list visibility setting.
     * 
     * @param string $visibility Visibility level: 'public', 'friends', or 'private'
     * @return array Updated settings information
     * @throws Exception If invalid value or update fails
     */
    public function updateFriendListVisibility($visibility) {
        return $this->updatePrivacySettings(['friend_list_visibility' => $visibility]);
    }

    /**
     * Update Who Can Send Requests
     * 
     * Convenience method to update only the friend request permissions setting.
     * 
     * @param string $permission Permission level: 'everyone', 'friends_of_friends', or 'nobody'
     * @return array Updated settings information
     * @throws Exception If invalid value or update fails
     */
    public function updateWhoCanSendRequests($permission) {
        return $this->updatePrivacySettings(['who_can_send_requests' => $permission]);
    }

    /**
     * Get Privacy Setting Value
     * 
     * Retrieves a specific privacy setting value.
     * 
     * @param string $setting The setting name to retrieve
     * @return string|null The setting value or null if not found
     * @throws Exception If request fails
     */
    public function getPrivacySetting($setting) {
        $settings = $this->getPrivacySettings();
        return $settings[$setting] ?? null;
    }

    /**
     * Reset Privacy Settings to Defaults
     * 
     * Resets all privacy settings to their default values.
     * 
     * @return array Updated settings information
     * @throws Exception If update fails
     */
    public function resetToDefaults() {
        $defaultSettings = [
            'post_default_privacy' => 'public',
            'profile_visibility' => 'public',
            'friend_list_visibility' => 'friends',
            'who_can_send_requests' => 'everyone'
        ];

        return $this->updatePrivacySettings($defaultSettings);
    }

    /**
     * Validate Privacy Settings
     * 
     * Validates privacy settings data before sending to API.
     * 
     * @param array $settings Settings to validate
     * @throws Exception If any setting has invalid value
     */
    private function validatePrivacySettings($settings) {
        $validValues = [
            'post_default_privacy' => ['public', 'friends', 'private'],
            'profile_visibility' => ['public', 'friends', 'private'],
            'friend_list_visibility' => ['public', 'friends', 'private'],
            'who_can_send_requests' => ['everyone', 'friends_of_friends', 'nobody']
        ];

        foreach ($settings as $key => $value) {
            if (!array_key_exists($key, $validValues)) {
                throw new Exception("Invalid privacy setting: $key");
            }

            if (!in_array($value, $validValues[$key])) {
                $validList = implode(', ', $validValues[$key]);
                throw new Exception("Invalid value '$value' for $key. Valid values are: $validList");
            }
        }
    }

    /**
     * Get Valid Privacy Values
     * 
     * Returns an array of valid values for each privacy setting.
     * Useful for building form dropdowns or validation.
     * 
     * @return array Array of valid values for each setting
     */
    public function getValidPrivacyValues() {
        return [
            'post_default_privacy' => ['public', 'friends', 'private'],
            'profile_visibility' => ['public', 'friends', 'private'],
            'friend_list_visibility' => ['public', 'friends', 'private'],
            'who_can_send_requests' => ['everyone', 'friends_of_friends', 'nobody']
        ];
    }

    /**
     * Get Privacy Settings with Labels
     * 
     * Returns privacy settings with human-readable labels for display purposes.
     * 
     * @return array Privacy settings with labels
     * @throws Exception If request fails
     */
    public function getPrivacySettingsWithLabels() {
        $settings = $this->getPrivacySettings();
        
        $labels = [
            'post_default_privacy' => 'Default Post Privacy',
            'profile_visibility' => 'Profile Visibility',
            'friend_list_visibility' => 'Friend List Visibility',
            'who_can_send_requests' => 'Who Can Send Friend Requests'
        ];

        $valueLabels = [
            'public' => 'Public',
            'friends' => 'Friends Only',
            'private' => 'Private',
            'everyone' => 'Everyone',
            'friends_of_friends' => 'Friends of Friends',
            'nobody' => 'Nobody'
        ];

        $labeledSettings = [];
        foreach ($settings as $key => $value) {
            if (isset($labels[$key])) {
                $labeledSettings[] = [
                    'key' => $key,
                    'label' => $labels[$key],
                    'value' => $value,
                    'value_label' => $valueLabels[$value] ?? $value,
                    'created_at' => $settings['created_at'] ?? null,
                    'updated_at' => $settings['updated_at'] ?? null
                ];
            }
        }

        return $labeledSettings;
    }
}
