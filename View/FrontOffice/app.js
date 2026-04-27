/**
 * app.js
 * Entry point for CivicPortal FrontOffice
 */

import controller from './controller.js';

// Global error handler for unhandled promise rejections
window.addEventListener('unhandledrejection', event => {
    console.error('Unhandled Promise Rejection:', event.reason);
    const app = document.getElementById('app');
    if (app) {
        app.innerHTML = `
            <div style="padding: 2rem; text-align: center; color: #c00;">
                <h2>Error Loading CivicPortal</h2>
                <p>${event.reason?.message || 'Unknown error occurred'}</p>
                <p style="font-size: 0.9rem; color: #999;">Please refresh the page or contact support.</p>
                <button onclick="location.reload()" style="padding: 0.5rem 1rem; margin-top: 1rem;">Refresh</button>
            </div>
        `;
    }
});

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
});
