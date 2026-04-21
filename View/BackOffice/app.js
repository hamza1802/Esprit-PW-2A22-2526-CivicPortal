/**
 * app.js
 * Entry point for CivicPortal BackOffice
 */

import controller from './controller.js';

// ── Cursor-tracking spotlight ────────────────────────────────────────────────
const initCursorGradient = () => {
    // Inject the cursor glow div
    const glow = document.createElement('div');
    glow.className = 'cursor-glow';
    document.body.appendChild(glow);

    let rafId = null;
    let tx = window.innerWidth / 2;
    let ty = window.innerHeight / 2;
    let cx = tx, cy = ty;

    document.addEventListener('mousemove', (e) => {
        tx = e.clientX;
        ty = e.clientY;
    });

    const lerp = (a, b, t) => a + (b - a) * t;

    const tick = () => {
        // Smooth lag follow
        cx = lerp(cx, tx, 0.08);
        cy = lerp(cy, ty, 0.08);

        document.documentElement.style.setProperty('--cursor-x', `${cx}px`);
        document.documentElement.style.setProperty('--cursor-y', `${cy}px`);

        glow.style.left = `${cx}px`;
        glow.style.top  = `${cy}px`;

        rafId = requestAnimationFrame(tick);
    };

    tick();
};

// ── Intersection Observer for reveal animations ──────────────────────────────
const setupScrollAnimations = () => {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
};

// Make globally accessible so view.js can re-trigger after DOM injection
window.initScrollObserver = setupScrollAnimations;

// ── Bootstrap ────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    initCursorGradient();
    controller.init();

    // Role switcher (Staff Demo)
    const roleSwitcher = document.getElementById('demo-role-switcher');
    if (roleSwitcher) {
        roleSwitcher.addEventListener('change', (e) => {
            controller.handleRoleChange(e.target.value);
        });
    }

    console.log('%cCivicPortal Staff BackOffice v2 — Glass UI', 'color:#6366f1;font-weight:bold;font-size:12px;');
});
