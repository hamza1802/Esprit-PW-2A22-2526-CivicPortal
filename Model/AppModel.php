<?php
/**
 * AppModel.php
 * Session-based data management for CivicPortal
 * Mimics a database for the Esprit/IPSSI academic demo
 */

class AppModel {
    public static function init() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Initialize default data if session is empty
        if (!isset($_SESSION['initialized'])) {
            $_SESSION['users'] = [
                ['id' => 1, 'name' => 'John Citizen', 'role' => 'citizen', 'email' => 'john@example.com'],
                ['id' => 2, 'name' => 'Alice Worker', 'role' => 'worker', 'email' => 'alice@cityhall.gov'],
                ['id' => 3, 'name' => 'Admin User', 'role' => 'admin', 'email' => 'admin@cityhall.gov']
            ];

            $_SESSION['programs'] = [
                ['id' => 101, 'title' => 'Summer Pottery Workshop', 'category' => 'Arts', 'description' => 'Learn basic pottery techniques for all ages.', 'image' => 'pottery.jpg'],
                ['id' => 102, 'title' => 'Youth Swimming Program', 'category' => 'Sports', 'description' => 'Daily swimming lessons at the Municipal Pool.', 'image' => 'swimming.jpg'],
                ['id' => 103, 'title' => 'Community Gardening', 'category' => 'Environment', 'description' => 'Join our local group in the North Park garden.', 'image' => 'gardening.jpg']
            ];

            $_SESSION['requests'] = [
                ['id' => 501, 'type' => 'Birth Certificate', 'citizenId' => 1, 'status' => 'pending', 'date' => '2026-03-15'],
                ['id' => 502, 'type' => 'ID Card Renewal', 'citizenId' => 1, 'status' => 'validated', 'date' => '2026-03-10']
            ];

            $_SESSION['enrollments'] = [
                ['userId' => 1, 'programId' => 101]
            ];

            $_SESSION['complaints'] = [];
            $_SESSION['initialized'] = true;
        }
    }

    public static function getRequests() {
        self::init();
        return $_SESSION['requests'];
    }

    public static function addRequest($type, $citizenId) {
        self::init();
        $newRequest = [
            'id' => time(),
            'type' => $type,
            'citizenId' => $citizenId,
            'status' => 'pending',
            'date' => date('Y-m-d')
        ];
        $_SESSION['requests'][] = $newRequest;
        return $newRequest;
    }

    public static function updateRequestStatus($requestId, $status) {
        self::init();
        foreach ($_SESSION['requests'] as &$request) {
            if ($request['id'] == $requestId) {
                $request['status'] = $status;
                return true;
            }
        }
        return false;
    }

    public static function addComplaint($subject, $body, $userId) {
        self::init();
        $newComplaint = [
            'id' => time(),
            'subject' => $subject,
            'body' => $body,
            'userId' => $userId,
            'date' => date('Y-m-d')
        ];
        $_SESSION['complaints'][] = $newComplaint;
        return $newComplaint;
    }

    public static function getComplaints() {
        self::init();
        return $_SESSION['complaints'];
    }

    public static function getStats() {
        self::init();
        return [
            'usersCount' => count($_SESSION['users']),
            'programsCount' => count($_SESSION['programs']),
            'requestsCount' => count($_SESSION['requests']),
            'enrollmentsCount' => count($_SESSION['enrollments']),
            'complaintsCount' => count($_SESSION['complaints'])
        ];
    }
}
?>
