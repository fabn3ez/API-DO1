<?php
// FarmerController.php (robust loader + helpful error messages)

$tried = [];

// Candidate paths (relative to this controllers folder)
$candidates = [
    __DIR__ . '/../database/Database.php',
    __DIR__ . '/../../database/Database.php',
    __DIR__ . '/../config/Database.php',
    __DIR__ . '/../../config/Database.php',
    __DIR__ . '/Database.php'
];

$loaded = false;
foreach ($candidates as $path) {
    $tried[] = $path;
    if (file_exists($path)) {
        require_once $path;
        $loaded = true;
        break;
    }
}

// If the file wasn't loaded, show exact diagnostic and stop.
if (!$loaded) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "ERROR: Could not find Database.php. PHP tried the following paths (relative to controllers folder):\n\n";
    foreach ($tried as $p) {
        echo " - $p\n";
    }
    echo "\nPlease verify the actual Database.php location and adjust the require path in FarmerController.php accordingly.\n";
    exit;
}

// Confirm Database class exists
if (!class_exists('Database')) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "ERROR: Database.php was loaded but the class 'Database' was NOT found.\n";
    echo "Possible causes:\n";
    echo " - Database.php defines a different class name or namespace.\n";
    echo " - There is a fatal error inside Database.php that prevented class definition.\n\n";
    echo "Open the file and check the class name (it should be: class Database { ... }).\n";
    exit;
}

// Now the Database class exists — normal controller follows
class FarmerController
{
    private $conn;

    public function __construct()
    {
        try {
            $database = new Database();
            $this->conn = $database->getConnection();
        } catch (Exception $e) {
            // Show a friendly error so you can debug DB connection problems
            header('Content-Type: text/plain; charset=utf-8');
            echo "ERROR: Failed to create Database connection: " . $e->getMessage();
            exit;
        }
    }

    public function getStats()
    {
        try {
            $query = "
                SELECT 
                    COUNT(*) AS total_products,
                    IFNULL(SUM(price * quantity), 0) AS inventory_value,
                    (SELECT IFNULL(SUM(amount), 0) FROM sales) AS total_sales
                FROM products
            ";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error fetching stats: ' . $e->getMessage());
            return ["total_products" => 0, "inventory_value" => 0, "total_sales" => 0];
        }
    }

    public function getProductionTrend()
    {
        try {
            $query = "
                SELECT 
                    DATE_FORMAT(created_at, '%b') AS month,
                    SUM(quantity) AS total
                FROM products
                GROUP BY MONTH(created_at)
                ORDER BY MONTH(created_at)
            ";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error fetching production trend: ' . $e->getMessage());
            return [];
        }
    }

    public function getLivestockDistribution()
    {
        try {
            $query = "
                SELECT 
                    type AS type,
                    COUNT(*) AS count
                FROM livestock
                GROUP BY type
            ";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error fetching livestock data: ' . $e->getMessage());
            return [];
        }
    }

    // optional helpers used earlier
    public function getRecentOrders()
    {
        try {
            $query = "
                SELECT o.id, p.product_name AS product_name, o.quantity, o.amount, o.created_at
                FROM sales o
                LEFT JOIN products p ON o.product_id = p.id
                ORDER BY o.created_at DESC
                LIMIT 5
            ";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error fetching orders: ' . $e->getMessage());
            return [];
        }
    }

    public function getRecentActivity()
    {
        // If you don't have activity_log table, return empty array
        try {
            $query = "SELECT action, details, created_at FROM activity_log ORDER BY created_at DESC LIMIT 5";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
