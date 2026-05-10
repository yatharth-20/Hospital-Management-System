<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    
    // Common fields
    $dob = $_POST['dob'] ?? null;
    $gender = $_POST['gender'] ?? null;
    $contact = trim($_POST['contact'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
    // Doctor specific
    $specialization = trim($_POST['specialization'] ?? '');

    if (!empty($name) && !empty($username) && !empty($email) && !empty($password) && !empty($role)) {
        try {
            $pdo->beginTransaction();

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            if ($role == 'patient') {
                // 1. Create Patient Record
                $stmt = $pdo->prepare("INSERT INTO patients (name, dob, gender, contact, address) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$name, $dob, $gender, $contact, $address]);
                $patient_id = $pdo->lastInsertId();

                // 2. Create User Record
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, patient_id) VALUES (?, ?, ?, 'patient', ?)");
                $stmt->execute([$username, $email, $hashed_password, $patient_id]);
            } elseif ($role == 'doctor') {
                // 1. Create User Record
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'doctor')");
                $stmt->execute([$username, $email, $hashed_password]);
                $user_id = $pdo->lastInsertId();

                // 2. Create Doctor Record
                $stmt = $pdo->prepare("INSERT INTO doctors (user_id, name, specialization, contact) VALUES (?, ?, ?, ?)");
                $stmt->execute([$user_id, $name, $specialization, $contact]);
            } else {
                // Admin or other roles
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
                $stmt->execute([$username, $email, $hashed_password, $role]);
            }

            $pdo->commit();
            $success = "Registration as " . ucfirst($role) . " successful! You can now <a href='index.php'>login</a>.";
        } catch(PDOException $e) {
            $pdo->rollBack();
            if ($e->getCode() == 23000) {
                $error = "Username already exists. Please choose another one.";
            } else {
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration - HMS</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .role-fields { display: none; }
        .active-role { display: block; }
    </style>
</head>
<body class="login-container">
    <div class="login-card" style="max-width: 600px;">
        <h2>System Registration</h2>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" id="regForm">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="name" required placeholder="John Doe">
                </div>
                <div class="form-group">
                    <label>Select Role *</label>
                    <select name="role" id="roleSelect" required onchange="toggleFields()">
                        <option value="patient">Patient</option>
                        <option value="doctor">Doctor</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label>Username *</label>
                    <input type="text" name="username" required placeholder="johndoe123">
                </div>
                <div class="form-group">
                    <label>Password *</label>
                    <input type="password" name="password" required>
                </div>
            </div>

            <div class="form-group">
                <label>Email Address *</label>
                <input type="email" name="email" required placeholder="john@example.com">
            </div>

            <!-- Patient Specific Fields -->
            <div id="patientFields" class="role-fields active-role">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>Date of Birth *</label>
                        <input type="date" name="dob" id="dobInput">
                    </div>
                    <div class="form-group">
                        <label>Gender *</label>
                        <select name="gender" id="genderInput">
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" rows="2" style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 6px;"></textarea>
                </div>
            </div>

            <!-- Doctor Specific Fields -->
            <div id="doctorFields" class="role-fields">
                <div class="form-group">
                    <label>Specialization *</label>
                    <input type="text" name="specialization" id="specInput" placeholder="e.g. Cardiology">
                </div>
            </div>

            <div class="form-group">
                <label>Contact Number</label>
                <input type="text" name="contact" placeholder="1234567890">
            </div>
            
            <button type="submit" name="register" class="btn">Create Account</button>
            <p style="text-align: center; margin-top: 1rem; font-size: 0.9rem;">
                Already have an account? <a href="index.php">Login here</a>
            </p>
        </form>
    </div>

    <script>
    function toggleFields() {
        const role = document.getElementById('roleSelect').value;
        const patientFields = document.getElementById('patientFields');
        const doctorFields = document.getElementById('doctorFields');
        const dobInput = document.getElementById('dobInput');
        const specInput = document.getElementById('specInput');

        patientFields.classList.remove('active-role');
        doctorFields.classList.remove('active-role');
        
        dobInput.required = false;
        specInput.required = false;

        if (role === 'patient') {
            patientFields.classList.add('active-role');
            dobInput.required = true;
        } else if (role === 'doctor') {
            doctorFields.classList.add('active-role');
            specInput.required = true;
        }
    }
    // Set initial state
    toggleFields();
    </script>
</body>
</html>
