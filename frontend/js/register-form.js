// Register Form Handler
// Handles form validation, submission, and user registration

class RegisterForm {
    constructor() {
        this.form = document.getElementById('registerForm');
        this.firstNameInput = document.getElementById('firstName');
        this.lastNameInput = document.getElementById('lastName');
        this.dateOfBirthInput = document.getElementById('dateOfBirth');
        this.usernameInput = document.getElementById('username');
        this.emailInput = document.getElementById('email');
        this.passwordInput = document.getElementById('password');
        this.confirmPasswordInput = document.getElementById('confirmPassword');
        this.termsCheckbox = document.getElementById('termsAccepted');
        this.submitButton = document.getElementById('registerSubmit');
        this.submitText = this.submitButton.querySelector('.submit-text');
        this.loadingSpinner = this.submitButton.querySelector('.loading-spinner');
        this.passwordToggle = document.getElementById('passwordToggle');
        this.confirmPasswordToggle = document.getElementById('confirmPasswordToggle');
        
        // Password strength elements
        this.passwordStrength = document.getElementById('password-strength');
        this.strengthFill = document.getElementById('strength-fill');
        this.strengthText = document.getElementById('strength-text');
        
        this.apiHandler = new APIHandler();
        this.validator = ValidationUtils;
        
        this.isSubmitting = false;
        this.usernameCheckTimeout = null;
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.setMaxDate();
    }
    
    bindEvents() {
        // Form submission
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
        
        // Password toggles
        this.passwordToggle.addEventListener('click', () => this.togglePassword('password'));
        this.confirmPasswordToggle.addEventListener('click', () => this.togglePassword('confirmPassword'));
        
        // Field validation events
        this.firstNameInput.addEventListener('blur', () => this.validateFirstName());
        this.firstNameInput.addEventListener('input', () => this.clearError('firstName'));
        
        this.lastNameInput.addEventListener('blur', () => this.validateLastName());
        this.lastNameInput.addEventListener('input', () => this.clearError('lastName'));
        
        this.dateOfBirthInput.addEventListener('blur', () => this.validateDateOfBirth());
        this.dateOfBirthInput.addEventListener('change', () => this.validateDateOfBirth());
        
        this.usernameInput.addEventListener('blur', () => this.validateUsername());
        this.usernameInput.addEventListener('input', () => {
            this.clearError('username');
            this.debouncedUsernameCheck();
        });
        
        this.emailInput.addEventListener('blur', () => this.validateEmail());
        this.emailInput.addEventListener('input', () => this.clearError('email'));
        
        this.passwordInput.addEventListener('input', () => {
            this.clearError('password');
            this.updatePasswordStrength();
            this.validatePasswordMatch();
        });
        this.passwordInput.addEventListener('blur', () => this.validatePassword());
        
        this.confirmPasswordInput.addEventListener('input', () => {
            this.clearError('confirmPassword');
            this.validatePasswordMatch();
        });
        this.confirmPasswordInput.addEventListener('blur', () => this.validatePasswordMatch());
        
        this.termsCheckbox.addEventListener('change', () => this.validateTerms());
        
        // Keyboard navigation
        this.form.addEventListener('keydown', (e) => this.handleKeyDown(e));
        
        // Debounced username availability check
        this.debouncedUsernameCheck = this.validator.debounce(() => {
            if (this.usernameInput.value.trim().length >= 3) {
                this.checkUsernameAvailability();
            }
        }, 500);
    }
    
    setMaxDate() {
        // Set max date to today for date of birth
        const today = new Date().toISOString().split('T')[0];
        this.dateOfBirthInput.setAttribute('max', today);
        
        // Set min date to 120 years ago
        const minDate = new Date();
        minDate.setFullYear(minDate.getFullYear() - 120);
        this.dateOfBirthInput.setAttribute('min', minDate.toISOString().split('T')[0]);
    }
    
    handleKeyDown(e) {
        if (e.key === 'Enter' && !this.isSubmitting) {
            e.preventDefault();
            this.handleSubmit(e);
        }
    }
    
    togglePassword(fieldName) {
        const input = document.getElementById(fieldName);
        const toggle = document.getElementById(`${fieldName}Toggle`);
        const showIcon = toggle.querySelector('.show-icon');
        const hideIcon = toggle.querySelector('.hide-icon');
        
        if (input.type === 'password') {
            input.type = 'text';
            showIcon.style.display = 'none';
            hideIcon.style.display = 'block';
            toggle.setAttribute('aria-label', 'Hide password');
        } else {
            input.type = 'password';
            showIcon.style.display = 'block';
            hideIcon.style.display = 'none';
            toggle.setAttribute('aria-label', 'Show password');
        }
    }
    
