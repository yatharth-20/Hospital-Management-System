<?php
require_once '../../config/db.php';
require_once '../../includes/functions.php';

check_role(['admin', 'doctor', 'receptionist']);

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// If doctor, only show their appointments
if ($role == 'doctor') {
    $stmt = $pdo->prepare("SELECT a.*, p.name as patient_name, d.name as doctor_name 
                           FROM appointments a 
                           JOIN patients p ON a.patient_id = p.id 
                           JOIN doctors d ON a.doctor_id = d.id
                           WHERE d.user_id = ? 
                           ORDER BY a.appointment_date ASC");
    $stmt->execute([$user_id]);
} else {
    $stmt = $pdo->query("SELECT a.*, p.name as patient_name, d.name as doctor_name 
                         FROM appointments a 
                         JOIN patients p ON a.patient_id = p.id 
                         JOIN doctors d ON a.doctor_id = d.id
                         ORDER BY a.appointment_date ASC");
}

$appointments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Schedule - HMS</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-layout">
        <aside class="sidebar">
            <h2>HMS Admin</h2>
            <nav>
                <ul class="nav-links">
                    <li><a href="../../dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="view_calendar.php" class="active"><i class="fas fa-calendar-alt"></i> Schedule</a></li>
                    <li><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="header">
                <h1>Appointment Schedule</h1>
            </header>

            <div class="stat-card">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8f9fa;">
                            <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #dee2e6;">Date & Time</th>
                            <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #dee2e6;">Patient</th>
                            <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #dee2e6;">Doctor</th>
                            <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #dee2e6;">Status</th>
                            <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #dee2e6;">Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $app): ?>
                        <tr>
                            <td style="padding: 1rem; border-bottom: 1px solid #dee2e6;"><?php echo date('M d, Y h:i A', strtotime($app['appointment_date'])); ?></td>
                            <td style="padding: 1rem; border-bottom: 1px solid #dee2e6;"><?php echo htmlspecialchars($app['patient_name']); ?></td>
                            <td style="padding: 1rem; border-bottom: 1px solid #dee2e6;"><?php echo htmlspecialchars($app['doctor_name']); ?></td>
                            <td style="padding: 1rem; border-bottom: 1px solid #dee2e6;">
                                <span class="badge" style="padding: 5px 10px; border-radius: 20px; font-size: 0.8rem; background: #eee;">
                                    <?php echo ucfirst($app['status']); ?>
                                </span>
                            </td>
                            <td style="padding: 1rem; border-bottom: 1px solid #dee2e6;"><?php echo htmlspecialchars($app['notes']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($appointments)): ?>
                            <tr><td colspan="5" style="padding: 2rem; text-align: center;">No appointments found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
