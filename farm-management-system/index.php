<?php
/**
 * Farm Management System - Main Application Entry Point
 * 
 * @package FarmManagementSystem
 * @version 1.0.0
 */

// Display errors if in development mode
if (getenv('APP_ENV') === 'development' || (isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] === 'true')) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

// Set default timezone
date_default_timezone_set('UTC');

// Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

use FarmManagement\Utils\ErrorHandler;
use FarmManagement\Config\Database;
use FarmManagement\Controllers\AuthController;

// Register error handlers
set_exception_handler([ErrorHandler::class, 'handleException']);
set_error_handler([ErrorHandler::class, 'handleError']);

// CORS headers for API requests
header("Access-Control-Allow-Origin: " . ($_ENV['ALLOWED_ORIGINS'] ?? '*'));
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Security headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");

try {
    // Parse request URL and method
    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $requestMethod = $_SERVER['REQUEST_METHOD'];
    $queryString = $_SERVER['QUERY_STRING'] ?? '';
    
    // Remove base path if application is in a subdirectory
    $basePath = '/farm-management-system';
    if (strpos($requestUri, $basePath) === 0) {
        $requestUri = substr($requestUri, strlen($basePath));
    }
    
    // Remove trailing slash
    $requestUri = rtrim($requestUri, '/');
    
    // If empty request, default to root
    if (empty($requestUri) || $requestUri === '') {
        $requestUri = '/';
    }
    
    // Log the request (for debugging)
    if (getenv('APP_DEBUG') === 'true') {
        error_log("Request: $requestMethod $requestUri");
    }
    
    // Route the request
    $authController = new AuthController();
    
    switch (true) {
        // API Routes
        case $requestUri === '/api/register' && $requestMethod === 'POST':
            $authController->register();
            break;
            
        case $requestUri === '/api/login' && $requestMethod === 'POST':
            $authController->login();
            break;
            
        case $requestUri === '/api/verify-2fa' && $requestMethod === 'POST':
            $authController->verify2FA();
            break;
            
        case $requestUri === '/api/logout' && $requestMethod === 'POST':
            $authController->logout();
            break;
            
        case $requestUri === '/api/validate-token' && $requestMethod === 'GET':
            $authController->validateToken();
            break;
            
        case $requestUri === '/api/enable-2fa' && $requestMethod === 'POST':
            $authController->enable2FA();
            break;
            
        case $requestUri === '/api/disable-2fa' && $requestMethod === 'POST':
            $authController->disable2FA();
            break;
            
        case $requestUri === '/api/me' && $requestMethod === 'GET':
            $authController->getCurrentUser();
            break;
            
        // Web Routes
        case $requestUri === '/register' && $requestMethod === 'GET':
            $this->serveStaticPage('register');
            break;
            
        case $requestUri === '/login' && $requestMethod === 'GET':
            $this->serveStaticPage('login');
            break;
            
        case $requestUri === '/verify-2fa' && $requestMethod === 'GET':
            $this->serveStaticPage('verify-2fa');
            break;
            
        case $requestUri === '/dashboard' && $requestMethod === 'GET':
            $this->serveStaticPage('dashboard');
            break;
            
        case $requestUri === '/' && $requestMethod === 'GET':
            $this->serveWelcomePage();
            break;
            
        // Static asset routes
        case preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg)$/i', $requestUri):
            $this->serveStaticAsset($requestUri);
            break;
            
        // Health check endpoint
        case $requestUri === '/api/health' && $requestMethod === 'GET':
            $this->healthCheck();
            break;
            
        // Database test endpoint
        case $requestUri === '/api/test-db' && $requestMethod === 'GET':
            $this->testDatabase();
            break;
            
        default:
            $this->handleNotFound($requestUri);
            break;
    }
    
} catch (Throwable $e) {
    ErrorHandler::handleException($e);
}

/**
 * Serve welcome page
 */
