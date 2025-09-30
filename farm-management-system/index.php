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
    require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/config/Database.php';
// Remove or adjust the namespace if Database.php does not declare it
require_once __DIR__ . '/controllers/AuthController.php';
use FarmManagement\Controllers\AuthController;

// Manually require ErrorHandler if autoload is not working
if (!class_exists('FarmManagement\\Utils\\ErrorHandler')) {
    require_once __DIR__ . '/utils/ErrorHandler.php';
}
use FarmManagement\Utils\ErrorHandler;

// Register error handlers
//set_exception_handler(['FarmManagement\\Utils\\ErrorHandler', 'handleException']);
//set_error_handler(['FarmManagement\\Utils\\ErrorHandler', 'handleError']);

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
    $basePaths = ['/API-DO1/farm-management-system', '/farm-management-system'];
    foreach ($basePaths as $basePath) {
        if (strpos($requestUri, $basePath) === 0) {
            $requestUri = substr($requestUri, strlen($basePath));
        }
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
            serveStaticPage('register');
            break;
        case $requestUri === '/login' && $requestMethod === 'GET':
            serveStaticPage('login');
            break;
        case $requestUri === '/verify-2fa' && $requestMethod === 'GET':
            serveStaticPage('verify-2fa');
            break;
        case $requestUri === '/dashboard' && $requestMethod === 'GET':
            serveStaticPage('dashboard');
            break;
        case $requestUri === '/' && $requestMethod === 'GET':
            serveWelcomePage();
            break;
        // Static asset routes
        case preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg)$/i', $requestUri):
            serveStaticAsset($requestUri);
            break;
        // Health check endpoint
        case $requestUri === '/api/health' && $requestMethod === 'GET':
            healthCheck();
            break;
        // Database test endpoint
        case $requestUri === '/api/test-db' && $requestMethod === 'GET':
            testDatabase();
            break;
        default:
            handleNotFound($requestUri);
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
        header('Content-Type: text/html');
        echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Farm Management System</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            color: #2c3e50;
        }
        header {
            position: relative;
            width: 100%;
            height: 320px;
            overflow: hidden;
        }
        header img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: brightness(0.8);
        }
        header .overlay {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            color: #fff;
            text-align: center;
            background: rgba(0,0,0,0.3);
        }
        header h1 {
            font-size: 3rem;
            margin: 0;
        }
        header p {
            font-size: 1.2rem;
        }
        main {
            max-width: 1000px;
            margin: -40px auto 50px;
            background: #fff;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }
        .btn-group {
            text-align: center;
            margin-bottom: 30px;
        }
        .btn {
            background-color: #27ae60;
            color: #fff;
            padding: 12px 28px;
            margin: 0 8px;
            border: none;
            border-radius: 30px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: 0.3s;
        }
        .btn:hover {
            background-color: #229954;
        }
        .api-section h3 {
            text-align: center;
            color: #27ae60;
            margin-bottom: 20px;
        }
        .api-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .api-card {
            background: #eaf7ed;
            padding: 20px;
            border-radius: 15px;
            box-shadow: inset 0 0 8px rgba(0,0,0,0.05);
        }
        .api-card strong {
            color: #27ae60;
        }
    </style>
</head>
<body>
    <header>
        <!-- Replace this URL with your preferred farm banner image -->
        <img src="https://images.unsplash.com/photo-1504593811423-6dd665756598?auto=format&fit=crop&w=1600&q=80" 
             alt="Farm banner image">
        <div class="overlay">
            <h1>🌾 Farm Management System</h1>
            <p>Efficiently manage your agricultural operations</p>
        </div>
    </header>

    <main>
        <div class="btn-group">
            <a href="/login" class="btn">Log In</a>
            <a href="/register" class="btn">Sign Up</a>
        </div>

        <section class="api-section">
            <h3>Important API Endpoints</h3>
            <div class="api-cards">
                <div class="api-card"><strong>POST /api/register</strong><br>User registration</div>
                <div class="api-card"><strong>POST /api/login</strong><br>User login</div>
                <div class="api-card"><strong>POST /api/verify-2fa</strong><br>Verify 2FA code</div>
                <div class="api-card"><strong>POST /api/logout</strong><br>User logout</div>
                <div class="api-card"><strong>GET /api/validate-token</strong><br>Validate JWT token</div>
                <div class="api-card"><strong>GET /api/health</strong><br>System health check</div>
            </div>
        </section>
    </main>
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