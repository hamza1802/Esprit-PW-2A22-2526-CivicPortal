/**
 * app.js
 * Entry point for CivicPortal FrontOffice
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

    console.log('CivicPortal Citizen FrontOffice Initialized');
});

document.addEventListener('click', (e) => {
    const btn = e.target.closest('#context-toggle-btn');
    const menuItem = e.target.closest('.context-menu-item');
    const menu = document.getElementById('context-menu');

    if (btn && menu) {
        const isOpen = menu.style.display === 'block';
        menu.style.display = isOpen ? 'none' : 'block';
        return;
    }

    if (menuItem) {
        const selectedRole = menuItem.dataset.role;
        if (selectedRole === 'citizen') {
            window.location.href = '../FrontOffice/index.php';
            return;
        }
        window.location.href = `../BackOffice/index.php?role=${encodeURIComponent(selectedRole)}`;
        return;
    }

    if (menu && !e.target.closest('.context-menu-wrapper')) {
        menu.style.display = 'none';
    }
});
