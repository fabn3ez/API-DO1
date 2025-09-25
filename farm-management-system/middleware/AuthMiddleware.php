<?php
require_once 'services/AuthService.php';

class AuthMiddleware {
    private $authService;
    
    public function __construct() {
        $this->authService = new AuthService();
    }
    
    public function authenticate() {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';
        $token = str_replace('Bearer ', '', $authHeader);
        
        if (empty($token)) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Authentication token required']);
            exit;
        }
        
        $payload = $this->authService->verifyToken($token);
        
        if (!$payload) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid or expired token']);
            exit;
        }
        
        return $payload;
    }
    
    public function requireRole($requiredRole) {
        $user = $this->authenticate();
        
        if ($user['role'] !== $requiredRole && $user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
            exit;
        }
        
        return $user;
    }
    
    public function requireAnyRole($allowedRoles) {
        $user = $this->authenticate();
        
        if (!in_array($user['role'], $allowedRoles) && $user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
            exit;
        }
        
        return $user;
    }
}
?>