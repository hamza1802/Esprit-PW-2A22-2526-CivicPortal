<?php
/**
 * glass_polish.php
 * Removes legacy inline styles from BO and FO view.js files
 * that conflict with the new glassmorphism design system.
 */

function polish($file, $replacements) {
    $content = file_get_contents($file);
    foreach ($replacements as [$from, $to]) {
        $content = str_replace($from, $to, $content);
    }
    file_put_contents($file, $content);
    echo "Polished: $file\n";
}

// ── BackOffice view.js ────────────────────────────────────────────────────────
polish('View/BackOffice/view.js', [
    // Remove old navy inline color from nav brand
    ['<div class="nav-brand" style="color:var(--primary-red);">', '<div class="nav-brand">'],
    // Remove inline style from user-role-badge
    ['<div class="user-role-badge" style="background:var(--primary-red);color:white;">', '<div class="user-role-badge">'],
    // Fix h2 tags that reset their glass styles
    ['<h2 class="reveal" style="margin:0; border:none; padding:0;">', '<h2 class="reveal" style="margin:0; padding:0;">'],
    // Back-link style: replace navy color with modern style
    ['style="font-weight:800; text-transform:uppercase; text-decoration:none; color:var(--primary-navy); font-size:0.9rem; letter-spacing:1px;"',
     'class="back-link"'],
    // Transport type photo: round corners
    ['style="width:40px; height:40px; border-radius:4px; object-fit:cover;"',
     'style="width:42px; height:42px; border-radius:50%; object-fit:cover; border:1px solid rgba(255,255,255,0.1);"'],
    // Fleet photo  
    ['style="width:48px;height:48px;border-radius:4px;object-fit:cover;"',
     'style="width:48px;height:48px;border-radius:50%;object-fit:cover;border:1px solid rgba(255,255,255,0.1);"'],
]);

// ── FrontOffice view.js ───────────────────────────────────────────────────────
polish('View/FrontOffice/view.js', [
    // Fix any remaining sharp-border inline styles on transport  
    ['border: \'1px solid #333\'', 'border: \'1px solid rgba(255,255,255,0.08)\''],
    ['background: \'#1a1a2e\'', 'background: \'var(--bg-surface)\''],
    ['background: \'#0d0d1a\'', 'background: \'var(--bg-main)\''],
    ['color: \'#e0e0ff\'', 'color: \'var(--text-primary)\''],
    ['color: \'#a0a0c0\'', 'color: \'var(--text-secondary)\''],
    ['border-radius: \'8px\'', 'border-radius: \'var(--radius-md)\''],
    ['border-radius: \'16px\'', 'border-radius: \'var(--radius-xl)\''],
]);

// ── BackOffice/index.php — remove stale style override ───────────────────────
$idx = file_get_contents('View/BackOffice/index.php');
$idx = str_replace(
    "<style>\n        /* Quick override for staff portal header styling if needed */\n        .nav-brand { color: var(--primary-red); }\n    </style>",
    '',
    $idx
);
// Also remove the closing </style> if already stripped partially
file_put_contents('View/BackOffice/index.php', $idx);
echo "Polished: View/BackOffice/index.php\n";

// ── Add back-link CSS to end of style.css (additive) ─────────────────────────
$css = file_get_contents('View/assets/css/style.css');
if (strpos($css, '.back-link') === false) {
    $css .= "\n/* Back navigation link */\n";
    $css .= ".back-link {\n";
    $css .= "    display: inline-flex;\n";
    $css .= "    align-items: center;\n";
    $css .= "    gap: 6px;\n";
    $css .= "    text-decoration: none;\n";
    $css .= "    color: var(--text-secondary);\n";
    $css .= "    font-size: 0.8rem;\n";
    $css .= "    font-weight: 700;\n";
    $css .= "    text-transform: uppercase;\n";
    $css .= "    letter-spacing: 1px;\n";
    $css .= "    padding: 0.4rem 0.85rem;\n";
    $css .= "    border-radius: var(--radius-pill);\n";
    $css .= "    border: 1px solid var(--glass-border);\n";
    $css .= "    background: var(--glass-bg);\n";
    $css .= "    backdrop-filter: blur(8px);\n";
    $css .= "    transition: var(--transition-fast);\n";
    $css .= "    margin-bottom: 1.5rem;\n";
    $css .= "}\n";
    $css .= ".back-link:hover {\n";
    $css .= "    color: var(--text-primary);\n";
    $css .= "    border-color: var(--glass-border-hover);\n";
    $css .= "    background: var(--glass-bg-hover);\n";
    $css .= "}\n";
    file_put_contents('View/assets/css/style.css', $css);
    echo "Added .back-link to style.css\n";
}

echo "\nGlass polish complete!\n";
