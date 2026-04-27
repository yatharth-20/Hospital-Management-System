<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

check_login();

$role = $_SESSION['role'];

if ($role == 'patient') {
    header("Location: patient_dashboard.php");
    exit();
} elseif ($role == 'doctor') {
    header("Location: doctor_dashboard.php");
    exit();
} elseif ($role == 'test') {
    header("Location: test_dashboard.php");
    exit();
}

$username = $_SESSION['username'];

// Mock stats for demonstration
$stats = [
    'patients' => 0,
    'appointments' => 0,
    'doctors' => 0,
    'pending_bills' => 0
];

if ($role == 'admin') {
    $stats['patients'] = $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();
    $stats['appointments'] = $pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn();
    $stats['doctors'] = $pdo->query("SELECT COUNT(*) FROM doctors")->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - HMS</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-layout">
        <aside class="sidebar">
            <div class="brand-section">
                <i class="fas fa-hospital-alt"></i>
                <span class="brand-name">HMS ADMIN</span>
            </div>
            <nav>
                <ul class="nav-links">
                    <li><a href="dashboard.php" class="active"><i class="fas fa-columns"></i> <span>Overview</span></a></li>
                    
                    <?php if (in_array($role, ['admin', 'receptionist'])): ?>
                        <li><a href="modules/patients/register.php"><i class="fas fa-users-cog"></i> <span>Patient Mgmt</span></a></li>
                        <li><a href="modules/scheduling/book.php"><i class="fas fa-calendar-alt"></i> <span>Scheduling</span></a></li>
                    <?php endif; ?>

                    <?php if (in_array($role, ['admin', 'laboratory'])): ?>
                        <li><a href="modules/laboratory/reports.php"><i class="fas fa-file-prescription"></i> <span>Lab Works</span></a></li>
                    <?php endif; ?>

                    <?php if (in_array($role, ['admin', 'pharmacy'])): ?>
                        <li><a href="modules/pharmacy/inventory.php"><i class="fas fa-box-open"></i> <span>Pharmacy</span></a></li>
                    <?php endif; ?>

                    <?php if (in_array($role, ['admin', 'receptionist'])): ?>
                        <li><a href="modules/billing/generate.php"><i class="fas fa-receipt"></i> <span>Billing</span></a></li>
                    <?php endif; ?>

                    <li style="margin-top: auto;"><a href="logout.php"><i class="fas fa-power-off"></i> <span>Logout</span></a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="header">
                <h1>System Overview</h1>
                <div class="user-info">
                    <i class="fas fa-user-shield"></i>
                    <span><?php echo htmlspecialchars($username); ?> (<?php echo ucfirst($role); ?>)</span>
                </div>
            </header>

            <div class="card-grid">
                <div class="stat-card">
                    <h3>Total Registered Patients</h3>
                    <div class="value"><?php echo $stats['patients']; ?></div>
                    <div class="trend trend-up"><i class="fas fa-user-plus"></i> Patient Base</div>
                </div>
                <div class="stat-card">
                    <h3>Today's Appointments</h3>
                    <div class="value"><?php echo $stats['appointments']; ?></div>
                    <div class="trend trend-up"><i class="fas fa-calendar-check"></i> Consultations</div>
                </div>
                <div class="stat-card">
                    <h3>Active Medical Staff</h3>
                    <div class="value"><?php echo $stats['doctors']; ?></div>
                    <div class="trend trend-up"><i class="fas fa-user-md"></i> Doctors</div>
                </div>
            </div>

            <div class="table-container">
                <div style="padding: 1.5rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                    <h2 style="font-size: 1.25rem;">Quick Actions</h2>
                </div>
                <div style="padding: 2rem; display: flex; gap: 1rem;">
                    <?php if ($role == 'receptionist' || $role == 'admin'): ?>
                        <a href="modules/patients/register.php" class="btn" style="width: auto;">Register New Patient</a>
                        <a href="modules/scheduling/book.php" class="btn" style="width: auto; background: var(--accent-color);">Book Appointment</a>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
