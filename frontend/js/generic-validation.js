// Generic Validation Utilities
// Reusable validation functions for SocialConnect forms

class ValidationUtils {
  // Email validation
  static isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  }

  // Password strength validation
  static validatePassword(password) {
    const result = {
      score: 0,
      strength: 'weak',
      issues: [],
      valid: false
    };

    if (!password) {
      result.issues.push('Password is required');
      return result;
    }

    // Length check
    if (password.length < 8) {
      result.issues.push('Must be at least 8 characters long');
    } else {
      result.score += 1;
    }

    // Character variety checks
    if (!/[a-z]/.test(password)) {
      result.issues.push('Must contain lowercase letters');
    } else {
      result.score += 1;
    }

    if (!/[A-Z]/.test(password)) {
      result.issues.push('Must contain uppercase letters');
    } else {
      result.score += 1;
    }

    if (!/\d/.test(password)) {
      result.issues.push('Must contain numbers');
    } else {
      result.score += 1;
    }

    if (!/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) {
      result.issues.push('Must contain special characters');
    } else {
      result.score += 1;
    }

    // Determine strength
    if (result.score < 3) {
      result.strength = 'weak';
    } else if (result.score < 5) {
      result.strength = 'medium';
    } else {
      result.strength = 'strong';
    }

    result.valid = result.issues.length === 0;
    return result;
  }

  // Username validation
  static validateUsername(username) {
    const issues = [];

    if (!username) {
      issues.push('Username is required');
      return { valid: false, issues };
    }

    if (username.length < 3) {
      issues.push('Must be at least 3 characters long');
    }

    if (username.length > 20) {
      issues.push('Must be no more than 20 characters long');
    }

    if (!/^[a-zA-Z0-9_]+$/.test(username)) {
      issues.push('Only letters, numbers, and underscores allowed');
    }

    if (/^_|_$/.test(username)) {
      issues.push('Cannot start or end with underscore');
    }

    return {
      valid: issues.length === 0,
      issues
    };
  }

  // Name validation
  static validateName(name, fieldName = 'Name') {
    const issues = [];

    if (!name || name.trim() === '') {
      issues.push(`${fieldName} is required`);
      return { valid: false, issues };
    }

    if (name.length < 2) {
      issues.push(`${fieldName} must be at least 2 characters long`);
    }

    if (name.length > 50) {
      issues.push(`${fieldName} must be no more than 50 characters long`);
    }

    if (!/^[a-zA-Z\s'-]+$/.test(name)) {
      issues.push(`${fieldName} can only contain letters, spaces, hyphens, and apostrophes`);
    }

    return {
      valid: issues.length === 0,
      issues
    };
  }

  // Date of birth validation
  static validateDateOfBirth(dateString) {
    const issues = [];

    if (!dateString) {
      issues.push('Date of birth is required');
      return { valid: false, issues };
    }

    const date = new Date(dateString);
    const today = new Date();
    
    if (isNaN(date.getTime())) {
      issues.push('Invalid date format');
      return { valid: false, issues };
    }

    // Check if date is in the future
    if (date > today) {
      issues.push('Date of birth cannot be in the future');
    }

    // Check minimum age (13 years)
    const age = today.getFullYear() - date.getFullYear();
    const monthDiff = today.getMonth() - date.getMonth();
    const dayDiff = today.getDate() - date.getDate();
    
    let actualAge = age;
    if (monthDiff < 0 || (monthDiff === 0 && dayDiff < 0)) {
      actualAge--;
    }

    if (actualAge < 13) {
      issues.push('You must be at least 13 years old to register');
    }

    // Check maximum age (120 years)
    if (actualAge > 120) {
      issues.push('Please enter a valid date of birth');
    }

    return {
      valid: issues.length === 0,
      issues,
      age: actualAge
    };
  }

  // Display validation error
  static showFieldError(fieldId, messages) {
    const field = document.getElementById(fieldId);
    const errorContainer = document.getElementById(`${fieldId}-error`);
    
    if (!field || !errorContainer) return;

    if (messages && messages.length > 0) {
      field.classList.add('error');
      errorContainer.textContent = messages[0];
      errorContainer.style.display = 'block';
    } else {
      field.classList.remove('error');
      errorContainer.style.display = 'none';
    }
  }

  // Show field success
  static showFieldSuccess(fieldId) {
    const field = document.getElementById(fieldId);
    const errorContainer = document.getElementById(`${fieldId}-error`);
    
    if (!field) return;

    field.classList.remove('error');
    field.classList.add('success');
    
    if (errorContainer) {
      errorContainer.style.display = 'none';
    }
  }

  // Clear field validation state
  static clearFieldState(fieldId) {
    const field = document.getElementById(fieldId);
    const errorContainer = document.getElementById(`${fieldId}-error`);
    
    if (field) {
      field.classList.remove('error', 'success');
    }
    
    if (errorContainer) {
      errorContainer.style.display = 'none';
    }
  }

  // Debounce function for API calls
  static debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  }
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
  module.exports = ValidationUtils;
}
