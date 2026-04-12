<?php
/**
 * AppModel.php
 * MySQL-based data management for CivicPortal.
 * Refactored to use PDO and Blueprint entities.
 */

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/ServiceRequest.php';
require_once __DIR__ . '/Program.php';

class AppModel {
    private static function getDb() {
        return Database::getInstance()->getConnection();
    }

    public static function init() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * getRequests() - Fetch all requests from the database.
     */
    public static function getRequests() {
        $db = self::getDb();
        $stmt = $db->query("SELECT * FROM requests ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    /**
     * addRequest() - Insert a new service request.
     */
    public static function addRequest($type, $userId) {
        $db = self::getDb();
        $stmt = $db->prepare("INSERT INTO requests (user_id, title, status) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $type, 'pending']);
        
        $id = $db->lastInsertId();
        return [
            'id' => $id,
            'type' => $type,
            'userId' => $userId,
            'status' => 'pending',
            'date' => date('Y-m-d')
        ];
    }

    public static function updateRequestStatus($requestId, $status) {
        $db = self::getDb();
        $stmt = $db->prepare("UPDATE requests SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $requestId]);
    }

    /**
     * --- Parks & Recreation (Program CRUD) ---
     */

    public static function getPrograms() {
        $db = self::getDb();
        $stmt = $db->query("
            SELECT p.*,
                   COALESCE(ec.total_enrolled, 0) as enrollment_count,
                   COALESCE(ec.pending_count, 0) as pending_count,
                   COALESCE(ec.confirmed_count, 0) as confirmed_count
            FROM program p
            LEFT JOIN (
                SELECT program_id,
                       COUNT(*) as total_enrolled,
                       SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                       SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_count
                FROM enrollment
                WHERE status != 'cancelled'
                GROUP BY program_id
            ) ec ON p.id = ec.program_id
            WHERE p.status != 'cancelled'
            ORDER BY p.id DESC
        ");
        return $stmt->fetchAll();
    }

    public static function getProgramById($id) {
        $db = self::getDb();
        $stmt = $db->prepare("
            SELECT p.*,
                   COALESCE(ec.total_enrolled, 0) as enrollment_count,
                   COALESCE(ec.pending_count, 0) as pending_count,
                   COALESCE(ec.confirmed_count, 0) as confirmed_count
            FROM program p
            LEFT JOIN (
                SELECT program_id,
                       COUNT(*) as total_enrolled,
                       SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                       SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_count
                FROM enrollment
                WHERE status != 'cancelled'
                GROUP BY program_id
            ) ec ON p.id = ec.program_id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function getEnrollmentsByProgram($programId) {
        $db = self::getDb();
        $stmt = $db->prepare("
            SELECT e.*, u.username, u.email
            FROM enrollment e
            JOIN users u ON e.user_id = u.id
            WHERE e.program_id = ?
            ORDER BY e.enrolled_at DESC
        ");
        $stmt->execute([$programId]);
        return $stmt->fetchAll();
    }

    public static function addProgram($data, $imageFile = null) {
        $db = self::getDb();
        $imageName = self::handleFileUpload($imageFile);
        
        $stmt = $db->prepare("INSERT INTO program (title, description, category, capacity, location, status, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['title'],
            $data['description'],
            $data['category'],
            $data['capacity'],
            $data['location'],
            $data['status'] ?? 'active',
            $imageName
        ]);
        return $db->lastInsertId();
    }

    public static function updateProgram($id, $data, $imageFile = null) {
        $db = self::getDb();
        $imageName = self::handleFileUpload($imageFile);
        
        $sql = "UPDATE program SET title = ?, description = ?, category = ?, capacity = ?, location = ?, status = ?";
        $params = [
            $data['title'],
            $data['description'],
            $data['category'],
            $data['capacity'],
            $data['location'],
            $data['status']
        ];
        
        if ($imageName) {
            $sql .= ", image = ?";
            $params[] = $imageName;
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $id;

        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    }

    private static function handleFileUpload($file) {
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $uploadDir = __DIR__ . '/../View/assets/images/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = 'prog_' . time() . '_' . uniqid() . '.' . $extension;
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return $fileName;
        }

        return null;
    }

    public static function deleteProgram($id) {
        $db = self::getDb();
        // We do a soft delete by marking as cancelled per typical admin flow
        $stmt = $db->prepare("UPDATE program SET status = 'cancelled' WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * enrollUser() - Create an enrollment record.
     * Implements E1: Capacity Exceeded check.
     */
    public static function enrollUser($userId, $programId) {
        $db = self::getDb();
        
        // 1. Check for duplicate
        $stmt = $db->prepare("SELECT COUNT(*) FROM enrollment WHERE user_id = ? AND program_id = ? AND status != 'cancelled'");
        $stmt->execute([$userId, $programId]);
        if ($stmt->fetchColumn() > 0) return true;

        // 2. Check capacity
        $stmt = $db->prepare("SELECT capacity FROM program WHERE id = ?");
        $stmt->execute([$programId]);
        $capacity = (int)$stmt->fetchColumn();

        $stmt = $db->prepare("SELECT COUNT(*) FROM enrollment WHERE program_id = ? AND status IN ('confirmed', 'pending')");
        $stmt->execute([$programId]);
        $current = (int)$stmt->fetchColumn();

        $status = ($current < $capacity) ? 'pending' : 'waitlisted';

        // 3. Insert
        $stmt = $db->prepare("INSERT INTO enrollment (user_id, program_id, status) VALUES (?, ?, ?)");
        return $stmt->execute([$userId, $programId, $status]);
    }

    public static function getEnrollments($userId) {
        $db = self::getDb();
        $stmt = $db->prepare("SELECT * FROM enrollment WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function getPendingEnrollments() {
        $db = self::getDb();
        $stmt = $db->query("SELECT e.*, u.username, p.title as program_title 
                            FROM enrollment e 
                            JOIN users u ON e.user_id = u.id 
                            JOIN program p ON e.program_id = p.id 
                            WHERE e.status = 'pending' 
                            ORDER BY e.enrolled_at ASC");
        return $stmt->fetchAll();
    }

    public static function getAllEnrollmentsCounts() {
        $db = self::getDb();
        $total = (int)$db->query("SELECT COUNT(*) FROM enrollment WHERE status != 'cancelled'")->fetchColumn();
        $pending = (int)$db->query("SELECT COUNT(*) FROM enrollment WHERE status = 'pending'")->fetchColumn();
        return ['total' => $total, 'pending' => $pending];
    }

    public static function updateEnrollmentStatus($id, $status) {
        $db = self::getDb();
        $stmt = $db->prepare("UPDATE enrollment SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    /**
     * getStats() - Aggregate counts for Admin dashboard.
     */
    public static function getStats() {
        $db = self::getDb();
        
        $usersCount = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $programsCount = $db->query("SELECT COUNT(*) FROM program WHERE status = 'active'")->fetchColumn();
        $requestsCount = $db->query("SELECT COUNT(*) FROM requests")->fetchColumn();
        $enrollmentsCount = $db->query("SELECT COUNT(*) FROM enrollment")->fetchColumn();
        $complaintsCount = 0; // Assuming complaints table or logic to be added later

        return [
            'usersCount' => (int)$usersCount,
            'programsCount' => (int)$programsCount,
            'requestsCount' => (int)$requestsCount,
            'enrollmentsCount' => (int)$enrollmentsCount,
            'complaintsCount' => (int)$complaintsCount
        ];
    }
}
?>
