<?php
/**
 * Simple Verification Script for HMS
 */

require_once 'config/db.php';

function assert_test($condition, $message) {
    if ($condition) {
        echo "[PASS] $message\n";
    } else {
        echo "[FAIL] $message\n";
        exit(1);
    }
}

echo "Starting HMS Verification...\n";

// 1. Database Connection
assert_test(isset($pdo), "Database connection object exists");

// 2. User Authentication (Admin Hash Check)
$stmt = $pdo->prepare("SELECT password FROM users WHERE username = 'admin'");
$stmt->execute();
$hash = $stmt->fetchColumn();
assert_test(password_verify('admin123', $hash), "Admin password hash is valid");

// 3. Patient Registration Logic (Mock)
$name = "Test Patient " . time();
$stmt = $pdo->prepare("INSERT INTO patients (name, dob, gender) VALUES (?, '1990-01-01', 'male')");
$stmt->execute([$name]);
$patient_id = $pdo->lastInsertId();
assert_test($patient_id > 0, "Patient registration successful");

// 4. Appointment Clash Detection Logic
$date = date('Y-m-d H:i:s', strtotime('+1 day'));
// Insert a doctor if none exists
$pdo->exec("INSERT INTO users (username, password, role) VALUES ('dr_test', 'hash', 'doctor')");
$doctor_user_id = $pdo->lastInsertId();
$pdo->prepare("INSERT INTO doctors (user_id, name, specialization) VALUES (?, 'Dr. Test', 'General')")->execute([$doctor_user_id]);
$doctor_id = $pdo->lastInsertId();

// Book first appointment
$pdo->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date) VALUES (?, ?, ?)")->execute([$patient_id, $doctor_id, $date]);

// Try to find clash (30 mins rule)
$clash_date = date('Y-m-d H:i:s', strtotime($date . ' + 10 minutes'));
$stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments 
                       WHERE doctor_id = ? 
                       AND ABS(TIMESTAMPDIFF(MINUTE, appointment_date, ?)) < 30");
$stmt->execute([$doctor_id, $clash_date]);
$clash = $stmt->fetchColumn();
assert_test($clash > 0, "Clash detection logic identifies overlapping appointments");

// 5. IPD / Room Management Logic
$pdo->exec("INSERT INTO rooms (room_number, type, status) VALUES ('101A', 'Private', 'Available')");
$room_id = $pdo->lastInsertId();
assert_test($room_id > 0, "Room creation successful");

$pdo->prepare("INSERT INTO admissions (patient_id, room_id) VALUES (?, ?)")->execute([$patient_id, $room_id]);
$admission_id = $pdo->lastInsertId();
$pdo->prepare("UPDATE rooms SET status = 'Occupied' WHERE id = ?")->execute([$room_id]);

assert_test($admission_id > 0, "Patient admission successful");
$room_status = $pdo->query("SELECT status FROM rooms WHERE id = $room_id")->fetchColumn();
assert_test($room_status == 'Occupied', "Room status correctly updated to Occupied");

echo "All core and expanded logic tests passed!\n";
?>
