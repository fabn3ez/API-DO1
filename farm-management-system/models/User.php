<?php
// models/User.php
class User {
    private $conn;
    private $table = 'users';

    public $user_id;
    public $username;
    public $email;
    public $password_hash;
    public $role;
    public $is_active;
    public $login_attempts;
    public $locked_until;

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    public function create(array $data) {
        $sql = "INSERT INTO {$this->table} 
          (username, email, password_hash, role) VALUES (:username, :email, :password_hash, :role)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':username' => $data['username'],
            ':email' => $data['email'],
            ':password_hash' => $data['password_hash'],
            ':role' => $data['role'] ?? 'field_worker'
        ]);
        $this->user_id = $this->conn->lastInsertId();
        return $this->user_id;
    }

    public function findByUsername($username) {
        $sql = "SELECT * FROM {$this->table} WHERE username = :username AND is_active = 1 LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':username' => $username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByEmail($email) {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email AND is_active = 1 LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :id AND is_active = 1 LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateLoginAttempts($username, $attempts) {
        $sql = "UPDATE {$this->table} SET login_attempts = :attempts,
                locked_until = CASE WHEN :attempts >= 5 THEN DATE_ADD(NOW(), INTERVAL 30 MINUTE) ELSE NULL END
                WHERE username = :username";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':attempts' => $attempts, ':username' => $username]);
    }

    public function resetLogin($user_id) {
        $sql = "UPDATE {$this->table} SET login_attempts = 0, locked_until = NULL, last_login = NOW() WHERE user_id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $user_id]);
    }
}
