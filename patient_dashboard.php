<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

check_role(['patient']);

$patient_id = $_SESSION['patient_id'];
$username = $_SESSION['username'];

if (!$patient_id) {
    die("Error: This account is not linked to a patient record. Please contact administration.");
}

// Fetch Patient Data
$stmt = $pdo->prepare("SELECT * FROM patients WHERE id = ?");
$stmt->execute([$patient_id]);
$patient = $stmt->fetch();

// Fetch Appointments
$stmt = $pdo->prepare("SELECT a.*, d.name as doctor_name 
                       FROM appointments a 
                       JOIN doctors d ON a.doctor_id = d.id 
                       WHERE a.patient_id = ? 
                       ORDER BY a.appointment_date DESC");
$stmt->execute([$patient_id]);
$appointments = $stmt->fetchAll();

// Fetch Prescriptions
$stmt = $pdo->prepare("SELECT pr.*, d.name as doctor_name 
                       FROM prescriptions pr 
                       JOIN doctors d ON pr.doctor_id = d.id 
                       WHERE pr.patient_id = ? 
                       ORDER BY prescribed_at DESC");
$stmt->execute([$patient_id]);
$prescriptions = $stmt->fetchAll();

// Fetch Bills
$stmt = $pdo->prepare("SELECT * FROM bills WHERE patient_id = ? ORDER BY created_at DESC");
$stmt->execute([$patient_id]);
$bills = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Patient Portal - HMS</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-layout">
        <aside class="sidebar">
            <div class="brand-section">
                <i class="fas fa-heartbeat"></i>
                <span class="brand-name">HMS PATIENT</span>
            </div>
            <nav>
                <ul class="nav-links">
                    <li><a href="patient_dashboard.php" class="active"><i class="fas fa-user-circle"></i> <span>My Health Dashboard</span></a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="header">
                <h1>Welcome, <?php echo htmlspecialchars($patient['name']); ?></h1>
                <div class="user-info">
                    <i class="fas fa-user"></i>
                    <span>Patient Profile</span>
                </div>
            </header>

            <div class="card-grid">
                <div class="stat-card">
                    <h3>Upcoming Appointments</h3>
                    <div class="value"><?php echo count(array_filter($appointments, function($a) { return $a['status'] == 'pending'; })); ?></div>
                    <div class="trend trend-up"><i class="fas fa-calendar-check"></i> Scheduled</div>
                </div>

                <div class="stat-card">
                    <h3>Outstanding Balance</h3>
                    <?php 
                    $pending = 0;
                    foreach ($bills as $b) if ($b['status'] == 'unpaid') $pending += $b['amount'];
                    ?>
                    <div class="value">$<?php echo number_format($pending, 2); ?></div>
                    <div class="trend trend-down" style="<?php echo $pending > 0 ? 'color: var(--danger-color);' : ''; ?>">
                        <i class="fas fa-file-invoice-dollar"></i> <?php echo $pending > 0 ? 'Payment Due' : 'Paid in Full'; ?>
                    </div>
                </div>

                <div class="stat-card">
                    <h3>Recent Record</h3>
                    <div class="value">STABLE</div>
                    <div class="trend trend-up"><i class="fas fa-shield-alt"></i> Verified</div>
                </div>
            </div>

            <div class="table-container" style="margin-top: 2rem;">
                <div style="padding: 1.5rem; border-bottom: 1px solid #e2e8f0;">
                    <h2 style="font-size: 1.25rem;">My Prescriptions</h2>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Doctor</th>
                            <th>Medications</th>
                            <th>Instructions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($prescriptions as $pr): ?>
                        <tr>
                            <td><?php echo date('M d, Y', strtotime($pr['prescribed_at'])); ?></td>
                            <td><strong><?php echo htmlspecialchars($pr['doctor_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($pr['medications']); ?></td>
                            <td><?php echo htmlspecialchars($pr['instructions']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($prescriptions)): ?>
                        <tr>
                            <td colspan="4" style="text-align:center; padding: 2rem; color: var(--text-muted);">No prescriptions found.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
