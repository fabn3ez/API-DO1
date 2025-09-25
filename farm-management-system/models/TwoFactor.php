<?php
// models/TwoFactor.php
class TwoFactor {
    private $conn;
    private $table = 'two_factor_auth';

    public function __construct(PDO $db) { $this->conn = $db; }

    public function findByUserId($user_id) {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :user_id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetch();
    }

    public function createForUser($user_id, $method = 'email', $phone = null, array $codes = []) {
        $sql = "INSERT INTO {$this->table} (user_id, method, phone_number, backup_codes, is_enabled) VALUES (:user_id, :method, :phone, :codes, 0)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':user_id' => $user_id,
            ':method' => $method,
            ':phone' => $phone,
            ':codes' => json_encode($codes)
        ]);
    }

    public function enable($user_id, $method='email', $phone = null) {
        // create record if missing
        $exists = $this->findByUserId($user_id);
        if (!$exists) {
            return $this->createForUser($user_id, $method, $phone, []);
        }
        $sql = "UPDATE {$this->table} SET method = :method, phone_number = :phone, is_enabled = 1 WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':method'=>$method, ':phone'=>$phone, ':user_id'=>$user_id]);
    }

    public function updateBackupCodes($user_id, array $codes) {
        $sql = "UPDATE {$this->table} SET backup_codes = :codes WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':codes' => json_encode($codes), ':user_id' => $user_id]);
    }
}
