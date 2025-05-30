/* Generic Slideshow Component */
/* Reusable slideshow functionality for various pages */

class Slideshow {
  constructor(container, options = {}) {
    this.container = typeof container === 'string' ? document.querySelector(container) : container;
    
    if (!this.container) {
      console.error('Slideshow container not found');
      return;
    }

    // Default options
    this.options = {
      autoPlay: true,
      autoPlayInterval: 5000,
      showControls: true,
      showIndicators: true,
      pauseOnHover: true,
      loop: true,
      transitionDuration: 500,
      swipeThreshold: 50,
      ...options
    };

    this.currentSlide = 0;
    this.slides = [];
    this.isPlaying = false;
    this.intervalId = null;
    this.isTransitioning = false;

    this.init();
  }

  init() {
    this.setupSlides();
    this.setupControls();
    this.setupIndicators();
    this.setupEventListeners();
    
    if (this.slides.length > 0) {
      this.showSlide(0);
      if (this.options.autoPlay) {
        this.play();
      }
    }
  }

  setupSlides() {
    const slideElements = this.container.querySelectorAll('.slide');
    this.slides = Array.from(slideElements);
    
    if (this.slides.length === 0) {
      console.warn('No slides found in slideshow container');
      return;
    }

    // Hide all slides initially
    this.slides.forEach((slide, index) => {
      slide.style.display = 'none';
      slide.classList.remove('active');
      slide.setAttribute('data-slide-index', index);
    });
  }

  setupControls() {
    if (!this.options.showControls) return;

    const prevBtn = this.container.querySelector('.slide-btn.prev');
    const nextBtn = this.container.querySelector('.slide-btn.next');

    if (prevBtn) {
      prevBtn.addEventListener('click', () => this.previousSlide());
    }

    if (nextBtn) {
      nextBtn.addEventListener('click', () => this.nextSlide());
    }
  }

  setupIndicators() {
    if (!this.options.showIndicators) return;

    const indicatorsContainer = this.container.querySelector('.slide-indicators');
    if (!indicatorsContainer) return;

    // Clear existing indicators
    indicatorsContainer.innerHTML = '';

    // Create indicators for each slide
    this.slides.forEach((_, index) => {
      const indicator = document.createElement('button');
      indicator.className = 'indicator';
      indicator.setAttribute('data-slide', index);
      indicator.setAttribute('aria-label', `Go to slide ${index + 1}`);
      indicator.addEventListener('click', () => this.goToSlide(index));
      indicatorsContainer.appendChild(indicator);
    });
  }

  setupEventListeners() {
    // Pause on hover
    if (this.options.pauseOnHover) {
      this.container.addEventListener('mouseenter', () => this.pause());
      this.container.addEventListener('mouseleave', () => {
        if (this.options.autoPlay) this.play();
      });
    }

    // Keyboard navigation
    this.container.addEventListener('keydown', (e) => {
      switch (e.key) {
        case 'ArrowLeft':
          e.preventDefault();
          this.previousSlide();
          break;
        case 'ArrowRight':
          e.preventDefault();
          this.nextSlide();
          break;
        case ' ':
          e.preventDefault();
          this.isPlaying ? this.pause() : this.play();
          break;
      }
    });

    // Touch/swipe support
    this.setupTouchEvents();

    // Visibility API - pause when tab is not visible
    document.addEventListener('visibilitychange', () => {
      if (document.hidden) {
        this.pause();
      } else if (this.options.autoPlay) {
        this.play();
      }
    });
  }

  setupTouchEvents() {
    let startX = 0;
    let startY = 0;
    let endX = 0;
    let endY = 0;

    this.container.addEventListener('touchstart', (e) => {
      startX = e.touches[0].clientX;
      startY = e.touches[0].clientY;
    }, { passive: true });

    this.container.addEventListener('touchend', (e) => {
      endX = e.changedTouches[0].clientX;
      endY = e.changedTouches[0].clientY;
      this.handleSwipe(startX, startY, endX, endY);
    }, { passive: true });
  }

  handleSwipe(startX, startY, endX, endY) {
    const deltaX = endX - startX;
    const deltaY = endY - startY;
    const absDeltaX = Math.abs(deltaX);
    const absDeltaY = Math.abs(deltaY);

    // Only handle horizontal swipes that are longer than vertical
    if (absDeltaX > absDeltaY && absDeltaX > this.options.swipeThreshold) {
      if (deltaX > 0) {
        this.previousSlide();
      } else {
        this.nextSlide();
      }
    }
  }

  showSlide(index) {
    if (this.isTransitioning || index < 0 || index >= this.slides.length) {
      return;
    }

    this.isTransitioning = true;

    // Hide current slide
    if (this.slides[this.currentSlide]) {
      this.slides[this.currentSlide].classList.remove('active');
      this.slides[this.currentSlide].style.display = 'none';
    }

    // Show new slide
    this.currentSlide = index;
    this.slides[this.currentSlide].style.display = 'block';
    
    // Trigger reflow before adding active class for smooth transition
    this.slides[this.currentSlide].offsetHeight;
    this.slides[this.currentSlide].classList.add('active');

    // Update indicators
    this.updateIndicators();

    // Update controls
    this.updateControls();

    // Reset transition flag after animation
    setTimeout(() => {
      this.isTransitioning = false;
    }, this.options.transitionDuration);

    // Emit custom event
    this.container.dispatchEvent(new CustomEvent('slideChange', {
      detail: {
        currentSlide: this.currentSlide,
        totalSlides: this.slides.length
      }
    }));
  }

