<?php
// controllers/AuthController.php
require_once __DIR__ . '/../services/AuthService.php';

class AuthController {
    private $auth;
    public function __construct() { $this->auth = new AuthService(); }

    public function register() {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        try {
            $res = $this->auth->register($input);
            http_response_code(201);
            echo json_encode($res);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
        }
    }

    public function login() {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        try {
            $res = $this->auth->login($input['username'], $input['password'], $ip, $ua);
            echo json_encode($res);
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
        }
    }

    public function verify2FA() {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        try {
            $res = $this->auth->verify2FA($input['temp_session_id'], $input['token'], $ip, $ua);
            echo json_encode($res);
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
        }
    }

    public function enable2FA() {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        try {
            $this->auth->enable2FA($input['user_id'], $input['method'], $input['phone'] ?? null);
            echo json_encode(['success'=>true,'message'=>'2FA enabled']);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
        }
    }
}
