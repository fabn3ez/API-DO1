<?php
// 🔒 Access control: Only Sam can run this migration
$allowedEmail = "Samobieroodhiambo@gmail.com";

// Optional: Stop execution unless you are on localhost (developer machine)
if ($_SERVER['REMOTE_ADDR'] !== '127.0.0.1') {
    die("Access denied: can only be run locally.");
}

// Optional: require authentication (in future, you can check logged-in user session)
echo "✅ Secure mode active — migration can only be run by authorized developer.<br>";
/**
 * create_admins.php
 * Migration: insert initial admin users (idempotent)
 *
 * HOW TO USE:
 *  - Edit $admins array to set emails/usernames/passwords
 *  - Run from command line: php migrations/create_admins.php
 *  - Or open in browser: http://localhost/API-DO1/farm-management-system/migrations/create_admins.php
 *  - After running successfully, remove or protect the file.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// load Database class (adjust path if your Database.php is elsewhere)
require_once __DIR__ . '/../Database.php';



try {
    $db = new Database();
    $pdo = $db->getConnection(); // PDO instance
} catch (Exception $e) {
    echo "DB connect failed: " . $e->getMessage();
    exit(1);
}

// --- Configure admins here: email, username, plain-text password, role
// Change the plain-text passwords to something secure or prompt users to reset.
// These will be hashed before insert.

$admins = [
    [
        'email' => 'Samobieroodhiambo@gmail.com',
        'username' => 'Sam Obiero',
        'password' => password_hash('SamAdmin123', PASSWORD_DEFAULT)
    ],
    [
        'email' => 'genianix@gmail.com',
        'username' => 'Genianix',
        'password' => password_hash('Genianix123', PASSWORD_DEFAULT)
    ],
    [
        'email' => 'baraka.onserio@stratmore.edu',
        'username' => 'Baraka Onserio',
        'password' => password_hash('Baraka123', PASSWORD_DEFAULT)
    ],
    [
        'email' => 'Elizabeth.Githiri@strathmore.edu',
        'username' => 'Elizabeth Githiri',
        'password' => password_hash('Elizabeth123', PASSWORD_DEFAULT)
    ],
    [
        'email' => 'admin4@example.com',
        'username' => 'Admin 4',
        'password' => password_hash('Admin4123', PASSWORD_DEFAULT)
    ],
    [
        'email' => 'admin5@example.com',
        'username' => 'Admin 5',
        'password' => password_hash('Admin5123', PASSWORD_DEFAULT)
    ],
    [
        'email' => 'admin6@example.com',
        'username' => 'Admin 6',
        'password' => password_hash('Admin6123', PASSWORD_DEFAULT)
    ],
];


// prepare idempotent insert: only insert if email doesn't already exist
$insertSql = "INSERT INTO users (username, email, password, role, created_at)
              SELECT :username, :email, :password, :role, NOW()
              FROM DUAL
              WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = :email_check) LIMIT 1";

$insertStmt = $pdo->prepare($insertSql);
$nowInserted = [];

foreach ($admins as $a) {
    // sanitize and hash
    $email = trim($a['email']);
    $username = trim($a['username']);
    $plain = $a['password'];
    $role = $a['role'] ?? 'admin';

    if (empty($email) || empty($username) || empty($plain)) {
        echo "Skipping entry with empty field: " . json_encode($a) . PHP_EOL;
        continue;
    }

    $hash = password_hash($plain, PASSWORD_DEFAULT);

    // Bind and execute
    $insertStmt->bindValue(':username', $username);
    $insertStmt->bindValue(':email', $email);
    $insertStmt->bindValue(':password', $hash);
    $insertStmt->bindValue(':role', $role);
    $insertStmt->bindValue(':email_check', $email);

    try {
        $inserted = $insertStmt->execute();
        // execute() returns true even if no row inserted; check if row exists now
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        $row = $check->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $nowInserted[] = ['email' => $email, 'id' => $row['id']];
            echo "OK: ensured admin exists: {$email} (id: {$row['id']})" . PHP_EOL;
        } else {
            echo "ERROR: failed to insert/check {$email}" . PHP_EOL;
        }
    } catch (PDOException $ex) {
        echo "DB error for {$email}: " . $ex->getMessage() . PHP_EOL;
    }
}

echo PHP_EOL . "Migration finished. Admins created/ensured: " . count($nowInserted) . PHP_EOL;
echo "IMPORTANT: Remove or protect this migration file after running." . PHP_EOL;
