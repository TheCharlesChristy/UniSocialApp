/**
 * Welcome Page JavaScript
 * Handles all Welcome page specific interactions and initialization
 */

class WelcomePage {
    constructor() {
        this.isInitialized = false;
        this.statsLoaded = false;
        
        // Bind methods
        this.handleFormSubmissions = this.handleFormSubmissions.bind(this);
        this.handleNavigation = this.handleNavigation.bind(this);
        this.handleSampleContentInteraction = this.handleSampleContentInteraction.bind(this);
        
        this.init();
    }

    async init() {
        if (this.isInitialized) return;
        
        try {
            // Wait for DOM to be ready
            if (document.readyState === 'loading') {
                await new Promise(resolve => {
                    document.addEventListener('DOMContentLoaded', resolve);
                });
            }

            // Initialize all components
            await this.initializeComponents();
            
            this.isInitialized = true;
            console.log('Welcome page initialized successfully');
            
        } catch (error) {
            console.error('Error initializing welcome page:', error);
        }
    }

    async initializeComponents() {
        // Initialize statistics
        await this.initializeStats();
        
        // Initialize slideshow
        this.initializeSlideshow();
        
        // Initialize counters
        this.initializeCounters();
        
        // Initialize scroll animations
        this.initializeScrollAnimations();
        
        // Initialize form handling
        this.setupFormHandlers();
        
        // Initialize navigation
        this.setupNavigation();
        
        // Initialize sample content interactions
        this.setupSampleContentInteractions();
        
        // Initialize accessibility features
        this.setupAccessibility();
        
        // Initialize performance monitoring
        this.setupPerformanceMonitoring();
    }

    async initializeStats() {
        try {
            // Load real-time statistics from API
            if (window.SocialConnectAPI) {
                const stats = await window.SocialConnectAPI.getStats();
                this.updateStatsDisplay(stats);
                this.statsLoaded = true;
            }
        } catch (error) {
            console.warn('Failed to load real-time stats, using fallback values:', error);
            // Fallback stats are already in the HTML
        }
    }

    updateStatsDisplay(stats) {
        const statElements = {
            users: document.querySelector('[data-stat="users"]'),
            posts: document.querySelector('[data-stat="posts"]'),
            connections: document.querySelector('[data-stat="connections"]'),
            messages: document.querySelector('[data-stat="messages"]')
        };

        // Update counter target values
        Object.keys(stats).forEach(key => {
            const element = statElements[key];
            if (element && stats[key]) {
                element.dataset.target = stats[key];
                element.textContent = '0'; // Reset to 0 for animation
            }
        });
    }

    initializeSlideshow() {
        // Initialize feature slideshow if available
        if (window.GenericSlideshow) {
            const slideshowContainer = document.querySelector('.features-slideshow');
            if (slideshowContainer) {
                this.slideshow = new window.GenericSlideshow(slideshowContainer, {
                    autoPlay: true,
                    interval: 5000,
                    showControls: true,
                    showIndicators: true,
                    pauseOnHover: true,
                    accessibility: true
                });
            }
        }
    }

    initializeCounters() {
        // Initialize animated counters for statistics
        if (window.CounterAnimator) {
            const statCounters = document.querySelectorAll('.stat-number[data-counter]');
            
            statCounters.forEach(counter => {
                // Add some visual flair to the animation
                const config = {
                    duration: 2500,
                    delay: Math.random() * 500, // Stagger animations
                    easing: 'easeOutCubic',
                    separator: ',',
                    suffix: counter.dataset.suffix || ''
                };
                
                window.CounterAnimator.registerCounters(counter, config);
            });

            // Listen for counter completion to add celebration effect
            document.addEventListener('counterComplete', (e) => {
                this.addCelebrationEffect(e.detail.element);
            });
        }
    }

    initializeScrollAnimations() {
        // Initialize scroll-based animations
        if (window.ScrollAnimator) {
            // Animate sections as they come into view
            const animatedSections = document.querySelectorAll('.hero, .stats-section, .features-section, .sample-content, .testimonials-section, .final-cta');
            
            animatedSections.forEach(section => {
                window.ScrollAnimator.observeElement(section, {
                    animationType: 'fadeInUp',
                    delay: 200,
                    duration: 800
                });
            });

            // Animate individual cards with stagger effect
            const cards = document.querySelectorAll('.feature-card, .testimonial-card, .sample-post');
            cards.forEach((card, index) => {
                window.ScrollAnimator.observeElement(card, {
                    animationType: 'fadeInUp',
                    delay: index * 100,
                    duration: 600
                });
            });
        }
    }

    setupFormHandlers() {
        // Handle newsletter signup
        const newsletterForm = document.querySelector('.newsletter-form');
        if (newsletterForm) {
            newsletterForm.addEventListener('submit', this.handleNewsletterSubmit.bind(this));
        }

        // Handle quick signup forms
        const signupForms = document.querySelectorAll('.quick-signup-form');
        signupForms.forEach(form => {
            form.addEventListener('submit', this.handleQuickSignup.bind(this));
        });
    }

