// About Page JavaScript
// Interactive functionality for the SocialConnect About page

class AboutPageController {
  constructor() {
    this.faqItems = [];
    this.animationController = null;
    this.countersAnimated = false;
    
    this.init();
  }

  init() {
    this.setupFAQAccordion();
    this.setupSmoothScrolling();
    this.setupContactInteractions();
    this.setupNavigationHighlight();
    this.setupMobileNavigation();
    this.setupAnimationController();
    this.setupCounterAnimations();
    
    // Initialize on DOM ready
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () => this.onDOMReady());
    } else {
      this.onDOMReady();
    }
  }

  onDOMReady() {
    console.log('About page loaded successfully');
    this.animateOnScroll();
  }

  // FAQ Accordion Functionality
  setupFAQAccordion() {
    const faqQuestions = document.querySelectorAll('.faq-question');
    
    faqQuestions.forEach((question, index) => {
      this.faqItems.push({
        question: question,
        answer: question.nextElementSibling,
        isOpen: false
      });

      question.addEventListener('click', (e) => this.toggleFAQ(index));
      question.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          this.toggleFAQ(index);
        }
      });
    });
  }

  toggleFAQ(index) {
    const item = this.faqItems[index];
    if (!item) return;

    // Close other open items
    this.faqItems.forEach((otherItem, otherIndex) => {
      if (otherIndex !== index && otherItem.isOpen) {
        this.closeFAQ(otherIndex);
      }
    });

    // Toggle current item
    if (item.isOpen) {
      this.closeFAQ(index);
    } else {
      this.openFAQ(index);
    }
  }

  openFAQ(index) {
    const item = this.faqItems[index];
    if (!item) return;

    item.isOpen = true;
    item.question.setAttribute('aria-expanded', 'true');
    item.answer.classList.add('open');
    item.answer.style.display = 'block';
    
    // Smooth height animation
    const height = item.answer.scrollHeight;
    item.answer.style.height = '0px';
    item.answer.offsetHeight; // Force reflow
    item.answer.style.transition = 'height 0.3s ease';
    item.answer.style.height = height + 'px';
    
    setTimeout(() => {
      item.answer.style.height = 'auto';
    }, 300);
  }

  closeFAQ(index) {
    const item = this.faqItems[index];
    if (!item) return;

    item.isOpen = false;
    item.question.setAttribute('aria-expanded', 'false');
    
    const height = item.answer.scrollHeight;
    item.answer.style.height = height + 'px';
    item.answer.offsetHeight; // Force reflow
    item.answer.style.transition = 'height 0.3s ease';
    item.answer.style.height = '0px';
    
    setTimeout(() => {
      item.answer.classList.remove('open');
      item.answer.style.display = 'none';
      item.answer.style.height = 'auto';
    }, 300);
  }

  // Smooth Scrolling Navigation
  setupSmoothScrolling() {
    const navLinks = document.querySelectorAll('.page-nav-link, .footer-link[href^="#"]');
    
    navLinks.forEach(link => {
      link.addEventListener('click', (e) => {
        const href = link.getAttribute('href');
        if (href.startsWith('#')) {
          e.preventDefault();
          this.scrollToSection(href.substring(1));
        }
      });
    });
  }

  scrollToSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (!section) return;

    const headerHeight = document.querySelector('.header').offsetHeight;
    const pageNavHeight = document.querySelector('.page-nav').offsetHeight;
    const offset = headerHeight + pageNavHeight + 20;
    
    const targetPosition = section.offsetTop - offset;
    
    window.scrollTo({
      top: targetPosition,
      behavior: 'smooth'
    });

    // Update page navigation highlight
    this.updatePageNavHighlight(sectionId);
  }

  // Contact Information Interactions
  setupContactInteractions() {
    const contactValues = document.querySelectorAll('.contact-value[data-copy]');
    
    contactValues.forEach(contact => {
      contact.addEventListener('click', () => this.copyToClipboard(contact));
      contact.style.cursor = 'pointer';
      contact.title = 'Click to copy';
    });
  }

  async copyToClipboard(element) {
    const text = element.getAttribute('data-copy');
    const feedback = element.nextElementSibling;
    
    try {
      if (navigator.clipboard && window.isSecureContext) {
        await navigator.clipboard.writeText(text);
      } else {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.opacity = '0';
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
      }
      
      this.showCopyFeedback(feedback);
    } catch (err) {
      console.error('Failed to copy text: ', err);
      this.showCopyFeedback(feedback, 'Failed to copy');
    }
  }

  showCopyFeedback(feedbackElement, message = 'Copied to clipboard!') {
    if (!feedbackElement) return;
    
    feedbackElement.textContent = message;
    feedbackElement.classList.add('show');
    
    setTimeout(() => {
      feedbackElement.classList.remove('show');
    }, 2000);
  }

  // Navigation Highlight
  setupNavigationHighlight() {
    this.observePageSections();
  }

  observePageSections() {
    const sections = document.querySelectorAll('section[id]');
    const pageNavLinks = document.querySelectorAll('.page-nav-link');
    
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          this.updatePageNavHighlight(entry.target.id);
        }
      });
    }, {
      threshold: 0.3,
      rootMargin: '-100px 0px -50% 0px'
    });

    sections.forEach(section => observer.observe(section));
  }

  updatePageNavHighlight(activeId) {
    const pageNavLinks = document.querySelectorAll('.page-nav-link');
    
    pageNavLinks.forEach(link => {
      const href = link.getAttribute('href');
      if (href === `#${activeId}`) {
        link.style.background = 'var(--color-white)';
        link.style.color = 'var(--color-brand-purple)';
        link.style.boxShadow = 'var(--shadow-sm)';
      } else {
        link.style.background = 'transparent';
        link.style.color = 'var(--color-text-body)';
        link.style.boxShadow = 'none';
      }
    });
  }

  // Mobile Navigation
  setupMobileNavigation() {
    const navToggle = document.getElementById('navToggle');
    const navMenu = document.getElementById('navMenu');
    
    if (navToggle && navMenu) {
      navToggle.addEventListener('click', () => this.toggleMobileNav());
    }
  }

  toggleMobileNav() {
    const navToggle = document.getElementById('navToggle');
    const navMenu = document.getElementById('navMenu');
    
    const isOpen = navMenu.style.display === 'flex';
    
    if (isOpen) {
      navMenu.style.display = 'none';
      navToggle.setAttribute('aria-expanded', 'false');
    } else {
      navMenu.style.display = 'flex';
      navMenu.style.flexDirection = 'column';
      navMenu.style.position = 'absolute';
      navMenu.style.top = '100%';
      navMenu.style.left = '0';
      navMenu.style.right = '0';
      navMenu.style.background = 'var(--color-white)';
      navMenu.style.padding = 'var(--spacing-md)';
      navMenu.style.boxShadow = 'var(--shadow-lg)';
      navMenu.style.zIndex = '200';
      navToggle.setAttribute('aria-expanded', 'true');
    }
  }

  // Animation Controller Integration
  setupAnimationController() {
    // Use the generic animation controller if available
    if (window.AnimationController) {
      this.animationController = new window.AnimationController();
    }
  }

  animateOnScroll() {
    const revealElements = document.querySelectorAll('.reveal-up, .reveal-left, .reveal-right');
    
    const revealObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('revealed');
          revealObserver.unobserve(entry.target);
        }
      });
    }, {
      threshold: 0.1,
      rootMargin: '0px 0px -50px 0px'
    });

    revealElements.forEach(element => {
      revealObserver.observe(element);
    });
  }

  // Counter Animations
  setupCounterAnimations() {
    const counterElements = document.querySelectorAll('[data-count]');
    
    const counterObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting && !this.countersAnimated) {
          this.countersAnimated = true;
          this.animateCounters();
          counterObserver.disconnect();
        }
      });
    }, {
      threshold: 0.5
    });

    if (counterElements.length > 0) {
      counterObserver.observe(counterElements[0].closest('.story-stats'));
    }
  }

  animateCounters() {
    const counterElements = document.querySelectorAll('[data-count]');
    
    counterElements.forEach(element => {
      const target = parseInt(element.getAttribute('data-count'));
      const duration = 2000; // 2 seconds
      const step = target / (duration / 16); // 60fps
      let current = 0;
      
      const timer = setInterval(() => {
        current += step;
        if (current >= target) {
          current = target;
          clearInterval(timer);
        }
        
        // Format number based on size
        let displayValue;
        if (target >= 1000000) {
          displayValue = (current / 1000000).toFixed(1) + 'M';
        } else if (target >= 1000) {
          displayValue = (current / 1000).toFixed(0) + 'K';
        } else {
          displayValue = Math.floor(current).toString();
          if (target === 99) displayValue += '%';
        }
        
        element.textContent = displayValue;
      }, 16);
    });
  }

  // Utility Methods
  debounce(func, wait) {
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

  // Public API for external use
  scrollToTop() {
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  }

  openFAQByIndex(index) {
    if (this.faqItems[index] && !this.faqItems[index].isOpen) {
      this.toggleFAQ(index);
    }
  }

  closeFAQByIndex(index) {
    if (this.faqItems[index] && this.faqItems[index].isOpen) {
      this.toggleFAQ(index);
    }
  }

  closeAllFAQs() {
    this.faqItems.forEach((item, index) => {
      if (item.isOpen) {
        this.closeFAQ(index);
      }
    });
  }
}

