<?php
class TwoFactor {
    private $conn;
    private $table = "two_factor_auth";
    
    public $two_factor_id;
    public $user_id;
    public $method;
    public $secret_key;
    public $phone_number;
    public $backup_codes;
    public $is_enabled;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                 SET user_id=:user_id, method=:method, phone_number=:phone_number, 
                     backup_codes=:backup_codes";
        
        $stmt = $this->conn->prepare($query);
        
        $backup_codes_json = json_encode($this->backup_codes);
        
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":method", $this->method);
        $stmt->bindParam(":phone_number", $this->phone_number);
        $stmt->bindParam(":backup_codes", $backup_codes_json);
        
        return $stmt->execute();
    }
    
    public function findByUserId($user_id) {
        $query = "SELECT * FROM " . $this->table . " WHERE user_id = :user_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function update($user_id) {
        $query = "UPDATE " . $this->table . " 
                 SET method=:method, phone_number=:phone_number, is_enabled=:is_enabled 
                 WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":method", $this->method);
        $stmt->bindParam(":phone_number", $this->phone_number);
        $stmt->bindParam(":is_enabled", $this->is_enabled);
        $stmt->bindParam(":user_id", $user_id);
        
        return $stmt->execute();
    }
    
    public function enable2FA($user_id) {
        $query = "UPDATE " . $this->table . " SET is_enabled = true WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        return $stmt->execute();
    }
    
    public function disable2FA($user_id) {
        $query = "UPDATE " . $this->table . " SET is_enabled = false WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        return $stmt->execute();
    }
}
?>