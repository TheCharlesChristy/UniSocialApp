// Generic Smooth Scroll JavaScript
// Reusable smooth scrolling functionality for SocialConnect

class SmoothScrollController {
  constructor(options = {}) {
    this.options = {
      offset: 80, // Default offset for fixed headers
      duration: 800, // Scroll duration in milliseconds
      easing: 'easeInOutQuad', // Easing function
      updateURL: true, // Whether to update URL hash
      ...options
    };
    
    this.isScrolling = false;
    this.init();
  }

  init() {
    this.setupSmoothScrolling();
    this.setupScrollToTop();
    this.setupKeyboardNavigation();
  }

  setupSmoothScrolling() {
    // Handle all anchor links that start with #
    document.addEventListener('click', (e) => {
      const link = e.target.closest('a[href^="#"]');
      if (!link) return;

      const href = link.getAttribute('href');
      if (href === '#') return;

      e.preventDefault();
      this.scrollToTarget(href.substring(1));
    });
  }

  setupScrollToTop() {
    // Create scroll to top button if it doesn't exist
    let scrollTopBtn = document.getElementById('scroll-to-top');
    if (!scrollTopBtn) {
      scrollTopBtn = this.createScrollToTopButton();
    }

    // Show/hide scroll to top button based on scroll position
    this.handleScrollToTopVisibility();
    window.addEventListener('scroll', () => this.handleScrollToTopVisibility());

    // Handle scroll to top click
    scrollTopBtn.addEventListener('click', () => this.scrollToTop());
  }

  createScrollToTopButton() {
    const button = document.createElement('button');
    button.id = 'scroll-to-top';
    button.innerHTML = '↑';
    button.setAttribute('aria-label', 'Scroll to top');
    button.style.cssText = `
      position: fixed;
      bottom: 24px;
      right: 24px;
      width: 48px;
      height: 48px;
      border: none;
      border-radius: 50%;
      background: var(--color-brand-purple, #8B5CF6);
      color: white;
      font-size: 20px;
      font-weight: bold;
      cursor: pointer;
      opacity: 0;
      visibility: hidden;
      transition: all 0.3s ease;
      z-index: 1000;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    `;

    // Hover effects
    button.addEventListener('mouseenter', () => {
      button.style.transform = 'scale(1.1)';
      button.style.boxShadow = '0 6px 16px rgba(0, 0, 0, 0.2)';
    });

    button.addEventListener('mouseleave', () => {
      button.style.transform = 'scale(1)';
      button.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.15)';
    });

