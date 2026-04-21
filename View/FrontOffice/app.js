/**
 * app.js
 * Entry point for CivicPortal FrontOffice
 */

import controller from './controller.js';

// ── Cursor-tracking spotlight ────────────────────────────────────────────────
const initCursorGradient = () => {
    const glow = document.createElement('div');
    glow.className = 'cursor-glow';
    document.body.appendChild(glow);

    let tx = window.innerWidth / 2;
    let ty = window.innerHeight / 2;
    let cx = tx, cy = ty;

    document.addEventListener('mousemove', (e) => {
        tx = e.clientX;
        ty = e.clientY;
    });

    const lerp = (a, b, t) => a + (b - a) * t;

    const tick = () => {
        cx = lerp(cx, tx, 0.07);
        cy = lerp(cy, ty, 0.07);

        document.documentElement.style.setProperty('--cursor-x', `${cx}px`);
        document.documentElement.style.setProperty('--cursor-y', `${cy}px`);

        glow.style.left = `${cx}px`;
        glow.style.top  = `${cy}px`;

        requestAnimationFrame(tick);
    };

    tick();
};

// ── Intersection Observer ────────────────────────────────────────────────────
const setupScrollAnimations = () => {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) entry.target.classList.add('active');
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
};

window.initScrollObserver = setupScrollAnimations;

// ── Bootstrap ────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    initCursorGradient();
    controller.init();
    console.log('%cCivicPortal Citizen FrontOffice v2 — Glass UI', 'color:#22d3ee;font-weight:bold;font-size:12px;');
});
