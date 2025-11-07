<?php
class UserSession {
    private $conn;
    private $table = "user_sessions";
    
    public $session_id;
    public $user_id;
    public $login_time;
    public $logout_time;
    public $ip_address;
    public $user_agent;
    public $is_active;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                 SET session_id=:session_id, user_id=:user_id, ip_address=:ip_address, 
                     user_agent=:user_agent";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":session_id", $this->session_id);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":ip_address", $this->ip_address);
        $stmt->bindParam(":user_agent", $this->user_agent);
        
        return $stmt->execute();
    }
    
    public function logout($session_id) {
        $query = "UPDATE " . $this->table . " 
                 SET logout_time = NOW(), is_active = false 
                 WHERE session_id = :session_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":session_id", $session_id);
        return $stmt->execute();
    }
    
    public function findByToken($token) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE session_id = :session_id AND is_active = true 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":session_id", $token);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>