function serveWelcomePage() {
    if (isApiRequest()) {
        echo json_encode([
            'success' => true,
            'message' => 'Farm Management System API',
            'version' => '1.0.0',
            'endpoints' => [
                'POST /api/register' => 'User registration',
                'POST /api/login' => 'User login',
                'POST /api/verify-2fa' => 'Verify 2FA code',
                'POST /api/logout' => 'User logout',
                'GET /api/validate-token' => 'Validate JWT token',
                'GET /api/health' => 'System health check'
            ]
        ]);
    } else {
        // Serve HTML welcome page
        header('Content-Type: text/html');
        echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farm Management System</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            text-align: center;
            max-width: 500px;
        }
        h1 {
            color: #2d3748;
            margin-bottom: 10px;
        }
        p {
            color: #4a5568;
            margin-bottom: 20px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px;
        }
        .btn:hover {
            background: #5a6fd8;
        }
        .api-info {
            background: #f7fafc;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚜 Farm Management System</h1>
        <p>Welcome to your agricultural management platform</p>
        
        <div>
            <a href="/login" class="btn">Login</a>
            <a href="/register" class="btn">Register</a>
        </div>
        
        <div class="api-info">
            <h3>API Endpoints:</h3>
            <ul>
                <li><strong>POST /api/register</strong> - User registration</li>
                <li><strong>POST /api/login</strong> - User login</li>
                <li><strong>POST /api/verify-2fa</strong> - Verify 2FA code</li>
                <li><strong>GET /api/health</strong> - System status</li>
            </ul>
        </div>
    </div>
</body>
</html>
HTML;
    }
}

/**
 * Serve static pages
 */
function serveStaticPage($page) {
    $pageFile = __DIR__ . "/views/auth/{$page}.php";
    
    if (file_exists($pageFile)) {
        header('Content-Type: text/html');
        include $pageFile;
    } else {
        // Fallback to basic HTML if view file doesn't exist
        header('Content-Type: text/html');
        echo <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Farm Management System - {$page}</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div style="padding: 20px; text-align: center;">
        <h1>Farm Management System</h1>
        <h2>{$page} Page</h2>
        <p>This page is under construction. View file: views/auth/{$page}.php</p>
        <a href="/">← Back to Home</a>
    </div>
</body>
</html>
HTML;
    }
}

/**
 * Serve static assets
 */
function serveStaticAsset($path) {
    $assetPath = __DIR__ . '/public' . $path;
    
    if (file_exists($assetPath)) {
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'ico' => 'image/x-icon',
            'svg' => 'image/svg+xml'
        ];
        
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (isset($mimeTypes[$extension])) {
            header('Content-Type: ' . $mimeTypes[$extension]);
        }
        
        readfile($assetPath);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Asset not found']);
    }
}

/**
 * Health check endpoint
 */
function healthCheck() {
    $status = [
        'success' => true,
        'message' => 'System is healthy',
        'timestamp' => date('c'),
        'version' => '1.0.0',
        'environment' => getenv('APP_ENV') ?? 'production'
    ];
    
    // Check database connection
    try {
        $database = new Database();
        $conn = $database->getConnection();
        $status['database'] = 'connected';
    } catch (Exception $e) {
        $status['database'] = 'disconnected';
        $status['db_error'] = $e->getMessage();
    }
    
    echo json_encode($status);
}

/**
 * Database test endpoint
 */
function testDatabase() {
    try {
        $database = new Database();
        $conn = $database->getConnection();
        
        // Test basic query
        $stmt = $conn->query("SELECT 1 as test");
        $result = $stmt->fetch();
        
        echo json_encode([
            'success' => true,
            'message' => 'Database connection successful',
            'test_query' => $result
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed',
            'error' => $e->getMessage()
        ]);
    }
}

/**
 * Handle 404 Not Found
 */
function handleNotFound($requestUri) {
    if (isApiRequest()) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Endpoint not found: ' . $requestUri,
            'available_endpoints' => [
                '/api/register',
                '/api/login', 
                '/api/verify-2fa',
                '/api/logout',
                '/api/validate-token',
                '/api/health'
            ]
        ]);
    } else {
        http_response_code(404);
        header('Content-Type: text/html');
        echo <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>404 - Page Not Found</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            text-align: center; 
            padding: 50px;
            background: #f0f0f0;
        }
        h1 { color: #d63031; }
    </style>
</head>
<body>
    <h1>404 - Page Not Found</h1>
    <p>The requested page <strong>{$requestUri}</strong> was not found.</p>
    <a href="/">← Back to Home</a>
</body>
</html>
HTML;
    }
}

/**
 * Check if request is an API request
 */
function isApiRequest() {
    return strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false ||
           strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') === 0 ||
           ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_SERVER['HTTP_ACCEPT']));
}

?>