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
            // --- Transport & Route CRUD ---
            case 'list_transport_types':
                return AppModel::listTransportTypes();
            case 'add_transport_type':
                return AppModel::addTransportType($data, $data['image_file'] ?? null);
            case 'update_transport_type':
                return AppModel::updateTransportType($data['idTransportType'], $data, $data['image_file'] ?? null);
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

            default:
                throw new Exception("Invalid action: " . $action);
        }
    }
}
?>