    document.body.appendChild(button);
    return button;
  }

  handleScrollToTopVisibility() {
    const scrollTopBtn = document.getElementById('scroll-to-top');
    if (!scrollTopBtn) return;

    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    const showThreshold = 300;

    if (scrollTop > showThreshold) {
      scrollTopBtn.style.opacity = '1';
      scrollTopBtn.style.visibility = 'visible';
    } else {
      scrollTopBtn.style.opacity = '0';
      scrollTopBtn.style.visibility = 'hidden';
    }
  }

  setupKeyboardNavigation() {
    // Handle keyboard navigation for smooth scrolling
    document.addEventListener('keydown', (e) => {
      // Handle Page Up/Page Down for smoother scrolling
      if (e.key === 'PageUp' || e.key === 'PageDown') {
        e.preventDefault();
        const direction = e.key === 'PageUp' ? -1 : 1;
        const distance = window.innerHeight * 0.8 * direction;
        const currentScroll = window.pageYOffset;
        
        this.smoothScrollTo(currentScroll + distance);
      }
    });
  }

  scrollToTarget(targetId) {
    const target = document.getElementById(targetId);
    if (!target) {
      console.warn(`Element with ID "${targetId}" not found`);
      return;
    }

    const targetPosition = this.calculateTargetPosition(target);
    
    if (this.options.updateURL) {
      this.updateURL(targetId);
    }
    
    this.smoothScrollTo(targetPosition);
  }

  calculateTargetPosition(target) {
    const rect = target.getBoundingClientRect();
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    const targetTop = rect.top + scrollTop;
    
    // Calculate dynamic offset based on sticky elements
    let offset = this.options.offset;
    
    // Check for sticky header
    const header = document.querySelector('.header, header');
    if (header) {
      const headerStyle = window.getComputedStyle(header);
      if (headerStyle.position === 'sticky' || headerStyle.position === 'fixed') {
        offset = Math.max(offset, header.offsetHeight + 20);
      }
    }

    // Check for sticky navigation
    const nav = document.querySelector('.page-nav, .sub-nav');
    if (nav) {
      const navStyle = window.getComputedStyle(nav);
      if (navStyle.position === 'sticky' || navStyle.position === 'fixed') {
        offset += nav.offsetHeight;
      }
    }

    return Math.max(0, targetTop - offset);
  }

  scrollToTop() {
    this.smoothScrollTo(0);
  }

  smoothScrollTo(targetPosition) {
    if (this.isScrolling) return;

    this.isScrolling = true;
    const startPosition = window.pageYOffset;
    const distance = targetPosition - startPosition;
    const startTime = Date.now();

    const animateScroll = () => {
      const elapsed = Date.now() - startTime;
      const progress = Math.min(elapsed / this.options.duration, 1);
      
      const easedProgress = this.easing[this.options.easing](progress);
      const currentPosition = startPosition + (distance * easedProgress);
      
      window.scrollTo(0, currentPosition);

      if (progress < 1) {
        requestAnimationFrame(animateScroll);
      } else {
        this.isScrolling = false;
      }
    };

    requestAnimationFrame(animateScroll);
  }

  updateURL(targetId) {
    if (!history.pushState) return;
    
    const newURL = `${window.location.pathname}${window.location.search}#${targetId}`;
    history.pushState(null, null, newURL);
  }

  // Easing functions
  easing = {
    easeInOutQuad: (t) => {
      return t < 0.5 ? 2 * t * t : -1 + (4 - 2 * t) * t;
    },
    
    easeOutCubic: (t) => {
      return 1 - Math.pow(1 - t, 3);
    },
    
    easeInOutCubic: (t) => {
      return t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;
    },
    
    easeOutQuart: (t) => {
      return 1 - Math.pow(1 - t, 4);
    },
    
    linear: (t) => {
      return t;
    }
  };

  // Public methods for external use
  scrollToElement(element) {
    if (typeof element === 'string') {
      element = document.getElementById(element);
    }
    
    if (!element) return;
    
    const targetPosition = this.calculateTargetPosition(element);
    this.smoothScrollTo(targetPosition);
  }

  setOffset(offset) {
    this.options.offset = offset;
  }

  setDuration(duration) {
    this.options.duration = duration;
  }

  setEasing(easing) {
    if (this.easing[easing]) {
      this.options.easing = easing;
    }
  }

  destroy() {
    // Remove scroll to top button
    const scrollTopBtn = document.getElementById('scroll-to-top');
    if (scrollTopBtn) {
      scrollTopBtn.remove();
    }
    
    // Remove event listeners would require storing references
    console.log('SmoothScrollController destroyed');
  }
}

// Auto-initialize smooth scrolling
document.addEventListener('DOMContentLoaded', () => {
  // Check if smooth scrolling should be disabled for users who prefer reduced motion
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  
  if (!prefersReducedMotion) {
    window.smoothScrollController = new SmoothScrollController();
  } else {
    // For users who prefer reduced motion, still provide the scroll to top button
    // but without smooth scrolling
    window.smoothScrollController = new SmoothScrollController({
      duration: 0,
      easing: 'linear'
    });
  }
});

// Handle browser back/forward buttons
window.addEventListener('popstate', () => {
  const hash = window.location.hash;
  if (hash && hash.length > 1) {
    // Delay to ensure page is ready
    setTimeout(() => {
      if (window.smoothScrollController) {
        window.smoothScrollController.scrollToTarget(hash.substring(1));
      }
    }, 100);
  }
});

// Handle initial hash on page load
window.addEventListener('load', () => {
  const hash = window.location.hash;
  if (hash && hash.length > 1) {
    // Delay to ensure all elements are rendered
    setTimeout(() => {
      if (window.smoothScrollController) {
        window.smoothScrollController.scrollToTarget(hash.substring(1));
      }
    }, 500);
  }
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
  module.exports = SmoothScrollController;
}
