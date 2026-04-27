<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Redirect user if not logged in
 */
function check_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php");
        exit();
    }
}

/**
 * Check if user has specific role
 * @param array $allowed_roles
 */
function check_role($allowed_roles) {
    check_login();
    if (!in_array($_SESSION['role'], $allowed_roles)) {
        header("Location: dashboard.php?error=unauthorized");
        exit();
    }
}

/**
 * Success/Error Message display
 */
function display_message() {
    if (isset($_GET['success'])) {
        echo '<div class="alert alert-success">' . htmlspecialchars($_GET['success']) . '</div>';
    }
    if (isset($_GET['error'])) {
        echo '<div class="alert alert-danger">' . htmlspecialchars($_GET['error']) . '</div>';
    }
}
?>
