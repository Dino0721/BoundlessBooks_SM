<?php

require_once __DIR__ . '/../Core/Database.php';

class User {
    private $db;
    
    public $user_id;
    public $email;
    public $password;
    public $admin; // Role: 0 = Customer, 1 = Admin
    public $phone_number;
    public $profile_photo;
    public $reset_token;
    public $token_expiry;
    
    // Computed property for base.php compatibility
    public $role;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        // Compute role for legacy compatibility
        $this->role = ($this->admin == 1) ? 'admin' : 'user';
    }

    public function __sleep() {
        // Ensure db connection is not serialized
        unset($this->db);
        return ['user_id', 'email', 'password', 'admin', 'phone_number', 'profile_photo', 'reset_token', 'token_expiry', 'role'];
    }

    public function __wakeup() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM user WHERE email = ?");
        $stmt->execute([$email]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'User');
        return $stmt->fetch();
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM user WHERE user_id = ?");
        $stmt->execute([$id]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'User');
        return $stmt->fetch();
    }

    public function create($email, $passwordHash, $phoneNumber = '') {
        $stmt = $this->db->prepare("INSERT INTO user (email, password, phone_number, admin) VALUES (?, ?, ?, 0)");
        return $stmt->execute([$email, $passwordHash, $phoneNumber]);
    }

    public function isAdmin() {
        return $this->admin == 1;
    }

    public function setResetToken($email, $token, $expiry) {
        $stmt = $this->db->prepare("UPDATE user SET reset_token = ?, token_expiry = ? WHERE email = ?");
        return $stmt->execute([$token, $expiry, $email]);
    }

    public function updatePasswordByToken($token, $passwordHash) {
        $stmt = $this->db->prepare("UPDATE user SET password = ?, reset_token = NULL, token_expiry = NULL WHERE reset_token = ? AND token_expiry > NOW()");
        $stmt->execute([$passwordHash, $token]);
        return $stmt->rowCount() > 0;
    }
}
