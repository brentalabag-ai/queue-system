<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['student'])) {
    header('Location: index.php');
    exit();
}

$student = $_SESSION['student'];
$database = new Database();
$db = $database->getConnection();

// Get current queue status
$queueQuery = "SELECT * FROM queue WHERE student_id = :student_id AND status IN ('waiting', 'serving') ORDER BY queue_id DESC LIMIT 1";
$queueStmt = $db->prepare($queueQuery);
$queueStmt->bindParam(':student_id', $student['student_id']);
$queueStmt->execute();
$currentQueue = $queueStmt->fetch(PDO::FETCH_ASSOC);

// Get position in queue
$position = 0;
if ($currentQueue && $currentQueue['status'] === 'waiting') {
    $positionQuery = "SELECT COUNT(*) as position FROM queue WHERE status = 'waiting' AND queue_number < :queue_number";
    $positionStmt = $db->prepare($positionQuery);
    $positionStmt->bindParam(':queue_number', $currentQueue['queue_number']);
    $positionStmt->execute();
    $positionResult = $positionStmt->fetch(PDO::FETCH_ASSOC);
    $position = $positionResult['position'] ?? 0;
}
?>