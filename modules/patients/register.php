<?php
require_once '../../config/db.php';
require_once '../../includes/functions.php';

check_role(['admin', 'receptionist']);

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $contact = trim($_POST['contact']);
    $address = trim($_POST['address']);

    if (!empty($name) && !empty($dob) && !empty($gender)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO patients (name, dob, gender, contact, address) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $dob, $gender, $contact, $address]);
            $success = "Patient registered successfully!";
        } catch(PDOException $e) {
            $error = "Error: " . $e->getMessage();
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
    <title>Register Patient - HMS</title>
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
                    <li><a href="register.php" class="active"><i class="fas fa-user-plus"></i> Patients</a></li>
                    <li><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="header">
                <h1>Patient Registration</h1>
            </header>

            <?php if ($success) echo '<div class="alert alert-success">'.$success.'</div>'; ?>
            <?php if ($error) echo '<div class="alert alert-danger">'.$error.'</div>'; ?>

            <div class="stat-card" style="max-width: 600px; margin: 0 auto;">
                <form method="POST" action="">
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Date of Birth *</label>
                        <input type="date" name="dob" required>
                    </div>
                    <div class="form-group">
                        <label>Gender *</label>
                        <select name="gender" required>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Contact Number</label>
                        <input type="text" name="contact">
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <textarea name="address" style="width:100%; padding:0.8rem; border:1px solid #ddd; border-radius:6px;"></textarea>
                    </div>
                    <button type="submit" name="register" class="btn">Register Patient</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
