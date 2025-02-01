// script.js
// This JavaScript file handles interactive elements like a toggle menu or button animations.

// Example: Simple animation for the call-to-action button on hover
document.addEventListener('DOMContentLoaded', function() {
    const ctaButton = document.querySelector('.cta-button');
    if (ctaButton) {
        // Add an animation effect on mouse over
        ctaButton.addEventListener('mouseover', function() {
            ctaButton.style.transform = 'scale(1.05)';
        });
        // Revert the animation on mouse out
        ctaButton.addEventListener('mouseout', function() {
            ctaButton.style.transform = 'scale(1)';
        });
    }
    
    // Example: Toggle menu functionality (if you decide to add a mobile menu)
    const menuToggle = document.querySelector('.menu-toggle');
    if (menuToggle) {
        menuToggle.addEventListener('click', function() {
            const navMenu = document.querySelector('.footer-nav');
            navMenu.classList.toggle('active');
        });
    }
});
