<?php
/**
 * run_moderation.php — One-time script to batch-moderate all existing posts and comments.
 * Run from CLI: php run_moderation.php
 */
require_once __DIR__ . '/Controller/AIModerator.php';

echo "=== CivicPortal AI Moderator — Batch Run ===" . PHP_EOL . PHP_EOL;

echo "Moderating all posts..." . PHP_EOL;
$postResults = AIModerator::remoderateAllPosts();
foreach ($postResults as $postId => $result) {
    if ($result) {
        echo "  Post #$postId: flag={$result['flag']}, urgency={$result['urgency']}, reason={$result['reason']}" . PHP_EOL;
    } else {
        echo "  Post #$postId: FAILED" . PHP_EOL;
    }
}

echo PHP_EOL . "Moderating all comments..." . PHP_EOL;
$commentResults = AIModerator::remoderateAllComments();
foreach ($commentResults as $commentId => $result) {
    if ($result) {
        echo "  Comment #$commentId: flag={$result['flag']}, urgency={$result['urgency']}" . PHP_EOL;
    } else {
        echo "  Comment #$commentId: FAILED" . PHP_EOL;
    }
}

echo PHP_EOL . "Done! Moderated " . count($postResults) . " posts and " . count($commentResults) . " comments." . PHP_EOL;
