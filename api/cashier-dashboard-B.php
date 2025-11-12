<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] !== 'cashier' && $_SESSION['user']['role'] !== 'admin')) {
    header('Location: index.php?error=Access denied');
    exit();
}

$user = $_SESSION['user'];
$database = new Database();
$db = $database->getConnection();

// Handle actions
if ($_POST['action'] ?? '' === 'serve_next') {
    // Get next waiting student
    $nextQuery = "SELECT * FROM queue WHERE status = 'waiting' ORDER BY queue_number ASC LIMIT 1";
    $nextStmt = $db->prepare($nextQuery);
    $nextStmt->execute();
    
    if ($nextStmt->rowCount() > 0) {
        $nextStudent = $nextStmt->fetch(PDO::FETCH_ASSOC);
        $updateQuery = "UPDATE queue SET status = 'serving', timer_start = NOW() WHERE queue_id = :queue_id";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->bindParam(':queue_id', $nextStudent['queue_id']);
        $updateStmt->execute();
        header('Location: cashier-dashboard.php?message=Now serving queue #' . $nextStudent['queue_number']);
        exit();
    } else {
        header('Location: cashier-dashboard.php?error=No students in queue');
        exit();
    }
}

// Get current queue
$queueQuery = "
    SELECT q.*, s.name as student_name, s.course, s.year_level
    FROM queue q
    JOIN students s ON q.student_id = s.student_id
    WHERE q.status IN ('waiting', 'serving')
    ORDER BY q.queue_number ASC
";
$queueStmt = $db->prepare($queueQuery);
$queueStmt->execute();
$queue = $queueStmt->fetchAll(PDO::FETCH_ASSOC);

// Get statistics
$statsQuery = "
    SELECT 
        COUNT(*) as total_waiting,
        AVG(TIMESTAMPDIFF(MINUTE, time_in, NOW())) as avg_wait_time
    FROM queue 
    WHERE status = 'waiting'
";
$statsStmt = $db->prepare($statsQuery);
$statsStmt->execute();
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
?>