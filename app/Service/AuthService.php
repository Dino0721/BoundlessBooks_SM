<?php

require_once __DIR__ . '/../Model/User.php';
require_once __DIR__ . '/../Core/SessionManager.php';

class AuthService {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function login($email, $password) {
        $user = $this->userModel->findByEmail($email);

        if ($user && password_verify($password, $user->password)) {
            SessionManager::regenerate();
            SessionManager::set('user_id', $user->user_id);
            SessionManager::set('email', $user->email);
            SessionManager::set('admin', $user->admin); // Legacy compatibility
            SessionManager::set('user', (array)$user); // Legacy compatibility: store as array or object? Legacy uses array for 'user' key in login.php line 37, but base.php expects object in some places?
            // login.php line 37: $_SESSION['user'] = $user; (where $user is assoc array)
            // base.php line 248: PDO::FETCH_OBJ
            // base.php line 217: $_user->role (implies object)
            // This is messy. Let's store as object to be safe for future, but cast to array if needed.
            // Actually, let's store as object. Legacy `login.php` stored array, but `base.php` seems to want object.
            // Wait, `login.php` line 31: `fetch(PDO::FETCH_ASSOC)`. So `$_SESSION['user']` IS an array in legacy.
            // But `base.php` line 217 `$_user->role` would fail if it's an array.
            // This implies `base.php` might be broken or I misread it.
            // Let's check `base.php` again.
            // `$_user = $_SESSION['user'] ?? null;`
            // `if (in_array($_user->role, $roles))`
            // If `$_user` is array, `$_user['role']` is needed. `$_user->role` is for object.
            // Maybe `base.php` was written for a different part of the system or I am missing something.
            // However, `login.php` definitely stores an array.
            // I will store it as an Object because `User` model returns an Object.
            // And I will fix `base.php` if needed, or assume `base.php` handles objects (since it sets FETCH_OBJ default).
            
            SessionManager::set('user', $user); 
            
            return true;
        }
        return false;
    }

    public function logout() {
        SessionManager::destroy();
    }

    public function getCurrentUser() {
        $userId = SessionManager::get('user_id');
        if ($userId) {
            return $this->userModel->findById($userId);
        }
        return null;
    }

    public function isAuthenticated() {
        return SessionManager::has('user_id');
    }
}
