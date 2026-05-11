<?php
/**
 * MainController.php
 * The central router for CivicPortal.
 * Complaints/Grievances module REMOVED.
 * Added: Appointments, Notifications, enhanced Service Requests, BLOB image support.
 */

require_once __DIR__ . '/../Model/AppModel.php';
require_once __DIR__ . '/../Model/AIService.php';
require_once __DIR__ . '/../Model/Database.php';
require_once __DIR__ . '/../Model/Transport.php';
require_once __DIR__ . '/../Model/TransportType.php';
require_once __DIR__ . '/../Model/Trajet.php';
require_once __DIR__ . '/../Model/Ticket.php';
require_once __DIR__ . '/UserController.php';
require_once __DIR__ . '/ForumPostController.php';
require_once __DIR__ . '/ForumCommentController.php';

class MainController {

    // =========================================================================
    // AUTHORIZATION MIDDLEWARE
    // =========================================================================
    private static function authorize(string $action): void {
        static $map = [
            // Public — no session needed
            'login'                 => 'public',
            'register'              => 'public',
            'request_reset'         => 'public',
            'reset_password'        => 'public',
            'verify_otp'            => 'public',
            'get_programs'          => 'public',
            'list_transport_types'  => 'public',
            'get_service_types'     => 'public',
            'list_trajets'          => 'public',

            // Any authenticated user
            'logout'                => 'any',
            'get_my_requests'       => 'any',
            'add_request'           => 'any',
            'get_request'           => 'any',  // ownership re-checked below
            'update_request'        => 'any',  // citizen edits their own pending request
            'delete_request'        => 'any',  // citizen deletes their own pending request
            'get_request_history'   => 'any',  // ownership re-checked below
            'get_documents'         => 'any',  // ownership re-checked below
            'add_document'          => 'any',  // citizens may attach to their own request
            'upload_files'          => 'any',  // multipart upload alias
            'replace_file'          => 'any',
            'delete_document'       => 'any',  // ownership re-checked below
            'ai_improve_description'=> 'any',  // citizen-side AI helper
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
            // Notifications
            'get_notifications'     => 'any',
            'mark_notification_read'=> 'any',
            // Transport (citizens can view, only admins can create/edit)
            'list_transports'       => 'any',
            'get_transport'         => 'any',
            'list_all_trajets'      => 'any',
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
            'ai_analyze_request'         => 'staff',

            // Admin only
            'add_program'          => 'admin',
            'update_program'       => 'admin',
            'delete_program'       => 'admin',
            'add_category'         => 'admin',
            'update_category'      => 'admin',
            'delete_category'      => 'admin',
            'get_users'            => 'staff',
            'get_user'             => 'staff',
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

            // Forum moderation (admin)
            'get_forum_posts'      => 'admin',
            'get_forum_comments'   => 'admin',
            'forum_update_status'  => 'admin',
            'forum_delete_post'    => 'admin',
            'forum_delete_comment' => 'admin',
            'get_forum_stats'      => 'admin',
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
            case 'request_reset':
                return UserController::requestPasswordReset($data['email'] ?? '');
            case 'reset_password':
                return UserController::resetPassword(
                    $data['token'] ?? '',
                    $data['password'] ?? '',
                    $data['confirm_password'] ?? ''
                );
            case 'verify_otp':
                $userId = $_SESSION['pending_2fa_user_id'] ?? null;
                if (!$userId) throw new Exception("No pending verification.");
                if (UserController::verifyOtp((int)$userId, $data['otp_code'] ?? '')) {
                    $user = UserController::getUserById((int)$userId);
                    session_regenerate_id(true);
                    $_SESSION['user_id']    = $user->getId();
                    $_SESSION['user_name']  = $user->getDisplayName();
                    $_SESSION['user_email'] = $user->getEmail();
                    $_SESSION['user_role']  = $user->getRole();
                    unset($_SESSION['pending_2fa_user_id']);
                    return ['success' => 'Verified.', 'user' => $user];
                }
                return ['errors' => ['otp_code' => 'Invalid or expired code.']];
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
                return AppModel::getRequests([
                    'search' => trim((string)($data['search'] ?? '')),
                    'status' => trim((string)($data['status'] ?? '')),
                    'sort'   => (string)($data['sort']  ?? 'created_at'),
                    'order'  => (string)($data['order'] ?? 'DESC'),
                ]);
            case 'get_my_requests':
                $userId = $_SESSION['user_id'];
                return AppModel::getRequestsByUser((int)$userId);
            case 'get_request': {
                $reqId   = (int)($data['id'] ?? 0);
                $request = AppModel::getRequestById($reqId);
                if (!$request) throw new Exception("Request not found.");
                $role    = $_SESSION['user_role'] ?? '';
                $userId  = (int)($_SESSION['user_id'] ?? 0);
                $owner   = (int)($request['user_id'] ?? 0);
                if ($role === 'citizen' && $owner !== $userId) {
                    throw new Exception('Forbidden: not your request.');
                }
                $request['documents'] = AppModel::getDocumentsByRequest($reqId);
                $request['history']   = AppModel::getRequestHistory($reqId);
                return $request;
            }
            case 'get_request_history': {
                $reqId   = (int)($data['id'] ?? $data['requestId'] ?? 0);
                $request = AppModel::getRequestById($reqId);
                if (!$request) throw new Exception("Request not found.");
                $role    = $_SESSION['user_role'] ?? '';
                $userId  = (int)($_SESSION['user_id'] ?? 0);
                if ($role === 'citizen' && (int)$request['user_id'] !== $userId) {
                    throw new Exception('Forbidden: not your request.');
                }
                return AppModel::getRequestHistory($reqId);
            }
            case 'update_request': {
                $reqId   = (int)($data['id'] ?? 0);
                $request = AppModel::getRequestById($reqId);
                if (!$request) throw new Exception("Request not found.");
                $role    = $_SESSION['user_role'] ?? '';
                $userId  = (int)($_SESSION['user_id'] ?? 0);
                if ($role === 'citizen' && (int)$request['user_id'] !== $userId) {
                    throw new Exception('Forbidden: not your request.');
                }
                if ($role === 'citizen' && ($request['status'] ?? 'pending') !== 'pending') {
                    throw new Exception('Only pending requests can be edited.');
                }
                return AppModel::updateRequest($reqId, (string)($data['description'] ?? ''));
            }
            case 'delete_request': {
                $reqId   = (int)($data['id'] ?? 0);
                $request = AppModel::getRequestById($reqId);
                if (!$request) throw new Exception("Request not found.");
                $role    = $_SESSION['user_role'] ?? '';
                $userId  = (int)($_SESSION['user_id'] ?? 0);
                if ($role === 'citizen' && (int)$request['user_id'] !== $userId) {
                    throw new Exception('Forbidden: not your request.');
                }
                if ($role === 'citizen' && ($request['status'] ?? 'pending') !== 'pending') {
                    throw new Exception('Only pending requests can be deleted.');
                }
                AppModel::deleteRequest($reqId);
                return ['success' => 'Request deleted.'];
            }
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

            // --- Documents ---
            case 'get_documents': {
                $reqId   = (int)($data['requestId'] ?? $data['request_id'] ?? 0);
                $request = AppModel::getRequestById($reqId);
                if (!$request) throw new Exception("Request not found.");
                $role    = $_SESSION['user_role'] ?? '';
                $userId  = (int)($_SESSION['user_id'] ?? 0);
                if ($role === 'citizen' && (int)$request['user_id'] !== $userId) {
                    throw new Exception('Forbidden: not your request.');
                }
                return AppModel::getDocumentsByRequest($reqId);
            }
            case 'add_document': {
                $reqId   = (int)($data['requestId'] ?? $data['request_id'] ?? 0);
                $request = AppModel::getRequestById($reqId);
                if (!$request) throw new Exception("Request not found.");
                $role    = $_SESSION['user_role'] ?? '';
                $userId  = (int)($_SESSION['user_id'] ?? 0);
                if ($role === 'citizen' && (int)$request['user_id'] !== $userId) {
                    throw new Exception('Forbidden: not your request.');
                }
                return AppModel::addDocument(
                    $reqId,
                    (string)($data['filePath'] ?? ''),
                    (string)($data['type']     ?? 'other')
                );
            }
            case 'delete_document': {
                $docId = (int)($data['id'] ?? 0);
                $doc   = AppModel::getDocumentById($docId);
                if (!$doc) throw new Exception("Document not found.");
                $request = AppModel::getRequestById((int)$doc['requestId']);
                $role    = $_SESSION['user_role'] ?? '';
                $userId  = (int)($_SESSION['user_id'] ?? 0);
                if ($role === 'citizen' && $request && (int)$request['user_id'] !== $userId) {
                    throw new Exception('Forbidden: not your document.');
                }
                AppModel::deleteDocument($docId);
                return ['success' => 'Document deleted.'];
            }

            // --- AI assistant (Service Requests) ---
            case 'ai_improve_description':
                return AIService::improveDescription(
                    (string)($data['serviceType'] ?? ''),
                    (string)($data['description'] ?? ''),
                    is_array($data['requiredDocuments'] ?? null) ? $data['requiredDocuments'] : []
                );
            case 'ai_analyze_request': {
                $reqId   = (int)($data['requestId'] ?? $data['id'] ?? 0);
                $request = AppModel::getRequestById($reqId);
                if (!$request) throw new Exception("Request $reqId not found.");
                $documents = AppModel::getDocumentsByRequest($reqId);
                $result    = AIService::analyzeRequest($request, $documents);
                $rec       = strtoupper((string)($result['recommendation'] ?? 'review'));
                AppModel::logRequestEvent(
                    $reqId,
                    'ai_analyzed',
                    null,
                    null,
                    "AI analysis run — recommendation: {$rec}."
                );
                return $result;
            }

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
            case 'get_all_enrollments':
                return AppModel::getAllEnrollments();
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
                return UserController::getAllUsers(
                    trim((string)($data['search'] ?? '')),
                    (string)($data['sort'] ?? 'u.id DESC')
                );
            case 'get_user':
                $userId = (int)($data['id'] ?? $data['user_id'] ?? 0);
                $user = UserController::getUserById($userId);
                if (!$user) throw new Exception("User not found.");
                return $user;
            case 'create_user':
                return UserController::createUser($data);
            case 'update_user':
                $userId = (int)($data['id'] ?? $data['user_id'] ?? 0);
                if ($userId <= 0) throw new Exception("Target User ID is missing or invalid.");
                return UserController::updateProfile($userId, $data);
            case 'delete_user':
                $userId = (int)($data['id'] ?? $data['user_id'] ?? 0);
                return UserController::deleteUser($userId);
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

            // --- Forum Moderation (Admin) ---
            case 'get_forum_posts':
                return ForumPostController::getAllPosts(
                    $data['category'] ?? null,
                    $data['status']   ?? null
                );
            case 'get_forum_comments':
                if (!empty($data['post_id'])) {
                    return ForumCommentController::getCommentsByPost((int)$data['post_id']);
                }
                return ForumCommentController::getAllComments();
            case 'forum_update_status':
                $result = ForumPostController::updateStatus((int)$data['post_id'], $data['status']);
                if (!$result) throw new Exception('Failed to update post status.');
                return ['success' => 'Post status updated.'];
            case 'forum_delete_post':
                $result = ForumPostController::deletePost((int)$data['post_id'], 0, true);
                if (!$result) throw new Exception('Failed to delete post.');
                return ['success' => 'Post deleted.'];
            case 'forum_delete_comment':
                $result = ForumCommentController::deleteComment((int)$data['comment_id'], 0, true);
                if (!$result) throw new Exception('Failed to delete comment.');
                return ['success' => 'Comment deleted.'];
            case 'get_forum_stats':
                require_once __DIR__ . '/AIModerator.php';
                return AIModerator::getStats();

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
