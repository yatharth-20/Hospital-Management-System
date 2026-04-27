<?php
require_once '../../config/db.php';
require_once '../../includes/functions.php';

check_role(['admin', 'receptionist', 'doctor']);

$success = '';
$error = '';

// Handle Admission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['admit'])) {
    $patient_id = $_POST['patient_id'];
    $room_id = $_POST['room_id'];

    if (!empty($patient_id) && !empty($room_id)) {
        try {
            $pdo->beginTransaction();
            // Create admission record
            $stmt = $pdo->prepare("INSERT INTO admissions (patient_id, room_id) VALUES (?, ?)");
            $stmt->execute([$patient_id, $room_id]);
            // Update room status
            $stmt = $pdo->prepare("UPDATE rooms SET status = 'Occupied' WHERE id = ?");
            $stmt->execute([$room_id]);
            $pdo->commit();
            $success = "Patient admitted successfully!";
        } catch(PDOException $e) {
            $pdo->rollBack();
            $error = "Error: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}

// Handle Discharge
if (isset($_GET['discharge_id']) && isset($_GET['room_id'])) {
    $admission_id = $_GET['discharge_id'];
    $room_id = $_GET['room_id'];
    try {
        $pdo->beginTransaction();
        // Update admission record
        $stmt = $pdo->prepare("UPDATE admissions SET discharged_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$admission_id]);
        // Update room status
        $stmt = $pdo->prepare("UPDATE rooms SET status = 'Available' WHERE id = ?");
        $stmt->execute([$room_id]);
        $pdo->commit();
        $success = "Patient discharged successfully!";
    } catch(PDOException $e) {
        $pdo->rollBack();
        $error = "Error: " . $e->getMessage();
    }
}

$patients = $pdo->query("SELECT id, name FROM patients")->fetchAll();
$available_rooms = $pdo->query("SELECT id, room_number, type FROM rooms WHERE status = 'Available'")->fetchAll();
$occupied_rooms = $pdo->query("SELECT a.id as admission_id, a.room_id, r.room_number, r.type, p.name as patient_name, a.admitted_at 
                              FROM admissions a 
                              JOIN rooms r ON a.room_id = r.id 
                              JOIN patients p ON a.patient_id = p.id 
                              WHERE a.discharged_at IS NULL")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Room Management - HMS</title>
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
                    <li><a href="rooms.php" class="active"><i class="fas fa-bed"></i> Room Management</a></li>
                    <li><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="header">
                <h1>IPD / Room Management</h1>
            </header>

            <?php if ($success) echo '<div class="alert alert-success">'.$success.'</div>'; ?>
            <?php if ($error) echo '<div class="alert alert-danger">'.$error.'</div>'; ?>

            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">
                <div class="stat-card">
                    <h3>Admit Patient</h3>
                    <form method="POST" action="" style="margin-top: 1rem;">
                        <div class="form-group">
                            <label>Patient</label>
                            <select name="patient_id" required>
                                <option value="">-- Select Patient --</option>
                                <?php foreach ($patients as $p): ?>
                                    <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Available Room</label>
                            <select name="room_id" required>
                                <option value="">-- Select Room --</option>
                                <?php foreach ($available_rooms as $r): ?>
                                    <option value="<?php echo $r['id']; ?>">Room <?php echo $r['room_number']; ?> (<?php echo $r['type']; ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" name="admit" class="btn">Admit Patient</button>
                    </form>
                </div>

                <div class="stat-card">
                    <h3>Current Occupancy</h3>
                    <table style="width: 100%; border-collapse: collapse; margin-top: 1rem;">
                        <thead>
                            <tr style="background: #f8f9fa;">
                                <th>Room</th>
                                <th>Type</th>
                                <th>Patient</th>
                                <th>Admitted At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($occupied_rooms as $occ): ?>
                            <tr>
                                <td style="padding: 1rem; border-bottom: 1px solid #eee;">Room <?php echo $occ['room_number']; ?></td>
                                <td style="padding: 1rem; border-bottom: 1px solid #eee;"><?php echo $occ['type']; ?></td>
                                <td style="padding: 1rem; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars($occ['patient_name']); ?></td>
                                <td style="padding: 1rem; border-bottom: 1px solid #eee;"><?php echo date('M d, h:i A', strtotime($occ['admitted_at'])); ?></td>
                                <td style="padding: 1rem; border-bottom: 1px solid #eee;">
                                    <a href="rooms.php?discharge_id=<?php echo $occ['admission_id']; ?>&room_id=<?php echo $occ['room_id']; ?>" 
                                       class="btn" style="padding: 5px 10px; background: #e74c3c; width: auto; font-size: 0.8rem;">Discharge</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($occupied_rooms)): ?>
                                <tr><td colspan="5" style="padding: 2rem; text-align: center;">No rooms are currently occupied.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
