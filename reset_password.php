<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

$success = '';
$error = '';
$valid_token = false;
$token = $_GET['token'] ?? '';

if (!empty($token)) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        $valid_token = true;

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reset_password'])) {
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];

            if (!empty($password) && !empty($confirm_password)) {
                if ($password === $confirm_password) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
                    $stmt->execute([$hashed_password, $user['id']]);

                    $success = "Password reset successfully! You can now <a href='index.php'>login</a>.";
                    $valid_token = false; // Hide form after success
                } else {
                    $error = "Passwords do not match.";
                }
            } else {
                $error = "Please fill in all fields.";
            }
        }
    } else {
        $error = "Invalid or expired reset token.";
    }
} else {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - HMS</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="login-container">
    <div class="login-card">
        <h2>Reset Password</h2>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($valid_token): ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password" required minlength="6">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" name="reset_password" class="btn">Update Password</button>
            </form>
        <?php endif; ?>
        
        <p style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem;">
            <a href="index.php" style="color: var(--secondary-color); font-weight: 600; text-decoration: none;">Back to Login</a>
        </p>
    </div>
</body>
</html>
