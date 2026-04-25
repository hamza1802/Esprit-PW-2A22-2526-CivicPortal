<?php
/**
 * get_image.php
 * Shared BLOB image endpoint for ALL images stored in MySQL.
 *
 * Usage: <img src="get_image.php?type=profile&id=5">
 *
 * Supported types:
 *   - profile   → users.profile_pic / users.profile_pic_mime
 *   - service   → requests.attachment / requests.attachment_mime
 *   - transport → transport.vehicle_image / transport.vehicle_image_mime
 *   - program   → program.program_image / program.program_image_mime
 *
 * SECURITY:
 *   - type is validated against a whitelist
 *   - id is cast to int
 *   - No user input is interpolated into SQL (prepared statements only)
 *   - Cache headers set to allow browser caching for 1 hour
 */

require_once __DIR__ . '/Model/Database.php';

// ── Validate parameters ────────────────────────────────────────────────────
$type = $_GET['type'] ?? '';
$id   = (int)($_GET['id'] ?? 0);

// Whitelist of allowed image types and their DB mappings
$typeMap = [
    'profile' => [
        'table'      => 'users',
        'blob_col'   => 'profile_pic',
        'mime_col'   => 'profile_pic_mime',
        'id_col'     => 'id',
    ],
    'service' => [
        'table'      => 'requests',
        'blob_col'   => 'attachment',
        'mime_col'   => 'attachment_mime',
        'id_col'     => 'id',
    ],
    'transport' => [
        'table'      => 'transport',
        'blob_col'   => 'vehicle_image',
        'mime_col'   => 'vehicle_image_mime',
        'id_col'     => 'idTransport',
    ],
    'transport_type' => [
        'table'      => 'transport_type',
        'blob_col'   => 'type_image',
        'mime_col'   => 'type_image_mime',
        'id_col'     => 'idTransportType',
    ],
    'program' => [
        'table'      => 'program',
        'blob_col'   => 'program_image',
        'mime_col'   => 'program_image_mime',
        'id_col'     => 'id',
    ],
];

if (!isset($typeMap[$type]) || $id <= 0) {
    outputFallback();
    exit;
}

$config = $typeMap[$type];

// ── Query the database ─────────────────────────────────────────────────────
try {
    $pdo  = Database::getInstance()->getConnection();
    $sql  = "SELECT `{$config['blob_col']}` AS blob_data, `{$config['mime_col']}` AS mime_type 
             FROM `{$config['table']}` 
             WHERE `{$config['id_col']}` = :id 
             LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    if ($row && !empty($row['blob_data'])) {
        $mime = $row['mime_type'] ?: 'image/jpeg';

        // Validate MIME is actually an image type
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array($mime, $allowedMimes, true)) {
            $mime = 'image/jpeg';
        }

        // ── Output the image ────────────────────────────────────────────
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . strlen($row['blob_data']));
        header('Cache-Control: public, max-age=3600'); // cache 1 hour
        header('ETag: "' . md5($row['blob_data']) . '"');
        echo $row['blob_data'];
        exit;
    }
} catch (Exception $e) {
    error_log('[CivicPortal][get_image] ' . $e->getMessage());
}

// ── Fallback: 1×1 transparent PNG ──────────────────────────────────────────
outputFallback();

/**
 * Output a 1×1 transparent PNG as fallback when no image is found.
 */
function outputFallback(): void {
    header('Content-Type: image/png');
    header('Cache-Control: public, max-age=60');
    // Minimal 1×1 transparent PNG (67 bytes)
    echo base64_decode(
        'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='
    );
    exit;
}
?>
