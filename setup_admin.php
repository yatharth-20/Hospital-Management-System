<?php
require_once 'config/db.php';

try {
    // Clear existing data to avoid conflicts
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $pdo->exec("TRUNCATE TABLE prescriptions;");
    $pdo->exec("TRUNCATE TABLE appointments;");
    $pdo->exec("TRUNCATE TABLE doctors;");
    $pdo->exec("TRUNCATE TABLE patients;");
    $pdo->exec("TRUNCATE TABLE users;");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

    $password_hash = '$2y$10$mn9/7g8ifFiLG3P4VMevFOlH5ykPdi6DUoyMBhK5wp5xXIoxH5VQK'; // 'admin123'

    // 1. Admin
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->execute(['admin', $password_hash, 'admin']);

    // 2. Doctor
    $stmt->execute(['doctor1', $password_hash, 'doctor']);
    $doctor_user_id = $pdo->lastInsertId();
    $stmt_doc = $pdo->prepare("INSERT INTO doctors (user_id, name, specialization, contact) VALUES (?, ?, ?, ?)");
    $stmt_doc->execute([$doctor_user_id, 'John Smith', 'Cardiology', '1234567890']);
    $doctor_id = $pdo->lastInsertId();

    // 3. Patient
    $stmt_p = $pdo->prepare("INSERT INTO patients (name, dob, gender, contact, address) VALUES (?, ?, ?, ?, ?)");
    $stmt_p->execute(['Jane Doe', '1990-05-15', 'female', '9876543210', '123 Health St, Wellness City']);
    $patient_id = $pdo->lastInsertId();
    
    $stmt->execute(['patient1', $password_hash, 'patient']);
    $patient_user_id = $pdo->lastInsertId();
    $pdo->prepare("UPDATE users SET patient_id = ? WHERE id = ?")->execute([$patient_id, $patient_user_id]);

    // 4. Test User
    $stmt->execute(['test1', $password_hash, 'test']);

    // 5. Add a mock appointment
    $pdo->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, status) VALUES (?, ?, NOW(), 'pending')")
        ->execute([$patient_id, $doctor_id]);

    echo "System Reset & Test Accounts Created Successfully!\n";
    echo "-------------------------------------------\n";
    echo "Admin: admin / admin123\n";
    echo "Doctor: doctor1 / admin123\n";
    echo "Patient: patient1 / admin123\n";
    echo "Test: test1 / admin123\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
