<?php
require_once '../../config/db.php';
require_once '../../includes/functions.php';

check_role(['admin', 'receptionist']);

$success = '';
$error = '';

// Fetch patients and doctors for the form
$patients = $pdo->query("SELECT id, name FROM patients")->fetchAll();
$doctors = $pdo->query("SELECT id, name FROM doctors")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['book'])) {
    $patient_id = $_POST['patient_id'];
    $doctor_id = $_POST['doctor_id'];
    $appointment_date = $_POST['appointment_date'];
    $notes = trim($_POST['notes']);

    if (!empty($patient_id) && !empty($doctor_id) && !empty($appointment_date)) {
        // Clash detection: Check if doctor has another appointment within 30 mins
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments 
                               WHERE doctor_id = ? 
                               AND ABS(TIMESTAMPDIFF(MINUTE, appointment_date, ?)) < 30
                               AND status != 'cancelled'");
        $stmt->execute([$doctor_id, $appointment_date]);
        $clash = $stmt->fetchColumn();

        if ($clash > 0) {
            $error = "Clash detected! This doctor has another appointment scheduled near this time.";
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, notes) VALUES (?, ?, ?, ?)");
                $stmt->execute([$patient_id, $doctor_id, $appointment_date, $notes]);
                $success = "Appointment booked successfully!";
            } catch(PDOException $e) {
                $error = "Error: " . $e->getMessage();
            }
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book Appointment - HMS</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="dashboard-layout">
        <aside class="sidebar">
            <h2>HMS Admin</h2>
            <nav>
                <ul class="nav-links">
                    <li><a href="../../dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="book.php" class="active"><i class="fas fa-calendar-check"></i> Appointments</a></li>
                    <li><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="header">
                <h1>Book Appointment</h1>
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
                        <label>Select Doctor *</label>
                        <select name="doctor_id" required>
                            <option value="">-- Select Doctor --</option>
                            <?php foreach ($doctors as $d): ?>
                                <option value="<?php echo $d['id']; ?>"><?php echo htmlspecialchars($d['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Appointment Date & Time *</label>
                        <input type="datetime-local" name="appointment_date" required>
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea name="notes" style="width:100%; padding:0.8rem; border:1px solid #ddd; border-radius:6px;"></textarea>
                    </div>
                    <button type="submit" name="book" class="btn">Book Appointment</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
