/* Generic Animation and Scroll Effects */
/* Reusable animation utilities for enhanced user experience */

class AnimationController {
  constructor() {
    this.observers = new Map();
    this.animatedElements = new Set();
    this.prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    
    this.init();
  }

  init() {
    this.setupIntersectionObserver();
    this.setupScrollReveal();
    this.setupAnimationClasses();
    this.bindEvents();
  }

  setupIntersectionObserver() {
    if (!('IntersectionObserver' in window)) {
      console.warn('IntersectionObserver not supported');
      return;
    }

    // Observer for scroll reveal animations
    const revealObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting && !this.prefersReducedMotion) {
          entry.target.classList.add('revealed');
          this.animatedElements.add(entry.target);
        }
      });
    }, {
      threshold: 0.1,
      rootMargin: '0px 0px -50px 0px'
    });

    this.observers.set('reveal', revealObserver);

    // Observer for counting animations
    const countObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting && !this.animatedElements.has(entry.target)) {
          this.animateCounter(entry.target);
          this.animatedElements.add(entry.target);
        }
      });
    }, {
      threshold: 0.5
    });

    this.observers.set('count', countObserver);
  }

  setupScrollReveal() {
    // Find all elements that need scroll reveal
    const revealElements = document.querySelectorAll('.scroll-reveal, .scroll-reveal-left, .scroll-reveal-right');
    const revealObserver = this.observers.get('reveal');
    
    if (revealObserver) {
      revealElements.forEach(el => {
        revealObserver.observe(el);
      });
    }

    // Find all counter elements
    const counterElements = document.querySelectorAll('[data-target]');
    const countObserver = this.observers.get('count');
    
    if (countObserver) {
      counterElements.forEach(el => {
        countObserver.observe(el);
      });
    }
  }

  setupAnimationClasses() {
    // Add entrance animations to elements with entrance classes
    const entranceElements = document.querySelectorAll('[class*="entrance-"]');
    
    entranceElements.forEach(el => {
      if (!this.prefersReducedMotion) {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
      }
    });

    // Trigger entrance animations after page load
    window.addEventListener('load', () => {
      setTimeout(() => {
        entranceElements.forEach(el => {
          if (!this.prefersReducedMotion) {
            el.style.opacity = '1';
            el.style.transform = 'translateY(0)';
          }
        });
      }, 100);
    });
  }

  bindEvents() {
    // Listen for reduced motion preference changes
    const mediaQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
    mediaQuery.addEventListener('change', (e) => {
      this.prefersReducedMotion = e.matches;
      this.handleReducedMotionChange();
    });

    // Pause animations when tab is not visible
    document.addEventListener('visibilitychange', () => {
      if (document.hidden) {
        this.pauseAnimations();
      } else {
        this.resumeAnimations();
      }
    });
  }

  animateCounter(element) {
    const target = parseInt(element.getAttribute('data-target'), 10);
    const duration = parseInt(element.getAttribute('data-duration'), 10) || 2000;
    const startValue = parseInt(element.textContent, 10) || 0;
    
    if (isNaN(target) || this.prefersReducedMotion) {
      element.textContent = target;
      return;
    }

    const startTime = performance.now();
    const range = target - startValue;

    const updateCounter = (currentTime) => {
      const elapsed = currentTime - startTime;
      const progress = Math.min(elapsed / duration, 1);
      
      // Easing function for smooth animation
      const easeOutCubic = 1 - Math.pow(1 - progress, 3);
      const currentValue = Math.round(startValue + (range * easeOutCubic));
      
      element.textContent = currentValue;
      
      if (progress < 1) {
        requestAnimationFrame(updateCounter);
      } else {
        element.textContent = target;
      }
    };

    requestAnimationFrame(updateCounter);
  }

  // Utility methods for controlling animations
  fadeIn(element, duration = 300) {
    if (this.prefersReducedMotion) {
      element.style.opacity = '1';
      return Promise.resolve();
    }

    return new Promise(resolve => {
      element.style.opacity = '0';
      element.style.transition = `opacity ${duration}ms ease-out`;
      element.style.display = 'block';
      
      requestAnimationFrame(() => {
        element.style.opacity = '1';
        setTimeout(resolve, duration);
      });
    });
  }

  fadeOut(element, duration = 300) {
    if (this.prefersReducedMotion) {
      element.style.display = 'none';
      return Promise.resolve();
    }

    return new Promise(resolve => {
      element.style.opacity = '1';
      element.style.transition = `opacity ${duration}ms ease-in`;
      
      requestAnimationFrame(() => {
        element.style.opacity = '0';
        setTimeout(() => {
          element.style.display = 'none';
          resolve();
        }, duration);
      });
    });
  }

  slideDown(element, duration = 300) {
    if (this.prefersReducedMotion) {
      element.style.display = 'block';
      return Promise.resolve();
    }

    return new Promise(resolve => {
      element.style.overflow = 'hidden';
      element.style.height = '0';
      element.style.transition = `height ${duration}ms ease-out`;
      element.style.display = 'block';
      
      const targetHeight = element.scrollHeight + 'px';
      
      requestAnimationFrame(() => {
        element.style.height = targetHeight;
        setTimeout(() => {
          element.style.height = 'auto';
          element.style.overflow = '';
          resolve();
        }, duration);
      });
    });
  }

  slideUp(element, duration = 300) {
    if (this.prefersReducedMotion) {
      element.style.display = 'none';
      return Promise.resolve();
    }

    return new Promise(resolve => {
      element.style.overflow = 'hidden';
      element.style.height = element.scrollHeight + 'px';
      element.style.transition = `height ${duration}ms ease-in`;
      
      requestAnimationFrame(() => {
        element.style.height = '0';
        setTimeout(() => {
          element.style.display = 'none';
          element.style.height = '';
          element.style.overflow = '';
          resolve();
        }, duration);
      });
    });
  }

  // Stagger animations for multiple elements
  staggerElements(elements, delay = 100, animationClass = 'animate-fade-in-up') {
    if (this.prefersReducedMotion) return;

    elements.forEach((element, index) => {
      setTimeout(() => {
        element.classList.add(animationClass);
      }, index * delay);
    });
  }

  // Handle reduced motion preference
  handleReducedMotionChange() {
    if (this.prefersReducedMotion) {
      // Remove all animation classes and reset styles
      document.querySelectorAll('[class*="animate-"]').forEach(el => {
        el.style.transition = 'none';
        el.style.animation = 'none';
      });
      
      // Show all hidden elements immediately
      document.querySelectorAll('.scroll-reveal, .scroll-reveal-left, .scroll-reveal-right').forEach(el => {
        el.classList.add('revealed');
        el.style.opacity = '1';
        el.style.transform = 'none';
      });
    }
  }

  // Pause/resume animations for performance
  pauseAnimations() {
    document.querySelectorAll('[class*="animate-"]').forEach(el => {
      el.style.animationPlayState = 'paused';
    });
  }

  resumeAnimations() {
    document.querySelectorAll('[class*="animate-"]').forEach(el => {
      el.style.animationPlayState = 'running';
    });
  }

  // Clean up observers
  destroy() {
    this.observers.forEach(observer => {
      observer.disconnect();
    });
    this.observers.clear();
    this.animatedElements.clear();
  }
}