    setupNavigation() {
        // Handle CTA button clicks
        const ctaButtons = document.querySelectorAll('.cta-button[data-action]');
        ctaButtons.forEach(button => {
            button.addEventListener('click', this.handleCTAClick.bind(this));
        });

        // Handle smooth scrolling for anchor links
        const anchorLinks = document.querySelectorAll('a[href^="#"]');
        anchorLinks.forEach(link => {
            link.addEventListener('click', this.handleSmoothScroll.bind(this));
        });

        // Handle mobile menu toggle
        const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
        if (mobileMenuToggle) {
            mobileMenuToggle.addEventListener('click', this.toggleMobileMenu.bind(this));
        }
    }

    setupSampleContentInteractions() {
        const samplePosts = document.querySelectorAll('.sample-post');
        
        samplePosts.forEach(post => {
            // Add hover effects for sample posts
            post.addEventListener('mouseenter', this.handleSamplePostHover.bind(this));
            post.addEventListener('mouseleave', this.handleSamplePostLeave.bind(this));
            
            // Handle sample interaction buttons
            const likeBtn = post.querySelector('.sample-like-btn');
            const shareBtn = post.querySelector('.sample-share-btn');
            
            if (likeBtn) {
                likeBtn.addEventListener('click', this.handleSampleLike.bind(this));
            }
            
            if (shareBtn) {
                shareBtn.addEventListener('click', this.handleSampleShare.bind(this));
            }
        });
    }

    setupAccessibility() {
        // Setup keyboard navigation
        document.addEventListener('keydown', this.handleKeyboardNavigation.bind(this));
        
        // Setup focus management
        this.setupFocusManagement();
        
        // Setup screen reader announcements
        this.setupScreenReaderSupport();
    }

    setupPerformanceMonitoring() {
        // Monitor performance metrics
        if ('performance' in window) {
            window.addEventListener('load', () => {
                setTimeout(() => {
                    this.logPerformanceMetrics();
                }, 1000);
            });
        }
    }

    // Event Handlers
    async handleNewsletterSubmit(e) {
        e.preventDefault();
        
        const form = e.target;
        const emailInput = form.querySelector('input[type="email"]');
        const submitBtn = form.querySelector('button[type="submit"]');
        
        if (!this.validateEmail(emailInput.value)) {
            this.showMessage('Please enter a valid email address', 'error');
            return;
        }

        // Show loading state
        this.setButtonLoading(submitBtn, true);
        
        try {
            // Submit to API
            if (window.SocialConnectAPI) {
                await window.SocialConnectAPI.subscribeNewsletter(emailInput.value);
            }
            
            this.showMessage('Thank you for subscribing! Check your email for confirmation.', 'success');
            form.reset();
            
        } catch (error) {
            console.error('Newsletter subscription error:', error);
            this.showMessage('Something went wrong. Please try again later.', 'error');
        } finally {
            this.setButtonLoading(submitBtn, false);
        }
    }

    handleQuickSignup(e) {
        e.preventDefault();
        
        // For demo purposes, show modal or redirect to signup page
        this.showSignupModal();
    }

    handleCTAClick(e) {
        const action = e.target.dataset.action;
        
        switch (action) {
            case 'signup':
                this.navigateToSignup();
                break;
            case 'login':
                this.navigateToLogin();
                break;
            case 'demo':
                this.startDemo();
                break;
            case 'learn-more':
                this.scrollToFeatures();
                break;
            default:
                console.warn('Unknown CTA action:', action);
        }
    }

