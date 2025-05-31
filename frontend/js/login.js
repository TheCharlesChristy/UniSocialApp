/* Login Page JavaScript */
/* SocialConnect Authentication Handler */

/**
 * Login Page Controller
 * Handles all login form interactions, validation, and API communication
 * Uses generic-api.js for all API calls following documented specifications
 */

class LoginController {
  constructor() {
    this.form = null;
    this.submitButton = null;
    this.errorContainer = null;
    this.passwordToggle = null;
    this.isLoading = false;
    
    // API endpoint from documentation analysis
    this.loginEndpoint = '/auth/login';
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () => this.init());
    } else {
      this.init();
    }
  }

  /**
   * Initialize login page functionality
   */
  init() {
    this.setupElements();
    this.setupEventListeners();
    this.setupValidation();
    this.checkExistingAuth();
  }

  /**
   * Setup DOM element references
   */
  setupElements() {
    this.form = document.getElementById('login-form');
    this.submitButton = document.getElementById('login-button');
    this.errorContainer = document.getElementById('error-container');
    this.passwordToggle = document.querySelector('.password-toggle');
    this.credentialsInput = document.getElementById('credentials');
    this.passwordInput = document.getElementById('password');
    this.rememberMeCheckbox = document.getElementById('remember-me');

    if (!this.form || !this.submitButton) {
      console.error('Required login form elements not found');
      return;
    }
  }

  /**
   * Setup event listeners for form interactions
   */
  setupEventListeners() {
    // Form submission
    this.form.addEventListener('submit', (e) => this.handleSubmit(e));

    // Password visibility toggle
    if (this.passwordToggle) {
      this.passwordToggle.addEventListener('click', () => this.togglePasswordVisibility());
    }

    // Real-time validation
    this.credentialsInput?.addEventListener('blur', () => this.validateCredentials());
    this.passwordInput?.addEventListener('blur', () => this.validatePassword());

    // Clear errors on input
    this.credentialsInput?.addEventListener('input', () => this.clearFieldError('credentials'));
    this.passwordInput?.addEventListener('input', () => this.clearFieldError('password'));

    // Handle Enter key in form fields
    [this.credentialsInput, this.passwordInput].forEach(input => {
      input?.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
          e.preventDefault();
          this.handleSubmit(e);
        }
      });
    });
  }

  /**
   * Setup form validation rules
   */
  setupValidation() {
    // Set up HTML5 validation attributes
    if (this.credentialsInput) {
      this.credentialsInput.setAttribute('required', 'true');
    }
    if (this.passwordInput) {
      this.passwordInput.setAttribute('required', 'true');
      this.passwordInput.setAttribute('minlength', '8');
    }
  }

  /**
   * Check if user is already authenticated and redirect if needed
   */
  checkExistingAuth() {
    if (userAPI.isAuthenticated()) {
      // Check if token is still valid
      userAPI.getCurrentUser()
        .then(() => {
          // Token is valid, redirect to dashboard
          this.redirectToDashboard();
        })
        .catch(() => {
          // Token is invalid, clear it
          userAPI.removeAuthToken();
        });
    }
  }

  /**
   * Handle form submission
   * @param {Event} e - Submit event
   */
  async handleSubmit(e) {
    e.preventDefault();

    if (this.isLoading) {
      return;
    }

    // Clear previous errors
    this.clearAllErrors();

    // Validate form
    if (!this.validateForm()) {
      return;
    }

    // Set loading state
    this.setLoadingState(true);

    try {
      // Collect form data
      const formData = this.collectFormData();

      // Call login API using generic-api.js
      const response = await userAPI.login(formData.credentials, formData.password);

      // Handle successful login
      await this.handleLoginSuccess(response, formData.rememberMe);

    } catch (error) {
      // Handle login error
      this.handleLoginError(error);
    } finally {
      // Clear loading state
      this.setLoadingState(false);
    }
  }

  /**
   * Collect and format form data
   * @returns {Object} Form data object
   */
  collectFormData() {
    return {
      credentials: this.credentialsInput.value.trim(),
      password: this.passwordInput.value,
      rememberMe: this.rememberMeCheckbox.checked
    };
  }

  /**
   * Validate entire form
   * @returns {boolean} True if form is valid
   */
  validateForm() {
    let isValid = true;

    // Validate credentials
    if (!this.validateCredentials()) {
      isValid = false;
    }

    // Validate password
    if (!this.validatePassword()) {
      isValid = false;
    }

    return isValid;
  }

  /**
   * Validate credentials field (email or username)
   * @returns {boolean} True if valid
   */
  validateCredentials() {
    const credentials = this.credentialsInput.value.trim();

    if (!credentials) {
      this.showFieldError('credentials', 'Email or username is required');
      return false;
    }

    if (credentials.length < 3) {
      this.showFieldError('credentials', 'Please enter a valid email or username');
      return false;
    }

    // If it contains @, validate as email
    if (credentials.includes('@')) {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(credentials)) {
        this.showFieldError('credentials', 'Please enter a valid email address');
        return false;
      }
    }

    this.clearFieldError('credentials');
    return true;
  }

  /**
   * Validate password field
   * @returns {boolean} True if valid
   */
  validatePassword() {
    const password = this.passwordInput.value;

    if (!password) {
      this.showFieldError('password', 'Password is required');
      return false;
    }

    if (password.length < 8) {
      this.showFieldError('password', 'Password must be at least 8 characters long');
      return false;
    }

    this.clearFieldError('password');
    return true;
  }

  /**
   * Handle successful login response
   * @param {Object} response - API response
   * @param {boolean} rememberMe - Remember me checkbox state
   */
  async handleLoginSuccess(response, rememberMe) {
    try {
      // Token should already be stored by userAPI.login()
      // Show success message
      this.showSuccessMessage('Login successful! Redirecting...');

      // Small delay for UX
      await this.delay(1000);

      // Redirect to dashboard
      this.redirectToDashboard();

    } catch (error) {
      console.error('Error handling login success:', error);
      this.showError('Login successful, but there was an issue redirecting. Please refresh the page.');
    }
  }

  /**
   * Handle login error
   * @param {Error} error - Error object
   */
  handleLoginError(error) {
    console.error('Login error:', error);

    // Parse error message based on API documentation
    let errorMessage = 'An error occurred during login. Please try again.';

    if (error.message) {
      // Handle specific API error responses
      if (error.message.includes('401') || error.message.includes('Unauthorized')) {
        errorMessage = 'Invalid email/username or password. Please check your credentials and try again.';
      } else if (error.message.includes('400') || error.message.includes('Bad Request')) {
        errorMessage = 'Please check that all fields are filled out correctly.';
      } else if (error.message.includes('500') || error.message.includes('Server Error')) {
        errorMessage = 'Server error. Please try again in a few moments.';
      } else if (error.message.includes('Network')) {
        errorMessage = 'Network error. Please check your connection and try again.';
      } else if (error.message.toLowerCase().includes('inactive') || error.message.toLowerCase().includes('verify')) {
        errorMessage = 'Your account needs to be verified. Please check your email for a verification link.';
      }
    }

    this.showError(errorMessage);
  }

  /**
   * Toggle password visibility
   */
  togglePasswordVisibility() {
    const hideIcon = this.passwordToggle.querySelector('.password-hide-icon');
    const showIcon = this.passwordToggle.querySelector('.password-show-icon');

    if (this.passwordInput.type === 'password') {
      this.passwordInput.type = 'text';
      hideIcon.style.display = 'none';
      showIcon.style.display = 'block';
      this.passwordToggle.setAttribute('aria-label', 'Hide password');
    } else {
      this.passwordInput.type = 'password';
      hideIcon.style.display = 'block';
      showIcon.style.display = 'none';
      this.passwordToggle.setAttribute('aria-label', 'Show password');
    }
  }

  /**
   * Set loading state for form
   * @param {boolean} loading - Loading state
   */
  setLoadingState(loading) {
    this.isLoading = loading;
    this.submitButton.disabled = loading;

    if (loading) {
      this.submitButton.classList.add('loading');
      this.submitButton.querySelector('.loading-spinner').style.display = 'flex';
      this.submitButton.querySelector('.button-text').style.opacity = '0';
    } else {
      this.submitButton.classList.remove('loading');
      this.submitButton.querySelector('.loading-spinner').style.display = 'none';
      this.submitButton.querySelector('.button-text').style.opacity = '1';
    }

    // Disable form inputs during loading
    const inputs = this.form.querySelectorAll('input, button');
    inputs.forEach(input => {
      if (input !== this.submitButton) {
        input.disabled = loading;
      }
    });
  }

  /**
   * Show field-specific error
   * @param {string} fieldName - Field name
   * @param {string} message - Error message
   */
  showFieldError(fieldName, message) {
    const field = document.getElementById(fieldName);
    const errorElement = document.getElementById(`${fieldName}-error`);

    if (field) {
      field.classList.add('error');
    }

    if (errorElement) {
      errorElement.textContent = message;
      errorElement.classList.add('show');
    }
  }

  /**
   * Clear field-specific error
   * @param {string} fieldName - Field name
   */
  clearFieldError(fieldName) {
    const field = document.getElementById(fieldName);
    const errorElement = document.getElementById(`${fieldName}-error`);

    if (field) {
      field.classList.remove('error');
    }

    if (errorElement) {
      errorElement.classList.remove('show');
      errorElement.textContent = '';
    }
  }

  /**
   * Clear all form errors
   */
  clearAllErrors() {
    this.clearError();
    this.clearFieldError('credentials');
    this.clearFieldError('password');
  }

  /**
   * Show general error message
   * @param {string} message - Error message
   */
  showError(message) {
    if (!this.errorContainer) return;

    this.errorContainer.innerHTML = `
      <div class="error-message">
        ${message}
      </div>
    `;
    this.errorContainer.classList.add('show');
    this.errorContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }

  /**
   * Clear general error message
   */
  clearError() {
    if (this.errorContainer) {
      this.errorContainer.innerHTML = '';
      this.errorContainer.classList.remove('show');
    }
  }

  /**
   * Show success message using APIUtils
   * @param {string} message - Success message
   */
  showSuccessMessage(message) {
    if (typeof APIUtils !== 'undefined' && APIUtils.showSuccess) {
      APIUtils.showSuccess(message);
    } else {
      // Fallback success display
      this.showError(message); // Reuse error container with success styling
      if (this.errorContainer) {
        const errorMsg = this.errorContainer.querySelector('.error-message');
        if (errorMsg) {
          errorMsg.style.backgroundColor = '#F0FDF4';
          errorMsg.style.borderColor = '#BBF7D0';
          errorMsg.style.color = '#15803D';
        }
      }
    }
  }

  /**
   * Redirect to dashboard after successful login
   */
  redirectToDashboard() {
    // Check for return URL parameter
    const urlParams = new URLSearchParams(window.location.search);
    const returnUrl = urlParams.get('return');

    if (returnUrl) {
      // Validate return URL is on same domain for security
      try {
        const url = new URL(returnUrl, window.location.origin);
        if (url.origin === window.location.origin) {
          window.location.href = returnUrl;
          return;
        }
      } catch (e) {
        console.warn('Invalid return URL:', returnUrl);
      }
    }

    // Default redirect to dashboard
    window.location.href = 'dashboard.html';
  }

  /**
   * Utility: Create delay promise
   * @param {number} ms - Milliseconds to delay
   * @returns {Promise} Delay promise
   */
  delay(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
  }
}

