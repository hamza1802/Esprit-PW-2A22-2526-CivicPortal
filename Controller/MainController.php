<?php
/**
 * MainController.php
 * The "Brain" of CivicPortal - bridges Model and View.
 */

require_once __DIR__ . '/../Model/AppModel.php';

class MainController {

    /**
     * showData() - Retrieve and return all platform data for display.
     */
    public static function showData() {
        return [
            'requests'   => AppModel::getRequests(),
            'complaints' => AppModel::getComplaints(),
            'stats'      => AppModel::getStats(),
        ];
    }

    /**
     * handleRequest() - Routes API actions to the appropriate Model method.
     */
    public static function handleRequest($action, $data) {
        switch ($action) {
            case 'get_requests':
                return AppModel::getRequests();

            case 'add_request':
                return AppModel::addRequest($data['type'], $data['userId']);

            case 'update_status':
                return AppModel::updateRequestStatus($data['id'], $data['status']);

            case 'add_complaint':
                return AppModel::addComplaint($data['subject'], $data['body'], $data['userId']);

            case 'get_complaints':
                return AppModel::getComplaints();

            case 'get_stats':
                return AppModel::getStats();

            default:
                throw new Exception("Invalid action: " . $action);
        }
    }
}
?>
