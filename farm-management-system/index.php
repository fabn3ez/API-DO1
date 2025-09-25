<?php
// index.php
require_once __DIR__ . '/utils/ErrorHandler.php';
require_once __DIR__ . '/controllers/AuthController.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base = '/farm-management-system'; // change or set to ''
if (strpos($uri, $base) === 0) $uri = substr($uri, strlen($base));

$auth = new AuthController();

switch (true) {
    case $uri === '/register' && $_SERVER['REQUEST_METHOD'] === 'POST':
        $auth->register(); break;
    case $uri === '/login' && $_SERVER['REQUEST_METHOD'] === 'POST':
        $auth->login(); break;
    case $uri === '/verify-2fa' && $_SERVER['REQUEST_METHOD'] === 'POST':
        $auth->verify2FA(); break;
    case $uri === '/enable-2fa' && $_SERVER['REQUEST_METHOD'] === 'POST':
        $auth->enable2FA(); break;
    default:
        http_response_code(404);
        echo json_encode(['success'=>false,'message'=>'Endpoint not found']);
}
