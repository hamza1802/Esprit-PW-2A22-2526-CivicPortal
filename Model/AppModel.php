<?php
/**
 * AppModel.php
 * MySQL-based data management for CivicPortal.
 * Refactored: BLOB image storage, appointments, notifications.
 * Complaints/Grievances module REMOVED.
 */

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/ServiceRequest.php';
require_once __DIR__ . '/Program.php';
require_once __DIR__ . '/Appointment.php';
require_once __DIR__ . '/AppointmentSlot.php';
require_once __DIR__ . '/Notification.php';

class AppModel {
    private static function getDb() {
        return Database::getInstance()->getConnection();
    }

    public static function init() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    // =========================================================================
    // IMAGE BLOB HELPER
    // =========================================================================
    private const MAX_IMG = 2097152; // 2MB
    private const OK_MIME = ['image/jpeg','image/png','image/webp'];

    /**
     * Validate and read an uploaded image file into [blobData, mimeType].
     * Returns null if no file or validation fails.
     */
    private static function readImageUpload(?array $file): ?array {
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) return null;
        if ($file['size'] > self::MAX_IMG) throw new Exception('Image must be under 2MB.');
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if (!in_array($mime, self::OK_MIME, true)) {
            throw new Exception('Only JPEG, PNG, and WebP images are accepted.');
        }
        $data = file_get_contents($file['tmp_name']);
        if ($data === false) throw new Exception('Failed to read image file.');
        return [$data, $mime];
    }

    // =========================================================================
    // SERVICE REQUESTS (Module 2)
    // =========================================================================

    /**
     * Get all requests (admin view).
     */
    public static function getRequests() {
        $db = self::getDb();
        $stmt = $db->query("
            SELECT r.*, u.username as user_name, 
                   a.username as agent_name
            FROM requests r
            LEFT JOIN users u ON r.user_id = u.id
            LEFT JOIN users a ON r.assigned_to = a.id
            ORDER BY r.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Get requests for a specific citizen.
     */
    public static function getRequestsByUser(int $userId) {
        $db = self::getDb();
        $stmt = $db->prepare("
            SELECT r.*, a.username as agent_name
            FROM requests r
            LEFT JOIN users a ON r.assigned_to = a.id
            WHERE r.user_id = ?
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Get requests assigned to a specific agent.
     */
    public static function getRequestsByAssignee(int $agentId) {
        $db = self::getDb();
        $stmt = $db->prepare("
            SELECT r.*, u.username as user_name
            FROM requests r
            LEFT JOIN users u ON r.user_id = u.id
            WHERE r.assigned_to = ?
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([$agentId]);
        return $stmt->fetchAll();
    }

    /**
     * Add a service request with optional BLOB attachment.
     */
    public static function addRequest($data, $userId, $attachFile = null) {
        $db = self::getDb();
        $title = trim($data['title'] ?? $data['type'] ?? '');
        $desc  = trim($data['description'] ?? '');
        $cat   = trim($data['category'] ?? '');

        if (empty($title)) throw new Exception('Request title is required.');

        $blob = null; $mime = null;
        $imgData = self::readImageUpload($attachFile);
        if ($imgData) { [$blob, $mime] = $imgData; }

        $stmt = $db->prepare("
            INSERT INTO requests (user_id, title, description, category, status, attachment, attachment_mime)
            VALUES (?, ?, ?, ?, 'pending', ?, ?)
        ");
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, $title);
        $stmt->bindValue(3, $desc);
        $stmt->bindValue(4, $cat);
        $stmt->bindValue(5, $blob, PDO::PARAM_LOB);
        $stmt->bindValue(6, $mime);
        $stmt->execute();

        return [
            'id' => $db->lastInsertId(),
            'title' => $title,
            'description' => $desc,
            'category' => $cat,
            'userId' => $userId,
            'status' => 'pending',
            'date' => date('Y-m-d')
        ];
    }

    /**
     * Update request status and log timestamp.
     */
    public static function updateRequestStatus($requestId, $status) {
        $db = self::getDb();
        $stmt = $db->prepare("UPDATE requests SET status = ?, status_updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$status, $requestId]);
    }

    /**
     * Admin assigns a request to an agent.
     */
    public static function assignRequest(int $requestId, int $agentId): bool {
        $db = self::getDb();
        $stmt = $db->prepare("UPDATE requests SET assigned_to = ? WHERE id = ?");
        return $stmt->execute([$agentId, $requestId]);
    }

    // =========================================================================
    // PROGRAMS (Parks & Recreation)
    // =========================================================================

    public static function getPrograms() {
        $db = self::getDb();
        // Explicitly select columns to exclude heavy BLOB data
        $sql = "
            SELECT p.id, p.title, p.description, p.category, p.capacity, p.location, p.status, p.start_date, p.end_date,
                   (SELECT COUNT(*) FROM enrollment e WHERE e.program_id = p.id AND e.status != 'cancelled') as enrollment_count,
                   (SELECT COUNT(*) FROM enrollment e WHERE e.program_id = p.id AND e.status = 'pending') as pending_count,
                   (SELECT COUNT(*) FROM enrollment e WHERE e.program_id = p.id AND e.status = 'confirmed') as confirmed_count
            FROM program p
            WHERE p.status != 'cancelled'
            ORDER BY p.id DESC
        ";
        return $db->query($sql)->fetchAll();
    }

    public static function getProgramById($id) {
        $db = self::getDb();
        $stmt = $db->prepare("
            SELECT p.id, p.title, p.description, p.category, p.capacity, p.location, p.status, p.start_date, p.end_date,
                   (SELECT COUNT(*) FROM enrollment e WHERE e.program_id = p.id AND e.status != 'cancelled') as enrollment_count,
                   (SELECT COUNT(*) FROM enrollment e WHERE e.program_id = p.id AND e.status = 'pending') as pending_count,
                   (SELECT COUNT(*) FROM enrollment e WHERE e.program_id = p.id AND e.status = 'confirmed') as confirmed_count
            FROM program p
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function getEnrollmentsByProgram($programId) {
        $db = self::getDb();
        $stmt = $db->prepare("
            SELECT e.*, u.username, u.email
            FROM enrollment e JOIN users u ON e.user_id = u.id
            WHERE e.program_id = ? ORDER BY e.enrolled_at DESC
        ");
        $stmt->execute([$programId]);
        return $stmt->fetchAll();
    }

    /**
     * Add program with BLOB image storage.
     */
    public static function addProgram($data, $imageFile = null) {
        self::validateProgramData($data);
        $db = self::getDb();

        $blob = null; $mime = null;
        $imgData = self::readImageUpload($imageFile);
        if ($imgData) { [$blob, $mime] = $imgData; }

        $stmt = $db->prepare("
            INSERT INTO program (title, description, category, capacity, location, status, start_date, end_date, program_image, program_image_mime)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bindValue(1, $data['title']);
        $stmt->bindValue(2, $data['description']);
        $stmt->bindValue(3, $data['category']);
        $stmt->bindValue(4, $data['capacity'], PDO::PARAM_INT);
        $stmt->bindValue(5, $data['location']);
        $stmt->bindValue(6, $data['status'] ?? 'active');
        $stmt->bindValue(7, $data['start_date'] ?? null);
        $stmt->bindValue(8, $data['end_date'] ?? null);
        $stmt->bindValue(9, $blob, PDO::PARAM_LOB);
        $stmt->bindValue(10, $mime);
        $stmt->execute();
        return $db->lastInsertId();
    }

    /**
     * Update program with optional BLOB image.
     */
    public static function updateProgram($id, $data, $imageFile = null) {
        try {
            self::validateProgramData($data);
            $db = self::getDb();

            $imgData = self::readImageUpload($imageFile);

            if ($imgData) {
                [$blob, $mime] = $imgData;
                $stmt = $db->prepare("
                    UPDATE program SET title=?, description=?, category=?, capacity=?, location=?, status=?,
                           start_date=?, end_date=?, program_image=?, program_image_mime=? WHERE id=?
                ");
                $stmt->bindValue(1,  $data['title']);
                $stmt->bindValue(2,  $data['description']);
                $stmt->bindValue(3,  $data['category']);
                $stmt->bindValue(4,  $data['capacity'], PDO::PARAM_INT);
                $stmt->bindValue(5,  $data['location']);
                $stmt->bindValue(6,  $data['status'] ?? 'active');
                $stmt->bindValue(7,  $data['start_date']);
                $stmt->bindValue(8,  $data['end_date']);
                $stmt->bindValue(9,  $blob, PDO::PARAM_LOB);
                $stmt->bindValue(10, $mime);
                $stmt->bindValue(11, $id, PDO::PARAM_INT);
                return $stmt->execute();
            }

            $stmt = $db->prepare("
                UPDATE program SET title=?, description=?, category=?, capacity=?, location=?, status=?, start_date=?, end_date=? WHERE id=?
            ");
            $stmt->bindValue(1, $data['title']);
            $stmt->bindValue(2, $data['description']);
            $stmt->bindValue(3, $data['category']);
            $stmt->bindValue(4, $data['capacity'], PDO::PARAM_INT);
            $stmt->bindValue(5, $data['location']);
            $stmt->bindValue(6, $data['status'] ?? 'active');
            $stmt->bindValue(7, $data['start_date']);
            $stmt->bindValue(8, $data['end_date']);
            $stmt->bindValue(9, $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log('[CivicPortal][AppModel] Update Program failed: ' . $e->getMessage() . ' | Data: ' . json_encode($data));
            throw $e;
        }
    }

    /**
     * Server-side validation and sanitization for programs.
     */
    private static function validateProgramData(&$data) {
        $data['title']       = trim($data['title'] ?? '');
        $data['description'] = trim($data['description'] ?? '');
        $data['category']    = trim($data['category'] ?? '');
        $data['location']    = trim($data['location'] ?? '');
        $data['capacity']    = (isset($data['capacity']) && is_numeric($data['capacity'])) ? (int)$data['capacity'] : 0;
        $data['start_date']  = trim($data['start_date'] ?? '');
        $data['end_date']    = trim($data['end_date'] ?? '');
        $data['status']      = trim($data['status'] ?? 'active');

        if (empty($data['title']) || strlen($data['title']) < 5) throw new Exception("Title must be at least 5 characters.");
        if (empty($data['description']) || strlen($data['description']) < 20) throw new Exception("Description must be at least 20 characters.");
        if (empty($data['category'])) throw new Exception("Category is required.");
        if ($data['capacity'] <= 0) throw new Exception("Capacity must be a positive number.");
        if (empty($data['location']) || strlen($data['location']) < 3) throw new Exception("Location must be at least 3 characters.");
        
        if (empty($data['start_date'])) throw new Exception("Start date is required.");
        if (empty($data['end_date'])) throw new Exception("End date is required.");
        if (strtotime($data['start_date']) > strtotime($data['end_date'])) throw new Exception("Start date must be before end date.");

        $data['title']       = htmlspecialchars($data['title'], ENT_QUOTES, 'UTF-8');
        $data['description'] = htmlspecialchars($data['description'], ENT_QUOTES, 'UTF-8');
        $data['location']    = htmlspecialchars($data['location'], ENT_QUOTES, 'UTF-8');
    }

    public static function deleteProgram($id) {
        $db = self::getDb();
        $stmt = $db->prepare("UPDATE program SET status = 'cancelled' WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // =========================================================================
    // ENROLLMENTS
    // =========================================================================

    public static function enrollUser($userId, $programId) {
        $db = self::getDb();
        $stmt = $db->prepare("SELECT COUNT(*) FROM enrollment WHERE user_id = ? AND program_id = ? AND status != 'cancelled'");
        $stmt->execute([$userId, $programId]);
        if ($stmt->fetchColumn() > 0) return true;

        $stmt = $db->prepare("SELECT capacity FROM program WHERE id = ?");
        $stmt->execute([$programId]);
        $capacity = (int)$stmt->fetchColumn();

        $stmt = $db->prepare("SELECT COUNT(*) FROM enrollment WHERE program_id = ? AND status IN ('confirmed', 'pending')");
        $stmt->execute([$programId]);
        $current = (int)$stmt->fetchColumn();

        $status = ($current < $capacity) ? 'pending' : 'waitlisted';
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
        return $db->query("SELECT e.*, u.username, p.title as program_title 
                            FROM enrollment e JOIN users u ON e.user_id = u.id 
                            JOIN program p ON e.program_id = p.id 
                            WHERE e.status = 'pending' ORDER BY e.enrolled_at ASC")->fetchAll();
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

    // =========================================================================
    // STATS
    // =========================================================================

    public static function getStats() {
        $db = self::getDb();
        return [
            'usersCount'       => (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
            'programsCount'    => (int)$db->query("SELECT COUNT(*) FROM program WHERE status = 'active'")->fetchColumn(),
            'requestsCount'    => (int)$db->query("SELECT COUNT(*) FROM requests")->fetchColumn(),
            'enrollmentsCount' => (int)$db->query("SELECT COUNT(*) FROM enrollment")->fetchColumn(),
            'appointmentsCount'=> (int)$db->query("SELECT COUNT(*) FROM appointments")->fetchColumn(),
        ];
    }

    // =========================================================================
    // CATEGORIES
    // =========================================================================

    public static function getCategories() {
        return self::getDb()->query("SELECT * FROM program_category ORDER BY name ASC")->fetchAll();
    }

    public static function addCategory($name) {
        $name = trim($name);
        if (empty($name) || strlen($name) < 2) throw new Exception("Category name must be at least 2 characters.");
        $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $db = self::getDb();
        $check = $db->prepare("SELECT COUNT(*) FROM program_category WHERE name = ?");
        $check->execute([$name]);
        if ($check->fetchColumn() > 0) throw new Exception("Category '$name' already exists.");
        $stmt = $db->prepare("INSERT INTO program_category (name) VALUES (?)");
        $stmt->execute([$name]);
        return ['id' => $db->lastInsertId(), 'name' => $name];
    }

    public static function updateCategory($id, $name) {
        $name = trim($name);
        if (empty($name) || strlen($name) < 2) throw new Exception("Category name must be at least 2 characters.");
        $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $db = self::getDb();
        $check = $db->prepare("SELECT COUNT(*) FROM program_category WHERE name = ? AND id != ?");
        $check->execute([$name, $id]);
        if ($check->fetchColumn() > 0) throw new Exception("Category '$name' already exists.");
        $stmt = $db->prepare("UPDATE program_category SET name = ? WHERE id = ?");
        return $stmt->execute([$name, $id]);
    }

    public static function deleteCategory($id) {
        $db = self::getDb();
        $cat = $db->prepare("SELECT name FROM program_category WHERE id = ?");
        $cat->execute([$id]);
        $catName = $cat->fetchColumn();
        if ($catName) {
            $check = $db->prepare("SELECT COUNT(*) FROM program WHERE category = ?");
            $check->execute([$catName]);
            if ($check->fetchColumn() > 0) throw new Exception("Cannot delete: category '$catName' is in use.");
        }
        $stmt = $db->prepare("DELETE FROM program_category WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // =========================================================================
    // APPOINTMENTS (Module 3)
    // =========================================================================

    /**
     * Book an appointment with double-booking validation.
     */
    public static function createAppointment(array $data, int $userId): array {
        $db = self::getDb();
        $svcType = trim($data['service_type'] ?? '');
        $date    = trim($data['preferred_date'] ?? '');
        $time    = trim($data['preferred_time'] ?? '');
        $notes   = trim($data['notes'] ?? '');

        if (empty($svcType)) throw new Exception('Service type is required.');
        if (empty($date))    throw new Exception('Preferred date is required.');
        if (empty($time))    throw new Exception('Preferred time is required.');
        if (strtotime($date) < strtotime('today')) throw new Exception('Cannot book in the past.');

        // Find an available agent for this service type on this day
        $dayOfWeek = (int)date('w', strtotime($date));
        $slotStmt = $db->prepare("
            SELECT s.agent_id FROM appointment_slots s
            WHERE s.service_type = ? AND s.day_of_week = ? AND s.is_active = 1
              AND s.start_time <= ? AND s.end_time > ?
            LIMIT 1
        ");
        $slotStmt->execute([$svcType, $dayOfWeek, $time, $time]);
        $agentId = $slotStmt->fetchColumn() ?: null;

        // Check for double-booking (same agent, same date/time, confirmed)
        if ($agentId) {
            $dblCheck = $db->prepare("
                SELECT COUNT(*) FROM appointments 
                WHERE assigned_to = ? AND preferred_date = ? AND preferred_time = ?
                  AND status IN ('pending','confirmed')
            ");
            $dblCheck->execute([$agentId, $date, $time]);
            if ((int)$dblCheck->fetchColumn() > 0) {
                throw new Exception('This time slot is already booked. Please choose another.');
            }
        }

        $stmt = $db->prepare("
            INSERT INTO appointments (user_id, service_type, preferred_date, preferred_time, notes, assigned_to)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $svcType, $date, $time, $notes, $agentId]);
        $id = $db->lastInsertId();

        // Log notification
        self::createNotification($userId, 'Appointment Booked',
            "Your appointment for {$svcType} on {$date} at {$time} has been submitted.", 'appointment');

        return ['id' => $id, 'status' => 'pending'];
    }

    public static function getAppointmentsByUser(int $userId): array {
        $db = self::getDb();
        $stmt = $db->prepare("
            SELECT a.*, u.username as agent_name FROM appointments a
            LEFT JOIN users u ON a.assigned_to = u.id
            WHERE a.user_id = ? ORDER BY a.preferred_date DESC, a.preferred_time DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function getAppointmentsByAgent(int $agentId): array {
        $db = self::getDb();
        $stmt = $db->prepare("
            SELECT a.*, u.username as user_name FROM appointments a
            LEFT JOIN users u ON a.user_id = u.id
            WHERE a.assigned_to = ? ORDER BY a.preferred_date ASC, a.preferred_time ASC
        ");
        $stmt->execute([$agentId]);
        return $stmt->fetchAll();
    }

    public static function getAllAppointments(): array {
        $db = self::getDb();
        return $db->query("
            SELECT a.*, u.username as user_name, ag.username as agent_name
            FROM appointments a
            LEFT JOIN users u ON a.user_id = u.id
            LEFT JOIN users ag ON a.assigned_to = ag.id
            ORDER BY a.preferred_date DESC
        ")->fetchAll();
    }

    public static function updateAppointmentStatus(int $id, string $status, ?string $reason = null,
                                                    ?string $newDate = null, ?string $newTime = null): bool {
        $db = self::getDb();
        $stmt = $db->prepare("
            UPDATE appointments SET status = ?, reschedule_reason = ?, new_date = ?, new_time = ? WHERE id = ?
        ");
        $result = $stmt->execute([$status, $reason, $newDate, $newTime, $id]);

        // Get appointment details for notification
        $apt = $db->prepare("SELECT * FROM appointments WHERE id = ?");
        $apt->execute([$id]);
        $row = $apt->fetch();
        if ($row) {
            $msg = "Your appointment for {$row['service_type']} has been {$status}.";
            if ($reason) $msg .= " Reason: {$reason}";
            self::createNotification((int)$row['user_id'], "Appointment {$status}", $msg, 'appointment');
        }
        return $result;
    }

    public static function cancelAppointment(int $id, int $userId): bool {
        $db = self::getDb();
        // Only allow cancelling own future appointments
        $stmt = $db->prepare("
            SELECT id FROM appointments 
            WHERE id = ? AND user_id = ? AND preferred_date >= CURDATE() AND status NOT IN ('cancelled','completed')
        ");
        $stmt->execute([$id, $userId]);
        if (!$stmt->fetch()) throw new Exception('Cannot cancel this appointment.');

        $upd = $db->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ?");
        return $upd->execute([$id]);
    }

    // =========================================================================
    // APPOINTMENT SLOTS (Admin-managed)
    // =========================================================================

    public static function createSlot(array $data): int {
        $db = self::getDb();
        $stmt = $db->prepare("
            INSERT INTO appointment_slots (agent_id, service_type, day_of_week, start_time, end_time)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            (int)$data['agent_id'], trim($data['service_type']),
            (int)$data['day_of_week'], $data['start_time'], $data['end_time']
        ]);
        return (int)$db->lastInsertId();
    }

    public static function getSlotsByAgent(int $agentId): array {
        $db = self::getDb();
        $stmt = $db->prepare("SELECT * FROM appointment_slots WHERE agent_id = ? ORDER BY day_of_week, start_time");
        $stmt->execute([$agentId]);
        return $stmt->fetchAll();
    }

    public static function getAllSlots(): array {
        $db = self::getDb();
        return $db->query("
            SELECT s.*, u.username as agent_name FROM appointment_slots s
            LEFT JOIN users u ON s.agent_id = u.id ORDER BY s.day_of_week, s.start_time
        ")->fetchAll();
    }

    public static function deleteSlot(int $id): bool {
        $db = self::getDb();
        return $db->prepare("DELETE FROM appointment_slots WHERE id = ?")->execute([$id]);
    }

    public static function getAvailableSlots(string $serviceType, string $date): array {
        $db = self::getDb();
        $dow = (int)date('w', strtotime($date));
        $stmt = $db->prepare("
            SELECT s.*, u.username as agent_name FROM appointment_slots s
            LEFT JOIN users u ON s.agent_id = u.id
            WHERE s.service_type = ? AND s.day_of_week = ? AND s.is_active = 1
            ORDER BY s.start_time
        ");
        $stmt->execute([$serviceType, $dow]);
        return $stmt->fetchAll();
    }

    // =========================================================================
    // NOTIFICATIONS
    // =========================================================================

    public static function createNotification(int $userId, string $title, string $body, string $type = 'info'): void {
        $db = self::getDb();
        $stmt = $db->prepare("INSERT INTO notifications (user_id, title, body, type) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $title, $body, $type]);
    }

    public static function getNotificationsByUser(int $userId): array {
        $db = self::getDb();
        $stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function markNotificationRead(int $id): bool {
        $db = self::getDb();
        return $db->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?")->execute([$id]);
    }

    public static function getUnreadCount(int $userId): int {
        $db = self::getDb();
        $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    // =========================================================================
    // SERVICE TYPES (for appointment booking dropdown)
    // =========================================================================
    public static function getServiceTypes(): array {
        return [
            ['value' => 'Birth Certificate', 'label' => 'Birth Certificate'],
            ['value' => 'ID Card Renewal', 'label' => 'ID Card Renewal'],
            ['value' => 'Residence Certificate', 'label' => 'Residence Certificate'],
            ['value' => 'Building Permit', 'label' => 'Building Permit'],
            ['value' => 'General Inquiry', 'label' => 'General Inquiry'],
            ['value' => 'Document Verification', 'label' => 'Document Verification'],
        ];
    }
    // ============================================
    // TRANSPORT TYPE MANAGEMENT
    // ============================================

    public static function listTransportTypes() {
        $sql = "SELECT idTransportType, name, description FROM transport_type ORDER BY name ASC";
        $db = self::getDb();
        return $db->query($sql)->fetchAll();
    }

    public static function showTransportType($id) {
        $sql = "SELECT * FROM transport_type WHERE idTransportType = ?";
        $db = self::getDb();
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function addTransportType($data, $imageFile = null) {
        $db = self::getDb();
        if ($imageFile && isset($imageFile['tmp_name']) && $imageFile['error'] === UPLOAD_ERR_OK) {
            $blob = file_get_contents($imageFile['tmp_name']);
            $mime = mime_content_type($imageFile['tmp_name']) ?: 'image/jpeg';
            $sql = "INSERT INTO transport_type (name, description, image_blob, image_mime) VALUES (?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(1, $data['name']);
            $stmt->bindValue(2, $data['description']);
            $stmt->bindValue(3, $blob, PDO::PARAM_LOB);
            $stmt->bindValue(4, $mime);
            return $stmt->execute();
        } else {
            $sql = "INSERT INTO transport_type (name, description) VALUES (?, ?)";
            $stmt = $db->prepare($sql);
            return $stmt->execute([$data['name'], $data['description']]);
        }
    }

    public static function updateTransportType($id, $data, $imageFile = null) {
        $db = self::getDb();
        if ($imageFile && isset($imageFile['tmp_name']) && $imageFile['error'] === UPLOAD_ERR_OK) {
            $blob = file_get_contents($imageFile['tmp_name']);
            $mime = mime_content_type($imageFile['tmp_name']) ?: 'image/jpeg';
            $sql = "UPDATE transport_type SET name = ?, description = ?, image_blob = ?, image_mime = ? WHERE idTransportType = ?";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(1, $data['name']);
            $stmt->bindValue(2, $data['description']);
            $stmt->bindValue(3, $blob, PDO::PARAM_LOB);
            $stmt->bindValue(4, $mime);
            $stmt->bindValue(5, $id);
            return $stmt->execute();
        } else {
            $sql = "UPDATE transport_type SET name = ?, description = ? WHERE idTransportType = ?";
            $stmt = $db->prepare($sql);
            return $stmt->execute([$data['name'], $data['description'], $id]);
        }
    }

    public static function deleteTransportType($id) {
        $db = self::getDb();
        $sql = "DELETE FROM transport_type WHERE idTransportType = ?";
        $stmt = $db->prepare($sql);
        return $stmt->execute([$id]);
    }

    public static function registerAgent(array $data) {
        $db = self::getDb();
        $name = trim($data['name']);
        $email = trim($data['email']);
        $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);
        // Force the role to 'agent' securely
        $role = 'agent';

        $stmt = $db->prepare('INSERT INTO users (username, email, password_hash, role) VALUES (:username, :email, :password_hash, :role)');
        
        try {
            $stmt->execute([
                'username'      => $name,
                'email'         => $email,
                'password_hash' => $passwordHash,
                'role'          => $role
            ]);
            return $db->lastInsertId();
        } catch (\PDOException $e) {
            error_log('[CivicPortal][AppModel] Failed to register agent: ' . $e->getMessage());
            return false;
        }
    }

    private static function handleTypePhotoUpload($file) {
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $uploadDir = __DIR__ . '/../View/assets/images/types/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = 'type_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return 'types/' . $fileName;
        }

        return null;
    }

    // ============================================
    // TRANSPORT FLEET LOGIC
    // ============================================

    public static function listTransports() {
        $sql = "SELECT t.*, tt.name as typeName FROM transport t LEFT JOIN transport_type tt ON t.idTransportType = tt.idTransportType";
        $db = self::getDb();
        return $db->query($sql)->fetchAll();
    }

    public static function addTransport($data) {
        $sql = "INSERT INTO transport (name, type, capacity, status, idTransportType) VALUES (?, ?, ?, ?, ?)";
        $db = self::getDb();
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            $data['name'],
            $data['type'] ?? '',
            (int)$data['capacity'],
            $data['status'],
            isset($data['idTransportType']) ? (int)$data['idTransportType'] : null
        ]);
    }

    public static function updateTransport($id, $data) {
        $sql = "UPDATE transport SET name = ?, type = ?, capacity = ?, status = ?, idTransportType = ? WHERE idTransport = ?";
        $db = self::getDb();
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            $data['name'],
            $data['type'] ?? '',
            (int)$data['capacity'],
            $data['status'],
            isset($data['idTransportType']) ? (int)$data['idTransportType'] : null,
            $id
        ]);
    }

    public static function deleteTransport($idTransport) {
        $sql = "DELETE FROM transport WHERE idTransport = ?";
        $db = self::getDb();
        $stmt = $db->prepare($sql);
        return $stmt->execute([$idTransport]);
    }

    public static function showTransport($idTransport) {
        $sql = "SELECT t.*, tt.name as typeName FROM transport t LEFT JOIN transport_type tt ON t.idTransportType = tt.idTransportType WHERE t.idTransport = ?";
        $db = self::getDb();
        $stmt = $db->prepare($sql);
        $stmt->execute([$idTransport]);
        return $stmt->fetch();
    }

    // ============================================
    // TRAJET ROUTING LOGIC
    // ============================================

    public static function listTrajets() {
        $sql = "SELECT t.*, tr.name as transportName, tr.capacity as transportCapacity, tr.type as transportType FROM trajet t LEFT JOIN transport tr ON t.idTransport = tr.idTransport";
        $db = self::getDb();
        return $db->query($sql)->fetchAll();
    }

    public static function listTrajetsByTypeAndSort($type, $sortBy = 'departure', $order = 'ASC') {
        $allowedSorts = ['departure', 'destination', 'departureTime', 'price'];
        $sortBy = in_array($sortBy, $allowedSorts) ? $sortBy : 'departure';
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

        $sql = "SELECT t.*, tr.name as transportName, tr.capacity as transportCapacity
                FROM trajet t
                JOIN transport tr ON t.idTransport = tr.idTransport
                JOIN transport_type tt ON tr.idTransportType = tt.idTransportType
                WHERE tt.name = ?
                ORDER BY t.$sortBy $order";
        $db = self::getDb();
        $query = $db->prepare($sql);
        $query->execute([$type]);
        return $query->fetchAll();
    }

    public static function getTrajet($id) {
        $sql = "SELECT * FROM trajet WHERE idTrajet = ?";
        $db = self::getDb();
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function addTrajet($data) {
        $sql = "INSERT INTO trajet (departure, destination, idTransport, departureTime, price, depLat, depLng, depAddress, destLat, destLng, destAddress) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $db = self::getDb();
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            $data['departure'],
            $data['destination'],
            $data['idTransport'],
            $data['departureTime'],
            $data['price'],
            $data['depLat'] ?? null,
            $data['depLng'] ?? null,
            $data['depAddress'] ?? null,
            $data['destLat'] ?? null,
            $data['destLng'] ?? null,
            $data['destAddress'] ?? null
        ]);
    }

    public static function updateTrajet($id, $data) {
        $sql = "UPDATE trajet SET departure = ?, destination = ?, idTransport = ?, departureTime = ?, price = ?, depLat = ?, depLng = ?, depAddress = ?, destLat = ?, destLng = ?, destAddress = ? WHERE idTrajet = ?";
        $db = self::getDb();
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            $data['departure'],
            $data['destination'],
            $data['idTransport'],
            $data['departureTime'],
            $data['price'],
            $data['depLat'] ?? null,
            $data['depLng'] ?? null,
            $data['depAddress'] ?? null,
            $data['destLat'] ?? null,
            $data['destLng'] ?? null,
            $data['destAddress'] ?? null,
            $id
        ]);
    }

    public static function deleteTrajet($idTrajet) {
        $sql = "DELETE FROM trajet WHERE idTrajet = ?";
        $db = self::getDb();
        $req = $db->prepare($sql);
        return $req->execute([$idTrajet]);
    }

    public static function getOccupancy($idTrajet) {
        $db = self::getDb();
        $sql = "SELECT tr.capacity FROM trajet t JOIN transport tr ON t.idTransport = tr.idTransport WHERE t.idTrajet = ?";
        $query = $db->prepare($sql);
        $query->execute([$idTrajet]);
        $capacity = ($result = $query->fetch()) ? (int)$result['capacity'] : 0;

        $sql2 = "SELECT COUNT(*) as sold FROM ticket WHERE idTrajet = ? AND status = 'Valid'";
        $query2 = $db->prepare($sql2);
        $query2->execute([$idTrajet]);
        $sold = (int)$query2->fetch()['sold'];

        return ['sold' => $sold, 'capacity' => $capacity, 'pct' => $capacity > 0 ? round(($sold / $capacity) * 100) : 0];
    }

    // ============================================
    // TICKET LOGIC
    // ============================================

    public static function listTickets() {
        $sql = "SELECT tk.*, t.departure, t.destination FROM ticket tk LEFT JOIN trajet t ON tk.idTrajet = t.idTrajet ORDER BY tk.issuedAt DESC";
        $db = self::getDb();
        return $db->query($sql)->fetchAll();
    }

    public static function listTicketsEnriched($userId = null) {
        $sql = "SELECT tk.*, tk.idTicket, t.departure, t.destination, t.departureTime, t.price, t.depLat, t.depLng, t.depAddress, t.destLat, t.destLng, t.destAddress,
                       tr.name as transportName, tr.capacity,
                       tt.idTransportType as typeId, tt.name as typeName, tt.description as typeDescription
                FROM ticket tk
                LEFT JOIN trajet t ON tk.idTrajet = t.idTrajet
                LEFT JOIN transport tr ON t.idTransport = tr.idTransport
                LEFT JOIN transport_type tt ON tr.idTransportType = tt.idTransportType";

        $db = self::getDb();
        if ($userId !== null) {
            $sql .= " WHERE tk.user_id = ? ORDER BY tk.issuedAt DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } else {
            $sql .= " ORDER BY tk.issuedAt DESC";
            return $db->query($sql)->fetchAll();
        }
    }

    public static function addTicket($data) {
        $db = self::getDb();
        try {
            $db->beginTransaction();
            $sql = "INSERT INTO ticket (user_id, ref, citizenName, idTrajet, issuedAt, status) VALUES (?, ?, ?, ?, NOW(), 'Valid')";
            $query = $db->prepare($sql);
            $result = $query->execute([
                $data['user_id'],
                self::generateRef(),
                $data['citizenName'],
                $data['idTrajet']
            ]);
            $db->commit();
            return $result;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function cancelTicket($idTicket) {
        $sql = "UPDATE ticket SET status = 'Cancelled' WHERE idTicket = ?";
        $db = self::getDb();
        $query = $db->prepare($sql);
        return $query->execute([$idTicket]);
    }

    public static function deleteTicket($idTicket) {
        $sql = "DELETE FROM ticket WHERE idTicket = ?";
        $db = self::getDb();
        $req = $db->prepare($sql);
        return $req->execute([$idTicket]);
    }

    public static function generateRef() {
        return 'CIV-' . rand(1000, 9999);
    }
}
