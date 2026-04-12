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
        $stmt = $db->query("SELECT id, user_id AS userId, title, description, status, created_at AS createdAt, updated_at AS updatedAt FROM requests ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    /**
     * SELECT a single request by ID.
     */
    public static function getRequestById(int $requestId): ?array {
        $db = self::db();
        $stmt = $db->prepare("SELECT id, user_id AS userId, title, description, status, created_at AS createdAt, updated_at AS updatedAt FROM requests WHERE id = :id");
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

        return self::getRequestById($requestId);
    }

    /**
     * UPDATE a request's status (for workers/admin).
     */
    public static function updateRequestStatus(int $requestId, string $status): bool {
        $db = self::db();

        $current = self::getRequestById($requestId);
        if (!$current) return false;

        // Use Blueprint to demonstrate setter
        $requestObj = new ServiceRequest(
            $current['id'], $current['title'], $current['description'] ?? '',
            $current['userId'], $current['status'], $current['createdAt']
        );
        $requestObj->setStatus($status);

        $stmt = $db->prepare("UPDATE requests SET status = :status WHERE id = :id");
        $stmt->execute([
            ':status' => $requestObj->getStatus(),
            ':id'     => $requestId
        ]);

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

        $usersCount = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $programsCount = $db->query("SELECT COUNT(*) FROM program")->fetchColumn();
        $requestsCount = $db->query("SELECT COUNT(*) FROM requests")->fetchColumn();
        $enrollmentsCount = $db->query("SELECT COUNT(*) FROM enrollment")->fetchColumn();
        $documentsCount = $db->query("SELECT COUNT(*) FROM documents")->fetchColumn();
        $complaintsCount = count($_SESSION['complaints'] ?? []);

        return [
            'usersCount'       => (int)$usersCount,
            'programsCount'    => (int)$programsCount,
            'requestsCount'    => (int)$requestsCount,
            'enrollmentsCount' => (int)$enrollmentsCount,
            'documentsCount'   => (int)$documentsCount,
            'complaintsCount'  => $complaintsCount
        ];
    }
}
?>
