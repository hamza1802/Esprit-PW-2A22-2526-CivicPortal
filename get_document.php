<?php
/**
 * get_document.php
 * Serves a single supporting document file from /uploads/ by document id.
 *
 * Usage:  <a href="get_document.php?id=42" target="_blank">Open</a>
 *
 * SECURITY:
 *  - Requires an authenticated session.
 *  - Citizens may only access documents tied to their own request.
 *  - Filename is whitelisted (basename) to prevent path traversal.
 *  - Only inspect the documents.file_path stored in DB; never user-supplied paths.
 */

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/Model/AppModel.php';

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Authentication required.');
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    exit('Invalid id.');
}

try {
    $doc = AppModel::getDocumentById($id);
    if (!$doc) {
        http_response_code(404);
        exit('Document not found.');
    }

    // Permission check: citizens may only see their own request's documents.
    $request = AppModel::getRequestById((int)$doc['requestId']);
    $role    = $_SESSION['user_role'] ?? '';
    $userId  = (int)$_SESSION['user_id'];
    if ($role === 'citizen' && (!$request || (int)$request['user_id'] !== $userId)) {
        http_response_code(403);
        exit('Forbidden.');
    }

    $name = basename((string)$doc['filePath']);
    $abs  = __DIR__ . '/uploads/' . $name;
    if (!is_file($abs)) {
        http_response_code(404);
        exit('File missing on disk.');
    }

    $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $mime = match ($ext) {
        'pdf'           => 'application/pdf',
        'png'           => 'image/png',
        'jpg', 'jpeg'   => 'image/jpeg',
        'webp'          => 'image/webp',
        'gif'           => 'image/gif',
        default         => 'application/octet-stream',
    };

    header('Content-Type: '   . $mime);
    header('Content-Length: ' . filesize($abs));
    header('Content-Disposition: inline; filename="' . $name . '"');
    header('Cache-Control: private, max-age=3600');
    readfile($abs);

} catch (Throwable $e) {
    error_log('[CivicPortal][get_document] ' . $e->getMessage());
    http_response_code(500);
    exit('Server error.');
}
