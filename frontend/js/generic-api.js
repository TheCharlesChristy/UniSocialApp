/* Generic API Handler */
/* Standardized API calls for SocialConnect platform */

class APIHandler {
  constructor(baseURL = '/backend/src/api') {
    this.baseURL = baseURL;
    this.defaultHeaders = {
      'Content-Type': 'application/json',
    };
  }

  // Generic fetch wrapper with error handling
  async request(endpoint, options = {}) {
    const url = `${this.baseURL}${endpoint}`;
    const config = {
      headers: { ...this.defaultHeaders, ...options.headers },
      ...options
    };

    try {
      const response = await fetch(url, config);
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const contentType = response.headers.get('content-type');
      if (contentType && contentType.includes('application/json')) {
        return await response.json();
      } else {
        return await response.text();
      }
    } catch (error) {
      console.error('API request failed:', error);
      throw error;
    }
  }

  // GET request
  async get(endpoint, params = {}) {
    const urlParams = new URLSearchParams(params);
    const queryString = urlParams.toString();
    const fullEndpoint = queryString ? `${endpoint}?${queryString}` : endpoint;
    
    return this.request(fullEndpoint, {
      method: 'GET'
    });
  }

  // POST request
  async post(endpoint, data = {}) {
    return this.request(endpoint, {
      method: 'POST',
      body: JSON.stringify(data)
    });
  }

  // PUT request
  async put(endpoint, data = {}) {
    return this.request(endpoint, {
      method: 'PUT',
      body: JSON.stringify(data)
    });
  }

  // DELETE request
  async delete(endpoint) {
    return this.request(endpoint, {
      method: 'DELETE'
    });
  }

  // Upload file
  async upload(endpoint, formData) {
    const headers = { ...this.defaultHeaders };
    delete headers['Content-Type']; // Let browser set boundary for FormData

    return this.request(endpoint, {
      method: 'POST',
      headers,
      body: formData
    });
  }

  // Get with authentication token
  async authenticatedRequest(endpoint, options = {}) {
    const token = this.getAuthToken();
    if (!token) {
      throw new Error('No authentication token found');
    }

    const authHeaders = {
      'Authorization': `Bearer ${token}`,
      ...options.headers
    };

    return this.request(endpoint, {
      ...options,
      headers: authHeaders
    });
  }

  // Token management
  getAuthToken() {
    return localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token');
  }

  setAuthToken(token, persistent = false) {
    const storage = persistent ? localStorage : sessionStorage;
    storage.setItem('auth_token', token);
  }

  removeAuthToken() {
    localStorage.removeItem('auth_token');
    sessionStorage.removeItem('auth_token');
  }

  // Check if user is authenticated
  isAuthenticated() {
    return !!this.getAuthToken();
  }
}

// Statistics API methods
class StatsAPI extends APIHandler {
  async getPlatformStats() {
    try {
      // Try to get real stats, but fall back gracefully
      const stats = await this.get('/stats/platform');
      return stats;
    } catch (error) {
      console.warn('Failed to fetch platform stats, using fallback:', error);
      // Return fallback static data
      return {
        users: 50000,
        posts: 125000,
        connections: 25000,
        satisfaction: 95
      };
    }
  }

  async getUserStats(userId) {
    return this.authenticatedRequest(`/stats/user/${userId}`);
  }
}

// User API methods
class UserAPI extends APIHandler {
  async login(username, password) {
    const response = await this.post('/auth/login', {
      username,
      password
    });
    
    if (response.token) {
      this.setAuthToken(response.token, true);
    }
    
    return response;
  }

  async register(userData) {
    return this.post('/auth/register', userData);
  }

  async logout() {
    try {
      await this.authenticatedRequest('/auth/logout', { method: 'POST' });
    } catch (error) {
      console.warn('Logout API call failed:', error);
    } finally {
      this.removeAuthToken();
    }
  }

  async getCurrentUser() {
    return this.authenticatedRequest('/users/me');
  }

  async updateProfile(profileData) {
    return this.authenticatedRequest('/users/me', {
      method: 'PUT',
      body: JSON.stringify(profileData)
    });
  }
}

// Initialize API instances
const api = new APIHandler();
const statsAPI = new StatsAPI();
const userAPI = new UserAPI();

// Export for use in other files
if (typeof module !== 'undefined' && module.exports) {
  module.exports = { APIHandler, StatsAPI, UserAPI, api, statsAPI, userAPI };
} else {
  window.API = { APIHandler, StatsAPI, UserAPI, api, statsAPI, userAPI };
}

// Utility functions for common API patterns
const APIUtils = {
  // Handle loading states
  withLoading: async (element, asyncFn) => {
    const originalContent = element.innerHTML;
    element.innerHTML = '<div class="loading-spinner"></div>';
    element.disabled = true;
    
    try {
      const result = await asyncFn();
      return result;
    } catch (error) {
      console.error('Operation failed:', error);
      throw error;
    } finally {
      element.innerHTML = originalContent;
      element.disabled = false;
    }
  },

  // Display error messages
  showError: (message, container = document.body) => {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    errorDiv.style.cssText = `
      position: fixed;
      top: 20px;
      right: 20px;
      background: #EF4444;
      color: white;
      padding: 12px 24px;
      border-radius: 6px;
      z-index: 1000;
      animation: slideInRight 0.3s ease-out;
    `;
    
    container.appendChild(errorDiv);
    
    setTimeout(() => {
      errorDiv.remove();
    }, 5000);
  },

  // Display success messages
  showSuccess: (message, container = document.body) => {
    const successDiv = document.createElement('div');
    successDiv.className = 'success-message';
    successDiv.textContent = message;
    successDiv.style.cssText = `
      position: fixed;
      top: 20px;
      right: 20px;
      background: #10B981;
      color: white;
      padding: 12px 24px;
      border-radius: 6px;
      z-index: 1000;
      animation: slideInRight 0.3s ease-out;
    `;
    
    container.appendChild(successDiv);
    
    setTimeout(() => {
      successDiv.remove();
    }, 3000);
  },

  // Format numbers for display
  formatNumber: (num) => {
    if (num >= 1000000) {
      return (num / 1000000).toFixed(1) + 'M';
    } else if (num >= 1000) {
      return (num / 1000).toFixed(1) + 'K';
    }
    return num.toString();
  },

  // Debounce function for search inputs
  debounce: (func, wait) => {
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
};

// Make utilities available globally
if (typeof window !== 'undefined') {
  window.APIUtils = APIUtils;
}
