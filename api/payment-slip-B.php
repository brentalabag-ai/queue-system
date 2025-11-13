<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['student'])) {
    header('Location: index.php');
    exit();
}

$student = $_SESSION['student'];

// Check if student already has active queue
$database = new Database();
$db = $database->getConnection();

$checkQuery = "SELECT * FROM queue WHERE student_id = :student_id AND status IN ('waiting', 'serving')";
$checkStmt = $db->prepare($checkQuery);
$checkStmt->bindParam(':student_id', $student['student_id']);
$checkStmt->execute();

if ($checkStmt->rowCount() > 0) {
    header('Location: student-dashboard.php?error=You already have an active queue number');
    exit();
}
?>