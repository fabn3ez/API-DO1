<?php
// services/TokenService.php
require_once __DIR__ . '/../vendor/autoload.php'; // if using composer for firebase/php-jwt
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class TokenService {
    private $secret;
    private $algo = 'HS256';

    public function __construct() {
        $this->secret = getenv('JWT_SECRET') ?: 'change_this_secret';
    }

    public function generate2FAToken(): string {
        return sprintf("%06d", random_int(100000, 999999));
    }

    public function generateJWT(int $user_id, string $username, string $role): string {
        $issuedAt = time();
        $expire = $issuedAt + ((int)(getenv('JWT_EXPIRES_IN') ?: 86400));
        $payload = [
            'iat' => $issuedAt,
            'exp' => $expire,
            'user_id' => $user_id,
            'username' => $username,
            'role' => $role
        ];
        return JWT::encode($payload, $this->secret, $this->algo);
    }

    public function validateJWT(string $token): ?array {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, $this->algo));
            return (array)$decoded;
        } catch (Exception $e) {
            return null;
        }
    }
}
