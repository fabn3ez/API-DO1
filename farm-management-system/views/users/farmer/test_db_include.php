<?php
// Test if db.php is accessible and can be included
if (file_exists('../../config/db.php')) {
    echo 'db.php found<br>';
    try {
        require_once '../../config/db.php';
        echo 'db.php included successfully.';
    } catch (Exception $e) {
        echo 'Error including db.php: ' . $e->getMessage();
    }
} else {
    echo 'db.php NOT found';
}
?>