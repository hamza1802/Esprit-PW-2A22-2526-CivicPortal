/**
 * sidebar.js
 * Mobile sidebar toggle — hamburger, backdrop, swipe gestures.
 * Runs on <768px; desktop layout renders sidebar persistently via CSS.
 */

(function () {
    'use strict';

    const toggle   = document.getElementById('menu-toggle');
    const backdrop = document.getElementById('sidebar-backdrop');
    const nav      = document.querySelector('nav');

    if (!toggle || !backdrop || !nav) return;

    function openSidebar() {
        nav.classList.add('open');
        backdrop.classList.add('visible');
        toggle.innerHTML = '<i class="bi bi-x-lg"></i>';
        toggle.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden'; // prevent body scroll while sidebar open
    }

    function closeSidebar() {
        nav.classList.remove('open');
        backdrop.classList.remove('visible');
        toggle.innerHTML = '<i class="bi bi-list"></i>';
        toggle.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    }

    // Hamburger click
    toggle.addEventListener('click', () => {
        nav.classList.contains('open') ? closeSidebar() : openSidebar();
    });

    // Backdrop tap
    backdrop.addEventListener('click', closeSidebar);

    // Close on any nav link click (event delegation — works after renderNavBar())
    nav.addEventListener('click', (e) => {
        if (e.target.closest('a[href]')) {
            closeSidebar();
        }
    });

    // Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && nav.classList.contains('open')) {
            closeSidebar();
        }
    });

    // Swipe gestures
    let touchStartX = 0;
    let touchStartY = 0;

    document.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
        touchStartY = e.changedTouches[0].screenY;
    }, { passive: true });

    document.addEventListener('touchend', (e) => {
        const dx = e.changedTouches[0].screenX - touchStartX;
        const dy = e.changedTouches[0].screenY - touchStartY;

        // Only act on predominantly horizontal swipes
        if (Math.abs(dx) < Math.abs(dy) * 1.5) return;

        if (dx > 80 && touchStartX < 50) {
            openSidebar(); // swipe right from left edge
        } else if (dx < -80 && nav.classList.contains('open')) {
            closeSidebar(); // swipe left closes sidebar
        }
    }, { passive: true });

    // On resize to desktop: reset state so sidebar stays visible
    const mq = window.matchMedia('(min-width: 768px)');
    mq.addEventListener('change', (e) => {
        if (e.matches) {
            nav.classList.remove('open');
            backdrop.classList.remove('visible');
            toggle.innerHTML = '<i class="bi bi-list"></i>';
            toggle.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        }
    });
}());
