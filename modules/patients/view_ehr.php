<?php
require_once '../../config/db.php';
require_once '../../includes/functions.php';

check_role(['admin', 'doctor']);

$patient_id = $_GET['id'] ?? null;
$patient = null;
$appointments = [];
$prescriptions = [];
$lab_reports = [];
$bills = [];

if ($patient_id) {
    // 1. Fetch Patient Info
    $stmt = $pdo->prepare("SELECT * FROM patients WHERE id = ?");
    $stmt->execute([$patient_id]);
    $patient = $stmt->fetch();

    if ($patient) {
        // 2. Fetch Appointments
        $stmt = $pdo->prepare("SELECT a.*, d.name as doctor_name FROM appointments a JOIN doctors d ON a.doctor_id = d.id WHERE a.patient_id = ? ORDER BY a.appointment_date DESC");
        $stmt->execute([$patient_id]);
        $appointments = $stmt->fetchAll();

        // 3. Fetch Prescriptions
        $stmt = $pdo->prepare("SELECT p.*, d.name as doctor_name FROM prescriptions p JOIN doctors d ON p.doctor_id = d.id WHERE p.patient_id = ? ORDER BY p.prescribed_at DESC");
        $stmt->execute([$patient_id]);
        $prescriptions = $stmt->fetchAll();

        // 4. Fetch Lab Reports
        $stmt = $pdo->prepare("SELECT l.*, d.name as doctor_name FROM lab_reports l JOIN doctors d ON l.doctor_id = d.id WHERE l.patient_id = ? ORDER BY l.reported_at DESC");
        $stmt->execute([$patient_id]);
        $lab_reports = $stmt->fetchAll();

        // 5. Fetch Bills
        $stmt = $pdo->prepare("SELECT * FROM bills WHERE patient_id = ? ORDER BY created_at DESC");
        $stmt->execute([$patient_id]);
        $bills = $stmt->fetchAll();
    }
}