    handleSmoothScroll(e) {
        e.preventDefault();
        
        const targetId = e.target.getAttribute('href').substring(1);
        const targetElement = document.getElementById(targetId);
        
        if (targetElement) {
            targetElement.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    }

    handleSamplePostHover(e) {
        const post = e.target.closest('.sample-post');
        post.classList.add('hovered');
        
        // Add subtle animation
        if (post.style.transform !== 'translateY(-2px)') {
            post.style.transform = 'translateY(-2px)';
            post.style.boxShadow = '0 8px 25px rgba(0,0,0,0.15)';
        }
    }

    handleSamplePostLeave(e) {
        const post = e.target.closest('.sample-post');
        post.classList.remove('hovered');
        
        // Reset animation
        post.style.transform = '';
        post.style.boxShadow = '';
    }

    handleSampleLike(e) {
        e.preventDefault();
        
        const button = e.target.closest('.sample-like-btn');
        const likeCount = button.querySelector('.like-count');
        
        // Animate like interaction
        button.classList.add('liked');
        
        // Increment count for demo
        if (likeCount) {
            const currentCount = parseInt(likeCount.textContent);
            likeCount.textContent = currentCount + 1;
        }
        
        // Add heart animation
        this.addHeartAnimation(button);
        
        // Show tooltip
        this.showTooltip(button, 'Sign up to like posts!');
    }

    handleSampleShare(e) {
        e.preventDefault();
        
        const button = e.target.closest('.sample-share-btn');
        
        // Show share options or signup prompt
        this.showShareModal(button);
    }

    handleKeyboardNavigation(e) {
        // Handle escape key to close modals
        if (e.key === 'Escape') {
            this.closeAllModals();
        }
        
        // Handle tab navigation enhancement
        if (e.key === 'Tab') {
            this.enhanceTabNavigation(e);
        }
    }

    // Utility Methods
    validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    setButtonLoading(button, loading) {
        if (loading) {
            button.disabled = true;
            button.dataset.originalText = button.textContent;
            button.innerHTML = '<span class="loading-spinner"></span> Loading...';
            button.classList.add('loading');
        } else {
            button.disabled = false;
            button.textContent = button.dataset.originalText || 'Submit';
            button.classList.remove('loading');
        }
    }

    showMessage(message, type = 'info') {
        // Create and show toast message
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;
        
        document.body.appendChild(toast);
        
        // Trigger animation
        setTimeout(() => toast.classList.add('show'), 100);
        
        // Auto remove
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }

    addCelebrationEffect(element) {
        // Add celebration animation to completed counters
        element.style.animation = 'pulse 0.6s ease-in-out';
        
        setTimeout(() => {
            element.style.animation = '';
        }, 600);
    }

    addHeartAnimation(button) {
        const heart = document.createElement('div');
        heart.className = 'floating-heart';
        heart.innerHTML = '❤️';
        
        button.appendChild(heart);
        
        setTimeout(() => heart.remove(), 1000);
    }

    showTooltip(element, message) {
        const tooltip = document.createElement('div');
        tooltip.className = 'tooltip';
        tooltip.textContent = message;
        
        element.appendChild(tooltip);
        
        setTimeout(() => tooltip.remove(), 3000);
    }

    // Navigation Methods
    navigateToSignup() {
        // In a real app, this would navigate to signup page
        window.location.href = '/signup';
    }

    navigateToLogin() {
        // In a real app, this would navigate to login page
        window.location.href = '/login';
    }

    startDemo() {
        // Show demo modal or start guided tour
        this.showDemoModal();
    }

    scrollToFeatures() {
        const featuresSection = document.querySelector('.features-section');
        if (featuresSection) {
            featuresSection.scrollIntoView({ behavior: 'smooth' });
        }
    }

    // Modal Methods
    showSignupModal() {
        // Create and show signup modal
        console.log('Showing signup modal...');
        // Implementation would depend on modal system
    }

    showShareModal(button) {
        // Show share options
        this.showTooltip(button, 'Sign up to share posts!');
    }

    showDemoModal() {
        // Show demo modal
        console.log('Starting demo...');
        // Implementation would show interactive demo
    }

    closeAllModals() {
        // Close any open modals
        const modals = document.querySelectorAll('.modal.show');
        modals.forEach(modal => modal.classList.remove('show'));
    }

    // Accessibility Methods
    setupFocusManagement() {
        // Enhance focus visibility
        document.addEventListener('focusin', (e) => {
            if (e.target.matches('button, a, input, select, textarea')) {
                e.target.classList.add('keyboard-focus');
            }
        });

        document.addEventListener('focusout', (e) => {
            e.target.classList.remove('keyboard-focus');
        });
    }

    setupScreenReaderSupport() {
        // Add live region for announcements
        const liveRegion = document.createElement('div');
        liveRegion.setAttribute('aria-live', 'polite');
        liveRegion.setAttribute('aria-atomic', 'true');
        liveRegion.className = 'sr-only';
        liveRegion.id = 'live-announcements';
        
        document.body.appendChild(liveRegion);
    }

    enhanceTabNavigation(e) {
        // Skip to main content functionality
        if (e.target.classList.contains('skip-link')) {
            e.preventDefault();
            const main = document.querySelector('main') || document.querySelector('.main-content');
            if (main) {
                main.focus();
            }
        }
    }

    // Performance Methods
    logPerformanceMetrics() {
        if (!('performance' in window)) return;
        
        const perfData = performance.getEntriesByType('navigation')[0];
        const metrics = {
            loadTime: perfData.loadEventEnd - perfData.loadEventStart,
            domContentLoaded: perfData.domContentLoadedEventEnd - perfData.domContentLoadedEventStart,
            firstPaint: performance.getEntriesByType('paint').find(entry => entry.name === 'first-paint')?.startTime,
            largestContentfulPaint: performance.getEntriesByType('largest-contentful-paint')[0]?.startTime
        };
        
        console.log('Welcome Page Performance Metrics:', metrics);
        
        // In production, send to analytics
        // analytics.track('page_performance', metrics);
    }

    // Public API Methods
    refresh() {
        // Refresh page data
        this.initializeStats();
    }

    destroy() {
        // Clean up event listeners and resources
        if (this.slideshow) {
            this.slideshow.destroy();
        }
        
        // Remove global event listeners
        document.removeEventListener('keydown', this.handleKeyboardNavigation);
        
        this.isInitialized = false;
    }
}

// Initialize welcome page when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.welcomePage = new WelcomePage();
});

// Expose for debugging in development
if (typeof window !== 'undefined') {
    window.WelcomePage = WelcomePage;
}
