<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

$success = '';
$error = '';
$simulated_link = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reset_request'])) {
    $email = trim($_POST['email']);

    if (!empty($email)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
            $stmt->execute([$token, $expiry, $user['id']]);

            $success = "Password reset link has been generated.";
            
            // In a real app, you'd send this via email.
            // For this demo, we'll display it on screen.
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $host = $_SERVER['HTTP_HOST'];
            $baseDir = dirname($_SERVER['PHP_SELF']);
            $simulated_link = "$protocol://$host$baseDir/reset_password.php?token=$token";
        } else {
            $error = "No account found with that email address.";
        }
    } else {
        $error = "Please enter your email address.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - HMS</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="login-container">
    <div class="login-card">
        <h2>Forgot Password</h2>
        <p style="color: var(--text-muted); margin-bottom: 1.5rem; text-align: center;">Enter your email to receive a password reset link.</p>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
                <?php if ($simulated_link): ?>
                    <div style="margin-top: 1rem; padding: 1rem; background: #f8f9fa; border: 1px dashed #28a745; border-radius: 4px; word-break: break-all;">
                        <strong>Simulated Email Content:</strong><br>
                        Click the link below to reset your password:<br>
                        <a href="<?php echo $simulated_link; ?>"><?php echo $simulated_link; ?></a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required placeholder="john@example.com">
            </div>
            <button type="submit" name="reset_request" class="btn">Send Reset Link</button>
            <p style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem;">
                Remembered your password? <a href="index.php" style="color: var(--secondary-color); font-weight: 600; text-decoration: none;">Back to Login</a>
            </p>
        </form>
    </div>
</body>
</html>
