/**
 * Generic Counter Animation System
 * Provides animated number counting effects for statistics and metrics
 * Supports accessibility and performance optimization
 */

class CounterAnimator {
    constructor() {
        this.counters = new Map();
        this.observer = null;
        this.isReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        
        this.init();
    }

    init() {
        // Set up intersection observer for performance
        this.setupIntersectionObserver();
        
        // Listen for reduced motion preference changes
        window.matchMedia('(prefers-reduced-motion: reduce)').addEventListener('change', (e) => {
            this.isReducedMotion = e.matches;
        });
    }

    setupIntersectionObserver() {
        const options = {
            threshold: 0.5,
            rootMargin: '0px 0px -50px 0px'
        };

        this.observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const element = entry.target;
                    if (!element.dataset.animated) {
                        this.animateCounter(element);
                    }
                }
            });
        }, options);
    }

    /**
     * Register counter elements for animation
     * @param {string|Element|NodeList} selector - CSS selector, element, or NodeList
     * @param {Object} options - Animation options
     */
    registerCounters(selector, options = {}) {
        let elements;
        
        if (typeof selector === 'string') {
            elements = document.querySelectorAll(selector);
        } else if (selector instanceof Element) {
            elements = [selector];
        } else if (selector instanceof NodeList) {
            elements = selector;
        } else {
            console.warn('Invalid selector provided to registerCounters');
            return;
        }

        elements.forEach(element => {
            const config = {
                duration: options.duration || 2000,
                delay: options.delay || 0,
                easing: options.easing || 'easeOutCubic',
                separator: options.separator || ',',
                prefix: options.prefix || '',
                suffix: options.suffix || '',
                decimals: options.decimals || 0,
                ...options
            };

            // Store original value and config
            const targetValue = this.parseNumber(element.textContent || element.dataset.target);
            element.dataset.target = targetValue;
            
            this.counters.set(element, config);
            
            // Start observing
            this.observer.observe(element);
            
            // Set initial value to 0 (or starting value if specified)
            const startValue = config.startValue || 0;
            element.textContent = this.formatNumber(startValue, config);
        });
    }

    /**
     * Animate a single counter element
     * @param {Element} element - The counter element to animate
     */
    animateCounter(element) {
        const config = this.counters.get(element);
        if (!config) return;

        const targetValue = parseFloat(element.dataset.target);
        const startValue = config.startValue || 0;
        
        // Mark as animated to prevent re-animation
        element.dataset.animated = 'true';
        
        // If reduced motion is preferred, just set the final value
        if (this.isReducedMotion) {
            element.textContent = this.formatNumber(targetValue, config);
            return;
        }

        // Calculate animation parameters
        const duration = config.duration;
        const delay = config.delay;
        
        setTimeout(() => {
            this.runAnimation(element, startValue, targetValue, duration, config);
        }, delay);
    }

    /**
     * Run the actual counter animation
     * @param {Element} element - The element to animate
     * @param {number} start - Starting value
     * @param {number} end - Ending value
     * @param {number} duration - Animation duration in ms
     * @param {Object} config - Animation configuration
     */
    runAnimation(element, start, end, duration, config) {
        const startTime = performance.now();
        const difference = end - start;

        const step = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Apply easing function
            const easedProgress = this.getEasingValue(progress, config.easing);
            
            // Calculate current value
            const currentValue = start + (difference * easedProgress);
            
            // Update display
            element.textContent = this.formatNumber(currentValue, config);
            
            // Continue animation if not complete
            if (progress < 1) {
                requestAnimationFrame(step);
            } else {
                // Ensure final value is exact
                element.textContent = this.formatNumber(end, config);
                
                // Trigger completion event
                element.dispatchEvent(new CustomEvent('counterComplete', {
                    detail: { finalValue: end, element: element }
                }));
            }
        };

        requestAnimationFrame(step);
    }

    /**
     * Parse number from string, handling various formats
     * @param {string} str - String to parse
     * @returns {number} Parsed number
     */
    parseNumber(str) {
        if (typeof str === 'number') return str;
        
        // Remove common separators and non-numeric characters except decimal point
        const cleaned = str.replace(/[,\s]/g, '').replace(/[^\d.-]/g, '');
        return parseFloat(cleaned) || 0;
    }

    /**
     * Format number for display
     * @param {number} value - Number to format
     * @param {Object} config - Formatting configuration
     * @returns {string} Formatted number string
     */
    formatNumber(value, config) {
        // Round to specified decimal places
        const rounded = Math.round(value * Math.pow(10, config.decimals)) / Math.pow(10, config.decimals);
        
        // Convert to string with proper decimal places
        let formatted = rounded.toFixed(config.decimals);
        
        // Add thousands separator if enabled
        if (config.separator && config.separator !== 'none') {
            const parts = formatted.split('.');
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, config.separator);
            formatted = parts.join('.');
        }
        
        // Add prefix and suffix
        return `${config.prefix}${formatted}${config.suffix}`;
    }

    /**
     * Get eased value based on easing function
     * @param {number} t - Progress (0-1)
     * @param {string} easing - Easing function name
     * @returns {number} Eased value
     */
    getEasingValue(t, easing) {
        switch (easing) {
            case 'linear':
                return t;
            case 'easeInQuad':
                return t * t;
            case 'easeOutQuad':
                return t * (2 - t);
            case 'easeInOutQuad':
                return t < 0.5 ? 2 * t * t : -1 + (4 - 2 * t) * t;
            case 'easeInCubic':
                return t * t * t;
            case 'easeOutCubic':
                return (--t) * t * t + 1;
            case 'easeInOutCubic':
                return t < 0.5 ? 4 * t * t * t : (t - 1) * (2 * t - 2) * (2 * t - 2) + 1;
            case 'easeInQuart':
                return t * t * t * t;
            case 'easeOutQuart':
                return 1 - (--t) * t * t * t;
            case 'easeInOutQuart':
                return t < 0.5 ? 8 * t * t * t * t : 1 - 8 * (--t) * t * t * t;
            default:
                return (--t) * t * t + 1; // Default to easeOutCubic
        }
    }

    /**
     * Manually trigger counter animation for specific element
     * @param {Element} element - Element to animate
     */
    triggerCounter(element) {
        if (this.counters.has(element)) {
            element.dataset.animated = 'false';
            this.animateCounter(element);
        }
    }

    /**
     * Reset all counters to their starting values
     */
    resetCounters() {
        this.counters.forEach((config, element) => {
            element.dataset.animated = 'false';
            const startValue = config.startValue || 0;
            element.textContent = this.formatNumber(startValue, config);
        });
    }

    /**
     * Destroy the counter animator and clean up
     */
    destroy() {
        if (this.observer) {
            this.observer.disconnect();
        }
        this.counters.clear();
    }
}

// Create global instance
window.CounterAnimator = new CounterAnimator();

// Auto-initialize counters with data attributes on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    // Find all elements with data-counter attribute
    const autoCounters = document.querySelectorAll('[data-counter]');
    
    autoCounters.forEach(element => {
        const config = {
            duration: parseInt(element.dataset.duration) || 2000,
            delay: parseInt(element.dataset.delay) || 0,
            easing: element.dataset.easing || 'easeOutCubic',
            separator: element.dataset.separator || ',',
            prefix: element.dataset.prefix || '',
            suffix: element.dataset.suffix || '',
            decimals: parseInt(element.dataset.decimals) || 0
        };
        
        window.CounterAnimator.registerCounters(element, config);
    });
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CounterAnimator;
}
