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

// Get payment history
$history = [];
try {
    $historyQuery = "
        SELECT 
            ph.date,
            t.amount,
            t.payment_type,
            t.status,
            u.name as cashier_name,
            q.queue_number
        FROM payment_history ph
        JOIN transactions t ON ph.transaction_id = t.transaction_id
        JOIN queue q ON t.queue_id = q.queue_id
        LEFT JOIN users u ON t.cashier_id = u.user_id
        WHERE ph.student_id = :student_id
        ORDER BY ph.date DESC
        LIMIT 10
    ";
    $historyStmt = $db->prepare($historyQuery);
    $historyStmt->bindParam(':student_id', $student['student_id']);
    $historyStmt->execute();
    $history = $historyStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Handle error silently
}
?>