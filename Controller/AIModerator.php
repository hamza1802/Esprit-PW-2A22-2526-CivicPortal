<?php
/**
 * AIModerator.php — Controller/AIModerator.php
 * AI-powered content moderator using Groq (LLaMA 3.1).
 * Analyzes forum posts and comments for:
 *   - Explicit / inappropriate content
 *   - Urgency of civic feedback
 *   - Sentiment / tone
 * Returns structured moderation results that are stored alongside content.
 */

require_once __DIR__ . '/../Model/Database.php';

class AIModerator {

    /**
     * Load the Groq API key from .env or .apikey
     */
    private static function getApiKey(): string {
        // Try .env first
        $envPath = __DIR__ . '/../.env';
        if (file_exists($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if (!$line || strpos($line, '#') === 0 || strpos($line, '=') === false) continue;
                list($name, $value) = explode('=', $line, 2);
                if (trim($name) === 'GROQ_API_KEY') return trim($value);
            }
        }
        // Fallback to .apikey
        $apikeyPath = __DIR__ . '/../.apikey';
        if (file_exists($apikeyPath)) {
            return trim(file_get_contents($apikeyPath));
        }
        return '';
    }

    /**
     * Send a moderation request to Groq and return structured JSON.
     */
    private static function callGroq(string $contentToModerate, string $contentType = 'post'): ?array {
        $apiKey = self::getApiKey();
        if (empty($apiKey)) {
            error_log('AIModerator: No API key configured');
            return null;
        }

        $systemPrompt = <<<PROMPT
You are a content moderator for CivicPortal, a civic engagement platform where citizens discuss community issues with their local government.

Analyze the following {$contentType} and return a JSON object with exactly these fields:

1. "flag": One of "clean", "flagged", or "review"
   - "clean": Content is appropriate and constructive
   - "review": Content may be borderline or ambiguous — needs human review
   - "flagged": Content contains explicit language, hate speech, threats, harassment, spam, or severely inappropriate material

2. "urgency": One of "low", "medium", "high", or "critical"
   - "low": General discussion, opinions, or casual conversation
   - "medium": Feedback that warrants attention but is not time-sensitive
   - "high": Reports of ongoing issues affecting public safety or infrastructure
   - "critical": Emergencies, imminent safety threats, or urgent infrastructure failures

3. "reason": A brief, concise explanation (1-2 sentences) of why you assigned that flag and urgency level. Be specific.

Respond with ONLY valid JSON. No markdown, no explanation outside the JSON.
PROMPT;

        $url = 'https://api.groq.com/openai/v1/chat/completions';
        $data = [
            'model'       => 'llama-3.1-8b-instant',
            'messages'    => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user',   'content' => $contentToModerate]
            ],
            'max_tokens'      => 200,
            'temperature'     => 0.1,
            'response_format' => ['type' => 'json_object']
        ];

        $options = [
            'http' => [
                'header'        => "Content-type: application/json\r\nAuthorization: Bearer $apiKey\r\n",
                'method'        => 'POST',
                'content'       => json_encode($data),
                'ignore_errors' => true,
                'timeout'       => 10
            ]
        ];

