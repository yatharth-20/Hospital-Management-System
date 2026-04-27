<?php
require_once 'config/db.php';

echo "<h2>Login Diagnostic Tool</h2>";

try {
    // 1. Check if user exists
    $username = 'admin';
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user) {
        echo "<p>[PASS] User 'admin' found in database.</p>";
        echo "<p>Stored Hash: " . $user['password'] . "</p>";

        // 2. Test password_verify
        $test_pass = 'admin123';
        if (password_verify($test_pass, $user['password'])) {
            echo "<p style='color:green;'>[PASS] Password 'admin123' matches the stored hash!</p>";
            echo "<p>If you still see 'Invalid username' in index.php, check if sessions are working.</p>";
        } else {
            echo "<p style='color:red;'>[FAIL] Password 'admin123' does NOT match the stored hash.</p>";
            echo "<p>Please run setup_admin.php again to reset the password.</p>";
        }
    } else {
        echo "<p style='color:red;'>[FAIL] User 'admin' NOT found in 'users' table.</p>";
        echo "<p>Please import database.sql or run setup_admin.php.</p>";
    }

} catch (PDOException $e) {
    echo "<p style='color:red;'>[ERROR] Database error: " . $e->getMessage() . "</p>";
}
?>
