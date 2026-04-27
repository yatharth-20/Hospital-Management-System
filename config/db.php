<?php
// Database Configuration
$runtimePath = __DIR__ . DIRECTORY_SEPARATOR . 'runtime.php';
$runtime = [];
if (is_file($runtimePath)) {
    $loaded = require $runtimePath;
    if (is_array($loaded)) {
        $runtime = $loaded;
    }
}

$db = $runtime['db'] ?? [];

define('DB_HOST', $db['host'] ?? '127.0.0.1');
define('DB_PORT', (int)($db['port'] ?? 3307));
define('DB_USER', $db['user'] ?? 'root');
define('DB_PASS', $db['pass'] ?? '');
define('DB_NAME', $db['name'] ?? 'hms_db');

// Create connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS
    );
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("ERROR: Could not connect to the database. " . $e->getMessage());
}
?>
