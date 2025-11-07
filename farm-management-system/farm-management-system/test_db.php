<?php
require_once __DIR__ . '/database/db_connect.php';

if ($conn) {
    echo "✅ Database connection successful!";
} else {
    echo "❌ Database connection failed!";
}
?>