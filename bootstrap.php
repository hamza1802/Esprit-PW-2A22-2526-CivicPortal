<?php
/**
 * bootstrap.php
 * Centralized application initialization.
 * Include this FIRST in every PHP entry point (before any output or session_start).
 *
 * SECURITY — what this enforces:
 *  • display_errors = 0  → PHP errors are logged server-side, never shown to users.
 *  • HttpOnly cookie     → JavaScript cannot read the session cookie; blocks XSS session-theft.
 *  • SameSite = Lax      → Browser only sends the cookie on same-site navigations, not on
 *                          cross-origin form POSTs — primary CSRF mitigation for the JSON API.
 *  • Secure = (auto)     → Forces HTTPS-only cookie transmission in production.
 *  • session_regenerate  → Called on login (in UserController), not here, to avoid invalidating
 *                          legitimate guest sessions. Bootstrap only configures the cookie jar.
 */

// ── Error display ──────────────────────────────────────────────────────────────
ini_set('display_errors', '0');
ini_set('log_errors',     '1');
error_reporting(E_ALL);

// ── Production flag ────────────────────────────────────────────────────────────
// Set APP_ENV=production in your web server config for HTTPS-only cookies.
// In XAMPP local development this defaults to 'development'.
if (!defined('APP_ENV')) {
    define('APP_ENV', getenv('APP_ENV') ?: 'development');
}
if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', APP_ENV === 'development');
}

// ── Secure session cookie configuration ───────────────────────────────────────
// Must be called BEFORE session_start().
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,                        // session ends when browser closes
        'path'     => '/',
        'domain'   => '',                       // current domain only — no subdomain leakage
        'secure'   => (APP_ENV === 'production'), // HTTPS-only in production
        'httponly' => true,                     // blocks JS document.cookie access
        'samesite' => 'Lax',                    // CSRF mitigation without breaking GET links
    ]);
    session_start();
}

// ── CSRF token ─────────────────────────────────────────────────────────────────
// Lazily initialised once per session. Available globally as $_SESSION['csrf_token'].
// Validate with: hash_equals($_SESSION['csrf_token'], $submittedToken)
// hash_equals() is timing-safe, preventing timing-oracle attacks.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
