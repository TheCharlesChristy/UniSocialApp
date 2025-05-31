// Generic File Upload Utilities
// Reusable file upload functionality for SocialConnect

class FileUploadUtils {
  constructor() {
    this.allowedTypes = {
      image: ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'],
      video: ['video/mp4', 'video/webm', 'video/ogg'],
      document: ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
    };
    
    this.maxSizes = {
      image: 5 * 1024 * 1024, // 5MB
      video: 50 * 1024 * 1024, // 50MB
      document: 10 * 1024 * 1024 // 10MB
    };
  }

  // Validate file type and size
  validateFile(file, type = 'image') {
    const errors = [];

    if (!file) {
      errors.push('No file selected');
      return { valid: false, errors };
    }

    // Check file type
    if (!this.allowedTypes[type].includes(file.type)) {
      errors.push(`Invalid file type. Allowed: ${this.allowedTypes[type].map(t => t.split('/')[1]).join(', ')}`);
    }

    // Check file size
    if (file.size > this.maxSizes[type]) {
      const maxSizeMB = Math.round(this.maxSizes[type] / (1024 * 1024));
      errors.push(`File too large. Maximum size: ${maxSizeMB}MB`);
    }

    return {
      valid: errors.length === 0,
      errors,
      size: file.size,
      type: file.type
    };
  }

  // Create file preview
  createImagePreview(file, containerId) {
    return new Promise((resolve, reject) => {
      const container = document.getElementById(containerId);
      if (!container) {
        reject(new Error('Preview container not found'));
        return;
      }

      const reader = new FileReader();
      
      reader.onload = (e) => {
        const preview = document.createElement('div');
        preview.className = 'file-preview';
        preview.innerHTML = `
          <img src="${e.target.result}" alt="Preview" class="preview-image">
          <button type="button" class="remove-file-btn" onclick="this.parentElement.remove()">
            <span class="remove-icon">×</span>
          </button>
          <div class="file-info">
            <span class="file-name">${file.name}</span>
            <span class="file-size">${this.formatFileSize(file.size)}</span>
          </div>
        `;
        
        container.innerHTML = '';
        container.appendChild(preview);
        resolve(preview);
      };

      reader.onerror = () => {
        reject(new Error('Failed to read file'));
      };

      reader.readAsDataURL(file);
    });
  }

  // Format file size for display
  formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
  }

  // Setup file input with drag and drop
  setupFileInput(inputId, previewContainerId, options = {}) {
    const input = document.getElementById(inputId);
    const container = document.getElementById(previewContainerId);
    
    if (!input || !container) {
      console.error('File input or preview container not found');
      return;
    }

    const fileType = options.type || 'image';
    const allowMultiple = options.multiple || false;

    // Handle file selection
    input.addEventListener('change', async (e) => {
      const files = Array.from(e.target.files);
      
      if (!allowMultiple && files.length > 1) {
        this.showError(container, 'Please select only one file');
        return;
      }

      for (const file of files) {
        const validation = this.validateFile(file, fileType);
        
        if (!validation.valid) {
          this.showError(container, validation.errors[0]);
          continue;
        }

        if (fileType === 'image') {
          try {
            await this.createImagePreview(file, previewContainerId);
          } catch (error) {
            this.showError(container, 'Failed to create preview');
          }
        }
      }
    });

    // Setup drag and drop
    this.setupDragAndDrop(container, input, fileType);
  }

  // Setup drag and drop functionality
  setupDragAndDrop(container, input, fileType) {
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
      container.addEventListener(eventName, (e) => {
        e.preventDefault();
        e.stopPropagation();
      });
    });

    ['dragenter', 'dragover'].forEach(eventName => {
      container.addEventListener(eventName, () => {
        container.classList.add('drag-over');
      });
    });

    ['dragleave', 'drop'].forEach(eventName => {
      container.addEventListener(eventName, () => {
        container.classList.remove('drag-over');
      });
    });

    container.addEventListener('drop', (e) => {
      const files = Array.from(e.dataTransfer.files);
      
      if (files.length > 0) {
        const dataTransfer = new DataTransfer();
        files.forEach(file => dataTransfer.items.add(file));
        input.files = dataTransfer.files;
        
        // Trigger change event
        input.dispatchEvent(new Event('change', { bubbles: true }));
      }
    });
  }

  // Show error message
  showError(container, message) {
    const errorElement = container.querySelector('.upload-error') || 
                        document.createElement('div');
    errorElement.className = 'upload-error';
    errorElement.textContent = message;
    
    if (!container.contains(errorElement)) {
      container.appendChild(errorElement);
    }

    // Remove error after 5 seconds
    setTimeout(() => {
      if (container.contains(errorElement)) {
        container.removeChild(errorElement);
      }
    }, 5000);
  }

  // Upload file to server
  async uploadFile(file, endpoint, additionalData = {}) {
    const formData = new FormData();
    formData.append('file', file);
    
    // Add additional data
    Object.keys(additionalData).forEach(key => {
      formData.append(key, additionalData[key]);
    });

    try {
      const api = new APIHandler();
      const response = await api.upload(endpoint, formData);
      return response;
    } catch (error) {
      console.error('Upload failed:', error);
      throw error;
    }
  }

  // Get selected files from input
  getSelectedFiles(inputId) {
    const input = document.getElementById(inputId);
    return input ? Array.from(input.files) : [];
  }

  // Clear file input
  clearFileInput(inputId, previewContainerId = null) {
    const input = document.getElementById(inputId);
    if (input) {
      input.value = '';
    }

    if (previewContainerId) {
      const container = document.getElementById(previewContainerId);
      if (container) {
        container.innerHTML = '';
      }
    }
  }
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
  module.exports = FileUploadUtils;
}
