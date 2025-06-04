// Forgot Password Form Handler
// Handles form validation, submission, and password reset request

class ForgotPasswordForm {
    constructor() {
        this.form = document.getElementById('forgotPasswordForm');
        this.emailInput = document.getElementById('email');
        this.submitButton = document.getElementById('forgotPasswordSubmit');
        this.submitText = this.submitButton.querySelector('.submit-text');
        this.loadingSpinner = this.submitButton.querySelector('.loading-spinner');
        this.successMessage = document.getElementById('successMessage');
        this.resendButton = document.getElementById('resendBtn');
        
        this.apiHandler = new APIHandler();
        this.validator = ValidationUtils;
        
        this.isSubmitting = false;
        this.canResend = false;
        this.resendCooldown = 60; // 60 seconds
        this.resendTimer = null;
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.focusEmailInput();
    }
    
    bindEvents() {
        // Form submission
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
        
        // Real-time validation
        this.emailInput.addEventListener('blur', () => this.validateEmail());
        this.emailInput.addEventListener('input', () => this.clearError('email'));
        
        // Resend functionality
        this.resendButton.addEventListener('click', () => this.handleResend());
        
        // Keyboard navigation
        this.form.addEventListener('keydown', (e) => this.handleKeyDown(e));
        
        // Accessibility enhancements
        this.emailInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !this.isSubmitting) {
                e.preventDefault();
                this.handleSubmit(e);
            }
        });
    }
    
    focusEmailInput() {
        // Focus email input after a short delay for better UX
        setTimeout(() => {
            this.emailInput.focus();
        }, 100);
    }
    
    handleKeyDown(e) {
        if (e.key === 'Escape') {
            // Allow users to clear focus or navigate away
            document.activeElement.blur();
        }
    }
    
    async handleSubmit(e) {
        e.preventDefault();
        
        if (this.isSubmitting) {
            return;
        }
        
        // Validate form
        if (!this.validateForm()) {
            return;
        }
        
        try {
            this.setSubmittingState(true);
            
            const formData = {
                email: this.emailInput.value.trim()
            };
            
            // Make API request
            const response = await this.apiHandler.post('/auth/forgot-password', formData);
            
            if (response.success) {
                this.showSuccess();
                this.startResendCooldown();
                this.logSuccess();
            } else {
                throw new Error(response.message || 'Failed to send reset instructions');
            }
            
        } catch (error) {
            console.error('Forgot password error:', error);
            this.handleError(error);
        } finally {
            this.setSubmittingState(false);
        }
    }
    
    async handleResend() {
        if (!this.canResend || this.isSubmitting) {
            return;
        }
        
        try {
            this.setSubmittingState(true);
            
            const formData = {
                email: this.emailInput.value.trim()
            };
            
            const response = await this.apiHandler.post('/auth/forgot-password', formData);
            
            if (response.success) {
                this.showResendSuccess();
                this.startResendCooldown();
                this.logResend();
            } else {
                throw new Error(response.message || 'Failed to resend instructions');
            }
            
        } catch (error) {
            console.error('Resend error:', error);
            this.handleError(error);
        } finally {
            this.setSubmittingState(false);
        }
    }
    
    validateForm() {
        let isValid = true;
        
        // Validate email
        if (!this.validateEmail()) {
            isValid = false;
        }
        
        return isValid;
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
        
        this.clearError('email');
        this.markFieldAsValid('email');
        return true;
    }
    
    showError(fieldName, message) {
        const input = document.getElementById(fieldName);
        const errorElement = document.getElementById(`${fieldName}-error`);
        
        if (input && errorElement) {
            input.classList.add('error');
            input.classList.remove('success');
            errorElement.textContent = message;
            errorElement.classList.add('show');
            
            // Announce error to screen readers
            errorElement.setAttribute('aria-live', 'assertive');
            
            // Focus the field with error
            input.focus();
        }
    }
    
    clearError(fieldName) {
        const input = document.getElementById(fieldName);
        const errorElement = document.getElementById(`${fieldName}-error`);
        
        if (input && errorElement) {
            input.classList.remove('error');
            errorElement.classList.remove('show');
            errorElement.textContent = '';
            errorElement.setAttribute('aria-live', 'polite');
        }
    }
    
    markFieldAsValid(fieldName) {
        const input = document.getElementById(fieldName);
        if (input) {
            input.classList.add('success');
            input.classList.remove('error');
        }
    }
    
    setSubmittingState(isSubmitting) {
        this.isSubmitting = isSubmitting;
        
        if (isSubmitting) {
            this.submitButton.disabled = true;
            this.submitButton.classList.add('loading');
            this.loadingSpinner.style.display = 'block';
            this.emailInput.disabled = true;
            
            // Update screen reader announcement
            this.submitButton.setAttribute('aria-label', 'Sending reset instructions...');
        } else {
            this.submitButton.disabled = false;
            this.submitButton.classList.remove('loading');
            this.loadingSpinner.style.display = 'none';
            this.emailInput.disabled = false;
            
            // Restore original screen reader label
            this.submitButton.setAttribute('aria-label', 'Send reset instructions');
        }
    }
    
    showSuccess() {
        // Hide the form and show success message
        this.form.style.display = 'none';
        this.successMessage.style.display = 'block';
        this.successMessage.classList.add('show');
        
        // Focus the success message for screen readers
        this.successMessage.focus();
        this.successMessage.setAttribute('tabindex', '-1');
        
        // Announce success to screen readers
        const announcement = document.createElement('div');
        announcement.setAttribute('aria-live', 'assertive');
        announcement.setAttribute('class', 'sr-only');
        announcement.textContent = 'Password reset instructions have been sent. Please check your email.';
        document.body.appendChild(announcement);
        
        setTimeout(() => {
            document.body.removeChild(announcement);
        }, 1000);
    }
    
    showResendSuccess() {
        // Create a temporary success notification
        const notification = document.createElement('div');
        notification.className = 'resend-notification';
        notification.innerHTML = `
            <div class="notification-content">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 12l2 2 4-4"></path>
                    <circle cx="12" cy="12" r="9"></circle>
                </svg>
                <span>Reset instructions sent again!</span>
            </div>
        `;
        
        // Style the notification
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--color-success);
            color: white;
            padding: 12px 16px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            opacity: 0;
            transform: translateX(100%);
            transition: all 300ms ease-out;
        `;
        
        notification.querySelector('.notification-content').style.cssText = `
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 500;
        `;
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.style.opacity = '1';
            notification.style.transform = 'translateX(0)';
        }, 10);
        
        // Animate out and remove
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (notification.parentNode) {
                    document.body.removeChild(notification);
                }
            }, 300);
        }, 3000);
        
        // Announce to screen readers
        const announcement = document.createElement('div');
        announcement.setAttribute('aria-live', 'assertive');
        announcement.setAttribute('class', 'sr-only');
        announcement.textContent = 'Reset instructions have been sent again.';
        document.body.appendChild(announcement);
        
        setTimeout(() => {
            document.body.removeChild(announcement);
        }, 1000);
    }
    
    startResendCooldown() {
        this.canResend = false;
        this.resendButton.disabled = true;
        this.resendButton.style.display = 'block';
        
        let timeLeft = this.resendCooldown;
        this.updateResendButtonText(timeLeft);
        
        this.resendTimer = setInterval(() => {
            timeLeft--;
            this.updateResendButtonText(timeLeft);
            
            if (timeLeft <= 0) {
                clearInterval(this.resendTimer);
                this.canResend = true;
                this.resendButton.disabled = false;
                this.resendButton.textContent = 'Resend Instructions';
            }
        }, 1000);
    }
    
    updateResendButtonText(seconds) {
        this.resendButton.textContent = `Resend Available in ${seconds}s`;
    }
    
    handleError(error) {
        let errorMessage = 'Unable to send reset instructions. Please try again.';
        
        if (error.message) {
            // Handle specific error types
            if (error.message.includes('network') || error.message.includes('fetch')) {
                errorMessage = 'Network error. Please check your connection and try again.';
            } else if (error.message.includes('rate limit')) {
                errorMessage = 'Too many requests. Please wait a moment before trying again.';
            } else if (error.message.includes('server')) {
                errorMessage = 'Server error. Please try again in a few minutes.';
            }
        }
        
        this.showError('email', errorMessage);
        this.logError(error);
    }
    
    logSuccess() {
        console.log('Forgot password request sent successfully');
        
        // Optional: Send analytics event
        if (window.gtag) {
            gtag('event', 'forgot_password_request', {
                event_category: 'authentication',
                event_label: 'success'
            });
        }
    }
    
    logResend() {
        console.log('Forgot password instructions resent');
        
        // Optional: Send analytics event
        if (window.gtag) {
            gtag('event', 'forgot_password_resend', {
                event_category: 'authentication',
                event_label: 'resend'
            });
        }
    }
    
    logError(error) {
        console.error('Forgot password form error:', {
            message: error.message,
            stack: error.stack,
            timestamp: new Date().toISOString(),
            userAgent: navigator.userAgent,
            url: window.location.href
        });
        
        // Optional: Send error to analytics or error tracking service
        if (window.gtag) {
            gtag('event', 'forgot_password_error', {
                event_category: 'authentication',
                event_label: error.message || 'unknown_error'
            });
        }
    }
    
    // Cleanup method for when component is destroyed
    destroy() {
        if (this.resendTimer) {
            clearInterval(this.resendTimer);
        }
    }
}

// Initialize the form when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new ForgotPasswordForm();
});

// Handle page unload cleanup
window.addEventListener('beforeunload', () => {
    if (window.forgotPasswordForm) {
        window.forgotPasswordForm.destroy();
    }
});