// Initialize the About page controller
const aboutPageController = new AboutPageController();

// Make it globally accessible for debugging
window.aboutPageController = aboutPageController;

// Additional utility functions for enhanced UX
document.addEventListener('DOMContentLoaded', function() {
  // Add keyboard navigation for social links
  const socialLinks = document.querySelectorAll('.social-link');
  socialLinks.forEach(link => {
    link.addEventListener('keydown', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        window.open(link.href, '_blank', 'noopener,noreferrer');
      }
    });
  });

  // Add loading state for external links
  const externalLinks = document.querySelectorAll('a[href^="http"]');
  externalLinks.forEach(link => {
    link.addEventListener('click', function(e) {
      // Add loading indication for external links
      const originalText = link.textContent;
      link.textContent = 'Loading...';
      setTimeout(() => {
        link.textContent = originalText;
      }, 1000);
    });
  });

  // Enhanced accessibility for team member cards
  const teamMembers = document.querySelectorAll('.team-member');
  teamMembers.forEach(member => {
    member.setAttribute('role', 'article');
    member.setAttribute('tabindex', '0');
    
    member.addEventListener('keydown', function(e) {
      if (e.key === 'Enter') {
        // Focus on the first link within the team member card
        const firstLink = member.querySelector('.member-link');
        if (firstLink) {
          firstLink.focus();
        }
      }
    });
  });

  // Add progress indicator for page scrolling
  function updateScrollProgress() {
    const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
    const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
    const scrolled = (winScroll / height) * 100;
    
    // Create progress bar if it doesn't exist
    let progressBar = document.getElementById('scroll-progress');
    if (!progressBar) {
      progressBar = document.createElement('div');
      progressBar.id = 'scroll-progress';
      progressBar.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: ${scrolled}%;
        height: 3px;
        background: var(--color-brand-purple);
        z-index: 1000;
        transition: width 0.1s ease;
      `;
      document.body.appendChild(progressBar);
    } else {
      progressBar.style.width = scrolled + '%';
    }
  }

  // Throttled scroll progress update
  let scrollTimeout;
  window.addEventListener('scroll', function() {
    if (!scrollTimeout) {
      scrollTimeout = setTimeout(function() {
        updateScrollProgress();
        scrollTimeout = null;
      }, 10);
    }
  });

  // Initialize scroll progress
  updateScrollProgress();
});

// Export for potential module usage
if (typeof module !== 'undefined' && module.exports) {
  module.exports = AboutPageController;
}
