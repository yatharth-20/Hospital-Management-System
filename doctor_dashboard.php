<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

check_role(['doctor']);

$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];

// Get doctor details
$stmt = $pdo->prepare("SELECT * FROM doctors WHERE user_id = ?");
$stmt->execute([$user_id]);
$doctor = $stmt->fetch();

if (!$doctor) {
    die("Error: Doctor profile not found. Please contact administration.");
}

$doctor_id = $doctor['id'];

// Fetch today's appointments
$stmt = $pdo->prepare("SELECT a.*, p.name as patient_name 
                       FROM appointments a 
                       JOIN patients p ON a.patient_id = p.id 
                       WHERE a.doctor_id = ? AND DATE(a.appointment_date) = CURDATE()
                       ORDER BY a.appointment_date ASC");
$stmt->execute([$doctor_id]);
$today_appointments = $stmt->fetchAll();

// Stats
$stats = [
    'today' => count($today_appointments),
    'pending' => $pdo->query("SELECT COUNT(*) FROM appointments WHERE doctor_id = $doctor_id AND status = 'pending'")->fetchColumn(),
    'total_patients' => $pdo->query("SELECT COUNT(DISTINCT patient_id) FROM appointments WHERE doctor_id = $doctor_id")->fetchColumn()
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Doctor Portal - HMS</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-layout">
        <aside class="sidebar">
            <div class="brand-section">
                <i class="fas fa-user-md"></i>
                <span class="brand-name">HMS DOCTOR</span>
            </div>
            <nav>
                <ul class="nav-links">
                    <li><a href="doctor_dashboard.php" class="active"><i class="fas fa-calendar-day"></i> <span>Daily Schedule</span></a></li>
                    <li><a href="modules/doctors/prescribe.php"><i class="fas fa-file-medical"></i> <span>Prescriptions</span></a></li>
                    <li style="margin-top: auto;"><a href="logout.php"><i class="fas fa-power-off"></i> <span>Logout</span></a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="header">
                <h1>Welcome, Dr. <?php echo htmlspecialchars($doctor['name']); ?></h1>
                <div class="user-info">
                    <span><?php echo htmlspecialchars($doctor['specialization']); ?></span>
                </div>
            </header>

            <div class="card-grid">
                <div class="stat-card">
                    <h3>Appointments Today</h3>
                    <div class="value"><?php echo $stats['today']; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Pending Requests</h3>
                    <div class="value"><?php echo $stats['pending']; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Unique Patients</h3>
                    <div class="value"><?php echo $stats['total_patients']; ?></div>
                </div>
            </div>

            <div class="table-container" style="margin-top: 2rem;">
                <div style="padding: 1.5rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between;">
                    <h2 style="font-size: 1.25rem;">Today's Appointments</h2>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Patient Name</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($today_appointments as $app): ?>
                        <tr>
                            <td><?php echo date('h:i A', strtotime($app['appointment_date'])); ?></td>
                            <td><strong><?php echo htmlspecialchars($app['patient_name']); ?></strong></td>
                            <td><span class="status-badge <?php echo $app['status']; ?>"><?php echo ucfirst($app['status']); ?></span></td>
                            <td>
                                <a href="modules/doctors/prescribe.php?patient_id=<?php echo $app['patient_id']; ?>" class="btn-small"><i class="fas fa-edit"></i> Treat</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($today_appointments)): ?>
                        <tr>
                            <td colspan="4" style="text-align:center; padding: 2rem; color: var(--text-muted);">No appointments scheduled for today.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