/**
 * Form Auto-fill and Enhancement Features
 */
class LoginEnhancements {
  constructor() {
    this.init();
  }

  init() {
    this.setupAutofill();
    this.setupKeyboardShortcuts();
    this.setupAccessibility();
  }

  /**
   * Setup browser autofill enhancement
   */
  setupAutofill() {
    // Handle browser autofill for better UX
    const inputs = document.querySelectorAll('#credentials, #password');
    
    inputs.forEach(input => {
      // Check for autofilled values periodically
      const checkAutofill = () => {
        if (input.value && !input.classList.contains('has-value')) {
          input.classList.add('has-value');
          // Clear any existing validation errors
          const fieldName = input.getAttribute('name') || input.id;
          if (fieldName && window.loginController) {
            window.loginController.clearFieldError(fieldName.replace('_', '-'));
          }
        }
      };

      // Check immediately and after short delays
      setTimeout(checkAutofill, 100);
      setTimeout(checkAutofill, 500);
      setTimeout(checkAutofill, 1000);

      // Also check on input events
      input.addEventListener('input', checkAutofill);
    });
  }

  /**
   * Setup keyboard shortcuts
   */
  setupKeyboardShortcuts() {
    document.addEventListener('keydown', (e) => {
      // Ctrl/Cmd + Enter to submit form
      if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
        e.preventDefault();
        const form = document.getElementById('login-form');
        if (form && window.loginController) {
          window.loginController.handleSubmit(e);
        }
      }

      // Escape to clear errors
      if (e.key === 'Escape') {
        if (window.loginController) {
          window.loginController.clearAllErrors();
        }
      }
    });
  }

  /**
   * Setup accessibility enhancements
   */
  setupAccessibility() {
    // Announce form submission to screen readers
    const form = document.getElementById('login-form');
    if (form) {
      form.addEventListener('submit', () => {
        const announcement = document.createElement('div');
        announcement.setAttribute('aria-live', 'polite');
        announcement.setAttribute('aria-atomic', 'true');
        announcement.className = 'sr-only';
        announcement.textContent = 'Signing in, please wait...';
        document.body.appendChild(announcement);

        // Remove after announcement
        setTimeout(() => {
          if (announcement.parentNode) {
            announcement.parentNode.removeChild(announcement);
          }
        }, 3000);
      });
    }

    // Improve focus management
    const inputs = document.querySelectorAll('input, button, a');
    inputs.forEach(input => {
      input.addEventListener('focus', () => {
        input.classList.add('focused');
      });
      input.addEventListener('blur', () => {
        input.classList.remove('focused');
      });
    });
  }
}

