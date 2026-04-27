<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['patient_id'] = $user['patient_id'] ?? null;
            switch ($_SESSION['role']) {
                case 'patient':
                    header("Location: patient_dashboard.php");
                    break;
                case 'doctor':
                    header("Location: doctor_dashboard.php");
                    break;
                case 'test':
                    header("Location: test_dashboard.php");
                    break;
                default:
                    header("Location: dashboard.php");
                    break;
            }
            exit();
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Please enter both username and password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - HMS</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-container">
    <div class="login-card">
        <h2>Hospital Management System</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" name="login" class="btn"><i class="fas fa-sign-in-alt"></i> Login</button>
            <p style="text-align: center; margin-top: 1.5rem; color: var(--text-muted); font-size: 0.9rem;">
                New to HMS? <a href="register.php" style="color: var(--secondary-color); font-weight: 600; text-decoration: none;">Create an account</a>
            </p>
        </form>
    </div>
</body>
</html>
