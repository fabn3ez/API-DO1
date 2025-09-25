<?php
class User {
    private $conn;
    private $table = "users";
    
    public $user_id;
    public $username;
    public $email;
    public $phone;
    public $first_name;
    public $last_name;
    public $password_hash;
    public $role;
    public $user_type;
    public $is_active;
    public $is_verified;
    public $last_login;
    public $login_attempts;
    public $locked_until;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                 SET username=:username, email=:email, phone=:phone, 
                     first_name=:first_name, last_name=:last_name, 
                     password_hash=:password_hash, role=:role, user_type=:user_type";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        
        // Bind parameters
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":password_hash", $this->password_hash);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":user_type", $this->user_type);
        
        if($stmt->execute()) {
            $this->user_id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }
    
    public function findByUsername($username) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE username = :username AND is_active = true 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function findByEmail($email) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE email = :email AND is_active = true 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function findByEmailOrUsername($email, $username) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE (username = :username OR email = :email) 
                  AND is_active = true 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function updateLoginAttempts($username, $attempts) {
        $query = "UPDATE " . $this->table . " 
                 SET login_attempts = :attempts, 
                     locked_until = CASE WHEN :attempts >= 5 THEN NOW() + INTERVAL 30 MINUTE ELSE NULL END 
                 WHERE username = :username";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":attempts", $attempts);
        $stmt->bindParam(":username", $username);
        return $stmt->execute();
    }
    
    public function updateLastLogin($user_id) {
        $query = "UPDATE " . $this->table . " 
                 SET last_login = NOW(), login_attempts = 0, locked_until = NULL 
                 WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        return $stmt->execute();
    }
    
    public function getUserById($user_id) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE user_id = :user_id AND is_active = true 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function verifyEmail($user_id) {
        $query = "UPDATE " . $this->table . " SET is_verified = true WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        return $stmt->execute();
    }
}
?>