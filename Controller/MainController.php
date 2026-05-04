<?php
/**
 * MainController.php
 * The central router for CivicPortal.
 * Complaints/Grievances module REMOVED.
 * Added: Appointments, Notifications, enhanced Service Requests, BLOB image support.
 */

require_once __DIR__ . '/../Model/AppModel.php';
require_once __DIR__ . '/../Model/Database.php';
require_once __DIR__ . '/../Model/Transport.php';
require_once __DIR__ . '/../Model/TransportType.php';
require_once __DIR__ . '/../Model/Trajet.php';
require_once __DIR__ . '/../Model/Ticket.php';
require_once __DIR__ . '/UserController.php';
require_once __DIR__ . '/ForumPostController.php';

class MainController {

    // =========================================================================
    // AUTHORIZATION MIDDLEWARE
    // =========================================================================
    private static function authorize(string $action): void {
        static $map = [
            // Public — no session needed
            'login'    => 'public',
            'register' => 'public',

            // Any authenticated user
            'logout'                => 'any',
            'get_my_requests'       => 'any',
            'add_request'           => 'any',
            'get_programs'          => 'any',
            'get_program_detail'    => 'any',
            'get_enrollments'       => 'any',
            'enroll_user'           => 'any',
            'get_stats'             => 'any',
            'get_categories'        => 'any',
            'update_profile'        => 'any',
            'upload_profile_pic'    => 'any',
            'get_my_posts'          => 'any',
            // Appointments (citizen)
            'book_appointment'      => 'any',
            'get_my_appointments'   => 'any',
            'cancel_appointment'    => 'any',
            'get_available_slots'   => 'any',
            'get_service_types'     => 'any',
            // Notifications
            'get_notifications'     => 'any',
            'mark_notification_read'=> 'any',
            // Transport (citizens can view, only admins can create/edit)
            'list_transport_types'  => 'any',
            'list_transports'       => 'any',
            'get_transport'         => 'any',
            'list_all_trajets'      => 'any',
            'list_trajets'          => 'any',
            'list_tickets'          => 'any',
            'list_tickets_enriched' => 'any',
            'book_ticket'           => 'any',
            'cancel_ticket'         => 'any',

            // Staff (agent or admin)
            'get_requests'               => 'staff',
            'get_pending_enrollments'    => 'staff',
            'get_enrollments_by_program' => 'staff',
            'get_enrollment_counts'      => 'staff',
            'update_enrollment_status'   => 'staff',
            'update_status'              => 'staff',
            'get_assigned_requests'      => 'staff',
            'get_agent_appointments'     => 'staff',
            'update_appointment_status'  => 'staff',

            // Admin only
            'add_program'          => 'admin',
            'update_program'       => 'admin',
            'delete_program'       => 'admin',
            'add_category'         => 'admin',
            'update_category'      => 'admin',
            'delete_category'      => 'admin',
            'get_users'            => 'admin',
            'create_user'          => 'admin',
            'update_user'          => 'admin',
            'delete_user'          => 'admin',
            'toggle_user_active'   => 'admin',
            'get_agents'           => 'admin',
            'assign_request'       => 'admin',
            'get_all_appointments' => 'admin',
            'manage_slots'         => 'admin',
            'create_slot'          => 'admin',
            'delete_slot'          => 'admin',
            'get_all_slots'        => 'admin',
            'add_transport_type'   => 'admin',
            'update_transport_type'=> 'admin',
            'delete_transport_type'=> 'admin',
            'add_transport'        => 'admin',
            'update_transport'     => 'admin',
            'delete_transport'     => 'admin',
            'add_trajet'           => 'admin',
            'update_trajet'        => 'admin',
            'delete_trajet'        => 'admin',
        ];

        $required = $map[$action] ?? 'admin';
        $role     = $_SESSION['user_role'] ?? '';
        $userId   = $_SESSION['user_id']   ?? null;

        if ($required === 'public') return;
        if (!$userId) throw new Exception('Unauthorized: authentication required.');
        if ($required === 'any') return;
        if ($required === 'staff' && !in_array($role, ['agent', 'admin'], true))
            throw new Exception('Forbidden: staff access required.');
        if ($required === 'admin' && $role !== 'admin')
            throw new Exception('Forbidden: admin access required.');
    }

