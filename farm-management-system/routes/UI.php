<?php
require_once __DIR__ . '/../controllers/InventoryController.php';
require_once __DIR__ . '/../controllers/SalesController.php';
require_once __DIR__ . '/../controllers/CropController.php';
require_once __DIR__ . '/../controllers/LivestockController.php';
require_once __DIR__ . '/../controllers/ReportController.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'sales':
        echo json_encode((new SalesController())->getSales());
        break;

    case 'add_product':
        $input = json_decode(file_get_contents("php://input"), true);
        echo json_encode((new InventoryController())->addProduct($input));
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
}
?>