    validateFirstName() {
        const result = this.validator.validateName(this.firstNameInput.value.trim(), 'First name');
        if (!result.valid) {
            this.showError('firstName', result.issues[0]);
            return false;
        }
        this.showSuccess('firstName');
        return true;
    }
    
    validateLastName() {
        const result = this.validator.validateName(this.lastNameInput.value.trim(), 'Last name');
        if (!result.valid) {
            this.showError('lastName', result.issues[0]);
            return false;
        }
        this.showSuccess('lastName');
        return true;
    }
    
    validateDateOfBirth() {
        const result = this.validator.validateDateOfBirth(this.dateOfBirthInput.value);
        if (!result.valid) {
            this.showError('dateOfBirth', result.issues[0]);
            return false;
        }
        this.showSuccess('dateOfBirth');
        return true;
    }
    
    validateUsername() {
        const result = this.validator.validateUsername(this.usernameInput.value.trim());
        if (!result.valid) {
            this.showError('username', result.issues[0]);
            return false;
        }
        return true;
    }
    
    async checkUsernameAvailability() {
        const username = this.usernameInput.value.trim();
        
        if (!this.validateUsername()) {
            return;
        }
          try {
            const response = await this.apiHandler.get('/auth/check-username', { username });
            
            if (response.available) {
                this.showSuccess('username');
                this.showUsernameSuccess('Username is available!');
            } else {
                this.showError('username', 'Username is already taken');
            }        } catch (error) {
            console.error('Username check error:', error);
            
            // For username availability check, we'll be more lenient and not show detailed errors to the user
            // But we'll log detailed information for debugging
            let errorMessage = 'Unable to check username availability';
            if (error.message && !error.message.includes('HTTP error! status:')) {
                // Server provided a specific error message
                console.warn(`Username availability check failed: ${error.message}`);
            } else if (error.message) {
                // Generic HTTP error
                if (error.message.includes('404')) {
                    console.warn('Username availability check failed: API endpoint not found');
                } else if (error.message.includes('500')) {
                    console.warn('Username availability check failed: Server error');
                } else {
                    console.warn(`Username availability check failed: ${error.message}`);
                }
            }
            
            // Show a subtle warning instead of blocking the user
            this.showSuccess('username');
        }
    }
    
    validateEmail() {
        const email = this.emailInput.value.trim();
        
        if (!email) {
            this.showError('email', 'Email address is required');
            return false;
        }
        
        if (!this.validator.isValidEmail(email)) {
            this.showError('email', 'Please enter a valid email address');
            return false;
        }
        
        this.showSuccess('email');
        return true;
    }
    
    validatePassword() {
        const result = this.validator.validatePassword(this.passwordInput.value);
        if (!result.valid) {
            this.showError('password', result.issues[0]);
            return false;
        }
        this.showSuccess('password');
        return true;
    }
    
    validatePasswordMatch() {
        const password = this.passwordInput.value;
        const confirmPassword = this.confirmPasswordInput.value;
        
        if (!confirmPassword) {
            this.clearError('confirmPassword');
            return false;
        }
        
        if (password !== confirmPassword) {
            this.showError('confirmPassword', 'Passwords do not match');
            return false;
        }
        
        this.showSuccess('confirmPassword');
        return true;
    }
    
    validateTerms() {
        if (!this.termsCheckbox.checked) {
            this.showError('termsAccepted', 'You must accept the Terms of Service and Privacy Policy');
            return false;
        }
        
        this.clearError('termsAccepted');
        return true;
    }
    
