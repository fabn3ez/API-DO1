<?php
// models/UserSession.php
class UserSession {
    private $conn;
    private $table = 'user_sessions';

    public function __construct(PDO $db) { $this->conn = $db; }

    public function create($session_id, $user_id, $ip = null, $ua = null) {
        $sql = "INSERT INTO {$this->table} (session_id, user_id, ip_address, user_agent) VALUES (:session_id, :user_id, :ip, :ua)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':session_id'=>$session_id, ':user_id'=>$user_id, ':ip'=>$ip, ':ua'=>$ua]);
    }
}