// If no patient found or no ID, fetch list for the index view
if (!$patient) {
    $stmt = $pdo->query("SELECT * FROM patients ORDER BY created_at DESC");
    $patients = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $patient ? htmlspecialchars($patient['name']) . ' - EHR' : 'EHR Records - HMS'; ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .ehr-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem; background: white; padding: 2rem; border-radius: 15px; box-shadow: var(--card-shadow); }
        .patient-meta { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-top: 1rem; color: var(--text-muted); }
        .section-card { background: white; border-radius: 15px; padding: 1.5rem; margin-bottom: 2rem; box-shadow: var(--card-shadow); }
        .section-title { font-size: 1.25rem; font-weight: 700; color: var(--primary-color); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.75rem; }
        .record-item { padding: 1rem; border-radius: 10px; background: #f8fafc; margin-bottom: 1rem; border-left: 4px solid var(--secondary-color); }
        .record-meta { font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.5rem; }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <aside class="sidebar">
            <div class="brand-section">
                <i class="fas fa-hospital-alt"></i>
                <span class="brand-name">HMS EHR</span>
            </div>
            <nav>
                <ul class="nav-links">
                    <li><a href="../../dashboard.php"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
                    <li><a href="search.php"><i class="fas fa-search"></i> <span>Patient Search</span></a></li>
                    <li><a href="view_ehr.php" class="active"><i class="fas fa-file-medical"></i> <span>EHR Records</span></a></li>
                    <li style="margin-top: auto;"><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <?php if ($patient): ?>
                <!-- Detailed EHR View -->
                <header class="header" style="margin-bottom: 1rem;">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <a href="view_ehr.php" class="btn" style="width: auto; background: #94a3b8; padding: 0.5rem 1rem;"><i class="fas fa-arrow-left"></i></a>
                        <h1>Electronic Health Record</h1>
                    </div>
                </header>

                <div class="ehr-header">
                    <div style="flex: 1;">
                        <h2 style="font-size: 2rem; font-family: 'Outfit'; color: var(--primary-color);"><?php echo htmlspecialchars($patient['name']); ?></h2>
                        <div class="patient-meta">
                            <span><i class="fas fa-id-card"></i> ID: #<?php echo $patient['id']; ?></span>
                            <span><i class="fas fa-birthday-cake"></i> DOB: <?php echo $patient['dob']; ?></span>
                            <span><i class="fas fa-venus-mars"></i> Gender: <?php echo ucfirst($patient['gender']); ?></span>
                            <span><i class="fas fa-phone"></i> <?php echo htmlspecialchars($patient['contact']); ?></span>
                            <span style="grid-column: span 2;"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($patient['address']); ?></span>
                        </div>
                    </div>
                    <div class="user-badge" style="background: var(--bg-primary); border: 1px solid var(--accent-secondary);">
                        <i class="fas fa-heartbeat" style="color: var(--accent-color);"></i> Active Patient
                    </div>
                </div>

                <div class="card-grid">
                    <!-- Appointments -->
                    <div class="section-card">
                        <div class="section-title"><i class="fas fa-calendar-check"></i> Appointments</div>
                        <?php foreach ($appointments as $app): ?>
                            <div class="record-item">
                                <div class="record-meta"><?php echo date('M d, Y - H:i', strtotime($app['appointment_date'])); ?></div>
                                <div style="font-weight: 600;">Dr. <?php echo htmlspecialchars($app['doctor_name']); ?></div>
                                <div style="font-size: 0.9rem; margin-top: 0.25rem;"><?php echo htmlspecialchars($app['notes'] ?: 'No notes provided.'); ?></div>
                                <div class="status-badge <?php echo 'status-'.$app['status']; ?>" style="margin-top: 0.5rem;"><?php echo ucfirst($app['status']); ?></div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($appointments)) echo '<p class="text-muted">No appointment history.</p>'; ?>
                    </div>

                    <!-- Prescriptions -->
                    <div class="section-card">
                        <div class="section-title"><i class="fas fa-prescription"></i> Prescriptions</div>
                        <?php foreach ($prescriptions as $presc): ?>
                            <div class="record-item" style="border-left-color: #10b981;">
                                <div class="record-meta"><?php echo date('M d, Y', strtotime($presc['prescribed_at'])); ?></div>
                                <div style="font-weight: 600;">Dr. <?php echo htmlspecialchars($presc['doctor_name']); ?></div>
                                <div style="margin-top: 0.5rem; padding: 0.5rem; background: white; border-radius: 6px; font-size: 0.9rem;">
                                    <strong>Meds:</strong> <?php echo nl2br(htmlspecialchars($presc['medications'])); ?><br>
                                    <strong>Inst:</strong> <?php echo nl2br(htmlspecialchars($presc['instructions'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($prescriptions)) echo '<p class="text-muted">No prescriptions found.</p>'; ?>
                    </div>
                </div>

                <div class="card-grid" style="grid-template-columns: 2fr 1fr;">
                    <!-- Lab Reports -->
                    <div class="section-card">
                        <div class="section-title"><i class="fas fa-vial"></i> Lab Reports</div>
                        <?php foreach ($lab_reports as $lab): ?>
                            <div class="record-item" style="border-left-color: var(--accent-color);">
                                <div class="record-meta"><?php echo date('M d, Y', strtotime($lab['reported_at'])); ?></div>
                                <div style="font-weight: 600;"><?php echo htmlspecialchars($lab['test_name']); ?></div>
                                <div style="font-size: 0.9rem; margin: 0.5rem 0;"><?php echo nl2br(htmlspecialchars($lab['results'])); ?></div>
                                <div class="status-badge" style="background: <?php echo $lab['status'] == 'ready' ? '#dcfce7' : '#fef9c3'; ?>; color: <?php echo $lab['status'] == 'ready' ? '#166534' : '#854d0e'; ?>;">
                                    <?php echo ucfirst($lab['status']); ?>
                                </div>
                                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.5rem;">Ordered by: Dr. <?php echo htmlspecialchars($lab['doctor_name']); ?></div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($lab_reports)) echo '<p class="text-muted">No lab reports found.</p>'; ?>
                    </div>

                    <!-- Billing -->
                    <div class="section-card">
                        <div class="section-title"><i class="fas fa-file-invoice-dollar"></i> Billing</div>
                        <?php foreach ($bills as $bill): ?>
                            <div class="record-item" style="border-left-color: <?php echo $bill['status'] == 'paid' ? '#10b981' : '#ef4444'; ?>;">
                                <div class="record-meta">INV-<?php echo str_pad($bill['id'], 5, '0', STR_PAD_LEFT); ?> | <?php echo date('M d, Y', strtotime($bill['created_at'])); ?></div>
                                <div style="font-size: 1.25rem; font-weight: 700;">$<?php echo number_format($bill['amount'], 2); ?></div>
                                <div class="status-badge" style="margin-top: 0.5rem; background: <?php echo $bill['status'] == 'paid' ? '#dcfce7' : '#fee2e2'; ?>; color: <?php echo $bill['status'] == 'paid' ? '#166534' : '#991b1b'; ?>;">
                                    <?php echo ucfirst($bill['status']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($bills)) echo '<p class="text-muted">No billing history.</p>'; ?>
                    </div>
                </div>

            <?php else: ?>
                <!-- List View (Current Behavior) -->
                <header class="header">
                    <h1>Patient EHR Records</h1>
                </header>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Patient Name</th>
                                <th>Contact</th>
                                <th>Health Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($patients as $p): ?>
                            <tr>
                                <td>#<?php echo $p['id']; ?></td>
                                <td style="font-weight: 600;"><?php echo htmlspecialchars($p['name']); ?></td>
                                <td><?php echo htmlspecialchars($p['contact']); ?></td>
                                <td><span class="status-badge status-completed">Stable</span></td>
                                <td>
                                    <a href="view_ehr.php?id=<?php echo $p['id']; ?>" class="btn" style="width: auto; padding: 0.5rem 1rem;">View Full EHR</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
