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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #e0ffe0 0%, #e0f7fa 100%);
            color: #2c3e50;
        }
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 3rem;
            background: #388e3c;
            color: #fff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .navbar .logo {
            font-size: 1.6rem;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .navbar ul {
            list-style: none;
            display: flex;
            gap: 1.5rem;
            margin: 0;
            padding: 0;
        }
        .navbar ul li a {
            text-decoration: none;
            color: #fff;
            font-weight: 500;
            transition: color 0.2s;
        }
        .navbar ul li a:hover {
            color: #e0ffe0;
        }
        .hero {
            width: 100%;
            background: linear-gradient(90deg, #388e3c 0%, #43a047 100%);
            color: #fff;
            padding: 60px 0 40px 0;
            text-align: center;
        }
        .hero h1 {
            font-size: 3rem;
            margin-bottom: 0.5rem;
        }
        .hero p {
            font-size: 1.3rem;
            margin-bottom: 1.5rem;
        }
        .hero .btn-group {
            margin-top: 1.5rem;
        }
        .hero .btn {
            background-color: #fff;
            color: #388e3c;
            padding: 14px 32px;
            margin: 0 8px;
            border: none;
            border-radius: 30px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 1.1rem;
            box-shadow: 0 2px 8px rgba(56,142,60,0.12);
            transition: 0.3s;
        }
        .hero .btn:hover {
            background-color: #e0ffe0;
            color: #2c3e50;
        }
        .features-section {
            max-width: 1100px;
            margin: 0 auto;
            padding: 40px 0 60px 0;
        }
        .features-title {
            text-align: center;
            font-size: 2.2rem;
            color: #388e3c;
            margin-bottom: 2rem;
            font-weight: 700;
        }
        .feature-cards {
            display: flex;
            gap: 2rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        .feature-card {
            flex: 1 1 220px;
            max-width: 260px;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.07);
            padding: 2rem 1.2rem;
            text-align: center;
            color: #388e3c;
            transition: transform 0.25s, box-shadow 0.25s;
            position: relative;
        }
        .feature-card:hover {
            transform: translateY(-10px) scale(1.05);
            box-shadow: 0 12px 36px rgba(56,142,60,0.15);
        }
        .feature-card h3 {
            font-size: 1.3rem;
            margin-bottom: 0.7rem;
            font-weight: 600;
        }
        .feature-card ul {
            padding: 0;
            margin: 1rem 0;
            font-size: 1rem;
            list-style: none;
        }
        .feature-card li {
            margin: 0.5rem 0;
            text-align: left;
            padding-left: 1.2rem;
            position: relative;
        }
        .feature-card li.crops::before { content: "🌾 "; position: absolute; left: 0; }
        .feature-card li.livestock::before { content: "🐄 "; position: absolute; left: 0; }
        .feature-card li.inventory::before { content: "📦 "; position: absolute; left: 0; }
        .feature-card li.reports::before { content: "📊 "; position: absolute; left: 0; }
        .feature-card li.users::before { content: "👤 "; position: absolute; left: 0; }
        .feature-card li.settings::before { content: "⚙️ "; position: absolute; left: 0; }
        @media (max-width: 900px) {
            .feature-cards {
                flex-direction: column;
                gap: 1.5rem;
            }
            .features-section {
                padding: 1.5rem;
            }
            .navbar {
                flex-direction: column;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo">🚜 Farm Management</div>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="crops.php">Crops</a></li>
            <li><a href="livestock.php">Livestock</a></li>
            <li><a href="inventory.php">Inventory</a></li>
            <li><a href="reports.php">Reports</a></li>
            <li><a href="users.php">Users</a></li>
            <li><a href="settings.php">Settings</a></li>
        </ul>
    </nav>
    <section class="hero">
        <h1>Welcome to Your Farm Management System</h1>
        <p>Efficiently manage your crops, livestock, inventory, and more!</p>
        <div class="btn-group">
            <a href="/login" class="btn">Log In</a>
            <a href="/register" class="btn">Sign Up</a>
        </div>
    </section>
    <section class="features-section">
        <div class="features-title">Farm Features</div>
        <div class="feature-cards">
            <div class="feature-card">
                <h3>Crops</h3>
                <ul>
                    <li class="crops">Manage crop records</li>
                    <li class="crops">Track planting & harvest</li>
                    <li class="crops">Monitor crop health</li>
                </ul>
            </div>
            <div class="feature-card">
                <h3>Livestock</h3>
                <ul>
                    <li class="livestock">Manage animal records</li>
                    <li class="livestock">Track breeding & health</li>
                    <li class="livestock">Monitor feed & production</li>
                </ul>
            </div>
            <div class="feature-card">
                <h3>Inventory</h3>
                <ul>
                    <li class="inventory">Supplies & equipment</li>
                    <li class="inventory">Stock levels</li>
                    <li class="inventory">Usage history</li>
                </ul>
            </div>
            <div class="feature-card">
                <h3>Reports</h3>
                <ul>
                    <li class="reports">Production reports</li>
                    <li class="reports">Sales & expenses</li>
                    <li class="reports">Performance analytics</li>
                </ul>
            </div>
            <div class="feature-card">
                <h3>Users</h3>
                <ul>
                    <li class="users">User management</li>
                    <li class="users">Roles & permissions</li>
                </ul>
            </div>
            <div class="feature-card">
                <h3>Settings</h3>
                <ul>
                    <li class="settings">System configuration</li>
                    <li class="settings">Notifications</li>
                </ul>
            </div>
        </div>
    </section>
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