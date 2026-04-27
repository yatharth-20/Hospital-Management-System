<?php
require_once '../../config/db.php';
require_once '../../includes/functions.php';

check_role(['admin', 'receptionist']);

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generate_bill'])) {
    $patient_id = $_POST['patient_id'];
    $appointment_id = $_POST['appointment_id'];
    $amount = $_POST['amount'];

    // FIX: Convert empty appointment_id to NULL to satisfy foreign key constraint
    $final_appointment_id = (!empty($appointment_id)) ? $appointment_id : null;

    if (!empty($patient_id) && !empty($amount)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO bills (patient_id, appointment_id, amount, status) VALUES (?, ?, ?, 'unpaid')");
            $stmt->execute([$patient_id, $final_appointment_id, $amount]);
            $success = "Bill generated successfully for Patient ID #$patient_id!";
        } catch(PDOException $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    } else {
        $error = "Please provide both Patient and Total Amount.";
    }
}

$patients = $pdo->query("SELECT id, name FROM patients ORDER BY name ASC")->fetchAll();
$appointments = $pdo->query("SELECT a.id, p.name as patient_name, a.appointment_date 
                             FROM appointments a 
                             JOIN patients p ON a.patient_id = p.id 
                             WHERE a.status = 'completed'
                             ORDER BY a.appointment_date DESC LIMIT 20")->fetchAll();
$bills = $pdo->query("SELECT b.*, p.name as patient_name FROM bills b JOIN patients p ON b.patient_id = p.id ORDER BY b.created_at DESC LIMIT 15")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Billing & Invoicing - HMS</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-layout">
        <aside class="sidebar">
            <div class="brand-section">
                <i class="fas fa-file-invoice-dollar"></i>
                <span class="brand-name">HMS BILLING</span>
            </div>
            <nav>
                <ul class="nav-links">
                    <li><a href="../../dashboard.php"><i class="fas fa-th-large"></i> <span>Dashboard</span></a></li>
                    <li><a href="generate.php" class="active"><i class="fas fa-plus-circle"></i> <span>New Invoice</span></a></li>
                    <li><a href="../../modules/patients/search.php"><i class="fas fa-users"></i> <span>Patients</span></a></li>
                    <li style="margin-top: auto;"><a href="../../logout.php"><i class="fas fa-power-off"></i> <span>Logout</span></a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="header">
                <h1>Financial Management</h1>
                <div class="user-info">
                    <i class="fas fa-cash-register"></i>
                    <span>Billing Dept</span>
                </div>
            </header>

            <?php if ($success) echo '<div class="alert alert-success"><i class="fas fa-check-circle"></i> '.$success.'</div>'; ?>
            <?php if ($error) echo '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> '.$error.'</div>'; ?>

            <div class="card-grid" style="grid-template-columns: 1fr 2fr;">
                <!-- Generate Form -->
                <div class="table-container" style="padding: 1.5rem;">
                    <h2 style="margin-bottom: 1.5rem; font-size: 1.25rem;"><i class="fas fa-file-signature"></i> Create Invoice</h2>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label>Select Patient *</label>
                            <select name="patient_id" required style="width: 100%; padding: 0.8rem; border-radius: 8px; border: 1px solid #ddd; background: #f8fafc;">
                                <option value="">-- Choose Patient --</option>
                                <?php foreach ($patients as $p): ?>
                                    <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['name']); ?> (ID: <?php echo $p['id']; ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Linked Appointment (Optional)</label>
                            <select name="appointment_id" style="width: 100%; padding: 0.8rem; border-radius: 8px; border: 1px solid #ddd; background: #f8fafc;">
                                <option value="">-- Independent Charge --</option>
                                <?php foreach ($appointments as $a): ?>
                                    <option value="<?php echo $a['id']; ?>">Visit #<?php echo $a['id']; ?> - <?php echo $a['patient_name']; ?> (<?php echo date('M d', strtotime($a['appointment_date'])); ?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Only showing completed appointments</small>
                        </div>
                        <div class="form-group">
                            <label>Invoice Amount (USD) *</label>
                            <div style="position: relative;">
                                <span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #64748b;">$</span>
                                <input type="number" step="0.01" name="amount" required style="padding-left: 1.8rem;" placeholder="0.00">
                            </div>
                        </div>
                        <button type="submit" name="generate_bill" class="btn" style="background: var(--primary-color);">
                            <i class="fas fa-check"></i> Generate & Save Invoice
                        </button>
                    </form>
                </div>

                <!-- Recent Invoices Table -->
                <div class="table-container">
                    <div style="padding: 1.5rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                        <h2 style="font-size: 1.25rem;"><i class="fas fa-list-ul"></i> Recent Transactions</h2>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Invoice ID</th>
                                <th>Patient</th>
                                <th>Total Amount</th>
                                <th>Payment Status</th>
                                <th>Issued Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bills as $bill): ?>
                            <tr>
                                <td style="font-weight: 600;">#INV-<?php echo str_pad($bill['id'], 5, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo htmlspecialchars($bill['patient_name']); ?></td>
                                <td style="font-size: 1.1rem; color: var(--primary-color); font-weight: 700;">$<?php echo number_format($bill['amount'], 2); ?></td>
                                <td>
                                    <span class="status-badge" style="background: <?php echo $bill['status'] == 'paid' ? '#dcfce7' : '#fee2e2'; ?>; color: <?php echo $bill['status'] == 'paid' ? '#166534' : '#991b1b'; ?>;">
                                        <?php echo ucfirst($bill['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($bill['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($bills)): ?>
                                <tr><td colspan="5" style="padding: 3rem; text-align: center; color: var(--text-muted);">No invoices generated yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