    public static function showData() {
        return [
            'requests' => AppModel::getRequests(),
            'programs' => AppModel::getPrograms(),
            'stats'    => AppModel::getStats(),
        ];
    }

    public static function handleRequest($action, $data) {
        self::authorize($action);

        switch ($action) {
            // --- Authentication & User ---
            case 'login':
                return UserController::login($data);
            case 'register':
                return UserController::register($data);
            case 'logout':
                UserController::logout();
                return ['success' => 'Logged out successfully'];
            case 'update_profile':
                $userId = $_SESSION['user_id'] ?? null;
                if (!$userId) throw new Exception("Unauthorized.");
                return UserController::updateProfile((int)$userId, $data);
            case 'upload_profile_pic':
                $userId = $_SESSION['user_id'] ?? null;
                if (!$userId) throw new Exception("Unauthorized.");
                $file = $data['profile_pic_file'] ?? null;
                if (!$file) throw new Exception("No file provided.");
                return UserController::uploadProfilePic((int)$userId, $file);

            // --- Service Requests ---
            case 'get_requests':
                return AppModel::getRequests();
            case 'get_my_requests':
                $userId = $_SESSION['user_id'];
                return AppModel::getRequestsByUser((int)$userId);
            case 'get_my_posts':
                $userId = $_SESSION['user_id'];
                return ForumPostController::getPostsByUserId((int)$userId);
            case 'add_request':
                $userId = $_SESSION['user_id'];
                $attachFile = $data['attachment_file'] ?? null;
                return AppModel::addRequest($data, (int)$userId, $attachFile);
            case 'update_status':
                return AppModel::updateRequestStatus($data['id'], $data['status']);
            case 'get_assigned_requests':
                $agentId = $_SESSION['user_id'];
                return AppModel::getRequestsByAssignee((int)$agentId);
            case 'assign_request':
                return AppModel::assignRequest((int)$data['request_id'], (int)$data['agent_id']);

            // --- Programs ---
            case 'get_programs':
                return AppModel::getPrograms();
            case 'add_program':
                $imageFile = $data['image_file'] ?? null;
                return AppModel::addProgram($data, $imageFile);
            case 'update_program':
                if (!isset($data['id'])) {
                    error_log('[CivicPortal][MainController] Missing program ID for update.');
                    throw new Exception("Program ID is required for update.");
                }
                $imageFile = $data['image_file'] ?? null;
                error_log('[CivicPortal][MainController] Updating program ' . $data['id'] . ' with data: ' . json_encode($data));
                return AppModel::updateProgram($data['id'], $data, $imageFile);
            case 'delete_program':
                return AppModel::deleteProgram($data['id']);
            case 'get_program_detail':
                return AppModel::getProgramById($data['id']);

            // --- Enrollments ---
            case 'get_enrollments':
                return AppModel::getEnrollments($data['userId']);
            case 'get_pending_enrollments':
                return AppModel::getPendingEnrollments();
            case 'get_enrollments_by_program':
                return AppModel::getEnrollmentsByProgram($data['programId']);
            case 'get_enrollment_counts':
                return AppModel::getAllEnrollmentsCounts();
            case 'enroll_user':
                return AppModel::enrollUser($data['userId'], $data['programId']);
            case 'update_enrollment_status':
                return AppModel::updateEnrollmentStatus($data['id'], $data['status']);

            // --- Stats ---
            case 'get_stats':
                return AppModel::getStats();
            // --- Complaints ---
            case 'get_complaints':
                return AppModel::getRequests();
            case 'add_complaint':
                return AppModel::addRequest($data['subject'] ?? $data['title'] ?? 'Complaint', $data['userId'] ?? 1);

                        // --- Transport & Route CRUD ---
            case 'list_transport_types':
                return AppModel::listTransportTypes();
            case 'add_transport_type':
                return AppModel::addTransportType($data, $data['type_image_file'] ?? null);
            case 'update_transport_type':
                return AppModel::updateTransportType($data['idTransportType'], $data, $data['type_image_file'] ?? null);
            case 'delete_transport_type':
                return AppModel::deleteTransportType($data['idTransportType']);

            case 'list_transports':
                return AppModel::listTransports();
            case 'add_transport':
                return AppModel::addTransport($data);
            case 'update_transport':
                return AppModel::updateTransport($data['idTransport'], $data);
            case 'delete_transport':
                return AppModel::deleteTransport($data['idTransport']);

            case 'list_all_trajets':
                $trajets = AppModel::listTrajets();
                $enriched = [];
                foreach ($trajets as $t) {
                    $occ = AppModel::getOccupancy($t['idTrajet']);
                    $enriched[] = array_merge($t, ['capacity' => $occ['capacity'], 'sold' => $occ['sold']]);
                }
                return $enriched;
            case 'list_trajets':
                $type = $data['type'] ?? 'Bus';
                $sortBy = $data['sortBy'] ?? 'departure';
                $order = $data['order'] ?? 'ASC';
                $trajets = AppModel::listTrajetsByTypeAndSort($type, $sortBy, $order);
                $enriched = [];
                foreach ($trajets as $t) {
                    $occ = AppModel::getOccupancy($t['idTrajet']);
                    $enriched[] = array_merge($t, ['capacity' => $occ['capacity'], 'sold' => $occ['sold']]);
                }
                return $enriched;
            case 'add_trajet':
                return AppModel::addTrajet($data);
            case 'update_trajet':
                return AppModel::updateTrajet($data['idTrajet'], $data);
            case 'delete_trajet':
                return AppModel::deleteTrajet($data['idTrajet']);

            case 'list_tickets':
                return AppModel::listTickets();
            case 'list_tickets_enriched':
                return AppModel::listTicketsEnriched();
            case 'book_ticket':
                $occ = AppModel::getOccupancy($data['idTrajet']);
                if ($occ['capacity'] > 0 && $occ['sold'] >= $occ['capacity']) {
                    throw new Exception('Route is sold out.');
                }
                return AppModel::addTicket($data);
            case 'cancel_ticket':
                return AppModel::cancelTicket($data['idTicket']);

            // --- Categories ---
            case 'get_categories':
                return AppModel::getCategories();
            case 'add_category':
                return AppModel::addCategory($data['name']);
            case 'update_category':
                return AppModel::updateCategory($data['id'], $data['name']);
            case 'delete_category':
                return AppModel::deleteCategory($data['id']);

            // --- User Management (Admin) ---
            case 'get_users':
                return UserController::getAllUsers();
            case 'create_user':
                return UserController::createUser($data);
            case 'update_user':
                return UserController::updateProfile((int)$data['id'], $data);
            case 'delete_user':
                return UserController::deleteUser((int)$data['id']);
            case 'toggle_user_active':
                return UserController::toggleUserActive((int)$data['id'], (bool)$data['active']);
            case 'get_agents':
                return UserController::getAgents();

            // --- Appointments ---
            case 'book_appointment':
                return AppModel::createAppointment($data, (int)$_SESSION['user_id']);
            case 'get_my_appointments':
                return AppModel::getAppointmentsByUser((int)$_SESSION['user_id']);
            case 'cancel_appointment':
                return AppModel::cancelAppointment((int)$data['id'], (int)$_SESSION['user_id']);
            case 'get_agent_appointments':
                return AppModel::getAppointmentsByAgent((int)$_SESSION['user_id']);
            case 'get_all_appointments':
                return AppModel::getAllAppointments();
            case 'update_appointment_status':
                return AppModel::updateAppointmentStatus(
                    (int)$data['id'], $data['status'],
                    $data['reason'] ?? null, $data['new_date'] ?? null, $data['new_time'] ?? null
                );
            case 'get_available_slots':
                return AppModel::getAvailableSlots($data['service_type'], $data['date']);
            case 'get_service_types':
                return AppModel::getServiceTypes();

            // --- Appointment Slots (Admin) ---
            case 'create_slot':
                return ['id' => AppModel::createSlot($data)];
            case 'delete_slot':
                AppModel::deleteSlot((int)$data['id']);
                return ['success' => 'Slot deleted.'];
            case 'get_all_slots':
                return AppModel::getAllSlots();

            // --- Notifications ---
            case 'get_notifications':
                return AppModel::getNotificationsByUser((int)$_SESSION['user_id']);
            case 'mark_notification_read':
                AppModel::markNotificationRead((int)$data['id']);
                return ['success' => 'Notification marked as read.'];

            default:
                throw new Exception("Invalid action: " . $action);
        }
    }

