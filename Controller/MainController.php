<?php
/**
 * MainController.php
 * The "Brain" of CivicPortal - bridges Model and View.
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/AppModel.php';
require_once __DIR__ . '/../Model/Transport.php';
require_once __DIR__ . '/../Model/TransportType.php';
require_once __DIR__ . '/../Model/Trajet.php';
require_once __DIR__ . '/../Model/Ticket.php';

class MainController {

    // ============================================
    // CORE SYSTEM & API LOGIC
    // ============================================
    
    public static function showData() {
        return [
            'requests'   => AppModel::getRequests(),
            'complaints' => AppModel::getComplaints(),
            'stats'      => AppModel::getStats(),
        ];
    }

    public static function handleRequest($action, $data) {
        switch ($action) {
            case 'get_requests': return AppModel::getRequests();
            case 'add_request': return AppModel::addRequest($data['type'], $data['userId']);
            case 'update_status': return AppModel::updateRequestStatus($data['id'], $data['status']);
            case 'add_complaint': return AppModel::addComplaint($data['subject'], $data['body'], $data['userId']);
            case 'get_complaints': return AppModel::getComplaints();
            case 'get_stats': return AppModel::getStats();
            default: throw new Exception("Invalid app action: " . $action);
        }
    }

    // ============================================
    // TRANSPORT TYPE MANAGEMENT
    // ============================================

    public static function listTransportTypes() {
        $sql = "SELECT * FROM transport_type ORDER BY name ASC";
        $db = config::getConnexion();
        try {
            $liste = $db->query($sql);
            return $liste->fetchAll();
        } catch (Exception $e) { die('Erreur: ' . $e->getMessage()); }
    }

    public static function showTransportType($id) {
        $sql = "SELECT * FROM transport_type WHERE idTransportType = :id";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
            return $query->fetch();
        } catch (Exception $e) { die('Erreur: ' . $e->getMessage()); }
    }

    public static function addTransportType($transportType) {
        $sql = "INSERT INTO transport_type (name, description, photo_url) VALUES (:name, :description, :photo_url)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'name' => $transportType->getName(),
                'description' => $transportType->getDescription(),
                'photo_url' => $transportType->getPhotoUrl()
            ]);
        } catch (Exception $e) { die('Erreur: ' . $e->getMessage()); }
    }

    public static function updateTransportType($transportType, $id) {
        $sql = "UPDATE transport_type SET name = :name, description = :description, photo_url = :photo_url WHERE idTransportType = :id";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id' => $id,
                'name' => $transportType->getName(),
                'description' => $transportType->getDescription(),
                'photo_url' => $transportType->getPhotoUrl()
            ]);
        } catch (Exception $e) { die('Erreur: ' . $e->getMessage()); }
    }

    public static function deleteTransportType($id) {
        // 1. Fetch the record to get the photo path before deletion
        $existing = self::showTransportType($id);

        // 2. Delete the database row
        $sql = "DELETE FROM transport_type WHERE idTransportType = :id";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id);
        try { $req->execute(); } catch (Exception $e) { die('Erreur: ' . $e->getMessage()); }

        // 3. Cleanup: remove the associated photo file from disk
        if ($existing && !empty($existing['photo_url'])) {
            $filePath = __DIR__ . '/../View/assets/images/' . $existing['photo_url'];
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }
    }

    /**
     * Handle photo upload for transport types
     * @return string|null The relative path to the uploaded file or null on failure
     */
    public static function handlePhotoUpload($fileData) {
        if (!isset($fileData) || $fileData['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($fileData['type'], $allowedTypes)) {
            return null;
        }

        $ext = pathinfo($fileData['name'], PATHINFO_EXTENSION);
        $filename = 'type_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $uploadDir = __DIR__ . '/../View/assets/images/types/';
        $destination = $uploadDir . $filename;

        if (move_uploaded_file($fileData['tmp_name'], $destination)) {
            return 'types/' . $filename;
        }
        return null;
    }

    // ============================================
    // TRANSPORT FLEET LOGIC
    // ============================================

    public static function listTransports() {
        $sql = "SELECT t.*, tt.name as typeName FROM transport t LEFT JOIN transport_type tt ON t.idTransportType = tt.idTransportType";
        $db = config::getConnexion();
        try {
            $liste = $db->query($sql);
            return $liste->fetchAll();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public static function addTransport($transport) {
        $sql = "INSERT INTO transport (name, type, capacity, status, idTransportType) VALUES (:name, :type, :capacity, :status, :idTransportType)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'name' => $transport->getName(),
                'type' => $transport->getType(),
                'capacity' => $transport->getCapacity(),
                'status' => $transport->getStatus(),
                'idTransportType' => $transport->getIdTransportType()
            ]);
        } catch (Exception $e) { die('Erreur: ' . $e->getMessage()); }
    }

    public static function deleteTransport($idTransport) {
        $sql = "DELETE FROM transport WHERE idTransport = :idTransport";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':idTransport', $idTransport);
        try { $req->execute(); } catch (Exception $e) { die('Erreur: ' . $e->getMessage()); }
    }

    public static function showTransport($idTransport) {
        $sql = "SELECT t.*, tt.name as typeName FROM transport t LEFT JOIN transport_type tt ON t.idTransportType = tt.idTransportType WHERE t.idTransport = :id";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $idTransport]);
            return $query->fetch();
        } catch (Exception $e) { die('Erreur: ' . $e->getMessage()); }
    }

    public static function updateTransport($transport, $idTransport) {
        try {
            $db = config::getConnexion();
            $query = $db->prepare('UPDATE transport SET name = :name, type = :type, capacity = :capacity, status = :status, idTransportType = :idTransportType WHERE idTransport = :idTransport');
            $query->execute([
                'idTransport' => $idTransport,
                'name' => $transport->getName(),
                'type' => $transport->getType(),
                'capacity' => $transport->getCapacity(),
                'status' => $transport->getStatus(),
                'idTransportType' => $transport->getIdTransportType()
            ]);
        } catch (PDOException $e) { die('Erreur: ' . $e->getMessage()); }
    }

    // ============================================
    // TRAJET ROUTING LOGIC
    // ============================================

    public static function listTrajets() {
        $sql = "SELECT t.*, tr.name as transportName, tr.capacity as transportCapacity, tr.type as transportType FROM trajet t LEFT JOIN transport tr ON t.idTransport = tr.idTransport";
        $db = config::getConnexion();
        try {
            $liste = $db->query($sql);
            return $liste->fetchAll();
        } catch (Exception $e) { die('Erreur: ' . $e->getMessage()); }
    }

    public static function listTrajetsByTypeAndSort($type, $sortBy = 'departure', $order = 'ASC') {
        $allowedSorts = ['departure', 'destination', 'departureTime', 'price'];
        $sortBy = in_array($sortBy, $allowedSorts) ? $sortBy : 'departure';
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

        $sql = "SELECT t.*, tr.name as transportName, tr.capacity as transportCapacity FROM trajet t JOIN transport tr ON t.idTransport = tr.idTransport WHERE tr.type = :type ORDER BY t.$sortBy $order";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['type' => $type]);
            return $query->fetchAll();
        } catch (Exception $e) { die('Erreur: ' . $e->getMessage()); }
    }

    public static function addTrajet($trajet) {
        $sql = "INSERT INTO trajet (departure, destination, idTransport, departureTime, price, depLat, depLng, depAddress, destLat, destLng, destAddress) VALUES (:departure, :destination, :idTransport, :departureTime, :price, :depLat, :depLng, :depAddress, :destLat, :destLng, :destAddress)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'departure' => $trajet->getDeparture(),
                'destination' => $trajet->getDestination(),
                'idTransport' => $trajet->getIdTransport(),
                'departureTime' => $trajet->getDepartureTime(),
                'price' => $trajet->getPrice(),
                'depLat' => $trajet->getDepLat(),
                'depLng' => $trajet->getDepLng(),
                'depAddress' => $trajet->getDepAddress(),
                'destLat' => $trajet->getDestLat(),
                'destLng' => $trajet->getDestLng(),
                'destAddress' => $trajet->getDestAddress()
            ]);
        } catch (Exception $e) { die('Erreur: ' . $e->getMessage()); }
    }

    public static function deleteTrajet($idTrajet) {
        $sql = "DELETE FROM trajet WHERE idTrajet = :idTrajet";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':idTrajet', $idTrajet);
        try { $req->execute(); } catch (Exception $e) { die('Erreur: ' . $e->getMessage()); }
    }

    public static function showTrajet($idTrajet) {
        $sql = "SELECT * FROM trajet WHERE idTrajet = :idTrajet";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['idTrajet' => $idTrajet]);
            return $query->fetch();
        } catch (Exception $e) { die('Erreur: ' . $e->getMessage()); }
    }

    public static function updateTrajet($trajet, $idTrajet) {
        try {
            $db = config::getConnexion();
            $query = $db->prepare('UPDATE trajet SET departure = :departure, destination = :destination, idTransport = :idTransport, departureTime = :departureTime, price = :price, depLat = :depLat, depLng = :depLng, depAddress = :depAddress, destLat = :destLat, destLng = :destLng, destAddress = :destAddress WHERE idTrajet = :idTrajet');
            $query->execute([
                'idTrajet'      => $idTrajet,
                'departure'     => $trajet->getDeparture(),
                'destination'   => $trajet->getDestination(),
                'idTransport'   => $trajet->getIdTransport(),
                'departureTime' => $trajet->getDepartureTime(),
                'price'         => $trajet->getPrice(),
                'depLat'        => $trajet->getDepLat(),
                'depLng'        => $trajet->getDepLng(),
                'depAddress'    => $trajet->getDepAddress(),
                'destLat'       => $trajet->getDestLat(),
                'destLng'       => $trajet->getDestLng(),
                'destAddress'   => $trajet->getDestAddress()
            ]);
        } catch (PDOException $e) { die('Erreur: ' . $e->getMessage()); }
    }

    public static function getOccupancy($idTrajet) {
        $db = config::getConnexion();
        $sql = "SELECT tr.capacity FROM trajet t JOIN transport tr ON t.idTransport = tr.idTransport WHERE t.idTrajet = :idTrajet";
        $query = $db->prepare($sql);
        $query->execute(['idTrajet' => $idTrajet]);
        $capacity = ($result = $query->fetch()) ? (int)$result['capacity'] : 0;

        $sql2 = "SELECT COUNT(*) as sold FROM ticket WHERE idTrajet = :idTrajet AND status = 'Valid'";
        $query2 = $db->prepare($sql2);
        $query2->execute(['idTrajet' => $idTrajet]);
        $sold = (int)$query2->fetch()['sold'];

        return ['sold' => $sold, 'capacity' => $capacity, 'pct' => $capacity > 0 ? round(($sold / $capacity) * 100) : 0];
    }

    // ============================================
    // TICKET LOGIC
    // ============================================

    public static function listTickets() {
        $sql = "SELECT tk.*, t.departure, t.destination FROM ticket tk LEFT JOIN trajet t ON tk.idTrajet = t.idTrajet ORDER BY tk.issuedAt DESC";
        $db = config::getConnexion();
        try {
            $liste = $db->query($sql);
            return $liste->fetchAll();
        } catch (Exception $e) { die('Erreur: ' . $e->getMessage()); }
    }

    /**
     * Get enriched tickets with trajet + transport type info for the front-office Ticket View
     */
    public static function listTicketsEnriched() {
        $sql = "SELECT tk.*, t.departure, t.destination, t.departureTime, t.price, t.depLat, t.depLng, t.depAddress, t.destLat, t.destLng, t.destAddress,
                       tr.name as transportName, tr.capacity,
                       tt.name as typeName, tt.photo_url as typePhoto, tt.description as typeDescription
                FROM ticket tk
                LEFT JOIN trajet t ON tk.idTrajet = t.idTrajet
                LEFT JOIN transport tr ON t.idTransport = tr.idTransport
                LEFT JOIN transport_type tt ON tr.idTransportType = tt.idTransportType
                ORDER BY tk.issuedAt DESC";
        $db = config::getConnexion();
        try {
            $liste = $db->query($sql);
            return $liste->fetchAll();
        } catch (Exception $e) { die('Erreur: ' . $e->getMessage()); }
    }

    public static function addTicket($ticket) {
        $sql = "INSERT INTO ticket (idUser, ref, citizenName, idTrajet, issuedAt, status) VALUES (:idUser, :ref, :citizenName, :idTrajet, NOW(), 'Valid')";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'idUser' => $ticket->getIdUser(),
                'ref' => $ticket->getRef(),
                'citizenName' => $ticket->getCitizenName(),
                'idTrajet' => $ticket->getIdTrajet()
            ]);
        } catch (Exception $e) { die('Erreur: ' . $e->getMessage()); }
    }

    public static function cancelTicket($idTicket) {
        $sql = "UPDATE ticket SET status = 'Cancelled' WHERE idTicket = :idTicket";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['idTicket' => $idTicket]);
        } catch (Exception $e) { die('Erreur: ' . $e->getMessage()); }
    }

    public static function deleteTicket($idTicket) {
        $sql = "DELETE FROM ticket WHERE idTicket = :idTicket";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':idTicket', $idTicket);
        try { $req->execute(); } catch (Exception $e) { die('Erreur: ' . $e->getMessage()); }
    }

    public static function generateRef() {
        return 'CIV-' . rand(1000, 9999);
    }
}
?>
