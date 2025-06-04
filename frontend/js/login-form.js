// Login Form Handler
// Handles form validation, submission, and user authentication

class LoginForm {
    constructor() {
        this.form = document.getElementById('loginForm');
        this.usernameEmailInput = document.getElementById('usernameEmail');
        this.passwordInput = document.getElementById('password');
        this.passwordToggle = document.getElementById('passwordToggle');
        this.rememberMeCheckbox = document.getElementById('rememberMe');
        this.submitButton = document.getElementById('loginSubmit');
        this.submitText = this.submitButton.querySelector('.submit-text');
        this.loadingSpinner = this.submitButton.querySelector('.loading-spinner');
        
        this.apiHandler = new APIHandler();
        this.validator = ValidationUtils;
        
        this.isSubmitting = false;
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.checkStoredCredentials();
    }
    
    bindEvents() {
        // Form submission
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
        
        // Password toggle
        this.passwordToggle.addEventListener('click', () => this.togglePassword());
        
        // Real-time validation
        this.usernameEmailInput.addEventListener('blur', () => this.validateUsernameEmail());
        this.usernameEmailInput.addEventListener('input', () => this.clearError('usernameEmail'));
        
        this.passwordInput.addEventListener('blur', () => this.validatePassword());
        this.passwordInput.addEventListener('input', () => this.clearError('password'));
        
        // Keyboard navigation
        this.form.addEventListener('keydown', (e) => this.handleKeyDown(e));
    }
    
    handleKeyDown(e) {
        if (e.key === 'Enter' && !this.isSubmitting) {
            e.preventDefault();
            this.handleSubmit(e);
        }
    }
    
    togglePassword() {
        const showIcon = this.passwordToggle.querySelector('.show-icon');
        const hideIcon = this.passwordToggle.querySelector('.hide-icon');
        
        if (this.passwordInput.type === 'password') {
            this.passwordInput.type = 'text';
            showIcon.style.display = 'none';
            hideIcon.style.display = 'block';
            this.passwordToggle.setAttribute('aria-label', 'Hide password');
        } else {
            this.passwordInput.type = 'password';
            showIcon.style.display = 'block';
            hideIcon.style.display = 'none';
            this.passwordToggle.setAttribute('aria-label', 'Show password');
        }
    }
    
    validateUsernameEmail() {
        const value = this.usernameEmailInput.value.trim();
        
        if (!value) {
            this.showError('usernameEmail', 'Username or email is required');
            return false;
        }
        
        // Check if it looks like an email
        if (value.includes('@')) {
            if (!this.validator.isValidEmail(value)) {
                this.showError('usernameEmail', 'Please enter a valid email address');
                return false;
            }
        } else {
            // Username validation
            if (value.length < 3) {
                this.showError('usernameEmail', 'Username must be at least 3 characters long');
                return false;
            }
            if (!/^[a-zA-Z0-9_.-]+$/.test(value)) {
                this.showError('usernameEmail', 'Username can only contain letters, numbers, dots, dashes, and underscores');
                return false;
            }
        }
        
        this.showSuccess('usernameEmail');
        return true;
    }
    
    validatePassword() {
        const value = this.passwordInput.value;
        
        if (!value) {
            this.showError('password', 'Password is required');
            return false;
        }
        
        if (value.length < 6) {
            this.showError('password', 'Password must be at least 6 characters long');
            return false;
        }
        
        this.showSuccess('password');
        return true;
    }
    
    showError(fieldName, message) {
        const input = document.getElementById(fieldName);
        const errorElement = document.getElementById(`${fieldName}-error`);
        
        input.classList.add('error');
        input.classList.remove('success');
        errorElement.textContent = message;
        errorElement.style.display = 'flex';
    }
    
    showSuccess(fieldName) {
        const input = document.getElementById(fieldName);
        const errorElement = document.getElementById(`${fieldName}-error`);
        
        input.classList.remove('error');
        input.classList.add('success');
        errorElement.textContent = '';
        errorElement.style.display = 'none';
    }
    
    clearError(fieldName) {
        const input = document.getElementById(fieldName);
        const errorElement = document.getElementById(`${fieldName}-error`);
        
        input.classList.remove('error');
        errorElement.textContent = '';
        errorElement.style.display = 'none';
    }
    
    async handleSubmit(e) {
        e.preventDefault();
        
        if (this.isSubmitting) return;
        
        // Validate all fields
        const isUsernameEmailValid = this.validateUsernameEmail();
        const isPasswordValid = this.validatePassword();
        
        if (!isUsernameEmailValid || !isPasswordValid) {
            return;
        }
        
        // Show loading state
        this.setSubmittingState(true);
        
        try {
            const formData = this.getFormData();
            const response = await this.apiHandler.post('/auth/login', formData);
            
            if (response.success) {
                this.handleLoginSuccess(response);
            } else {
                this.handleLoginError(response.message || 'Login failed. Please try again.');
            }        } catch (error) {
            console.error('Login error:', error);
            
            // Prioritize server error message, fallback to detailed client-side messages
            let errorMessage = error.message;
            
            // If no server message or it's a generic HTTP error, provide detailed client-side message
            if (!errorMessage || errorMessage.includes('HTTP error! status:')) {
                if (errorMessage && errorMessage.includes('404')) {
                    errorMessage = 'Login service not found. Please contact support.';
                } else if (errorMessage && errorMessage.includes('500')) {
                    errorMessage = 'Server error occurred during login. Please try again later.';
                } else if (errorMessage && errorMessage.includes('400')) {
                    errorMessage = 'Invalid login credentials. Please check your information and try again.';
                } else if (errorMessage && errorMessage.includes('403')) {
                    errorMessage = 'Login is currently not allowed. Please contact support.';
                } else if (!errorMessage || errorMessage.includes('Failed to fetch') || errorMessage.includes('fetch')) {
                    errorMessage = 'Network connection failed. Please check your internet connection and try again.';
                } else if (errorMessage && errorMessage.includes('timeout')) {
                    errorMessage = 'Request timed out. Please check your connection and try again.';
                } else {
                    errorMessage = 'An unexpected error occurred during login. Please try again.';
                }
            }
            
            this.handleLoginError(errorMessage);
        } finally {
            this.setSubmittingState(false);
        }
    }
    