    // =========================================================================
    // TRANSPORT MODULE
    // =========================================================================

    private static function getDb(): \PDO {
        return Database::getInstance()->getConnection();
    }

    // --- Transport Types ---
    public static function listTransportTypes(): array {
        return self::getDb()->query("SELECT * FROM transport_type ORDER BY name ASC")->fetchAll();
    }

    // --- Transports ---
    public static function listTransports(): array {
        return self::getDb()->query(
            "SELECT t.*, tt.name as typeName FROM transport t
             LEFT JOIN transport_type tt ON t.idTransportType = tt.idTransportType"
        )->fetchAll();
    }

    public static function showTransport(int $id): ?array {
        $stmt = self::getDb()->prepare(
            "SELECT t.*, tt.name as typeName FROM transport t
             LEFT JOIN transport_type tt ON t.idTransportType = tt.idTransportType
             WHERE t.idTransport = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function addTransport(Transport $t, ?array $imageFile = null): void {
        $db = self::getDb();
        $blob = null; $mime = null;
        if ($imageFile && $imageFile['error'] === UPLOAD_ERR_OK) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($imageFile['tmp_name']);
            $blob = file_get_contents($imageFile['tmp_name']);
        }

        $stmt = $db->prepare(
            "INSERT INTO transport (name, type, capacity, status, idTransportType, vehicle_image, vehicle_image_mime)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bindValue(1, $t->getName());
        $stmt->bindValue(2, $t->getType());
        $stmt->bindValue(3, $t->getCapacity(), PDO::PARAM_INT);
        $stmt->bindValue(4, $t->getStatus());
        $stmt->bindValue(5, $t->getIdTransportType());
        $stmt->bindValue(6, $blob, PDO::PARAM_LOB);
        $stmt->bindValue(7, $mime);
        $stmt->execute();
    }

    public static function updateTransport(Transport $t, int $id, ?array $imageFile = null): void {
        $db = self::getDb();
        if ($imageFile && $imageFile['error'] === UPLOAD_ERR_OK) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($imageFile['tmp_name']);
            $blob = file_get_contents($imageFile['tmp_name']);
            $stmt = $db->prepare(
                "UPDATE transport SET name=?, type=?, capacity=?, status=?, idTransportType=?, vehicle_image=?, vehicle_image_mime=? WHERE idTransport=?"
            );
            $stmt->bindValue(1, $t->getName());
            $stmt->bindValue(2, $t->getType());
            $stmt->bindValue(3, $t->getCapacity(), PDO::PARAM_INT);
            $stmt->bindValue(4, $t->getStatus());
            $stmt->bindValue(5, $t->getIdTransportType());
            $stmt->bindValue(6, $blob, PDO::PARAM_LOB);
            $stmt->bindValue(7, $mime);
            $stmt->bindValue(8, $id, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $db->prepare(
                "UPDATE transport SET name=?, type=?, capacity=?, status=?, idTransportType=? WHERE idTransport=?"
            );
            $stmt->execute([$t->getName(), $t->getType(), $t->getCapacity(), $t->getStatus(), $t->getIdTransportType(), $id]);
        }
    }

    public static function deleteTransport(int $id): void {
        self::getDb()->prepare("DELETE FROM transport WHERE idTransport = ?")->execute([$id]);
    }

    // --- Trajets (Routes) ---
    public static function listTrajets(): array {
        return self::getDb()->query(
            "SELECT t.*, tr.name as transportName, tr.capacity as transportCapacity, tr.type as transportType
             FROM trajet t LEFT JOIN transport tr ON t.idTransport = tr.idTransport"
        )->fetchAll();
    }

    public static function listTrajetsByTypeAndSort(string $type, string $sortBy = 'departure', string $order = 'ASC'): array {
        $allowedSorts = ['departure', 'destination', 'departureTime', 'price'];
        $sortBy = in_array($sortBy, $allowedSorts) ? $sortBy : 'departure';
        $order  = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
        $stmt = self::getDb()->prepare(
            "SELECT t.*, tr.name as transportName, tr.capacity as transportCapacity
             FROM trajet t JOIN transport tr ON t.idTransport = tr.idTransport
             WHERE tr.type = ? ORDER BY t.{$sortBy} {$order}"
        );
        $stmt->execute([$type]);
        return $stmt->fetchAll();
    }

    public static function addTrajet(Trajet $t): void {
        $stmt = self::getDb()->prepare(
            "INSERT INTO trajet (departure, destination, idTransport, departureTime, price, depLat, depLng, depAddress, destLat, destLng, destAddress)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $t->getDeparture(), $t->getDestination(), $t->getIdTransport(),
            $t->getDepartureTime(), $t->getPrice(),
            $t->getDepLat(), $t->getDepLng(), $t->getDepAddress(),
            $t->getDestLat(), $t->getDestLng(), $t->getDestAddress(),
        ]);
    }

    public static function deleteTrajet(int $id): void {
        self::getDb()->prepare("DELETE FROM trajet WHERE idTrajet = ?")->execute([$id]);
    }

    public static function showTrajet(int $id): ?array {
        $stmt = self::getDb()->prepare("SELECT * FROM trajet WHERE idTrajet = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function getOccupancy(int $idTrajet): array {
        $db = self::getDb();
        $stmt = $db->prepare("SELECT tr.capacity FROM trajet t JOIN transport tr ON t.idTransport = tr.idTransport WHERE t.idTrajet = ?");
        $stmt->execute([$idTrajet]);
        $row = $stmt->fetch();
        $capacity = $row ? (int)$row['capacity'] : 0;

        $stmt2 = $db->prepare("SELECT COUNT(*) as sold FROM ticket WHERE idTrajet = ? AND status = 'Valid'");
        $stmt2->execute([$idTrajet]);
        $sold = (int)$stmt2->fetch()['sold'];

        return ['sold' => $sold, 'capacity' => $capacity, 'pct' => $capacity > 0 ? (int)round(($sold / $capacity) * 100) : 0];
    }

    // --- Tickets ---
    public static function listTickets(): array {
        return self::getDb()->query(
            "SELECT tk.*, t.departure, t.destination FROM ticket tk
             LEFT JOIN trajet t ON tk.idTrajet = t.idTrajet ORDER BY tk.issuedAt DESC"
        )->fetchAll();
    }

    public static function listTicketsEnriched(?int $userId = null): array {
        $db = self::getDb();
        $sql = "SELECT tk.*, tk.idTicket, t.departure, t.destination, t.departureTime, t.price,
                       t.depLat, t.depLng, t.depAddress, t.destLat, t.destLng, t.destAddress,
                       tr.name as transportName, tr.capacity,
                       tt.name as typeName, tt.photo_url as typePhoto, tt.description as typeDescription
                FROM ticket tk
                LEFT JOIN trajet t ON tk.idTrajet = t.idTrajet
                LEFT JOIN transport tr ON t.idTransport = tr.idTransport
                LEFT JOIN transport_type tt ON tr.idTransportType = tt.idTransportType";

        if ($userId !== null) {
            $sql .= " WHERE tk.user_id = ? ORDER BY tk.issuedAt DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute([$userId]);
        } else {
            $sql .= " ORDER BY tk.issuedAt DESC";
            $stmt = $db->query($sql);
        }
        return $stmt->fetchAll();
    }

    public static function addTicket(Ticket $ticket): void {
        $stmt = self::getDb()->prepare(
            "INSERT INTO ticket (user_id, ref, citizenName, idTrajet, issuedAt, status) VALUES (?, ?, ?, ?, NOW(), 'Valid')"
        );
        $stmt->execute([$ticket->getIdUser(), $ticket->getRef(), $ticket->getCitizenName(), $ticket->getIdTrajet()]);
    }

    public static function cancelTicket(int $idTicket): void {
        self::getDb()->prepare("UPDATE ticket SET status = 'Cancelled' WHERE idTicket = ?")->execute([$idTicket]);
    }

    public static function generateRef(): string {
        return 'CIV-' . rand(1000, 9999);
    }
}
