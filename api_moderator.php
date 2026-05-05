<?php
/**
 * api_moderator.php — Root endpoint for AI moderation actions.
 * Provides JSON API for:
 *   - POST /api_moderator.php?action=moderate_post&id=X
 *   - POST /api_moderator.php?action=moderate_comment&id=X
 *   - POST /api_moderator.php?action=remoderate_all
 *   - GET  /api_moderator.php?action=stats
 */
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/Controller/AIModerator.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$id     = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);

// Auth check — only logged-in users can trigger moderation
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Authentication required']);
    exit;
}

switch ($action) {
    case 'moderate_post':
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid post ID']);
            exit;
        }
        $result = AIModerator::moderatePost($id);
        if ($result) {
            echo json_encode(['success' => true, 'moderation' => $result]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Moderation failed — check API key or try again']);
        }
        break;

    case 'moderate_comment':
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid comment ID']);
            exit;
        }
        $result = AIModerator::moderateComment($id);
        if ($result) {
            echo json_encode(['success' => true, 'moderation' => $result]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Moderation failed — check API key or try again']);
        }
        break;

    case 'remoderate_all':
        // Admin only
        if (($_SESSION['user_role'] ?? '') !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Admin access required']);
            exit;
        }
        $postResults    = AIModerator::remoderateAllPosts();
        $commentResults = AIModerator::remoderateAllComments();
        echo json_encode([
            'success'  => true,
            'posts'    => count($postResults),
            'comments' => count($commentResults)
        ]);
        break;

    case 'stats':
        $stats = AIModerator::getStats();
        echo json_encode(['success' => true, 'stats' => $stats]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Unknown action. Valid: moderate_post, moderate_comment, remoderate_all, stats']);
        break;
}
