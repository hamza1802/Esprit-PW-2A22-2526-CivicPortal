<?php
header('Content-Type: application/json');

require_once __DIR__ . '/Controller/MainController.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['action'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid action.']);
    exit;
}

$action = $data['action'];

switch ($action) {

    // ============================================
    // TRANSPORT CRUD
    // ============================================
    case 'list_transports':
        $transports = MainController::listTransports();
        echo json_encode(['success' => true, 'data' => $transports]);
        break;

    case 'get_transport':
        $transport = MainController::showTransport($data['idTransport']);
        echo json_encode(['success' => true, 'data' => $transport]);
        break;

    case 'add_transport':
        $transport = new Transport(null, $data['name'], $data['type'], (int)$data['capacity'], $data['status']);
        MainController::addTransport($transport);
        echo json_encode(['success' => true]);
        break;

    case 'update_transport':
        $transport = new Transport($data['idTransport'], $data['name'], $data['type'], (int)$data['capacity'], $data['status']);
        MainController::updateTransport($transport, $data['idTransport']);
        echo json_encode(['success' => true]);
        break;

    case 'delete_transport':
        MainController::deleteTransport($data['idTransport']);
        echo json_encode(['success' => true]);
        break;

    // ============================================
    // TRAJET CRUD
    // ============================================
    case 'list_all_trajets':
        $trajets = MainController::listTrajets();
        $enriched = [];
        foreach ($trajets as $t) {
            $occ = MainController::getOccupancy($t['idTrajet']);
            $enriched[] = array_merge($t, [
                'capacity' => $occ['capacity'],
                'sold' => $occ['sold']
            ]);
        }
        echo json_encode(['success' => true, 'data' => $enriched]);
        break;

    case 'list_trajets':
        $type = $data['type'] ?? 'Plane';
        $sortBy = $data['sortBy'] ?? 'departure';
        $order = $data['order'] ?? 'ASC';
        
        $trajets = MainController::listTrajetsByTypeAndSort($type, $sortBy, $order);
        $enriched = [];
        foreach ($trajets as $t) {
            $occ = MainController::getOccupancy($t['idTrajet']);
            $enriched[] = [
                'idTrajet' => $t['idTrajet'],
                'departure' => $t['departure'],
                'destination' => $t['destination'],
                'departureTime' => $t['departureTime'],
                'price' => $t['price'],
                'transportName' => $t['transportName'],
                'capacity' => $occ['capacity'],
                'sold' => $occ['sold']
            ];
        }
        echo json_encode(['success' => true, 'data' => $enriched]);
        break;

    case 'add_trajet':
        $trajet = new Trajet(null, $data['departure'], $data['destination'], (int)$data['idTransport'], $data['departureTime'], (float)$data['price']);
        MainController::addTrajet($trajet);
        echo json_encode(['success' => true]);
        break;

    case 'delete_trajet':
        MainController::deleteTrajet($data['idTrajet']);
        echo json_encode(['success' => true]);
        break;

    // ============================================
    // TICKET CRUD
    // ============================================
    case 'list_tickets':
        $tickets = MainController::listTickets();
        echo json_encode(['success' => true, 'data' => $tickets]);
        break;

    case 'book_ticket':
        $idTrajet = $data['idTrajet'] ?? 0;
        $citizenName = $data['citizenName'] ?? '';
        $idUser = $data['idUser'] ?? null;
        
        if (empty($citizenName) || $idTrajet <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid input']);
            break;
        }

        $occ = MainController::getOccupancy($idTrajet);
        if ($occ['capacity'] > 0 && $occ['sold'] >= $occ['capacity']) {
            echo json_encode(['success' => false, 'error' => 'Route is sold out.']);
        } else {
            $ref = MainController::generateRef();
            $ticket = new Ticket(null, $idUser, $ref, $citizenName, $idTrajet, null, 'Valid');
            MainController::addTicket($ticket);
            echo json_encode(['success' => true]);
        }
        break;

    case 'cancel_ticket':
        MainController::cancelTicket($data['idTicket']);
        echo json_encode(['success' => true]);
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Unknown action.']);
}
?>