  updateIndicators() {
    const indicators = this.container.querySelectorAll('.indicator');
    indicators.forEach((indicator, index) => {
      indicator.classList.toggle('active', index === this.currentSlide);
    });
  }

  updateControls() {
    const prevBtn = this.container.querySelector('.slide-btn.prev');
    const nextBtn = this.container.querySelector('.slide-btn.next');

    if (!this.options.loop) {
      if (prevBtn) {
        prevBtn.disabled = this.currentSlide === 0;
      }
      if (nextBtn) {
        nextBtn.disabled = this.currentSlide === this.slides.length - 1;
      }
    }
  }

  nextSlide() {
    let nextIndex = this.currentSlide + 1;
    
    if (nextIndex >= this.slides.length) {
      nextIndex = this.options.loop ? 0 : this.slides.length - 1;
    }
    
    this.goToSlide(nextIndex);
  }

  previousSlide() {
    let prevIndex = this.currentSlide - 1;
    
    if (prevIndex < 0) {
      prevIndex = this.options.loop ? this.slides.length - 1 : 0;
    }
    
    this.goToSlide(prevIndex);
  }

  goToSlide(index) {
    if (index !== this.currentSlide) {
      this.showSlide(index);
    }
  }

  play() {
    if (this.isPlaying || this.slides.length <= 1) return;

    this.isPlaying = true;
    this.intervalId = setInterval(() => {
      this.nextSlide();
    }, this.options.autoPlayInterval);

    // Emit play event
    this.container.dispatchEvent(new CustomEvent('slideshowPlay'));
  }

  pause() {
    if (!this.isPlaying) return;

    this.isPlaying = false;
    if (this.intervalId) {
      clearInterval(this.intervalId);
      this.intervalId = null;
    }

    // Emit pause event
    this.container.dispatchEvent(new CustomEvent('slideshowPause'));
  }

  stop() {
    this.pause();
    this.goToSlide(0);
  }

  destroy() {
    this.pause();
    
    // Remove event listeners
    const prevBtn = this.container.querySelector('.slide-btn.prev');
    const nextBtn = this.container.querySelector('.slide-btn.next');
    const indicators = this.container.querySelectorAll('.indicator');

    if (prevBtn) prevBtn.removeEventListener('click', this.previousSlide);
    if (nextBtn) nextBtn.removeEventListener('click', this.nextSlide);
    
    indicators.forEach(indicator => {
      indicator.removeEventListener('click', this.goToSlide);
    });

    // Reset slides
    this.slides.forEach(slide => {
      slide.style.display = '';
      slide.classList.remove('active');
    });
  }

  // Public API methods
  getCurrentSlide() {
    return this.currentSlide;
  }

  getTotalSlides() {
    return this.slides.length;
  }

  isAutoPlaying() {
    return this.isPlaying;
  }

  updateOptions(newOptions) {
    this.options = { ...this.options, ...newOptions };
    
    // Reinitialize if necessary
    if (this.isPlaying && !this.options.autoPlay) {
      this.pause();
    } else if (!this.isPlaying && this.options.autoPlay) {
      this.play();
    }
  }
}

// Utility function to initialize all slideshows on page
function initSlideshows(options = {}) {
  const slideshows = [];
  const containers = document.querySelectorAll('.slideshow-container');
  
  containers.forEach(container => {
    const slideshow = new Slideshow(container, options);
    slideshows.push(slideshow);
  });
  
  return slideshows;
}

// Auto-initialize on DOM content loaded
document.addEventListener('DOMContentLoaded', () => {
  // Only auto-init if not already initialized
  if (!window.slideshowsInitialized) {
    window.slideshows = initSlideshows();
    window.slideshowsInitialized = true;
  }
});

// Export for use in other files
if (typeof module !== 'undefined' && module.exports) {
  module.exports = { Slideshow, initSlideshows };
} else {
  window.Slideshow = Slideshow;
  window.initSlideshows = initSlideshows;
}

// Accessibility improvements
function enhanceAccessibility() {
  const slideshows = document.querySelectorAll('.slideshow-container');
  
  slideshows.forEach(container => {
    // Add ARIA attributes
    container.setAttribute('role', 'region');
    container.setAttribute('aria-label', 'Image slideshow');
    
    // Make container focusable for keyboard navigation
    if (!container.hasAttribute('tabindex')) {
      container.setAttribute('tabindex', '0');
    }
    
    // Add live region for screen readers
    const liveRegion = document.createElement('div');
    liveRegion.setAttribute('aria-live', 'polite');
    liveRegion.setAttribute('aria-atomic', 'true');
    liveRegion.className = 'sr-only';
    container.appendChild(liveRegion);
    
    // Update live region on slide change
    container.addEventListener('slideChange', (e) => {
      const { currentSlide, totalSlides } = e.detail;
      liveRegion.textContent = `Slide ${currentSlide + 1} of ${totalSlides}`;
    });
  });
}

// Run accessibility enhancements
document.addEventListener('DOMContentLoaded', enhanceAccessibility);