        $context  = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            error_log('AIModerator: Failed to connect to Groq API');
            return null;
        }

        // Check HTTP status
        preg_match('{HTTP\/\S*\s(\d{3})}', $http_response_header[0], $match);
        $status = (int)($match[1] ?? 500);

        if ($status !== 200) {
            error_log("AIModerator: Groq API returned status $status — $response");
            return null;
        }

        $result = json_decode($response, true);
        $content = $result['choices'][0]['message']['content'] ?? null;
        if (!$content) {
            error_log('AIModerator: Empty response from Groq');
            return null;
        }

        $parsed = json_decode($content, true);
        if (!$parsed || !isset($parsed['flag']) || !isset($parsed['urgency'])) {
            error_log("AIModerator: Could not parse Groq response — $content");
            return null;
        }

        // Validate enum values
        $validFlags    = ['clean', 'flagged', 'review'];
        $validUrgency  = ['low', 'medium', 'high', 'critical'];

        return [
            'flag'    => in_array($parsed['flag'], $validFlags)   ? $parsed['flag']    : 'review',
            'urgency' => in_array($parsed['urgency'], $validUrgency) ? $parsed['urgency'] : 'low',
            'reason'  => substr($parsed['reason'] ?? 'No reason provided.', 0, 500)
        ];
    }

    /**
     * Moderate a forum post (title + content combined).
     * Updates the DB record directly.
     * Returns the moderation result array or null on failure.
     */
    public static function moderatePost(int $postId): ?array {
        $db = Database::getInstance()->getConnection();

        // Fetch the post content
        $stmt = $db->prepare("SELECT title, content FROM forum_posts WHERE post_id = ?");
        $stmt->execute([$postId]);
        $post = $stmt->fetch();
        if (!$post) return null;

        $textToModerate = "Post Title: {$post['title']}\n\nPost Content: {$post['content']}";
        $result = self::callGroq($textToModerate, 'forum post');

        if ($result) {
            $update = $db->prepare(
                "UPDATE forum_posts SET ai_flag = ?, ai_urgency = ?, ai_reason = ? WHERE post_id = ?"
            );
            $update->execute([$result['flag'], $result['urgency'], $result['reason'], $postId]);
        }

        return $result;
    }

    /**
     * Moderate a forum comment.
     * Updates the DB record directly.
     * Returns the moderation result array or null on failure.
     */
    public static function moderateComment(int $commentId): ?array {
        $db = Database::getInstance()->getConnection();

        // Fetch the comment content
        $stmt = $db->prepare("SELECT content FROM forum_comments WHERE comment_id = ?");
        $stmt->execute([$commentId]);
        $comment = $stmt->fetch();
        if (!$comment) return null;

        $result = self::callGroq($comment['content'], 'forum comment');

        if ($result) {
            $update = $db->prepare(
                "UPDATE forum_comments SET ai_flag = ?, ai_urgency = ?, ai_reason = ? WHERE comment_id = ?"
            );
            $update->execute([$result['flag'], $result['urgency'], $result['reason'], $commentId]);
        }

        return $result;
    }

    /**
     * Re-moderate all posts (admin batch action).
     */
    public static function remoderateAllPosts(): array {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT post_id FROM forum_posts ORDER BY post_id ASC");
        $results = [];
        while ($row = $stmt->fetch()) {
            $result = self::moderatePost($row['post_id']);
            $results[$row['post_id']] = $result;
            // Small sleep to respect rate limits
            usleep(300000); // 300ms
        }
        return $results;
    }

    /**
     * Re-moderate all comments (admin batch action).
     */
    public static function remoderateAllComments(): array {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT comment_id FROM forum_comments ORDER BY comment_id ASC");
        $results = [];
        while ($row = $stmt->fetch()) {
            $result = self::moderateComment($row['comment_id']);
            $results[$row['comment_id']] = $result;
            usleep(300000);
        }
        return $results;
    }

    /**
     * Get moderation stats for dashboard.
     */
    public static function getStats(): array {
        $db = Database::getInstance()->getConnection();

        $postStats = $db->query(
            "SELECT ai_flag, COUNT(*) as count FROM forum_posts GROUP BY ai_flag"
        )->fetchAll();

        $commentStats = $db->query(
            "SELECT ai_flag, COUNT(*) as count FROM forum_comments GROUP BY ai_flag"
        )->fetchAll();

        $urgencyStats = $db->query(
            "SELECT ai_urgency, COUNT(*) as count FROM forum_posts WHERE ai_urgency IN ('high','critical') GROUP BY ai_urgency"
        )->fetchAll();

        $flaggedPosts = $db->query(
            "SELECT fp.post_id, fp.title, fp.ai_flag, fp.ai_urgency, fp.ai_reason, u.username AS author_name, fp.created_at
             FROM forum_posts fp
             JOIN users u ON fp.user_id = u.id
             WHERE fp.ai_flag IN ('flagged','review')
             ORDER BY FIELD(fp.ai_flag, 'flagged', 'review'), fp.created_at DESC"
        )->fetchAll();

        $flaggedComments = $db->query(
            "SELECT fc.comment_id, fc.content, fc.ai_flag, fc.ai_urgency, fc.ai_reason, u.username AS author_name, fc.created_at, fp.title AS post_title
             FROM forum_comments fc
             JOIN users u ON fc.user_id = u.id
             JOIN forum_posts fp ON fc.post_id = fp.post_id
             WHERE fc.ai_flag IN ('flagged','review')
             ORDER BY FIELD(fc.ai_flag, 'flagged', 'review'), fc.created_at DESC"
        )->fetchAll();

        return [
            'post_flags'       => $postStats,
            'comment_flags'    => $commentStats,
            'urgency_alerts'   => $urgencyStats,
            'flagged_posts'    => $flaggedPosts,
            'flagged_comments' => $flaggedComments
        ];
    }
}
?>