// Smooth scrolling utility
class SmoothScroll {
  constructor(options = {}) {
    this.options = {
      duration: 800,
      easing: 'easeInOutCubic',
      offset: 0,
      ...options
    };
    
    this.init();
  }

  init() {
    // Handle anchor links
    document.addEventListener('click', (e) => {
      const link = e.target.closest('a[href^="#"]');
      if (!link) return;

      const targetId = link.getAttribute('href').slice(1);
      if (!targetId) return;

      const target = document.getElementById(targetId);
      if (!target) return;

      e.preventDefault();
      this.scrollToElement(target);
    });
  }

  scrollToElement(element, customOffset = null) {
    const offset = customOffset !== null ? customOffset : this.options.offset;
    const targetPosition = element.getBoundingClientRect().top + window.pageYOffset - offset;
    
    this.scrollTo(targetPosition);
  }

  scrollTo(targetPosition) {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      window.scrollTo(0, targetPosition);
      return;
    }

    const startPosition = window.pageYOffset;
    const distance = targetPosition - startPosition;
    const startTime = performance.now();

    const scroll = (currentTime) => {
      const elapsed = currentTime - startTime;
      const progress = Math.min(elapsed / this.options.duration, 1);
      const ease = this.easingFunctions[this.options.easing](progress);
      
      window.scrollTo(0, startPosition + (distance * ease));
      
      if (progress < 1) {
        requestAnimationFrame(scroll);
      }
    };

