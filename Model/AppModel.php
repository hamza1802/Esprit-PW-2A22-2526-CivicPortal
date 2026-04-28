<?php
/**
 * AppModel.php
 * Database-backed data management for CivicPortal.
 * Uses PDO prepared statements for all CRUD operations.
 * Uses Blueprints (ServiceRequest.php, Document.php) as entity objects.
 */

require_once __DIR__ . '/../Config/Database.php';
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/ServiceRequest.php';
require_once __DIR__ . '/Document.php';

class AppModel {

    /** Get the PDO connection */
    private static function db(): PDO {
        return Database::getConnection();
    }

    /**
     * Create auxiliary tables used by workflow/audit features.
     */
    private static function ensureAuxiliarySchema(): void {
        $db = self::db();

        $db->exec("
            CREATE TABLE IF NOT EXISTS request_reviews (
                request_id INT PRIMARY KEY,
                rejection_reason TEXT NULL,
                reviewer_role VARCHAR(50) NULL,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");

        $db->exec("
            CREATE TABLE IF NOT EXISTS request_audit_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                request_id INT NOT NULL,
                action VARCHAR(64) NOT NULL,
                from_status VARCHAR(32) NULL,
                to_status VARCHAR(32) NULL,
                note TEXT NULL,
                actor_role VARCHAR(50) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Ensure workflow statuses are not blocked by strict ENUM definitions.
        // We need values like "under review" for the approval pipeline.
        $db->exec("
            ALTER TABLE requests
            MODIFY status VARCHAR(32) NOT NULL DEFAULT 'pending'
        ");
    }

    private static function addAuditLog(
        int $requestId,
        string $action,
        ?string $fromStatus = null,
        ?string $toStatus = null,
        ?string $note = null,
        ?string $actorRole = null
    ): void {
        self::ensureAuxiliarySchema();
        $db = self::db();
        $stmt = $db->prepare("
            INSERT INTO request_audit_logs (request_id, action, from_status, to_status, note, actor_role)
            VALUES (:request_id, :action, :from_status, :to_status, :note, :actor_role)
        ");
        $stmt->execute([
            ':request_id' => $requestId,
            ':action' => $action,
            ':from_status' => $fromStatus,
            ':to_status' => $toStatus,
            ':note' => $note,
            ':actor_role' => $actorRole
        ]);
    }

    /**
     * Seed a default citizen user if the users table is empty.
     * This ensures foreign keys work out of the box for the demo.
     */
    public static function ensureDefaultUser(): void {
        $db = self::db();
        $stmt = $db->query("SELECT COUNT(*) FROM users");
        if ($stmt->fetchColumn() == 0) {
            $db->exec("INSERT INTO users (id, username, email, password_hash, role) VALUES
                (1, 'john_citizen', 'john@example.com', '" . password_hash('password', PASSWORD_DEFAULT) . "', 'citizen'),
                (2, 'alice_worker', 'alice@cityhall.gov', '" . password_hash('password', PASSWORD_DEFAULT) . "', 'agent'),
                (3, 'admin_user',   'admin@cityhall.gov', '" . password_hash('password', PASSWORD_DEFAULT) . "', 'admin')
            ");
        }
    }

    // =========================================================================
    //  REQUEST CRUD  (maps to `requests` table)
    // =========================================================================

    /**
     * SELECT all requests.
     */
    public static function getRequests(): array {
        $db = self::db();
        self::ensureAuxiliarySchema();
        $stmt = $db->query("
            SELECT
                r.id,
                r.user_id AS userId,
                r.title,
                r.description,
                r.status,
                r.created_at AS createdAt,
                r.updated_at AS updatedAt,
                rr.rejection_reason AS rejectionReason
            FROM requests r
            LEFT JOIN request_reviews rr ON rr.request_id = r.id
            ORDER BY r.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * SELECT a single request by ID.
     */
    public static function getRequestById(int $requestId): ?array {
        $db = self::db();
        self::ensureAuxiliarySchema();
        $stmt = $db->prepare("
            SELECT
                r.id,
                r.user_id AS userId,
                r.title,
                r.description,
                r.status,
                r.created_at AS createdAt,
                r.updated_at AS updatedAt,
                rr.rejection_reason AS rejectionReason
            FROM requests r
            LEFT JOIN request_reviews rr ON rr.request_id = r.id
            WHERE r.id = :id
        ");
        $stmt->execute([':id' => $requestId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * INSERT a new request.
     */
    public static function addRequest(string $title, string $description, int $userId): array {
        $db = self::db();

        // Create Blueprint object before saving
        $requestObj = new ServiceRequest(null, $title, $description, $userId, 'pending');

        $stmt = $db->prepare("INSERT INTO requests (user_id, title, description, status) VALUES (:userId, :title, :description, :status)");
        $stmt->execute([
            ':userId'      => $requestObj->getUserId(),
            ':title'       => $requestObj->getTitle(),
            ':description' => $requestObj->getDescription(),
            ':status'      => $requestObj->getStatus()
        ]);

        $newId = (int)$db->lastInsertId();
        self::addAuditLog($newId, 'request_created', null, 'pending', 'Citizen submitted request', 'citizen');
        return self::getRequestById($newId);
    }

    /**
     * UPDATE a request's description (only if still pending).
     */
    public static function updateRequest(int $requestId, string $newDescription): array {
        $db = self::db();

        // Fetch current state
        $current = self::getRequestById($requestId);
        if (!$current) {
            throw new Exception("Request not found.");
        }
        if ($current['status'] !== 'pending') {
            throw new Exception("Cannot edit a request that is no longer pending.");
        }

        // Use Blueprint to demonstrate setter usage
        $requestObj = new ServiceRequest(
            $current['id'], $current['title'], $current['description'],
            $current['userId'], $current['status'], $current['createdAt']
        );
        $requestObj->setDescription($newDescription);

        $stmt = $db->prepare("UPDATE requests SET description = :description WHERE id = :id");
        $stmt->execute([
            ':description' => $requestObj->getDescription(),
            ':id'          => $requestId
        ]);

        $before = trim((string)($current['description'] ?? ''));
        $after = trim($newDescription);
        if ($before !== $after) {
            self::addAuditLog(
                $requestId,
                'request_edited',
                $current['status'] ?? null,
                $current['status'] ?? null,
                "Description updated: \"{$before}\" -> \"{$after}\"",
                'citizen'
            );
        }

        return self::getRequestById($requestId);
    }

    /**
     * UPDATE a request's status (for workers/admin).
     */
    public static function updateRequestStatus(
        int $requestId,
        string $status,
        ?string $rejectionReason = null,
        string $actorRole = 'worker'
    ): bool {
        $db = self::db();

        $current = self::getRequestById($requestId);
        if (!$current) return false;

        $fromStatus = $current['status'] ?? 'pending';
        $normalizedStatus = strtolower(trim($status));
        $allowedStatuses = ['pending', 'under review', 'approved', 'rejected'];
        if (!in_array($normalizedStatus, $allowedStatuses, true)) {
            throw new Exception("Invalid status transition target.");
        }

        $allowedTransitions = [
            'pending' => ['under review'],
            'under review' => ['approved', 'rejected'],
            'approved' => [],
            'rejected' => []
        ];
        $nextAllowed = $allowedTransitions[$fromStatus] ?? [];
        if (!in_array($normalizedStatus, $nextAllowed, true)) {
            throw new Exception("Invalid status transition from '{$fromStatus}' to '{$normalizedStatus}'.");
        }

        if ($normalizedStatus === 'rejected') {
            if ($rejectionReason === null || trim($rejectionReason) === '') {
                throw new Exception("Rejection reason is required.");
            }
        }

        // Use Blueprint to demonstrate setter
        $requestObj = new ServiceRequest(
            $current['id'], $current['title'], $current['description'] ?? '',
            $current['userId'], $current['status'], $current['createdAt']
        );
        $requestObj->setStatus($normalizedStatus);

        $stmt = $db->prepare("UPDATE requests SET status = :status WHERE id = :id");
        $stmt->execute([
            ':status' => $requestObj->getStatus(),
            ':id'     => $requestId
        ]);

        self::ensureAuxiliarySchema();
        $reviewStmt = $db->prepare("
            INSERT INTO request_reviews (request_id, rejection_reason, reviewer_role)
            VALUES (:request_id, :rejection_reason, :reviewer_role)
            ON DUPLICATE KEY UPDATE
                rejection_reason = VALUES(rejection_reason),
                reviewer_role = VALUES(reviewer_role)
        ");
        $reviewStmt->execute([
            ':request_id' => $requestId,
            ':rejection_reason' => $normalizedStatus === 'rejected' ? trim($rejectionReason ?? '') : null,
            ':reviewer_role' => $actorRole
        ]);

        self::addAuditLog(
            $requestId,
            'status_updated',
            $fromStatus,
            $normalizedStatus,
            $normalizedStatus === 'rejected' ? trim($rejectionReason ?? '') : null,
            $actorRole
        );

        return $stmt->rowCount() > 0;
    }

    /**
     * DELETE a request (documents cascade-delete via FK).
     */
    public static function deleteRequest(int $requestId): bool {
        $db = self::db();

        // Also delete the physical files first
        $docs = self::getDocumentsByRequest($requestId);
        foreach ($docs as $doc) {
            $fullPath = __DIR__ . '/../uploads/' . $doc['filePath'];
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }

        $stmt = $db->prepare("DELETE FROM requests WHERE id = :id");
        $stmt->execute([':id' => $requestId]);

        if ($stmt->rowCount() === 0) {
            throw new Exception("Request not found.");
        }
        self::addAuditLog($requestId, 'request_deleted', null, null, 'Request and documents deleted', 'citizen');
        return true;
    }

    // =========================================================================
    //  DOCUMENT CRUD  (maps to `documents` table)
    // =========================================================================

    /**
     * SELECT all documents for a specific request.
     */
    public static function getDocumentsByRequest(int $requestId): array {
        $db = self::db();
        $stmt = $db->prepare("SELECT id, request_id AS requestId, file_path AS filePath, type, uploaded_at AS uploadedAt FROM documents WHERE request_id = :requestId ORDER BY uploaded_at ASC");
        $stmt->execute([':requestId' => $requestId]);
        return $stmt->fetchAll();
    }

    /**
     * SELECT a single document by ID.
     */
    public static function getDocumentById(int $documentId): ?array {
        $db = self::db();
        $stmt = $db->prepare("SELECT id, request_id AS requestId, file_path AS filePath, type, uploaded_at AS uploadedAt FROM documents WHERE id = :id");
        $stmt->execute([':id' => $documentId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * INSERT a new document.
     */
    public static function addDocument(int $requestId, string $filePath, string $type): array {
        $db = self::db();

        // Verify request exists
        if (!self::getRequestById($requestId)) {
            throw new Exception("Request not found.");
        }

        // Create Blueprint object before saving
        $docObj = new Document(null, $requestId, $filePath, $type);

        $stmt = $db->prepare("INSERT INTO documents (request_id, file_path, type) VALUES (:requestId, :filePath, :type)");
        $stmt->execute([
            ':requestId' => $docObj->getRequestId(),
            ':filePath'  => $docObj->getFilePath(),
            ':type'      => $docObj->getType()
        ]);

        $newId = (int)$db->lastInsertId();
        self::addAuditLog(
            $requestId,
            'document_added',
            null,
            null,
            "Document added: {$filePath} ({$type})",
            'citizen'
        );
        return self::getDocumentById($newId);
    }

    /**
     * UPDATE a document (replace file).
     */
    public static function updateDocument(int $documentId, string $filePath, string $type): array {
        $db = self::db();

        $current = self::getDocumentById($documentId);
        if (!$current) {
            throw new Exception("Document not found.");
        }

        // Delete old file
        $oldPath = __DIR__ . '/../uploads/' . $current['filePath'];
        if (file_exists($oldPath)) {
            unlink($oldPath);
        }

        // Use Blueprint
        $docObj = new Document($current['id'], $current['requestId'], $current['filePath'], $current['type'], $current['uploadedAt']);
        $docObj->setFilePath($filePath);
        $docObj->setType($type);

        $stmt = $db->prepare("UPDATE documents SET file_path = :filePath, type = :type WHERE id = :id");
        $stmt->execute([
            ':filePath' => $docObj->getFilePath(),
            ':type'     => $docObj->getType(),
            ':id'       => $documentId
        ]);

        self::addAuditLog(
            (int)$current['requestId'],
            'document_replaced',
            null,
            null,
            "Document replaced: {$current['filePath']} -> {$filePath} ({$type})",
            'citizen'
        );

        return self::getDocumentById($documentId);
    }

    /**
     * DELETE a document by ID.
     */
    public static function deleteDocument(int $documentId): bool {
        $db = self::db();

        // Delete physical file
        $doc = self::getDocumentById($documentId);
        if (!$doc) {
            throw new Exception("Document not found.");
        }
        $fullPath = __DIR__ . '/../uploads/' . $doc['filePath'];
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }

        $stmt = $db->prepare("DELETE FROM documents WHERE id = :id");
        $stmt->execute([':id' => $documentId]);

        self::addAuditLog(
            (int)$doc['requestId'],
            'document_deleted',
            null,
            null,
            "Document deleted: {$doc['filePath']} ({$doc['type']})",
            'citizen'
        );

        return $stmt->rowCount() > 0;
    }

    // =========================================================================
    //  COMPLAINTS (session-based, no DB table for this module)
    // =========================================================================

    public static function initSession(): void {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['complaints'])) {
            $_SESSION['complaints'] = [];
        }
    }

    public static function addComplaint(string $subject, string $body, int $userId): array {
        self::initSession();
        $newComplaint = [
            'id' => time(),
            'subject' => $subject,
            'body' => $body,
            'userId' => $userId,
            'date' => date('Y-m-d')
        ];
        $_SESSION['complaints'][] = $newComplaint;
        return $newComplaint;
    }

    public static function getComplaints(): array {
        self::initSession();
        return $_SESSION['complaints'];
    }

    // =========================================================================
    //  STATS
    // =========================================================================

    public static function getStats(): array {
        $db = self::db();
        self::initSession();
        self::ensureAuxiliarySchema();

        $usersCount = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $programsCount = $db->query("SELECT COUNT(*) FROM program")->fetchColumn();
        $requestsCount = $db->query("SELECT COUNT(*) FROM requests")->fetchColumn();
        $enrollmentsCount = $db->query("SELECT COUNT(*) FROM enrollment")->fetchColumn();
        $documentsCount = $db->query("SELECT COUNT(*) FROM documents")->fetchColumn();
        $complaintsCount = count($_SESSION['complaints'] ?? []);

        $statusRows = $db->query("
            SELECT status, COUNT(*) AS total
            FROM requests
            GROUP BY status
        ")->fetchAll();
        $statusBreakdown = [
            'pending' => 0,
            'under review' => 0,
            'approved' => 0,
            'rejected' => 0
        ];
        foreach ($statusRows as $row) {
            $statusBreakdown[$row['status']] = (int)$row['total'];
        }

        $servicesRows = $db->query("
            SELECT title, COUNT(*) AS total
            FROM requests
            GROUP BY title
            ORDER BY total DESC
            LIMIT 5
        ")->fetchAll();

        $dailyRows = $db->query("
            SELECT DATE(created_at) AS period, COUNT(*) AS total
            FROM requests
            GROUP BY DATE(created_at)
            ORDER BY period DESC
            LIMIT 7
        ")->fetchAll();

        $monthlyRows = $db->query("
            SELECT DATE_FORMAT(created_at, '%Y-%m') AS period, COUNT(*) AS total
            FROM requests
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY period DESC
            LIMIT 6
        ")->fetchAll();

        $recentActivity = $db->query("
            SELECT request_id AS requestId, action, from_status AS fromStatus, to_status AS toStatus, note, actor_role AS actorRole, created_at AS createdAt
            FROM request_audit_logs
            ORDER BY created_at DESC
            LIMIT 10
        ")->fetchAll();

        return [
            'usersCount'       => (int)$usersCount,
            'programsCount'    => (int)$programsCount,
            'requestsCount'    => (int)$requestsCount,
            'enrollmentsCount' => (int)$enrollmentsCount,
            'documentsCount'   => (int)$documentsCount,
            'complaintsCount'  => $complaintsCount,
            'statusBreakdown'  => $statusBreakdown,
            'topServices'      => $servicesRows,
            'dailyRequests'    => $dailyRows,
            'monthlyRequests'  => $monthlyRows,
            'recentActivity'   => $recentActivity
        ];
    }

    public static function getRequestAuditLogs(int $requestId): array {
        self::ensureAuxiliarySchema();
        $db = self::db();
        $stmt = $db->prepare("
            SELECT id, request_id AS requestId, action, from_status AS fromStatus, to_status AS toStatus, note, actor_role AS actorRole, created_at AS createdAt
            FROM request_audit_logs
            WHERE request_id = :request_id
            ORDER BY created_at DESC
        ");
        $stmt->execute([':request_id' => $requestId]);
        return $stmt->fetchAll();
    }
}
?>
