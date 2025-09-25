<?php
require_once 'models/User.php';
require_once 'models/TwoFactor.php';
require_once 'models/UserSession.php';
require_once 'services/TokenService.php';
require_once 'services/EmailService.php';

class AuthService {
    private $db;
    private $userModel;
    private $twoFactorModel;
    private $sessionModel;
    private $tokenService;
    private $emailService;
    
    private $tempTokens = [];
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->userModel = new User($this->db);
        $this->twoFactorModel = new TwoFactor($this->db);
        $this->sessionModel = new UserSession($this->db);
        $this->tokenService = new TokenService();
        $this->emailService = new EmailService();
    }
    
    public function register($data) {
        // Validate required fields
        $required = ['username', 'email', 'password', 'first_name', 'last_name'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("Field '$field' is required");
            }
        }
        
        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }
        
        // Check password strength
        if (strlen($data['password']) < 8) {
            throw new Exception("Password must be at least 8 characters long");
        }
        
        // Check if user already exists
        $existingUser = $this->userModel->findByEmailOrUsername($data['email'], $data['username']);
        if ($existingUser) {
            throw new Exception("User with this email or username already exists");
        }
        
        // Create new user
        $this->userModel->username = $data['username'];
        $this->userModel->email = $data['email'];
        $this->userModel->phone = $data['phone'] ?? null;
        $this->userModel->first_name = $data['first_name'];
        $this->userModel->last_name = $data['last_name'];
        $this->userModel->password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
        $this->userModel->role = $data['role'] ?? 'field_worker';
        $this->userModel->user_type = $data['user_type'] ?? 'employee';
        
        if (!$this->userModel->create()) {
            throw new Exception("Failed to create user account");
        }
        
        // Create 2FA configuration (disabled by default)
        $this->twoFactorModel->user_id = $this->userModel->user_id;
        $this->twoFactorModel->method = 'email';
        $this->twoFactorModel->phone_number = $data['phone'] ?? null;
        $this->twoFactorModel->backup_codes = $this->tokenService->generateBackupCodes();
        
        if (!$this->twoFactorModel->create()) {
            throw new Exception("Failed to create 2FA configuration");
        }
        
        // Send verification email
        $verificationToken = $this->tokenService->generate2FAToken();
        $this->emailService->sendVerificationEmail($data['email'], $verificationToken);
        
        // Log the registration
        $this->logAuthAttempt($this->userModel->user_id, 'register', 'success', '', 
                            $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '');
        
        return [
            'success' => true,
            'message' => 'User registered successfully. Please check your email for verification.',
            'user_id' => $this->userModel->user_id
        ];
    }
    
    public function login($username, $password, $ipAddress = '', $userAgent = '') {
        // Check if user is rate limited
        if ($this->isRateLimited($username)) {
            throw new Exception("Too many login attempts. Please try again in 30 minutes.");
        }
        
        // Find user by username or email
        $user = $this->userModel->findByUsername($username);
        if (!$user) {
            // Try finding by email
            $user = $this->userModel->findByEmail($username);
        }
        
        if (!$user || !$user['is_active']) {
            $this->logAuthAttempt(null, 'login', 'failure', 'Invalid username/email', $ipAddress, $userAgent);
            throw new Exception("Invalid credentials");
        }
        
        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            $newAttempts = $user['login_attempts'] + 1;
            $this->userModel->updateLoginAttempts($user['username'], $newAttempts);
            $this->logAuthAttempt($user['user_id'], 'login', 'failure', 'Invalid password', $ipAddress, $userAgent);
            throw new Exception("Invalid credentials");
        }
        
        // Check if user is verified
        if (!$user['is_verified']) {
            throw new Exception("Please verify your email before logging in");
        }
        
        // Check if 2FA is enabled
        $twoFactorConfig = $this->twoFactorModel->findByUserId($user['user_id']);
        
        if ($twoFactorConfig && $twoFactorConfig['is_enabled']) {
            return $this->initiate2FA($user, $twoFactorConfig, $ipAddress, $userAgent);
        }
        
        // Login without 2FA
        return $this->completeLogin($user, $ipAddress, $userAgent);
    }
    
    private function initiate2FA($user, $twoFactorConfig, $ipAddress, $userAgent) {
        $token = $this->tokenService->generate2FAToken();
        $tempSessionId = uniqid('2fa_', true);
        
        // Store temporary token (10-minute expiry)
        $this->tempTokens[$tempSessionId] = [
            'user_id' => $user['user_id'],
            'token' => $token,
            'expires' => time() + 600,
            'ip_address' => $ipAddress
        ];
        
        // Send 2FA code via selected method
        if ($twoFactorConfig['method'] === 'email') {
            $this->emailService->send2FACode($user['email'], $token);
        } elseif ($twoFactorConfig['method'] === 'sms' && !empty($twoFactorConfig['phone_number'])) {
            // SMS implementation would go here
            // $this->smsService->send2FACode($twoFactorConfig['phone_number'], $token);
        }
        
        $this->logAuthAttempt($user['user_id'], '2fa_initiated', 'success', '', $ipAddress, $userAgent);
        
        return [
            'success' => true,
            'message' => '2FA code sent to your ' . $twoFactorConfig['method'],
            'temp_session_id' => $tempSessionId,
            'method' => $twoFactorConfig['method']
        ];
    }
    
    public function verify2FA($tempSessionId, $token, $ipAddress = '', $userAgent = '') {
        if (!isset($this->tempTokens[$tempSessionId])) {
            throw new Exception("Invalid or expired 2FA session");
        }
        
        $tempSession = $this->tempTokens[$tempSessionId];
        
        // Check expiration
        if ($tempSession['expires'] < time()) {
            unset($this->tempTokens[$tempSessionId]);
            throw new Exception("2FA token has expired");
        }
        
        // Check IP address (basic security)
        if ($tempSession['ip_address'] !== $ipAddress) {
            $this->logAuthAttempt($tempSession['user_id'], '2fa_verify', 'failure', 'IP mismatch', $ipAddress, $userAgent);
            throw new Exception("Security violation detected");
        }
        
        // Verify token
        if ($tempSession['token'] !== $token) {
            $this->logAuthAttempt($tempSession['user_id'], '2fa_verify', 'failure', 'Invalid token', $ipAddress, $userAgent);
            throw new Exception("Invalid verification code");
        }
        
        // Get fresh user data
        $user = $this->userModel->getUserById($tempSession['user_id']);
        if (!$user) {
            throw new Exception("User not found");
        }
        
        // Complete login
        $result = $this->completeLogin($user, $ipAddress, $userAgent);
        
        // Clean up temporary session
        unset($this->tempTokens[$tempSessionId]);
        
        $this->logAuthAttempt($user['user_id'], '2fa_verify', 'success', '', $ipAddress, $userAgent);
        
        return $result;
    }
    
    private function completeLogin($user, $ipAddress, $userAgent) {
        // Update last login and reset attempts
        $this->userModel->updateLastLogin($user['user_id']);
        
        // Generate JWT token
        $token = $this->tokenService->generateJWT(
            $user['user_id'], 
            $user['username'], 
            $user['role']
        );
        
        // Create session record
        $this->sessionModel->session_id = $token;
        $this->sessionModel->user_id = $user['user_id'];
        $this->sessionModel->ip_address = $ipAddress;
        $this->sessionModel->user_agent = $userAgent;
        $this->sessionModel->create();
        
        $this->logAuthAttempt($user['user_id'], 'login', 'success', '', $ipAddress, $userAgent);
        
        return [
            'success' => true,
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'user_id' => $user['user_id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name']
            ]
        ];
    }
    
    public function logout($token) {
        $session = $this->sessionModel->findByToken($token);
        if ($session) {
            $this->sessionModel->logout($token);
            $this->logAuthAttempt($session['user_id'], 'logout', 'success');
        }
        return ['success' => true, 'message' => 'Logged out successfully'];
    }
    
    public function verifyToken($token) {
        $payload = $this->tokenService->validateJWT($token);
        if (!$payload) {
            return false;
        }
        
        // Check if session is still active
        $session = $this->sessionModel->findByToken($token);
        if (!$session) {
            return false;
        }
        
        return $payload;
    }
    
    public function enable2FA($user_id, $method, $phone = null) {
        $twoFactorConfig = $this->twoFactorModel->findByUserId($user_id);
        
        if (!$twoFactorConfig) {
            throw new Exception("2FA configuration not found");
        }
        
        $this->twoFactorModel->method = $method;
        if ($method === 'sms' && $phone) {
            $this->twoFactorModel->phone_number = $phone;
        }
        $this->twoFactorModel->is_enabled = true;
        
        $result = $this->twoFactorModel->update($user_id);
        
        if ($result) {
            $this->logAuthAttempt($user_id, 'enable_2fa', 'success', "Method: $method");
        }
        
        return $result;
    }
    
    public function disable2FA($user_id) {
        $result = $this->twoFactorModel->disable2FA($user_id);
        
        if ($result) {
            $this->logAuthAttempt($user_id, 'disable_2fa', 'success');
        }
        
        return $result;
    }
    
    private function isRateLimited($username) {
        $user = $this->userModel->findByUsername($username);
        if (!$user) return false;
        
        // Check if account is locked
        if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
            return true;
        }
        
        return false;
    }
    
    private function logAuthAttempt($user_id, $action, $status, $details = '', $ipAddress = '', $userAgent = '') {
        $query = "INSERT INTO auth_audit_log (user_id, action, ip_address, user_agent, status, details) 
                  VALUES (:user_id, :action, :ip_address, :user_agent, :status, :details)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":action", $action);
        $stmt->bindParam(":ip_address", $ipAddress);
        $stmt->bindParam(":user_agent", $userAgent);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":details", $details);
        
        $stmt->execute();
    }
}
?>