<?php
require_once __DIR__ . '/../Model/Database.php';
$db = Database::getInstance()->getConnection();
$tables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
echo "=== Tables in civicportal ===\n";
foreach ($tables as $t) echo "  - $t\n";

// Check if forum tables exist
$hasPosts = in_array('forum_posts', $tables);
$hasComments = in_array('forum_comments', $tables);
$hasComplaints = in_array('complaints', $tables);

echo "\nforum_posts: " . ($hasPosts ? "EXISTS" : "MISSING") . "\n";
echo "forum_comments: " . ($hasComments ? "EXISTS" : "MISSING") . "\n";
echo "complaints: " . ($hasComplaints ? "EXISTS (needs DROP)" : "ALREADY GONE") . "\n";

// If forum tables missing, create them
if (!$hasPosts) {
    echo "\nCreating forum_posts...\n";
    $db->exec("CREATE TABLE IF NOT EXISTS `forum_posts` (
        `post_id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `title` varchar(255) NOT NULL,
        `content` text NOT NULL,
        `category` varchar(100) DEFAULT NULL,
        `status` enum('open','closed','pinned') NOT NULL DEFAULT 'open',
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`post_id`),
        KEY `user_id` (`user_id`),
        CONSTRAINT `forum_posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
    echo "  Done.\n";
}

if (!$hasComments) {
    echo "Creating forum_comments...\n";
    $db->exec("CREATE TABLE IF NOT EXISTS `forum_comments` (
        `comment_id` int(11) NOT NULL AUTO_INCREMENT,
        `post_id` int(11) NOT NULL,
        `user_id` int(11) NOT NULL,
        `content` text NOT NULL,
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`comment_id`),
        KEY `post_id` (`post_id`),
        KEY `user_id` (`user_id`),
        CONSTRAINT `forum_comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `forum_posts` (`post_id`) ON DELETE CASCADE,
        CONSTRAINT `forum_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
    echo "  Done.\n";
}

// Drop complaints table
if ($hasComplaints) {
    echo "\nDropping complaints table...\n";
    $db->exec("DROP TABLE IF EXISTS `complaints`");
    echo "  Done.\n";
}

echo "\n=== Migration complete ===\n";
?>
