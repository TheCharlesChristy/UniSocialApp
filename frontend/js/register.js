// Register Page JavaScript
// Handles registration form functionality for SocialConnect

document.addEventListener('DOMContentLoaded', function() {
  // Initialize the registration page
  initializeRegistrationPage();
});

function initializeRegistrationPage() {
  // Initialize API handler
  const api = new APIHandler();
  
  // Initialize file upload handler
  const fileUpload = new FileUploadUtils();
  
  // Initialize form elements
  const form = document.getElementById('registration-form');
  const submitBtn = document.getElementById('submit-btn');
  const successMessage = document.getElementById('registration-success');
  
  // Setup form validation and handlers
  setupFormValidation();
  setupPasswordHandlers();
  setupUsernameChecker();
  setupFileUpload();
  setupFormSubmission();

  // Form validation setup
  function setupFormValidation() {
    // Real-time validation for all form fields
    const validationFields = [
      { id: 'first-name', validator: (value) => ValidationUtils.validateName(value, 'First name') },
      { id: 'last-name', validator: (value) => ValidationUtils.validateName(value, 'Last name') },
      { id: 'username', validator: (value) => ValidationUtils.validateUsername(value) },
      { id: 'email', validator: (value) => ({ valid: ValidationUtils.isValidEmail(value), issues: ValidationUtils.isValidEmail(value) ? [] : ['Please enter a valid email address'] }) },
      { id: 'password', validator: (value) => ValidationUtils.validatePassword(value) },
      { id: 'date-of-birth', validator: (value) => ValidationUtils.validateDateOfBirth(value) },
    ];

    validationFields.forEach(({ id, validator }) => {
      const field = document.getElementById(id);
      if (!field) return;

      // Validate on blur
      field.addEventListener('blur', function() {
        const result = validator(this.value);
        if (result.valid) {
          ValidationUtils.showFieldSuccess(id);
        } else {
          ValidationUtils.showFieldError(id, result.issues);
        }
      });

      // Clear validation state on focus
      field.addEventListener('focus', function() {
        ValidationUtils.clearFieldState(id);
      });
    });

    // Password confirmation validation
    const confirmPassword = document.getElementById('confirm-password');
    const password = document.getElementById('password');
    
    function validatePasswordMatch() {
      if (confirmPassword.value && password.value) {
        if (confirmPassword.value === password.value) {
          ValidationUtils.showFieldSuccess('confirm-password');
        } else {
          ValidationUtils.showFieldError('confirm-password', ['Passwords do not match']);
        }
      }
    }

    confirmPassword.addEventListener('blur', validatePasswordMatch);
    confirmPassword.addEventListener('input', ValidationUtils.debounce(validatePasswordMatch, 300));

    // Terms agreement validation
    const termsCheckbox = document.getElementById('terms-agreement');
    termsCheckbox.addEventListener('change', function() {
      if (this.checked) {
        ValidationUtils.clearFieldState('terms-agreement');
      } else {
        ValidationUtils.showFieldError('terms-agreement', ['You must agree to the terms and privacy policy']);
      }
    });
  }

  // Password strength and visibility handlers
  function setupPasswordHandlers() {
    const passwordField = document.getElementById('password');
    const passwordToggle = document.getElementById('password-toggle');
    const strengthBar = document.getElementById('strength-bar');
    const strengthText = document.getElementById('strength-text');

    // Password strength indicator
    passwordField.addEventListener('input', function() {
      const password = this.value;
      const strength = ValidationUtils.validatePassword(password);
      
      // Update strength bar
      strengthBar.className = 'strength-bar';
      strengthText.className = 'strength-text';
      
      if (password.length === 0) {
        strengthText.textContent = 'Enter a password';
        return;
      }

      strengthBar.classList.add(strength.strength);
      strengthText.classList.add(strength.strength);
      
      if (strength.strength === 'weak') {
        strengthText.textContent = 'Weak password';
      } else if (strength.strength === 'medium') {
        strengthText.textContent = 'Medium password';
      } else {
        strengthText.textContent = 'Strong password';
      }

      // Show improvement suggestions
      if (strength.issues.length > 0 && password.length > 0) {
        strengthText.textContent += ' - ' + strength.issues[0];
      }
    });

    // Password visibility toggle
    passwordToggle.addEventListener('click', function() {
      const isPassword = passwordField.type === 'password';
      passwordField.type = isPassword ? 'text' : 'password';
      this.querySelector('.toggle-icon').textContent = isPassword ? '🙈' : '👁️';
    });
  }
  // Username availability checker
  function setupUsernameChecker() {
    const usernameField = document.getElementById('username');
    const statusElement = document.getElementById('username-status');
    
    // Since username availability check requires authentication, 
    // we'll only validate format and show availability status on submission
    usernameField.addEventListener('input', function() {
      const username = this.value;
      
      if (!username || username.length < 3) {
        statusElement.textContent = '';
        statusElement.className = 'username-status';
        return;
      }

      // Validate username format
      const validation = ValidationUtils.validateUsername(username);
      if (validation.valid) {
        statusElement.textContent = 'Format is valid';
        statusElement.className = 'username-status available';
      } else {
        statusElement.textContent = '';
        statusElement.className = 'username-status';
      }
    });
  }

  // File upload setup
  function setupFileUpload() {
    fileUpload.setupFileInput('profile-picture', 'profile-picture-preview', {
      type: 'image',
      multiple: false
    });

    // Add click handler to upload area
    const uploadArea = document.getElementById('file-upload-area');
    const fileInput = document.getElementById('profile-picture');
    
    uploadArea.addEventListener('click', () => {
      fileInput.click();
    });
  }

  // Form submission handler
  function setupFormSubmission() {
    form.addEventListener('submit', async function(e) {
      e.preventDefault();

      // Clear any previous general error
      const generalError = document.getElementById('general-error');
      generalError.style.display = 'none';

      // Validate all fields
      if (!validateAllFields()) {
        showGeneralError('Please correct the errors above before submitting.');
        return;
      }

      // Show loading state
      setSubmitButtonLoading(true);

      try {
        // Collect form data
        const formData = collectFormData();
        
        // Submit registration
        const response = await api.post('/auth/register', formData);
        
        if (response.success) {
          // Show success message
          showRegistrationSuccess();
        } else {
          // Handle server validation errors
          handleServerErrors(response);
        }      
    } catch (error) {
        console.error('Registration failed:', error);
        
        // Handle different error types
        if (error.message.includes('409') || error.message.includes('409')) {
          // Parse the response to get the actual error message
          try {
            const errorResponse = await error.response?.json();
            if (errorResponse?.message?.includes('Username')) {
              ValidationUtils.showFieldError('username', ['This username is already taken']);
            } else if (errorResponse?.message?.includes('Email')) {
              ValidationUtils.showFieldError('email', ['This email is already registered']);
            } else {
              showGeneralError('Username or email already exists. Please try different values.');
            }
          } catch {
            showGeneralError('Username or email already exists. Please try different values.');
          }
        } else if (error.message.includes('400')) {
          showGeneralError('Invalid data submitted. Please check your information and try again.');
        } else {
          showGeneralError('Registration failed. Please try again later.');
        }
      } finally {
        setSubmitButtonLoading(false);
      }
    });
  }

  // Helper functions
  function validateAllFields() {
    let isValid = true;

    // Validate required fields
    const requiredFields = [
      { id: 'first-name', validator: (value) => ValidationUtils.validateName(value, 'First name') },
      { id: 'last-name', validator: (value) => ValidationUtils.validateName(value, 'Last name') },
      { id: 'username', validator: (value) => ValidationUtils.validateUsername(value) },
      { id: 'email', validator: (value) => ({ valid: ValidationUtils.isValidEmail(value), issues: ValidationUtils.isValidEmail(value) ? [] : ['Please enter a valid email address'] }) },
      { id: 'password', validator: (value) => ValidationUtils.validatePassword(value) },
      { id: 'date-of-birth', validator: (value) => ValidationUtils.validateDateOfBirth(value) },
    ];

    requiredFields.forEach(({ id, validator }) => {
      const field = document.getElementById(id);
      if (!field) {
        console.warn(`Field with ID ${id} not found`);
        return;
      };

      const result = validator(field.value);
      if (!result.valid) {
        ValidationUtils.showFieldError(id, result.issues);
        isValid = false;
      }
    });

    // Validate password confirmation
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm-password').value;
    
    if (password !== confirmPassword) {
      ValidationUtils.showFieldError('confirm-password', ['Passwords do not match']);
      isValid = false;
    }

    // Validate terms agreement
    const termsAgreed = document.getElementById('terms-agreement').checked;
    if (!termsAgreed) {
      ValidationUtils.showFieldError('terms-agreement', ['You must agree to the terms and privacy policy']);
      isValid = false;
    }

    return isValid;
  }

  function collectFormData() {
    return {
      username: document.getElementById('username').value.trim(),
      email: document.getElementById('email').value.trim(),
      password: document.getElementById('password').value,
      first_name: document.getElementById('first-name').value.trim(),
      last_name: document.getElementById('last-name').value.trim(),
      date_of_birth: document.getElementById('date-of-birth').value,
    };
  }

  function handleServerErrors(response) {
    if (response.errors) {
      // Handle field-specific errors
      Object.keys(response.errors).forEach(field => {
        const fieldId = field.replace('_', '-');
        ValidationUtils.showFieldError(fieldId, [response.errors[field]]);
      });
    } else {
      showGeneralError(response.message || 'Registration failed. Please try again.');
    }
  }

  function showRegistrationSuccess() {
    // Hide form and show success message
    form.style.display = 'none';
    successMessage.style.display = 'block';
    
    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  function showGeneralError(message) {
    const generalError = document.getElementById('general-error');
    generalError.textContent = message;
    generalError.style.display = 'block';
    
    // Scroll to error
    generalError.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }

  function setSubmitButtonLoading(loading) {
    const btnText = submitBtn.querySelector('.btn-text');
    const btnSpinner = submitBtn.querySelector('.btn-spinner');
    
    if (loading) {
      submitBtn.disabled = true;
      btnText.textContent = 'Creating Account...';
      btnSpinner.style.display = 'inline-flex';
    } else {
      submitBtn.disabled = false;
      btnText.textContent = 'Create Account';
      btnSpinner.style.display = 'none';
    }
  }

  // Social registration handlers (placeholder)
  document.querySelectorAll('.btn-social').forEach(btn => {
    btn.addEventListener('click', function() {
      const provider = this.classList.contains('btn-google') ? 'Google' : 'Facebook';
      alert(`${provider} registration coming soon!`);
    });
  });

  // Terms and privacy policy link handlers (placeholder)
  document.querySelector('.terms-link').addEventListener('click', function(e) {
    e.preventDefault();
    alert('Terms of Service page coming soon!');
  });

  document.querySelector('.privacy-link').addEventListener('click', function(e) {
    e.preventDefault();
    alert('Privacy Policy page coming soon!');
  });
}
