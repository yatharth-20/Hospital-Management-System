<?php
require_once '../../config/db.php';
require_once '../../includes/functions.php';

check_role(['admin', 'laboratory', 'doctor']);

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_report'])) {
    $patient_id = $_POST['patient_id'];
    $doctor_id = $_POST['doctor_id'];
    $test_name = trim($_POST['test_name']);
    $results = trim($_POST['results']);

    if (!empty($patient_id) && !empty($doctor_id) && !empty($test_name)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO lab_reports (patient_id, doctor_id, test_name, results, status) VALUES (?, ?, ?, ?, 'ready')");
            $stmt->execute([$patient_id, $doctor_id, $test_name, $results]);
            $success = "Lab report submitted successfully!";
        } catch(PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}

$patients = $pdo->query("SELECT id, name FROM patients")->fetchAll();
$doctors = $pdo->query("SELECT id, name FROM doctors")->fetchAll();
$reports = $pdo->query("SELECT l.*, p.name as patient_name, d.name as doctor_name 
                        FROM lab_reports l 
                        JOIN patients p ON l.patient_id = p.id 
                        JOIN doctors d ON l.doctor_id = d.id 
                        ORDER BY reported_at DESC LIMIT 15")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lab Reports - HMS</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-layout">
        <aside class="sidebar">
            <div class="brand-section">
                <i class="fas fa-microscope"></i>
                <span class="brand-name">HMS LAB</span>
            </div>
            <nav>
                <ul class="nav-links">
                    <li><a href="../../dashboard.php"><i class="fas fa-columns"></i> <span>Overview</span></a></li>
                    <li><a href="reports.php" class="active"><i class="fas fa-file-medical-alt"></i> <span>Reports</span></a></li>
                    <li><a href="../../modules/patients/search.php"><i class="fas fa-users"></i> <span>Patients</span></a></li>
                    <li style="margin-top: auto;"><a href="../../logout.php"><i class="fas fa-power-off"></i> <span>Logout</span></a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="header">
                <h1>Laboratory Management</h1>
                <div class="user-info">
                    <i class="fas fa-user-md"></i>
                    <span>Lab Technician</span>
                </div>
            </header>

            <?php if ($success) echo '<div class="alert alert-success"><i class="fas fa-check-circle"></i> '.$success.'</div>'; ?>
            <?php if ($error) echo '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> '.$error.'</div>'; ?>

            <div class="card-grid" style="grid-template-columns: 1fr 2fr;">
                <!-- Submit Form -->
                <div class="table-container" style="padding: 1.5rem;">
                    <h2 style="margin-bottom: 1.5rem; font-size: 1.25rem;"><i class="fas fa-plus-circle"></i> New Report</h2>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label>Select Patient</label>
                            <select name="patient_id" required style="width: 100%; padding: 0.8rem; border-radius: 8px; border: 1px solid #ddd; background: #f8fafc;">
                                <option value="">-- Choose --</option>
                                <?php foreach ($patients as $p): ?>
                                    <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['name']); ?> (ID: <?php echo $p['id']; ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Requesting Doctor</label>
                            <select name="doctor_id" required style="width: 100%; padding: 0.8rem; border-radius: 8px; border: 1px solid #ddd; background: #f8fafc;">
                                <option value="">-- Choose --</option>
                                <?php foreach ($doctors as $d): ?>
                                    <option value="<?php echo $d['id']; ?>">Dr. <?php echo htmlspecialchars($d['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Test Name</label>
                            <input type="text" name="test_name" required placeholder="e.g. Complete Blood Count">
                        </div>
                        <div class="form-group">
                            <label>Clinical Results</label>
                            <textarea name="results" required style="width:100%; min-height: 100px; padding:0.8rem; border-radius: 8px; border: 1px solid #ddd; font-family: inherit;"></textarea>
                        </div>
                        <button type="submit" name="submit_report" class="btn" style="background: var(--primary-color);">
                            <i class="fas fa-paper-plane"></i> Submit Report
                        </button>
                    </form>
                </div>

                <!-- Recent Reports Table -->
                <div class="table-container">
                    <div style="padding: 1.5rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                        <h2 style="font-size: 1.25rem;"><i class="fas fa-history"></i> Recent Lab Results</h2>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Report Date</th>
                                <th>Patient Name</th>
                                <th>Test Performed</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reports as $report): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($report['reported_at'])); ?></td>
                                <td style="font-weight: 600;"><?php echo htmlspecialchars($report['patient_name']); ?></td>
                                <td><span style="color: var(--secondary-color); font-weight: 500;"><?php echo htmlspecialchars($report['test_name']); ?></span></td>
                                <td>
                                    <span class="status-badge" style="background: <?php echo $report['status'] == 'ready' ? '#dcfce7' : '#fef9c3'; ?>; color: <?php echo $report['status'] == 'ready' ? '#166534' : '#854d0e'; ?>;">
                                        <?php echo ucfirst($report['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="view_report.php?id=<?php echo $report['id']; ?>" class="btn" style="width: auto; padding: 0.4rem 1rem; font-size: 0.85rem;">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
