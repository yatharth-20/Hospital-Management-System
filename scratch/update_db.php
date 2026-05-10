<?php
require_once 'config/db.php';

try {
    $pdo->exec("ALTER TABLE users ADD COLUMN email VARCHAR(100) UNIQUE NULL AFTER patient_id");
    $pdo->exec("ALTER TABLE users ADD COLUMN reset_token VARCHAR(100) DEFAULT NULL AFTER email");
    $pdo->exec("ALTER TABLE users ADD COLUMN reset_token_expiry DATETIME DEFAULT NULL AFTER reset_token");
    echo "Database updated successfully!";
} catch (PDOException $e) {
    echo "Error updating database: " . $e->getMessage();
}
?>
