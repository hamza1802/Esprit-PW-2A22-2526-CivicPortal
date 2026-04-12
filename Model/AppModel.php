<?php
/**
 * AppModel.php
 * Session-based data management for CivicPortal
 * Mimics a database for the Esprit/IPSSI academic demo.
 * Uses Blueprints (User.php, ServiceRequest.php) for state structure.
 */

require_once __DIR__ . '/User.php';
require_once __DIR__ . '/ServiceRequest.php';

class AppModel {
    public static function init() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Initialize default data if session is empty
        if (!isset($_SESSION['initialized'])) {
            // Using Blueprint (User entity) to define initial state
            $_SESSION['users'] = [
                (new User(1, 'John Citizen', 'citizen', 'john@example.com'))->toArray(),
                (new User(2, 'Alice Worker', 'worker', 'alice@cityhall.gov'))->toArray(),
                (new User(3, 'Admin User', 'admin', 'admin@cityhall.gov'))->toArray()
            ];

            $_SESSION['programs'] = [
                ['id' => 101, 'title' => 'Summer Pottery Workshop', 'category' => 'Arts', 'description' => 'Learn basic pottery techniques for all ages.', 'image' => 'pottery.jpg'],
                ['id' => 102, 'title' => 'Youth Swimming Program', 'category' => 'Sports', 'description' => 'Daily swimming lessons at the Municipal Pool.', 'image' => 'swimming.jpg'],
                ['id' => 103, 'title' => 'Community Gardening', 'category' => 'Environment', 'description' => 'Join our local group in the North Park garden.', 'image' => 'gardening.jpg']
            ];

            // Using Blueprint (ServiceRequest entity) to define initial state
            $_SESSION['requests'] = [
                (new ServiceRequest(501, 'Birth Certificate', 1, 'pending', '2026-03-15'))->toArray(),
                (new ServiceRequest(502, 'ID Card Renewal', 1, 'validated', '2026-03-10'))->toArray()
            ];

            $_SESSION['enrollments'] = [
                ['userId' => 1, 'programId' => 101]
            ];

            $_SESSION['complaints'] = [];
            $_SESSION['friends'] = [
                ['id' => 1, 'name' => 'Julie Martin', 'email' => 'julie.m@example.com', 'role' => 'citizen', 'status' => 'Active'],
                ['id' => 2, 'name' => 'Olivier Dupont', 'email' => 'olivier.d@example.com', 'role' => 'agent', 'status' => 'Active'],
                ['id' => 3, 'name' => 'Leila Haddad', 'email' => 'leila.h@example.com', 'role' => 'citizen', 'status' => 'Pending']
            ];
            $_SESSION['user_avatars'] = [];
            $_SESSION['initialized'] = true;
        }
    }

    public static function getRequests() {
        self::init();
        return $_SESSION['requests'];
    }

    public static function addRequest($type, $citizenId) {
        self::init();
        // Create an object from your Model as a blueprint before saving
        $requestObj = new ServiceRequest(time(), $type, $citizenId, 'pending', date('Y-m-d'));
        $data = $requestObj->toArray();
        
        $_SESSION['requests'][] = $data;
        return $data;
    }

    public static function updateRequestStatus($requestId, $status) {
        self::init();
        foreach ($_SESSION['requests'] as &$request) {
            if ($request['id'] == $requestId) {
                // Demonstrate using setter on object logic
                $requestObj = new ServiceRequest($request['id'], $request['type'], $request['userId'] ?? $request['citizenId'] ?? 0, $request['status'], $request['date']);
                $requestObj->setStatus($status);
                
                $request['status'] = $requestObj->getStatus();
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

    private static function ensureFriendsInitialized(): void {
        if (!isset($_SESSION['friends']) || !is_array($_SESSION['friends'])) {
            $_SESSION['friends'] = [];
        }
    }

    public static function getFriends() {
        self::init();
        self::ensureFriendsInitialized();
        return $_SESSION['friends'];
    }

    public static function addFriend($name, $email, $role) {
        self::init();
        self::ensureFriendsInitialized();
        $newId = count($_SESSION['friends']) ? max(array_column($_SESSION['friends'], 'id')) + 1 : 1;
        $_SESSION['friends'][] = [
            'id' => $newId,
            'name' => trim($name),
            'email' => trim($email),
            'role' => trim($role) ?: 'citizen',
            'status' => 'Active'
        ];
        return $_SESSION['friends'][count($_SESSION['friends']) - 1];
    }

    public static function removeFriend($id) {
        self::init();
        self::ensureFriendsInitialized();
        self::init();
        foreach ($_SESSION['friends'] as $index => $friend) {
            if ($friend['id'] == $id) {
                array_splice($_SESSION['friends'], $index, 1);
                return true;
            }
        }
        return false;
    }

    public static function getAvatarUrl(int $userId): ?string {
        self::init();
        return $_SESSION['user_avatars'][$userId] ?? null;
    }

    public static function setAvatarUrl(int $userId, string $url): void {
        self::init();
        $_SESSION['user_avatars'][$userId] = $url;
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
