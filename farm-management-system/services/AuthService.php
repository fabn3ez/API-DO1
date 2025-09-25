<?php
// services/AuthService.php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/TwoFactor.php';
require_once __DIR__ . '/../models/UserSession.php';
require_once __DIR__ . '/TokenService.php';
require_once __DIR__ . '/EmailService.php';

class AuthService {
    private $db;
    private $userModel;
    private $twoFactor;
    private $sessionModel;
    private $tokenService;
    private $emailService;

    // In-memory temp tokens for demo. Replace with DB/Redis in prod.
    private $tempTokens = [];

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->userModel = new User($this->db);
        $this->twoFactor = new TwoFactor($this->db);
        $this->sessionModel = new UserSession($this->db);
        $this->tokenService = new TokenService();
        $this->emailService = new EmailService();
    }

    public function register(array $data) {
        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            throw new Exception("username, email and password are required");
        }
        if ($this->userModel->findByUsername($data['username']) || $this->userModel->findByEmail($data['email'])) {
            throw new Exception("User exists");
        }
        $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $user_id = $this->userModel->create($data);
        // create 2FA config (disabled)
        $this->twoFactor->createForUser($user_id, 'email', $data['phone'] ?? null, []);
        return ['success'=>true,'user_id'=>$user_id];
    }

    public function login(string $username, string $password, string $ip = '', string $ua = '') {
        $u = $this->userModel->findByUsername($username);
        if (!$u) {
            throw new Exception("Invalid credentials");
        }
        if (!empty($u['locked_until']) && strtotime($u['locked_until']) > time()) {
            throw new Exception("Account locked. Try later.");
        }
        if (!password_verify($password, $u['password'])) {
            $this->userModel->updateLoginAttempts($username, ($u['login_attempts'] ?? 0) + 1);
            throw new Exception("Invalid credentials");
        }
        $tf = $this->twoFactor->findByUserId($u['id']);
        if ($tf && $tf['is_enabled']) {
            return $this->initiate2FA($u, $tf, $ip, $ua);
        }
        return $this->completeLogin($u, $ip, $ua);
    }

    private function initiate2FA(array $user, array $twoFactorConfig, $ip, $ua) {
        $code = $this->tokenService->generate2FAToken();
        $tempSessionId = uniqid('2fa_', true);
        // store in memory
        $this->tempTokens[$tempSessionId] = [
            'user_id' => $user['id'],
            'token' => $code,
            'expires' => time() + 600,
            'ip' => $ip
        ];
        // send according to method
        if ($twoFactorConfig['method'] === 'sms' && !empty($twoFactorConfig['phone_number'])) {
            // todo: integrate SMS provider here
        } else {
            $this->emailService->send2FACode($user['email'], $code);
        }
        return ['message'=>'2FA required','temp_session_id'=>$tempSessionId,'method'=>$twoFactorConfig['method']];
    }

    public function verify2FA($tempSessionId, $token, $ip = '', $ua = '') {
        if (!isset($this->tempTokens[$tempSessionId])) throw new Exception("Invalid or expired 2FA session");
        $t = $this->tempTokens[$tempSessionId];
        if ($t['expires'] < time()) { unset($this->tempTokens[$tempSessionId]); throw new Exception("2FA expired"); }
        // optional IP check:
        // if ($t['ip'] !== $ip) { throw new Exception("IP mismatch"); }
        if ($t['token'] !== $token) { throw new Exception("Invalid code"); }
        $user = $this->userModel->findById($t['user_id']);
        unset($this->tempTokens[$tempSessionId]);
        return $this->completeLogin($user, $ip, $ua);
    }

    private function completeLogin(array $user, $ip, $ua) {
        $this->userModel->resetLogin($user['id']);
        $jwt = $this->tokenService->generateJWT($user['id'], $user['username'], $user['role'] ?? 'field_worker');
        $this->sessionModel->create($jwt, $user['id'], $ip, $ua);
        return ['message'=>'Login successful','token'=>$jwt,'user'=>['id'=>$user['id'],'username'=>$user['username'],'email'=>$user['email']]];
    }

    public function enable2FA($user_id, $method='email', $phone = null) {
        return $this->twoFactor->enable($user_id, $method, $phone);
    }
}
