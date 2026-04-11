/**
 * app.js
 * Entry point for CivicPortal BackOffice
 */

import controller from './controller.js';

// Setup Intersection Observer for Scroll Animations
const setupScrollAnimations = () => {
    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.15 // Trigger when 15% of the element is visible
    };

    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
            }
        });
    }, observerOptions);

    const revealElements = document.querySelectorAll('.reveal');
    revealElements.forEach(el => observer.observe(el));
};

// Make it globally accessible so view.js can re-trigger it
window.initScrollObserver = setupScrollAnimations;

document.addEventListener('DOMContentLoaded', () => {
    // Initialize the controller
    controller.init();

    // Link the role switcher (Staff Demo-only logic)
    const roleSwitcher = document.getElementById('demo-role-switcher');
    if (roleSwitcher) {
        roleSwitcher.addEventListener('change', (e) => {
            const newRole = e.target.value;
            // Trigger role change in controller
            controller.handleRoleChange(newRole);
        });
    }

    console.log('CivicPortal Staff BackOffice Initialized');
});
