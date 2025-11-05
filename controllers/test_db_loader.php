<?php
// quick tester to validate Database.php can be loaded and Database class exists
echo "Testing Database loader...\n\n";

$paths = [
    __DIR__ . '/../database/Database.php',
    __DIR__ . '/../../database/Database.php',
    __DIR__ . '/../config/Database.php',
    __DIR__ . '/../../config/Database.php'
];

foreach ($paths as $p) {
    echo "Checking: $p ... ";
    if (file_exists($p)) {
        echo "FOUND\n";
    } else {
        echo "NOT FOUND\n";
    }
}

echo "\nAttempting require_once on first existing path...\n";
$found = false;
foreach ($paths as $p) {
    if (file_exists($p)) {
        require_once $p;
        echo "Included: $p\n";
        $found = true;
        break;
    }
}
if (!$found) {
    echo "No Database.php found in candidates. Please check file location.\n";
    exit;
}

echo "\nChecking class existence...\n";
if (class_exists('Database')) {
    echo "Class Database exists ✅\n";
    try {
        $db = new Database();
        $conn = $db->getConnection();
        if ($conn) {
            echo "Database->getConnection() succeeded ✅\n";
        } else {
            echo "getConnection() returned falsy value.\n";
        }
    } catch (Exception $e) {
        echo "getConnection() failed: " . $e->getMessage() . "\n";
    }
} else {
    echo "Class Database does NOT exist. Open Database.php and confirm it defines 'class Database'.\n";
}
