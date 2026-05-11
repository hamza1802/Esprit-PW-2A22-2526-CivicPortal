/**
 * glass-animations.js
 * Vanilla JS implementation of cursor tracking glow and scroll fade-in animations
 */

class InteractiveUI {
    constructor() {
        this.initReveal();
        this.initCursorTracking();
        
        // Listen for DOM changes to re-init in case of dynamic content
        this.observeDOM();
    }

    /**
     * Entry Animations using IntersectionObserver
     */
    initReveal() {
        const animateQuery = '.editorial-card, .program-card, .program-mgmt-card, .hero-section, .form-card, .pf-detail-card, .pf-edit-form-wrap';
        document.querySelectorAll(animateQuery).forEach(el => {
            if (!el.classList.contains('reveal')) {
                el.classList.add('reveal');
            }
        });

        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.1
        };

        this.observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                    // Stop observing once revealed for one-time animation
                    // this.observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        document.querySelectorAll('.reveal').forEach(el => {
            this.observer.observe(el);
        });
    }

    /**
     * Cursor Tracking: Dynamic radial gradient via custom properties
     */
    initCursorTracking() {
        const glowElements = '.editorial-card, .program-card, .program-mgmt-card, .form-card, .hero-section, .pf-detail-card, .pf-edit-form-wrap';
        document.querySelectorAll(glowElements).forEach(el => {
            if (!el.classList.contains('glow-effect')) {
                el.classList.add('glow-effect');
            }
        });

        document.addEventListener('mousemove', (e) => {
            document.querySelectorAll('.glow-effect').forEach(card => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                card.style.setProperty('--x', `${x}px`);
                card.style.setProperty('--y', `${y}px`);
            });
        });
    }

    /**
     * Observes DOM changes to apply effects to dynamically added elements
     */
    observeDOM() {
        const targetNode = document.body;
        const config = { childList: true, subtree: true };

        const callback = (mutationsList, observer) => {
            let shouldReinit = false;
            for (const mutation of mutationsList) {
                if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                    shouldReinit = true;
                    break;
                }
            }
            
            if (shouldReinit) {
                clearTimeout(this.debounceTimer);
                this.debounceTimer = setTimeout(() => {
                    this.initReveal();
                    this.initCursorTracking();
                }, 100);
            }
        };

        const domObserver = new MutationObserver(callback);
        domObserver.observe(targetNode, config);
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => new InteractiveUI());
} else {
    new InteractiveUI();
}

/**
 * Prevent session restoration via browser back button
 */
window.addEventListener('pageshow', function(event) {
    if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {
        window.location.reload();
    }
});