/**
 * Security and Analytics Features
 */
class LoginSecurity {
  constructor() {
    this.init();
  }

  init() {
    this.setupCSRFProtection();
    this.setupSecurityHeaders();
    this.logSecurityEvent('login_page_loaded');
  }

  /**
   * Setup CSRF protection
   */
  setupCSRFProtection() {
    // This would typically involve getting a CSRF token from the server
    // For now, we'll prepare the structure
    const form = document.getElementById('login-form');
    if (form && !form.querySelector('input[name="csrf_token"]')) {
      // CSRF token would be injected by server-side template
      // or fetched via separate API call
      console.log('CSRF protection setup ready');
    }
  }

  /**
   * Setup security headers validation
   */
  setupSecurityHeaders() {
    // Verify HTTPS connection
    if (location.protocol !== 'https:' && location.hostname !== 'localhost') {
      console.warn('Insecure connection detected');
      // In production, this might redirect to HTTPS
    }

    // Update security indicator
    const securityIndicator = document.querySelector('.security-indicator');
    if (securityIndicator) {
      const isSecure = location.protocol === 'https:' || location.hostname === 'localhost';
      securityIndicator.style.color = isSecure ? '#10B981' : '#F59E0B';
    }
  }

  /**
   * Log security events (would integrate with analytics/monitoring)
   * @param {string} event - Event name
   * @param {Object} data - Event data
   */
  logSecurityEvent(event, data = {}) {
    const eventData = {
      event,
      timestamp: new Date().toISOString(),
      url: window.location.href,
      userAgent: navigator.userAgent,
      ...data
    };

    // In production, this would send to analytics/monitoring service
    console.log('Security Event:', eventData);
  }
}

// Initialize login page when script loads
document.addEventListener('DOMContentLoaded', () => {
  // Initialize main login controller
  window.loginController = new LoginController();
  
  // Initialize enhancements
  new LoginEnhancements();
  
  // Initialize security features
  new LoginSecurity();
});

// Export for potential use in other scripts
if (typeof module !== 'undefined' && module.exports) {
  module.exports = { LoginController, LoginEnhancements, LoginSecurity };
} else if (typeof window !== 'undefined') {
  window.LoginPageModules = { LoginController, LoginEnhancements, LoginSecurity };
}
