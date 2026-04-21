<?php
/**
 * Fix BackOffice JS files where transport methods got appended OUTSIDE the object literal.
 * Pattern: the original object closed with `}\n};` then transport methods followed, then another `};\nexport default X;`
 * Fix: remove the first `};` so transport methods are inside the object.
 */

// --- Fix view.js ---
$file = 'View/BackOffice/view.js';
$content = file_get_contents($file);

// The problem: after renderProgramForm's closing `}` there's `};` that closes the object,
// then transport methods follow outside, with their own `};` and `export default view;`
// We need to remove the FIRST `};` after renderProgramForm and keep the transport block's `};`

// Find the pattern: `    }\r\n};\r\n\r\n    /* ===` (the premature close before TRANSPORT)
$content = preg_replace(
    '/(\s+this\.triggerObserver\(\);\r?\n\s+\})\r?\n\};\r?\n(\r?\n\s+\/\* =+\r?\n\s+TRANSPORT)/',
    "$1,\n$2",
    $content
);

// Also remove the duplicate `export default view;` if present
// The file should end with `};\nexport default view;` only once
$count = substr_count($content, 'export default view;');
if ($count > 1) {
    // Remove all but the last occurrence
    $pos = strrpos($content, 'export default view;');
    $before = substr($content, 0, $pos);
    $before = str_replace('export default view;', '', $before);
    $content = $before . substr($content, $pos);
}

file_put_contents($file, $content);
echo "Fixed $file\n";

// --- Fix controller.js ---
$file = 'View/BackOffice/controller.js';
$content = file_get_contents($file);

// Same pattern: object closes prematurely with `};\n` before transport handler methods
$content = preg_replace(
    '/(\s+\})\r?\n\};\r?\n(\r?\n\s+async handleTransportTypeSave)/',
    "$1,\n$2",
    $content
);

$count = substr_count($content, 'export default controller;');
if ($count > 1) {
    $pos = strrpos($content, 'export default controller;');
    $before = substr($content, 0, $pos);
    $before = str_replace('export default controller;', '', $before);
    $content = $before . substr($content, $pos);
}

file_put_contents($file, $content);
echo "Fixed $file\n";

// --- Fix model.js ---
$file = 'View/BackOffice/model.js';
$content = file_get_contents($file);

// Check if transport methods are outside the object
$content = preg_replace(
    '/(\s+\})\r?\n\};\r?\n(\r?\n\s+\/\/ --- Transport API ---)/',
    "$1,\n$2",
    $content
);

$count = substr_count($content, 'export default model;');
if ($count > 1) {
    $pos = strrpos($content, 'export default model;');
    $before = substr($content, 0, $pos);
    $before = str_replace('export default model;', '', $before);
    $content = $before . substr($content, $pos);
}

file_put_contents($file, $content);
echo "Fixed $file\n";

echo "\nDone! All BackOffice JS files fixed.\n";
