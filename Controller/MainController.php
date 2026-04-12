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
        // Ensure default users exist for FK constraints
        AppModel::ensureDefaultUser();

        switch ($action) {
            // ── Request CRUD ─────────────────────────────────────
            case 'get_requests':
                return AppModel::getRequests();

            case 'get_request':
                return AppModel::getRequestById((int)$data['id']);

            case 'add_request':
                return AppModel::addRequest(
                    $data['title'],
                    $data['description'] ?? '',
                    (int)$data['userId']
                );

            case 'update_request':
                return AppModel::updateRequest(
                    (int)$data['id'],
                    $data['description']
                );

            case 'update_status':
                return AppModel::updateRequestStatus((int)$data['id'], $data['status']);

            case 'delete_request':
                return AppModel::deleteRequest((int)$data['id']);

            // ── Document CRUD ────────────────────────────────────
            case 'get_documents':
                return AppModel::getDocumentsByRequest((int)$data['requestId']);

            case 'add_document':
                return AppModel::addDocument(
                    (int)$data['requestId'],
                    $data['filePath'],
                    $data['type'] ?? 'other'
                );

            case 'update_document':
                return AppModel::updateDocument(
                    (int)$data['id'],
                    $data['filePath'],
                    $data['type'] ?? 'other'
                );

            case 'delete_document':
                return AppModel::deleteDocument((int)$data['id']);

            // ── Complaints ───────────────────────────────────────
            case 'add_complaint':
                return AppModel::addComplaint($data['subject'], $data['body'], (int)$data['userId']);

            case 'get_complaints':
                return AppModel::getComplaints();

            // ── Stats ────────────────────────────────────────────
            case 'get_stats':
                return AppModel::getStats();

            default:
                throw new Exception("Invalid action: " . $action);
        }
    }
}
?>
