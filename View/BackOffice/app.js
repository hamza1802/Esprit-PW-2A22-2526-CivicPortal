/**
 * app.js
 * Entry point for CivicPortal BackOffice
 */
import controller from './controller.js?v=3';

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

document.addEventListener('DOMContentLoaded', async () => {
    try {
        await controller.init();
    } catch (err) {
        console.error('[CivicPortal] Init failed:', err);
        const app = document.getElementById('app');
        if (app) {
            app.innerHTML = `<div style="padding:2rem;color:red;font-family:monospace;">
                <strong>Staff Portal failed to load</strong><br>${err.message}
            </div>`;
        }
    }
});
