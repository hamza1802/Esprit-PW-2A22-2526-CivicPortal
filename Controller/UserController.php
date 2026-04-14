<?php

require_once __DIR__ . '/../model/User.php';
require_once __DIR__ . '/../model/Profile.php';

class UserController {
    public static function getAllUsers(): array {
        return User::fetchAll();
    }

    public static function getUserById(int $id): ?User {
        return User::findById($id);
    }

    public static function login(array $input): array {
        $errors = User::validateLogin($input);
        if (!empty($errors)) {
            return ['errors' => $errors];
        }

        $user = User::authenticate($input['email'], $input['password']);
        if ($user === null) {
            return ['errors' => ['email' => 'Invalid email or password.']];
        }

        $_SESSION['user_id'] = $user->getId();
        $_SESSION['user_name'] = $user->getDisplayName();
        $_SESSION['user_email'] = $user->getEmail();
        $_SESSION['user_role'] = $user->getRole();
        Profile::createIfMissing($user->getId());

        return ['success' => 'Login successful.', 'user' => $user];
    }

    public static function logout(): void {
        unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_role']);
    }

    public static function register(array $input): array {
        $errors = User::validate($input, true);
        if (!empty($errors)) {
            return ['errors' => $errors];
        }

        $user = User::create($input);
        Profile::createIfMissing($user->getId());
        // Extract clean name from input (User::create already handles admin- prefix)
        $cleanName = trim($input['name']);
        if (strpos($cleanName, 'admin-') === 0) {
            $cleanName = substr($cleanName, 6);
        }
        Profile::update($user->getId(), ['first_name' => $cleanName]);
        return ['success' => 'Registration successful.', 'user' => $user];
    }

    public static function updateProfile(int $id, array $input): array {
        $user = User::findById($id);
        if ($user === null) {
            return ['errors' => ['general' => 'User not found.']];
        }

        if (!isset($input['role']) || trim($input['role']) === '') {
            $input['role'] = $user->getRole();
        }

        $errors = User::validate($input, false, $id);
        if (!empty($input['first_name']) && preg_match('/\d/', $input['first_name'])) {
            $errors['first_name'] = 'First name must not contain numbers.';
        }
        if (!empty($input['last_name']) && preg_match('/\d/', $input['last_name'])) {
            $errors['last_name'] = 'Last name must not contain numbers.';
        }
        if (!empty($errors)) {
            return ['errors' => $errors];
        }

        $updated = User::update($id, $input);
        if (!$updated) {
            return ['errors' => ['general' => 'Unable to update profile.']];
        }

        // Get the updated user to use display name
        $updatedUser = User::findById($id);
        
        $profile = Profile::createIfMissing($id);
        // Extract clean name from input for profile storage
        $cleanName = trim($input['name']);
        if (strpos($cleanName, 'admin-') === 0) {
            $cleanName = substr($cleanName, 6);
        }
        $profileData = [
            'first_name' => $cleanName ?? $input['first_name'] ?? $profile->getFirstName(),
            'last_name' => $input['last_name'] ?? $profile->getLastName(),
            'bio' => $input['bio'] ?? $profile->getBio(),
            'avatar_url' => $input['avatar_url'] ?? $profile->getAvatarUrl(),
            'phone_number' => $input['phone_number'] ?? $profile->getPhoneNumber(),
            'date_of_birth' => $input['date_of_birth'] ?? $profile->getDateOfBirth(),
        ];
        Profile::update($id, $profileData);

        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] === $id) {
            $_SESSION['user_name'] = $updatedUser ? $updatedUser->getDisplayName() : $cleanName;
            if (!empty($input['email'])) {
                $_SESSION['user_email'] = $input['email'];
            }
        }

        return ['success' => 'Profile updated successfully.'];
    }

    public static function createUser(array $input): array {
        $errors = User::validate($input, true);
        if (!empty($errors)) {
            return ['errors' => $errors];
        }

        $user = User::create($input);
        Profile::createIfMissing($user->getId());
        return ['success' => 'User added successfully.', 'user' => $user];
    }

    public static function deleteUser(int $id): array {
        if (!User::delete($id)) {
            return ['errors' => ['general' => 'Deletion failed.']];
        }

        return ['success' => 'User deleted successfully.'];
    }
}
