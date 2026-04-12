<?php
/**
 * MainController.php
 * The "Brain" of CivicPortal - bridges Model and View.
 * Updated to handle Parks and Recreation CRUD.
 */

require_once __DIR__ . '/../Model/AppModel.php';

class MainController {

    public static function showData() {
        return [
            'requests'   => AppModel::getRequests(),
            'programs'   => AppModel::getPrograms(),
            'stats'      => AppModel::getStats(),
        ];
    }

    public static function handleRequest($action, $data) {
        switch ($action) {
            // --- Services & Requests ---
            case 'get_requests':
                return AppModel::getRequests();
            case 'add_request':
                return AppModel::addRequest($data['type'], $data['userId']);
            case 'update_status':
                return AppModel::updateRequestStatus($data['id'], $data['status']);

            // --- Parks & Recreation (Program CRUD) ---
            case 'get_programs':
                return AppModel::getPrograms();
            case 'add_program':
                // Check for uploaded file in 'image' key of $data or Global $_FILES
                $imageFile = $data['image_file'] ?? null;
                return AppModel::addProgram($data, $imageFile);
            case 'update_program':
                $id = $data['id'];
                $imageFile = $data['image_file'] ?? null;
                return AppModel::updateProgram($id, $data, $imageFile);
            case 'delete_program':
                return AppModel::deleteProgram($data['id']);

            // --- Enrollments ---
            case 'get_enrollments':
                return AppModel::getEnrollments($data['userId']);
            case 'get_pending_enrollments':
                return AppModel::getPendingEnrollments();
            case 'get_enrollments_by_program':
                return AppModel::getEnrollmentsByProgram($data['programId']);
            case 'get_program_detail':
                return AppModel::getProgramById($data['id']);
            case 'get_enrollment_counts':
                return AppModel::getAllEnrollmentsCounts();
            case 'enroll_user':
                return AppModel::enrollUser($data['userId'], $data['programId']);
            case 'update_enrollment_status':
                return AppModel::updateEnrollmentStatus($data['id'], $data['status']);

            // --- Stats ---
            case 'get_stats':
                return AppModel::getStats();

            default:
                throw new Exception("Invalid action: " . $action);
        }
    }
}
?>
