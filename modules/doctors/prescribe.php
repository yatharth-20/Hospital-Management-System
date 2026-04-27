<?php
require_once '../../config/db.php';
require_once '../../includes/functions.php';

check_role(['admin', 'doctor']);

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['prescribe'])) {
    $patient_id = $_POST['patient_id'];
    $appointment_id = $_POST['appointment_id'];
    $medications = trim($_POST['medications']);
    $instructions = trim($_POST['instructions']);
    $doctor_id = $_SESSION['user_id']; // This would ideally be the doctor_id from doctors table

    if (!empty($patient_id) && !empty($medications)) {
        try {
            // Get actual doctor_id from doctors table
            $stmt = $pdo->prepare("SELECT id FROM doctors WHERE user_id = ?");
            $stmt->execute([$doctor_id]);
            $actual_doctor_id = $stmt->fetchColumn();

            if ($actual_doctor_id) {
                $stmt = $pdo->prepare("INSERT INTO prescriptions (appointment_id, doctor_id, patient_id, medications, instructions) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$appointment_id ?: null, $actual_doctor_id, $patient_id, $medications, $instructions]);
                $success = "Prescription issued successfully!";
            } else {
                $error = "Doctor record not found for this user.";
            }
        } catch(PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}

$patients = $pdo->query("SELECT id, name FROM patients")->fetchAll();
$appointments = $pdo->query("SELECT a.id, p.name as patient_name, a.appointment_date 
                             FROM appointments a 
                             JOIN patients p ON a.patient_id = p.id 
                             WHERE a.status != 'cancelled'")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Issue Prescription - HMS</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="dashboard-layout">
        <aside class="sidebar">
            <h2>HMS Admin</h2>
            <nav>
                <ul class="nav-links">
                    <li><a href="../../dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="prescribe.php" class="active"><i class="fas fa-prescription"></i> Prescribe</a></li>
                    <li><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="header">
                <h1>Issue Prescription</h1>
            </header>

            <?php if ($success) echo '<div class="alert alert-success">'.$success.'</div>'; ?>
            <?php if ($error) echo '<div class="alert alert-danger">'.$error.'</div>'; ?>

            <div class="stat-card" style="max-width: 600px; margin: 0 auto;">
                <form method="POST" action="">
                    <div class="form-group">
                        <label>Select Patient *</label>
                        <select name="patient_id" required>
                            <option value="">-- Select Patient --</option>
                            <?php foreach ($patients as $p): ?>
                                <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Related Appointment (Optional)</label>
                        <select name="appointment_id">
                            <option value="">-- None --</option>
                            <?php foreach ($appointments as $a): ?>
                                <option value="<?php echo $a['id']; ?>">#<?php echo $a['id']; ?> - <?php echo $a['patient_name']; ?> (<?php echo $a['appointment_date']; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Medications *</label>
                        <textarea name="medications" required style="width:100%; height: 100px; padding:0.8rem; border:1px solid #ddd; border-radius:6px;"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Instructions</label>
                        <textarea name="instructions" style="width:100%; padding:0.8rem; border:1px solid #ddd; border-radius:6px;"></textarea>
                    </div>
                    <button type="submit" name="prescribe" class="btn">Issue Prescription</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
