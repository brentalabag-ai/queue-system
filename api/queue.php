<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user']) && !isset($_SESSION['student'])) {
    header('Location: ../index.php?error=Not authenticated');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Check if action is set
if (!isset($_POST['action'])) {
    // If no action specified, redirect based on user type
    if (isset($_SESSION['student'])) {
        header('Location: ../student-dashboard.php?error=Invalid request');
    } else {
        header('Location: ../cashier-dashboard.php?error=Invalid request');
    }
    exit();
}

$action = $_POST['action'];

if ($action === 'request_queue') {
    if (!isset($_SESSION['student'])) {
        header('Location: ../student-dashboard.php?error=Student not logged in');
        exit();
    }
    
    $studentId = $_POST['student_id'];
    $amount = $_POST['amount'] ?? 0;
    $paymentFor = $_POST['payment_for'] ?? [];
    $otherSpecify = $_POST['other_specify'] ?? '';
    
    try {
        // Validate amount
        if (empty($amount) || $amount <= 0) {
            header('Location: ../payment-slip.php?error=Please enter a valid amount');
            exit();
        }
        
        // Validate payment type
        if (empty($paymentFor)) {
            header('Location: ../payment-slip.php?error=Please select at least one payment type');
            exit();
        }
        
        // Validate "others" specification
        if (in_array('others', $paymentFor) && empty($otherSpecify)) {
            header('Location: ../payment-slip.php?error=Please specify the other payment type');
            exit();
        }
        
        // Check for active queue
        $checkQuery = "SELECT * FROM queue WHERE student_id = :student_id AND status IN ('waiting', 'serving')";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':student_id', $studentId);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() > 0) {
            $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
            header('Location: ../student-dashboard.php?error=You already have an active queue number: ' . $existing['queue_number']);
            exit();
        }
        
        // Get next queue number
        $maxQuery = "SELECT MAX(queue_number) as max_number FROM queue WHERE DATE(time_in) = CURDATE()";
        $maxStmt = $db->prepare($maxQuery);
        $maxStmt->execute();
        $maxResult = $maxStmt->fetch(PDO::FETCH_ASSOC);
        $nextQueueNumber = ($maxResult['max_number'] ?? 0) + 1;
        
        // Insert new queue only (skip transactions table to avoid foreign key issues)
        $insertQuery = "INSERT INTO queue (student_id, queue_number, status, time_in) VALUES (:student_id, :queue_number, 'waiting', NOW())";
        $insertStmt = $db->prepare($insertQuery);
        $insertStmt->bindParam(':student_id', $studentId);
        $insertStmt->bindParam(':queue_number', $nextQueueNumber);
        
        if ($insertStmt->execute()) {
            // Success - just create the queue without transaction records
            header('Location: ../student-dashboard.php?message=Queue number ' . $nextQueueNumber . ' assigned successfully!');
        } else {
            header('Location: ../payment-slip.php?error=Failed to get queue number');
        }
        
    } catch (PDOException $e) {
        error_log("Queue Error: " . $e->getMessage());
        header('Location: ../payment-slip.php?error=System error. Please try again.');
        exit();
    }
    
} elseif ($action === 'void_queue') {
    if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] !== 'admin' && $_SESSION['user']['role'] !== 'cashier')) {
        header('Location: ../cashier-dashboard.php?error=Insufficient permissions');
        exit();
    }
    
    $queueId = $_POST['queue_id'];
    
    try {
        $updateQuery = "UPDATE queue SET status = 'voided', time_out = NOW() WHERE queue_id = :queue_id";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->bindParam(':queue_id', $queueId);
        
        if ($updateStmt->execute()) {
            header('Location: ../cashier-dashboard.php?message=Queue entry voided successfully');
        } else {
            header('Location: ../cashier-dashboard.php?error=Failed to void queue entry');
        }
    } catch (PDOException $e) {
        header('Location: ../cashier-dashboard.php?error=Database error: ' . $e->getMessage());
    }
    
} else {
    // Unknown action
    if (isset($_SESSION['student'])) {
        header('Location: ../student-dashboard.php?error=Unknown action');
    } else {
        header('Location: ../cashier-dashboard.php?error=Unknown action');
    }
}

exit();
?>