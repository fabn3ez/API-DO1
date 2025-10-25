<?php
require_once __DIR__ . '/../controllers/UserController.php';
require_once __DIR__ . '/../controllers/FarmController.php';
require_once __DIR__ . '/../controllers/InventoryController.php';
require_once __DIR__ . '/../controllers/SalesController.php';
require_once __DIR__ . '/../controllers/CropController.php';
require_once __DIR__ . '/../controllers/LivestockController.php';
require_once __DIR__ . '/../controllers/ReportController.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$action = $_GET['action'] ?? null;

try {
    switch ($action) {
        case 'user':
            echo json_encode((new UserController())->getCurrentUser());
            break;

        case 'stats':
            echo json_encode((new FarmController())->getFarmStats());
            break;

        case 'inventory_list':
            echo json_encode((new InventoryController())->listProducts());
            break;

        case 'add_product':
            $input = json_decode(file_get_contents("php://input"), true) ?: $_POST;
            echo json_encode((new InventoryController())->addProduct($input));
            break;

        case 'sales_recent':
            echo json_encode((new SalesController())->getRecentSales());
            break;

        case 'sales_monthly':
            echo json_encode((new SalesController())->getMonthlyProduction());
            break;

        case 'crop_status':
            echo json_encode((new CropController())->getCropStatus());
            break;

        case 'livestock_status':
            echo json_encode((new LivestockController())->getLivestockStatus());
            break;

        case 'notifications':
            echo json_encode((new ReportController())->getNotifications());
            break;

        default:
            echo json_encode(["error" => "Invalid action"]);
            break;
    }
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