    requestAnimationFrame(scroll);
  }

  easingFunctions = {
    linear: t => t,
    easeInQuad: t => t * t,
    easeOutQuad: t => t * (2 - t),
    easeInOutQuad: t => t < 0.5 ? 2 * t * t : -1 + (4 - 2 * t) * t,
    easeInCubic: t => t * t * t,
    easeOutCubic: t => (--t) * t * t + 1,
    easeInOutCubic: t => t < 0.5 ? 4 * t * t * t : (t - 1) * (2 * t - 2) * (2 * t - 2) + 1
  };
}

// Parallax scrolling utility
class ParallaxController {
  constructor() {
    this.elements = [];
    this.isScrolling = false;
    this.prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    
    if (!this.prefersReducedMotion) {
      this.init();
    }
  }

  init() {
    this.findParallaxElements();
    this.bindEvents();
    this.update();
  }

  findParallaxElements() {
    const parallaxElements = document.querySelectorAll('[data-parallax]');
    
    this.elements = Array.from(parallaxElements).map(el => ({
      element: el,
      speed: parseFloat(el.getAttribute('data-parallax')) || 0.5,
      offset: el.getBoundingClientRect().top + window.pageYOffset
    }));
  }

  bindEvents() {
    let ticking = false;
    
    window.addEventListener('scroll', () => {
      if (!ticking) {
        requestAnimationFrame(() => {
          this.update();
          ticking = false;
        });
        ticking = true;
      }
    }, { passive: true });

    // Update on resize
    window.addEventListener('resize', () => {
      this.findParallaxElements();
    });
  }

  update() {
    if (this.prefersReducedMotion) return;

    const scrollTop = window.pageYOffset;
    
    this.elements.forEach(({ element, speed, offset }) => {
      const yPos = (scrollTop - offset) * speed;
      element.style.transform = `translateY(${yPos}px)`;
    });
  }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
  // Initialize animation controller
  window.animationController = new AnimationController();
  
  // Initialize smooth scrolling
  window.smoothScroll = new SmoothScroll();
  
  // Initialize parallax (only if not reduced motion)
  if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    window.parallaxController = new ParallaxController();
  }
});

// Export for use in other files
if (typeof module !== 'undefined' && module.exports) {
  module.exports = { AnimationController, SmoothScroll, ParallaxController };
} else {
  window.AnimationController = AnimationController;
  window.SmoothScroll = SmoothScroll;
  window.ParallaxController = ParallaxController;
}

// Utility functions for common animation patterns
const AnimationUtils = {
  // Fade and slide in elements with stagger
  revealElements: (selector, staggerDelay = 100) => {
    const elements = document.querySelectorAll(selector);
    elements.forEach((el, index) => {
      setTimeout(() => {
        el.classList.add('scroll-reveal', 'revealed');
      }, index * staggerDelay);
    });
  },

  // Animate number counting
  countUp: (element, target, duration = 2000) => {
    if (window.animationController) {
      window.animationController.animateCounter(element);
    }
  },

  // Add entrance animation to element
  addEntranceAnimation: (element, animationClass = 'animate-fade-in-up', delay = 0) => {
    setTimeout(() => {
      element.classList.add(animationClass);
    }, delay);
  },

  // Check if element is in viewport
  isInViewport: (element, threshold = 0) => {
    const rect = element.getBoundingClientRect();
    const windowHeight = window.innerHeight || document.documentElement.clientHeight;
    const windowWidth = window.innerWidth || document.documentElement.clientWidth;
    
    return (
      rect.top >= -threshold &&
      rect.left >= -threshold &&
      rect.bottom <= windowHeight + threshold &&
      rect.right <= windowWidth + threshold
    );
  }
};

// Make utilities available globally
if (typeof window !== 'undefined') {
  window.AnimationUtils = AnimationUtils;
}
