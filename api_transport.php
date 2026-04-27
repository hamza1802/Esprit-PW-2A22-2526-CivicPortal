<?php
/**
 * api_transport.php
 * Dedicated API router for the Transport module.
 * Session-aware: ticket queries are scoped to the logged-in user.
 * Uses the Database singleton (no legacy config.php dependency).
 */

/**
 * SECURITY improvements:
 *  • bootstrap.php provides HttpOnly/SameSite session cookies and suppresses error display.
 *  • citizenName is length-validated; idTrajet is cast and range-checked before use.
 *  • Exceptions are logged server-side; only a generic message reaches the client.
 */
require_once __DIR__ . '/bootstrap.php';
define('_CIVICPORTAL_BOOTSTRAP_', true);

header('Content-Type: application/json');

require_once __DIR__ . '/Controller/MainController.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['action'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid action.']);
    exit;
}

$action = $input['action'];

try { switch ($action) {

        // ============================================
        // TRANSPORT TYPES
        // ============================================
        case 'list_transport_types':
            $types = AppModel::listTransportTypes();
            echo json_encode(['success' => true, 'data' => $types]);
            break;

        // ============================================
        // TRANSPORT VEHICLES
        // ============================================
        case 'list_transports':
            $transports = AppModel::listTransports();
            echo json_encode(['success' => true, 'data' => $transports]);
            break;

        case 'get_transport':
            $transport = AppModel::showTransport((int)($input['idTransport'] ?? 0));
            echo json_encode(['success' => true, 'data' => $transport]);
            break;

        case 'add_transport':
            $transport = [
                'name' => $input['name'],
                'type' => $input['type'],
                'capacity' => (int)$input['capacity'],
                'status' => $input['status'],
                'idTransportType' => !empty($input['idTransportType']) ? (int)$input['idTransportType'] : null
            ];
            AppModel::addTransport($transport);
            echo json_encode(['success' => true]);
            break;

        case 'update_transport':
            $transport = [
                'name' => $input['name'],
                'type' => $input['type'],
                'capacity' => (int)$input['capacity'],
                'status' => $input['status'],
                'idTransportType' => !empty($input['idTransportType']) ? (int)$input['idTransportType'] : null
            ];
            AppModel::updateTransport((int)$input['idTransport'], $transport);
            echo json_encode(['success' => true]);
            break;

        case 'delete_transport':
            AppModel::deleteTransport((int)($input['idTransport'] ?? 0));
            echo json_encode(['success' => true]);
            break;

        // ============================================
        // TRAJETS (ROUTES)
        // ============================================
        case 'list_all_trajets':
            $trajets  = AppModel::listTrajets();
            $enriched = [];
            foreach ($trajets as $t) {
                $occ        = AppModel::getOccupancy($t['idTrajet']);
                $enriched[] = array_merge($t, ['capacity' => $occ['capacity'], 'sold' => $occ['sold']]);
            }
            echo json_encode(['success' => true, 'data' => $enriched]);
            break;

        case 'list_trajets':
            $type   = $input['type']    ?? 'Bus';
            $sortBy = $input['sortBy']  ?? 'departure';
            $order  = $input['order']   ?? 'ASC';

            $trajets  = AppModel::listTrajetsByTypeAndSort($type, $sortBy, $order);
            $enriched = [];
            foreach ($trajets as $t) {
                $occ        = AppModel::getOccupancy($t['idTrajet']);
                $enriched[] = [
                    'idTrajet'      => $t['idTrajet'],
                    'departure'     => $t['departure'],
                    'destination'   => $t['destination'],
                    'departureTime' => $t['departureTime'],
                    'price'         => $t['price'],
                    'transportName' => $t['transportName'],
                    'capacity'      => $occ['capacity'],
                    'sold'          => $occ['sold'],
                    'depLat'        => $t['depLat']     ?? null,
                    'depLng'        => $t['depLng']     ?? null,
                    'depAddress'    => $t['depAddress'] ?? null,
                    'destLat'       => $t['destLat']    ?? null,
                    'destLng'       => $t['destLng']    ?? null,
                    'destAddress'   => $t['destAddress'] ?? null,
                ];
            }
            echo json_encode(['success' => true, 'data' => $enriched]);
            break;

        case 'add_trajet':
            $trajet = [
                'departure'     => $input['departure'],
                'destination'   => $input['destination'],
                'idTransport'   => (int)$input['idTransport'],
                'departureTime' => $input['departureTime'],
                'price'         => (float)$input['price'],
                'depLat'        => isset($input['depLat']) ? (float)$input['depLat'] : null,
                'depLng'        => isset($input['depLng']) ? (float)$input['depLng'] : null,
                'depAddress'    => $input['depAddress'] ?? null,
                'destLat'       => isset($input['destLat']) ? (float)$input['destLat'] : null,
                'destLng'       => isset($input['destLng']) ? (float)$input['destLng'] : null,
                'destAddress'   => $input['destAddress'] ?? null
            ];
            AppModel::addTrajet($trajet);
            echo json_encode(['success' => true]);
            break;

        case 'update_trajet':
            $trajet = [
                'departure'     => $input['departure'],
                'destination'   => $input['destination'],
                'idTransport'   => (int)$input['idTransport'],
                'departureTime' => $input['departureTime'],
                'price'         => (float)$input['price'],
                'depLat'        => isset($input['depLat']) ? (float)$input['depLat'] : null,
                'depLng'        => isset($input['depLng']) ? (float)$input['depLng'] : null,
                'depAddress'    => $input['depAddress'] ?? null,
                'destLat'       => isset($input['destLat']) ? (float)$input['destLat'] : null,
                'destLng'       => isset($input['destLng']) ? (float)$input['destLng'] : null,
                'destAddress'   => $input['destAddress'] ?? null
            ];
            AppModel::updateTrajet((int)$input['idTrajet'], $trajet);
            echo json_encode(['success' => true]);
            break;

        case 'delete_trajet':
            AppModel::deleteTrajet((int)($input['idTrajet'] ?? 0));
            echo json_encode(['success' => true]);
            break;

        // ============================================
        // TICKETS
        // ============================================
        case 'list_tickets':
            $tickets = AppModel::listTickets();
            echo json_encode(['success' => true, 'data' => $tickets]);
            break;

        case 'list_tickets_enriched':
            // Front-office: scope to logged-in citizen only
            $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
            $tickets = AppModel::listTicketsEnriched($userId);
            echo json_encode(['success' => true, 'data' => $tickets]);
            break;

        case 'book_ticket':
            if (empty($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'error' => 'Not authenticated.']);
                break;
            }

            $idTrajet    = (int)($input['idTrajet'] ?? 0);
            $citizenName = trim($input['citizenName'] ?? '');
            $user_id     = (int)$_SESSION['user_id'];

            // SECURITY: server-side validation — never trust client-supplied lengths.
            // idTrajet must be a positive integer; citizenName is bounded to DB column width.
            if ($idTrajet <= 0) {
                echo json_encode(['success' => false, 'error' => 'Invalid route ID.']);
                break;
            }
            if ($citizenName === '' || mb_strlen($citizenName) > 255) {
                echo json_encode(['success' => false, 'error' => 'Invalid passenger name.']);
                break;
            }

            $occ = AppModel::getOccupancy($idTrajet);
            if ($occ['capacity'] > 0 && $occ['sold'] >= $occ['capacity']) {
                echo json_encode(['success' => false, 'error' => 'Route is sold out.']);
                break;
            }

            $data = [
                'user_id'     => $user_id,
                'citizenName' => $citizenName,
                'idTrajet'    => $idTrajet
            ];
            AppModel::addTicket($data);
            echo json_encode(['success' => true]);
            break;

        case 'cancel_ticket':
            if (empty($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'error' => 'Not authenticated.']);
                break;
            }
            AppModel::cancelTicket((int)($input['idTicket'] ?? 0));
            echo json_encode(['success' => true]);
            break;

        default:
            echo json_encode(['success' => false, 'error' => 'Unknown action.']);

} } catch (Exception $e) {
    // SECURITY: log full detail; return only a safe message to the client.
    error_log('[CivicPortal][Transport] ' . $e->getMessage()
        . ' | File: ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    $clientMessage = APP_DEBUG ? $e->getMessage() : 'A server error occurred.';
    echo json_encode(['success' => false, 'error' => $clientMessage]);
}
?>
