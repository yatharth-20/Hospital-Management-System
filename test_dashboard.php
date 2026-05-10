<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

check_role(['test']);

$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Test Dashboard - HMS</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-layout">
        <aside class="sidebar">
            <div class="brand-section">
                <i class="fas fa-vial"></i>
                <span class="brand-name">HMS TEST</span>
            </div>
            <nav>
                <ul class="nav-links">
                    <li><a href="test_dashboard.php" class="active"><i class="fas fa-home"></i> <span>Overview</span></a></li>
                    <li style="margin-top: auto;"><a href="logout.php"><i class="fas fa-power-off"></i> <span>Logout</span></a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="header">
                <h1>Test Account Dashboard</h1>
            </header>

            <div class="card-grid">
                <div class="stat-card">
                    <h3>Current User</h3>
                    <div class="value"><?php echo htmlspecialchars($username); ?></div>
                </div>
                <div class="stat-card">
                    <h3>Account Type</h3>
                    <div class="value">QA / Testing</div>
                </div>
            </div>

            <div style="padding: 2rem; background: white; border-radius: 12px; margin-top: 2rem; box-shadow: var(--card-shadow);">
                <h2>Testing Instructions</h2>
                <p>This is a restricted test account used for quality assurance. Most medical modules are restricted to their respective roles.</p>
                <ul style="margin-top: 1rem; line-height: 1.6;">
                    <li>Use <strong>Admin</strong> for system configuration and user management.</li>
                    <li>Use <strong>Doctor</strong> for appointments and prescriptions.</li>
                    <li>Use <strong>Patient</strong> to view personal health records.</li>
                </ul>
            </div>
        </main>
    </div>
</body>
</html>
