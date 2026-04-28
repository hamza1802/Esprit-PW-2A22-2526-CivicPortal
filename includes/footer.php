</main>
<footer style="padding: 3rem 5%; text-align: center; border-top: 1px solid #e0d9cc; background: var(--bg-neutral, #F0EADC);">
    <img src="View/assets/images/logo.png" alt="CivicPortal Logo" style="height: 50px; width: auto; display: block; margin: 0 auto; margin-bottom: 1.5rem;">
    <p style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; color: var(--primary-navy, #1D2A44); letter-spacing: 2px;">&copy; 2026 CivicPortal - All Rights Reserved</p>
</footer>
<script src="View/assets/js/glass-animations.js"></script>
<script src="assets/js/glass-animations.js"></script>
<script>
    // Suppress 404s for the dual-path loading strategy
    window.addEventListener('error', function(e) {
        if(e.target.tagName === 'SCRIPT' && e.target.src.includes('glass-animations.js')) {
            e.preventDefault();
        }
    }, true);
</script>
</body>
</html>