    getFormData() {
        const usernameEmail = this.usernameEmailInput.value.trim();
        
        // Determine if input is email or username
        const isEmail = usernameEmail.includes('@');
        
        const data = {
            password: this.passwordInput.value,
            remember_me: this.rememberMeCheckbox.checked
        };
        
        if (isEmail) {
            data.email = usernameEmail;
        } else {
            data.username = usernameEmail;
        }
        
        return data;
    }
    
    handleLoginSuccess(response) {
        // Store JWT token
        if (response.token) {
            if (this.rememberMeCheckbox.checked) {
                localStorage.setItem('auth_token', response.token);
                localStorage.setItem('remember_login', 'true');
            } else {
                sessionStorage.setItem('auth_token', response.token);
            }
        }
        
        // Store user info if provided
        if (response.user) {
            const storage = this.rememberMeCheckbox.checked ? localStorage : sessionStorage;
            storage.setItem('user_info', JSON.stringify(response.user));
        }
        
        // Show success message briefly
        this.showSuccessMessage('Login successful! Redirecting...');
        setTimeout(() => {
            window.location.href = response.redirect_url || 'feed.php';
        }, 1000);
    }
    
    handleLoginError(message) {
        // Check for specific error types
        if (message.toLowerCase().includes('password')) {
            this.showError('password', message);
        } else if (message.toLowerCase().includes('email') || message.toLowerCase().includes('username')) {
            this.showError('usernameEmail', message);
        } else {
            // Generic error - show at top of form
            this.showFormError(message);
        }
    }
    
    showFormError(message) {
        // Create or update general error message
        let errorDiv = this.form.querySelector('.form-error');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'form-error error-message';
            errorDiv.style.marginBottom = 'var(--spacing-md)';
            errorDiv.style.padding = 'var(--spacing-sm)';
            errorDiv.style.backgroundColor = '#FEF2F2';
            errorDiv.style.border = '1px solid var(--color-error)';
            errorDiv.style.borderRadius = 'var(--border-radius-md)';
            this.form.insertBefore(errorDiv, this.form.firstChild);
        }
        
        errorDiv.textContent = message;
        errorDiv.style.display = 'flex';
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            if (errorDiv.parentNode) {
                errorDiv.style.display = 'none';
            }
        }, 5000);
    }
    
    showSuccessMessage(message) {
        // Create success message
        let successDiv = this.form.querySelector('.form-success');
        if (!successDiv) {
            successDiv = document.createElement('div');
            successDiv.className = 'form-success success-message';
            successDiv.style.marginBottom = 'var(--spacing-md)';
            successDiv.style.padding = 'var(--spacing-sm)';
            successDiv.style.backgroundColor = '#F0FDF4';
            successDiv.style.border = '1px solid var(--color-success)';
            successDiv.style.borderRadius = 'var(--border-radius-md)';
            successDiv.style.color = 'var(--color-success)';
            this.form.insertBefore(successDiv, this.form.firstChild);
        }
        
        successDiv.textContent = message;
        successDiv.style.display = 'flex';
    }
    
    setSubmittingState(isSubmitting) {
        this.isSubmitting = isSubmitting;
        this.submitButton.disabled = isSubmitting;
        
        if (isSubmitting) {
            this.submitText.style.opacity = '0';
            this.loadingSpinner.style.display = 'block';
        } else {
            this.submitText.style.opacity = '1';
            this.loadingSpinner.style.display = 'none';
        }
        
        // Update form elements
        this.usernameEmailInput.disabled = isSubmitting;
        this.passwordInput.disabled = isSubmitting;
        this.rememberMeCheckbox.disabled = isSubmitting;
    }
    
    checkStoredCredentials() {
        // Check if user has "remember me" enabled
        const rememberLogin = localStorage.getItem('remember_login');
        const token = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token');
        
        if (rememberLogin === 'true') {
            this.rememberMeCheckbox.checked = true;
        }
        
        // If user already has valid token, could redirect to dashboard
        // This would require token validation with the server
        if (token) {
            // Optional: Validate token and auto-redirect if valid
            // this.validateTokenAndRedirect(token);
        }
    }
    
    // Optional method for token validation
    async validateTokenAndRedirect(token) {
        try {
            const response = await this.apiHandler.get('/auth/validate', {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });
              if (response.success) {
                window.location.href = '/frontend/pages/welcome.php';
            }
        } catch (error) {
            // Token invalid, continue with login form
            console.log('Token validation failed:', error);
        }
    }
}

// Initialize login form when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new LoginForm();
});