    updatePasswordStrength() {
        const password = this.passwordInput.value;
        
        if (!password) {
            this.passwordStrength.style.display = 'none';
            return;
        }
        
        const result = this.validator.validatePassword(password);
        this.passwordStrength.style.display = 'block';
        
        // Update strength bar
        this.strengthFill.className = `strength-fill ${result.strength}`;
        this.strengthText.className = `strength-text ${result.strength}`;
        
        // Update strength text
        let strengthLabel = result.strength.charAt(0).toUpperCase() + result.strength.slice(1);
        if (result.issues.length > 0) {
            this.strengthText.textContent = `${strengthLabel} - ${result.issues[0]}`;
        } else {
            this.strengthText.textContent = `${strengthLabel} password`;
        }
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
    
    showUsernameSuccess(message) {
        const successElement = document.getElementById('username-success');
        successElement.textContent = message;
        successElement.style.display = 'flex';
    }
    
    async handleSubmit(e) {
        e.preventDefault();
        
        if (this.isSubmitting) return;
        
        // Validate all fields
        const validations = [
            this.validateFirstName(),
            this.validateLastName(),
            this.validateDateOfBirth(),
            this.validateUsername(),
            this.validateEmail(),
            this.validatePassword(),
            this.validatePasswordMatch(),
            this.validateTerms()
        ];
        
        const isValid = validations.every(result => result === true);
        
        if (!isValid) {
            this.showFormError('Please correct the errors above before submitting.');
            return;
        }
        
        // Show loading state
        this.setSubmittingState(true);
          try {
            const formData = this.getFormData();
            const response = await this.apiHandler.post('/auth/register', formData);
            
            if (response.success) {
                this.handleRegistrationSuccess(response);
            } else {
                this.handleRegistrationError(response.message || 'Registration failed. Please try again.');
            }        } catch (error) {
            console.error('Registration error:', error);
            console.log('Error details:', {
                message: error.message,
                type: typeof error.message,
                hasMessage: !!error.message,
                includesHTTP: error.message ? error.message.includes('HTTP error! status:') : false
            });
            
            // Prioritize server error message, fallback to detailed client-side messages
            let errorMessage = error.message;
            
            // If no server message or it's a generic HTTP error, provide detailed client-side message
            if (!errorMessage || errorMessage.includes('HTTP error! status:')) {
                if (errorMessage && errorMessage.includes('404')) {
                    errorMessage = 'Registration service not found. Please contact support.';
                } else if (errorMessage && errorMessage.includes('500')) {
                    errorMessage = 'Server error occurred during registration. Please try again later.';
                } else if (errorMessage && errorMessage.includes('400')) {
                    errorMessage = 'Invalid registration data. Please check your information and try again.';
                } else if (errorMessage && errorMessage.includes('403')) {
                    errorMessage = 'Registration is currently not allowed. Please contact support.';
                } else if (!errorMessage || errorMessage.includes('Failed to fetch') || errorMessage.includes('fetch')) {
                    errorMessage = 'Network connection failed. Please check your internet connection and try again.';
                } else if (errorMessage && errorMessage.includes('timeout')) {
                    errorMessage = 'Request timed out. Please check your connection and try again.';
                } else {
                    errorMessage = 'An unexpected error occurred during registration. Please try again.';
                }
            }
            
            console.log('Final error message to display:', errorMessage);
            this.handleRegistrationError(errorMessage);
        } finally {
            this.setSubmittingState(false);
        }
    }
    
    getFormData() {
        return {
            first_name: this.firstNameInput.value.trim(),
            last_name: this.lastNameInput.value.trim(),
            date_of_birth: this.dateOfBirthInput.value,
            username: this.usernameInput.value.trim(),
            email: this.emailInput.value.trim(),
            password: this.passwordInput.value,
            terms_accepted: this.termsCheckbox.checked
        };
    }
    
    handleRegistrationSuccess(response) {
        // Clear form
        this.form.reset();
        this.passwordStrength.style.display = 'none';
        
        // Show success message
        this.showSuccessMessage(
            'Account created successfully! Please check your email to verify your account before signing in.'
        );
        
        // Redirect after delay
        setTimeout(() => {
            window.location.href = response.redirect_url || 'login.php';
        }, 3000);
    }
    
    handleRegistrationError(message) {
        // Check for specific field errors
        if (message.toLowerCase().includes('username')) {
            this.showError('username', message);
        } else if (message.toLowerCase().includes('email')) {
            this.showError('email', message);
        } else {
            this.showFormError(message);
        }
    }
    
    showFormError(message) {
        // Create or update general error message
        let errorDiv = this.form.querySelector('.form-error');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'form-error';
            this.form.insertBefore(errorDiv, this.submitButton);
        }
        
        errorDiv.textContent = message;
        errorDiv.style.display = 'flex';
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            if (errorDiv) {
                errorDiv.style.display = 'none';
            }
        }, 5000);
    }
    
    showSuccessMessage(message) {
        // Create success message
        let successDiv = this.form.querySelector('.form-success');
        if (!successDiv) {
            successDiv = document.createElement('div');
            successDiv.className = 'form-success';
            this.form.insertBefore(successDiv, this.submitButton);
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
        const inputs = [
            this.firstNameInput,
            this.lastNameInput,
            this.dateOfBirthInput,
            this.usernameInput,
            this.emailInput,
            this.passwordInput,
            this.confirmPasswordInput,
            this.termsCheckbox
        ];
        
        inputs.forEach(input => {
            input.disabled = isSubmitting;
        });
    }
}

// Initialize register form when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new RegisterForm();
});
