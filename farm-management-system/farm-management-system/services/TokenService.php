<?php
class TokenService {
    private $secret_key;
    
    public function __construct() {
        $this->secret_key = getenv('JWT_SECRET') ?: 'farm_management_secret_key';
    }
    
    public function generate2FAToken() {
        return sprintf("%06d", mt_rand(1, 999999));
    }
    
    public function generateJWT($user_id, $username, $role) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode([
            'user_id' => $user_id,
            'username' => $username,
            'role' => $role,
            'exp' => time() + (24 * 60 * 60) // 24 hours
        ]);
        
        $base64UrlHeader = $this->base64UrlEncode($header);
        $base64UrlPayload = $this->base64UrlEncode($payload);
        
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $this->secret_key, true);
        $base64UrlSignature = $this->base64UrlEncode($signature);
        
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }
    
    public function validateJWT($jwt) {
        $parts = explode('.', $jwt);
        if (count($parts) != 3) return false;
        
        list($header, $payload, $signature) = $parts;
        
        $validSignature = $this->base64UrlEncode(
            hash_hmac('sha256', $header . "." . $payload, $this->secret_key, true)
        );
        
        if ($signature !== $validSignature) return false;
        
        $decodedPayload = json_decode($this->base64UrlDecode($payload), true);
        
        if (isset($decodedPayload['exp']) && $decodedPayload['exp'] < time()) {
            return false; // Token expired
        }
        
        return $decodedPayload;
    }
    
    private function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    private function base64UrlDecode($data) {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
    
    public function generateBackupCodes() {
        $codes = [];
        for ($i = 0; $i < 5; $i++) {
            $codes[] = sprintf("%08d", mt_rand(1, 99999999));
        }
        return $codes;
    }
}
?>