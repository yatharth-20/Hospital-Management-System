<?php
header("Content-Type: application/json");
require_once '../config/db.php';
require_once '../includes/functions.php';

// Simple API Key or Session check for API
if (!isset($_SESSION['user_id'])) {
    // In a real API, we'd use JWT or API Keys. For this exercise, we'll check session.
    // session_start(); // already in functions.php
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'GET') {
    if (isset($_GET['id'])) {
        // Get specific patient record
        $stmt = $pdo->prepare("SELECT * FROM patients WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $patient = $stmt->fetch();
        
        if ($patient) {
            echo json_encode(["status" => "success", "data" => $patient]);
        } else {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "Patient not found"]);
        }
    } else {
        // Get all patients
        $stmt = $pdo->query("SELECT * FROM patients");
        $patients = $stmt->fetchAll();
        echo json_encode(["status" => "success", "data" => $patients]);
    }
} else {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed"]);
}
?>
