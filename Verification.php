<?php
/**
 * Verification.php
 * Entry point for all form/API actions.
 * Captures $_POST data, creates objects from Model, and passes it to the Controller.
 */

require_once __DIR__ . '/Controller/MainController.php';

try {
    // 1. Capture data (Captures $_POST or JSON input)
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $_POST['action'] ?? $input['action'] ?? null;
    $data   = $_POST['data']   ?? $input['data']   ?? [];
    
    // Determine if it's an API request expecting JSON
    $isApi = !empty($input) || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

    if (!$action) throw new Exception("No action provided");

    // ============================================
    // Form Routing (BackOffice)
    // ============================================
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        
        // === TransportType Forms ===
        if ($action === 'addTransportType') {
            $photoPath = MainController::handlePhotoUpload($_FILES['photo'] ?? null);
            $type = new TransportType(null, $_POST['name'], $_POST['description'] ?? '', $photoPath);
            MainController::addTransportType($type);
            header('Location: View/BackOffice/showTransportType.php?success=1');
            exit();
        }
        elseif ($action === 'updateTransportType') {
            $existing = MainController::showTransportType($_POST['idTransportType']);
            $photoPath = $existing['photo_url']; // keep existing photo by default
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $newPhoto = MainController::handlePhotoUpload($_FILES['photo']);
                if ($newPhoto) $photoPath = $newPhoto;
            }
            $type = new TransportType($_POST['idTransportType'], $_POST['name'], $_POST['description'] ?? '', $photoPath);
            MainController::updateTransportType($type, $_POST['idTransportType']);
            header('Location: View/BackOffice/showTransportType.php?updated=1');
            exit();
        }
        elseif ($action === 'deleteTransportType') {
            MainController::deleteTransportType($_POST['idTransportType']);
            header('Location: View/BackOffice/showTransportType.php?deleted=1');
            exit();
        }

        // === Transport Forms ===
        elseif ($action === 'addTransport') {
            $idTT = !empty($_POST['idTransportType']) ? (int)$_POST['idTransportType'] : null;
            // Look up the type name from transport_type table
            $typeName = $_POST['type'] ?? '';
            if ($idTT && empty($typeName)) {
                $tt = MainController::showTransportType($idTT);
                $typeName = $tt ? $tt['name'] : '';
            }
            $transport = new Transport(null, $_POST['name'], $typeName, (int)$_POST["capacity"], $_POST['status'], $idTT);
            MainController::addTransport($transport);
            header('Location: View/BackOffice/showTransport.php?success=1');
            exit();
        }
        elseif ($action === 'deleteTransport') {
            MainController::deleteTransport($_POST["idTransport"]);
            header('Location: View/BackOffice/showTransport.php?deleted=1');
            exit();
        }
        elseif ($action === 'updateTransport') {
            $idTT = !empty($_POST['idTransportType']) ? (int)$_POST['idTransportType'] : null;
            $typeName = $_POST['type'] ?? '';
            if ($idTT && empty($typeName)) {
                $tt = MainController::showTransportType($idTT);
                $typeName = $tt ? $tt['name'] : '';
            }
            $transport = new Transport($_POST['idTransport'], $_POST['name'], $typeName, (int)$_POST["capacity"], $_POST['status'], $idTT);
            MainController::updateTransport($transport, $_POST['idTransport']);
            header('Location: View/BackOffice/showTransport.php?updated=1');
            exit();
        }
        
        // === Trajet Forms ===
        elseif ($action === 'addTrajet') {
            $trajet = new Trajet(
                null,
                $_POST['departure'],
                $_POST['destination'],
                (int)$_POST['idTransport'],
                $_POST['departureTime'],
                (float)$_POST['price'],
                !empty($_POST['depLat']) ? (float)$_POST['depLat'] : null,
                !empty($_POST['depLng']) ? (float)$_POST['depLng'] : null,
                $_POST['depAddress'] ?? null,
                !empty($_POST['destLat']) ? (float)$_POST['destLat'] : null,
                !empty($_POST['destLng']) ? (float)$_POST['destLng'] : null,
                $_POST['destAddress'] ?? null
            );
            MainController::addTrajet($trajet);
            header('Location: View/BackOffice/showTrajet.php?success=1');
            exit();
        }
        elseif ($action === 'deleteTrajet') {
            MainController::deleteTrajet($_POST["idTrajet"]);
            header('Location: View/BackOffice/showTrajet.php?deleted=1');
            exit();
        }
        elseif ($action === 'updateTrajet') {
            $trajet = new Trajet(
                $_POST['idTrajet'],
                $_POST['departure'],
                $_POST['destination'],
                (int)$_POST['idTransport'],
                $_POST['departureTime'],
                (float)$_POST['price'],
                !empty($_POST['depLat']) ? (float)$_POST['depLat'] : null,
                !empty($_POST['depLng']) ? (float)$_POST['depLng'] : null,
                $_POST['depAddress'] ?? null,
                !empty($_POST['destLat']) ? (float)$_POST['destLat'] : null,
                !empty($_POST['destLng']) ? (float)$_POST['destLng'] : null,
                $_POST['destAddress'] ?? null
            );
            MainController::updateTrajet($trajet, $_POST['idTrajet']);
            header('Location: View/BackOffice/showTrajet.php?updated=1');
            exit();
        }

        // === Ticket Forms ===
        elseif ($action === 'cancelTicket') {
            MainController::cancelTicket($_POST['idTicket']);
            header('Location: View/BackOffice/showTicket.php');
            exit();
        }
    }

    // ============================================
    // API Routing (FrontOffice / SPA)
    // ============================================
    
    // Pass to Controller (The Brain)
    $response = MainController::handleRequest($action, $data);
    
    if ($isApi) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $response]);
    }

} catch (Exception $e) {
    if (isset($isApi) && $isApi) {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    } else {
        die("Error: " . $e->getMessage());
    }
}
?>
