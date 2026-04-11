<?php
/**
 * MainController.php
 * The "Brain" of CivicPortal - bridges Model and View.
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/AppModel.php';
require_once __DIR__ . '/../Model/Transport.php';
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
    // TRANSPORT FLEET LOGIC
    // ============================================

    public static function listTransports() {
        $sql = "SELECT * FROM transport";
        $db = config::getConnexion();
        try {
            $liste = $db->query($sql);
            return $liste->fetchAll();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public static function addTransport($transport) {
        $sql = "INSERT INTO transport (name, type, capacity, status) VALUES (:name, :type, :capacity, :status)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'name' => $transport->getName(),
                'type' => $transport->getType(),
                'capacity' => $transport->getCapacity(),
                'status' => $transport->getStatus()
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
        $sql = "SELECT * FROM transport WHERE idTransport = $idTransport";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute();
            return $query->fetch();
        } catch (Exception $e) { die('Erreur: ' . $e->getMessage()); }
    }

    public static function updateTransport($transport, $idTransport) {
        try {
            $db = config::getConnexion();
            $query = $db->prepare('UPDATE transport SET name = :name, type = :type, capacity = :capacity, status = :status WHERE idTransport = :idTransport');
            $query->execute([
                'idTransport' => $idTransport,
                'name' => $transport->getName(),
                'type' => $transport->getType(),
                'capacity' => $transport->getCapacity(),
                'status' => $transport->getStatus()
            ]);
        } catch (PDOException $e) { die('Erreur: ' . $e->getMessage()); }
    }

    // ============================================
    // TRAJET ROUTING LOGIC
    // ============================================

    public static function listTrajets() {
        $sql = "SELECT t.*, tr.name as transportName, tr.capacity as transportCapacity FROM trajet t LEFT JOIN transport tr ON t.idTransport = tr.idTransport";
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
        $sql = "INSERT INTO trajet (departure, destination, idTransport, departureTime, price) VALUES (:departure, :destination, :idTransport, :departureTime, :price)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'departure' => $trajet->getDeparture(),
                'destination' => $trajet->getDestination(),
                'idTransport' => $trajet->getIdTransport(),
                'departureTime' => $trajet->getDepartureTime(),
                'price' => $trajet->getPrice()
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
            $query = $db->prepare('UPDATE trajet SET departure = :departure, destination = :destination, idTransport = :idTransport, departureTime = :departureTime, price = :price WHERE idTrajet = :idTrajet');
            $query->execute([
                'idTrajet'      => $idTrajet,
                'departure'     => $trajet->getDeparture(),
                'destination'   => $trajet->getDestination(),
                'idTransport'   => $trajet->getIdTransport(),
                'departureTime' => $trajet->getDepartureTime(),
                'price'         => $trajet->getPrice()
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
