/**
 * backoffice-ux.js
 * Premium UI enhancements for the BackOffice
 */

const ux = {
    init() {
        this.staggerGridItems();
        this.observePageChanges();
    },

    /**
     * Stagger the entrance of grid items for a premium feel
     */
    staggerGridItems() {
        const items = document.querySelectorAll('.editorial-card, .program-mgmt-card, tr');
        items.forEach((item, index) => {
            if (!item.classList.contains('reveal')) {
                item.classList.add('reveal');
            }
            // Delay based on index, capped at 1s
            const delay = Math.min(index * 50, 1000);
            item.style.transitionDelay = `${delay}ms`;
            
            // Trigger active state
            setTimeout(() => {
                item.classList.add('active');
            }, 100);
        });
    },

    /**
     * Re-run animations when the hash changes (SPA navigation)
     */
    observePageChanges() {
        window.addEventListener('hashchange', () => {
            // Small delay to let the view.js render the new content
            setTimeout(() => this.staggerGridItems(), 200);
        });
    }
};

document.addEventListener('DOMContentLoaded', () => ux.init());
