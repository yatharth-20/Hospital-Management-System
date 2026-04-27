<?php
require_once '../../config/db.php';
require_once '../../includes/functions.php';

check_role(['admin', 'pharmacy', 'doctor']);

// Handle Dispensing (Simple status toggle for demo)
if (isset($_GET['dispense'])) {
    $id = $_GET['dispense'];
    // In a real system, we'd have a 'status' column in prescriptions.
    // For now, let's just show a success message as a mock action.
    $success = "Medication dispensed successfully for Prescription #$id!";
}

$stmt = $pdo->query("SELECT pr.*, p.name as patient_name, d.name as doctor_name 
                     FROM prescriptions pr 
                     JOIN patients p ON pr.patient_id = p.id 
                     JOIN doctors d ON pr.doctor_id = d.id 
                     ORDER BY prescribed_at DESC");
$prescriptions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pharmacy Management - HMS</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-layout">
        <aside class="sidebar">
            <div class="brand-section">
                <i class="fas fa-pills"></i>
                <span class="brand-name">HMS PHARMA</span>
            </div>
            <nav>
                <ul class="nav-links">
                    <li><a href="../../dashboard.php"><i class="fas fa-columns"></i> <span>Dashboard</span></a></li>
                    <li><a href="inventory.php" class="active"><i class="fas fa-prescription-bottle-alt"></i> <span>Inventory</span></a></li>
                    <li style="margin-top: auto;"><a href="../../logout.php"><i class="fas fa-power-off"></i> <span>Logout</span></a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="header">
                <h1>Pharmacy Inventory & Dispensing</h1>
                <div class="user-info">
                    <i class="fas fa-user-nurse"></i>
                    <span>Pharmacist</span>
                </div>
            </header>

            <?php if (isset($success)) echo '<div class="alert alert-success"><i class="fas fa-check-circle"></i> '.$success.'</div>'; ?>

            <div class="stat-card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h3><i class="fas fa-clipboard-list"></i> Pending Prescriptions</h3>
                </div>
                
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Date Issued</th>
                                <th>Patient Name</th>
                                <th>Prescribing Doctor</th>
                                <th>Medications</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($prescriptions as $pr): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($pr['prescribed_at'])); ?></td>
                                <td style="font-weight: 600;"><?php echo htmlspecialchars($pr['patient_name']); ?></td>
                                <td>Dr. <?php echo htmlspecialchars($pr['doctor_name']); ?></td>
                                <td>
                                    <div style="font-size: 0.9rem; background: #f1f5f9; padding: 0.5rem; border-radius: 6px; border-left: 3px solid var(--secondary-color);">
                                        <?php echo nl2br(htmlspecialchars($pr['medications'])); ?>
                                    </div>
                                </td>
                                <td>
                                    <a href="?dispense=<?php echo $pr['id']; ?>" class="btn" style="width: auto; padding: 0.5rem 1rem; background: var(--secondary-color);">
                                        <i class="fas fa-hand-holding-medical"></i> Dispense
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($prescriptions)): ?>
                                <tr><td colspan="5" style="padding: 3rem; text-align: center; color: var(--text-muted);">
                                    <i class="fas fa-box-open" style="font-size: 3rem; display: block; margin-bottom: 1rem; opacity: 0.3;"></i>
                                    No pending prescriptions found in the system.
                                </td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
