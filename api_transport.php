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
            $types = MainController::listTransportTypes();
            echo json_encode(['success' => true, 'data' => $types]);
            break;

        // ============================================
        // TRANSPORT VEHICLES
        // ============================================
        case 'list_transports':
            $transports = MainController::listTransports();
            echo json_encode(['success' => true, 'data' => $transports]);
            break;

        case 'get_transport':
            $transport = MainController::showTransport((int)($input['idTransport'] ?? 0));
            echo json_encode(['success' => true, 'data' => $transport]);
            break;

        case 'add_transport':
            $transport = new Transport(
                null,
                $input['name'],
                $input['type'],
                (int)$input['capacity'],
                $input['status'],
                isset($input['idTransportType']) ? (int)$input['idTransportType'] : null
            );
            MainController::addTransport($transport);
            echo json_encode(['success' => true]);
            break;

        case 'update_transport':
            $transport = new Transport(
                (int)$input['idTransport'],
                $input['name'],
                $input['type'],
                (int)$input['capacity'],
                $input['status'],
                isset($input['idTransportType']) ? (int)$input['idTransportType'] : null
            );
            MainController::updateTransport($transport, (int)$input['idTransport']);
            echo json_encode(['success' => true]);
            break;

        case 'delete_transport':
            MainController::deleteTransport((int)($input['idTransport'] ?? 0));
            echo json_encode(['success' => true]);
            break;

        // ============================================
        // TRAJETS (ROUTES)
        // ============================================
        case 'list_all_trajets':
            $trajets  = MainController::listTrajets();
            $enriched = [];
            foreach ($trajets as $t) {
                $occ        = MainController::getOccupancy($t['idTrajet']);
                $enriched[] = array_merge($t, ['capacity' => $occ['capacity'], 'sold' => $occ['sold']]);
            }
            echo json_encode(['success' => true, 'data' => $enriched]);
            break;

        case 'list_trajets':
            $type   = $input['type']    ?? 'Bus';
            $sortBy = $input['sortBy']  ?? 'departure';
            $order  = $input['order']   ?? 'ASC';

            $trajets  = MainController::listTrajetsByTypeAndSort($type, $sortBy, $order);
            $enriched = [];
            foreach ($trajets as $t) {
                $occ        = MainController::getOccupancy($t['idTrajet']);
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
            $trajet = new Trajet(
                null,
                $input['departure'],
                $input['destination'],
                (int)$input['idTransport'],
                $input['departureTime'],
                (float)$input['price'],
                isset($input['depLat'])  ? (float)$input['depLat']  : null,
                isset($input['depLng'])  ? (float)$input['depLng']  : null,
                $input['depAddress']  ?? null,
                isset($input['destLat']) ? (float)$input['destLat'] : null,
                isset($input['destLng']) ? (float)$input['destLng'] : null,
                $input['destAddress'] ?? null
            );
            MainController::addTrajet($trajet);
            echo json_encode(['success' => true]);
            break;

        case 'delete_trajet':
            MainController::deleteTrajet((int)($input['idTrajet'] ?? 0));
            echo json_encode(['success' => true]);
            break;

        // ============================================
        // TICKETS
        // ============================================
        case 'list_tickets':
            $tickets = MainController::listTickets();
            echo json_encode(['success' => true, 'data' => $tickets]);
            break;

        case 'list_tickets_enriched':
            // Front-office: scope to logged-in citizen only
            $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
            $tickets = MainController::listTicketsEnriched($userId);
            echo json_encode(['success' => true, 'data' => $tickets]);
            break;

        case 'book_ticket':
            if (empty($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'error' => 'Not authenticated.']);
                break;
            }

            $idTrajet    = (int)($input['idTrajet'] ?? 0);
            $citizenName = trim($input['citizenName'] ?? '');
            $idUser      = (int)$_SESSION['user_id'];

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

            $occ = MainController::getOccupancy($idTrajet);
            if ($occ['capacity'] > 0 && $occ['sold'] >= $occ['capacity']) {
                echo json_encode(['success' => false, 'error' => 'Route is sold out.']);
                break;
            }

            $ref    = MainController::generateRef();
            $ticket = new Ticket(null, $idUser, $ref, $citizenName, $idTrajet, null, 'Valid');
            MainController::addTicket($ticket);
            echo json_encode(['success' => true, 'ref' => $ref]);
            break;

        case 'cancel_ticket':
            if (empty($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'error' => 'Not authenticated.']);
                break;
            }
            MainController::cancelTicket((int)($input['idTicket'] ?? 0));
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
