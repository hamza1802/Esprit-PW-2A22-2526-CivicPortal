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
        self::validateProgramData($data);
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
        self::validateProgramData($data);
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

    /**
     * validateProgramData() - Server-side validation and sanitization logic.
     */
    private static function validateProgramData(&$data) {
        $data['title']       = trim($data['title'] ?? '');
        $data['description'] = trim($data['description'] ?? '');
        $data['category']    = trim($data['category'] ?? '');
        $data['location']    = trim($data['location'] ?? '');
        $data['capacity']    = (isset($data['capacity']) && is_numeric($data['capacity'])) ? (int)$data['capacity'] : 0;

        if (empty($data['title']) || strlen($data['title']) < 5) {
            throw new Exception("Title must be at least 5 characters.");
        }
        if (empty($data['description']) || strlen($data['description']) < 20) {
            throw new Exception("Description must be at least 20 characters.");
        }
        if (empty($data['category'])) {
            throw new Exception("Category is required.");
        }
        if ($data['capacity'] <= 0) {
            throw new Exception("Capacity must be a positive number.");
        }
        if (empty($data['location']) || strlen($data['location']) < 3) {
            throw new Exception("Location must be at least 3 characters.");
        }
        
        // Basic HTML sanitization for fields that are displayed
        $data['title']       = htmlspecialchars($data['title'], ENT_QUOTES, 'UTF-8');
        $data['description'] = htmlspecialchars($data['description'], ENT_QUOTES, 'UTF-8');
        $data['location']    = htmlspecialchars($data['location'], ENT_QUOTES, 'UTF-8');
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
    // ============================================
    // TRANSPORT TYPE MANAGEMENT
    // ============================================

    public static function listTransportTypes() {
        $sql = "SELECT * FROM transport_type ORDER BY name ASC";
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
        $photoUrl = self::handleTypePhotoUpload($imageFile);
        $sql = "INSERT INTO transport_type (name, description, photo_url) VALUES (?, ?, ?)";
        $db = self::getDb();
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            $data['name'],
            $data['description'],
            $photoUrl
        ]);
    }

    public static function updateTransportType($id, $data, $imageFile = null) {
        $db = self::getDb();
        $photoUrl = self::handleTypePhotoUpload($imageFile);
        
        if ($photoUrl) {
            $sql = "UPDATE transport_type SET name = ?, description = ?, photo_url = ? WHERE idTransportType = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$data['name'], $data['description'], $photoUrl, $id]);
        } else {
            $sql = "UPDATE transport_type SET name = ?, description = ? WHERE idTransportType = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$data['name'], $data['description'], $id]);
        }
        return true;
    }

    public static function deleteTransportType($id) {
        $db = self::getDb();
        // 1. Fetch the record to get the photo path before deletion
        $existing = self::showTransportType($id);

        // 2. Delete the database row
        $sql = "DELETE FROM transport_type WHERE idTransportType = ?";
        $req = $db->prepare($sql);
        $req->execute([$id]);

        // 3. Cleanup: remove the associated photo file from disk
        if ($existing && !empty($existing['photo_url'])) {
            $filePath = __DIR__ . '/../View/assets/images/' . $existing['photo_url'];
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }
        return true;
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

        $sql = "SELECT t.*, tr.name as transportName, tr.capacity as transportCapacity FROM trajet t JOIN transport tr ON t.idTransport = tr.idTransport WHERE tr.type = ? ORDER BY t.$sortBy $order";
        $db = self::getDb();
        $query = $db->prepare($sql);
        $query->execute([$type]);
        return $query->fetchAll();
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

    public static function listTicketsEnriched() {
        $sql = "SELECT tk.*, t.departure, t.destination, t.departureTime, t.price, t.depLat, t.depLng, t.depAddress, t.destLat, t.destLng, t.destAddress,
                       tr.name as transportName, tr.capacity,
                       tt.name as typeName, tt.photo_url as typePhoto, tt.description as typeDescription
                FROM ticket tk
                LEFT JOIN trajet t ON tk.idTrajet = t.idTrajet
                LEFT JOIN transport tr ON t.idTransport = tr.idTransport
                LEFT JOIN transport_type tt ON tr.idTransportType = tt.idTransportType
                ORDER BY tk.issuedAt DESC";
        $db = self::getDb();
        return $db->query($sql)->fetchAll();
    }

    public static function addTicket($data) {
        $sql = "INSERT INTO ticket (user_id, ref, citizenName, idTrajet, issuedAt, status) VALUES (?, ?, ?, ?, NOW(), 'Valid')";
        $db = self::getDb();
        $query = $db->prepare($sql);
        return $query->execute([
            $data['idUser'],
            self::generateRef(),
            $data['citizenName'],
            $data['idTrajet']
        ]);
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
?>

