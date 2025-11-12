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

// Handle payment slip submission and queue request
if ($_POST['action'] ?? '' === 'submit_payment_slip') {
    // Validate payment slip data
    $amount = $_POST['amount'] ?? '';
    $payment_for = $_POST['payment_for'] ?? [];
    $other_specify = $_POST['other_specify'] ?? '';
    
    if (empty($amount) || empty($payment_for)) {
        header('Location: student-dashboard.php?error=Please fill all required fields in the payment slip');
        exit();
    }
    
    // Check for active queue
    $checkQuery = "SELECT * FROM queue WHERE student_id = :student_id AND status IN ('waiting', 'serving')";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(':student_id', $student['student_id']);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() > 0) {
        $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
        header('Location: student-dashboard.php?error=You already have queue number ' . $existing['queue_number']);
        exit();
    }
    
    // Get next queue number
    $maxQuery = "SELECT MAX(queue_number) as max_number FROM queue WHERE DATE(time_in) = CURDATE()";
    $maxStmt = $db->prepare($maxQuery);
    $maxStmt->execute();
    $maxResult = $maxStmt->fetch(PDO::FETCH_ASSOC);
    $nextQueueNumber = ($maxResult['max_number'] ?? 0) + 1;
    
    // Insert new queue with payment slip data
    $insertQuery = "INSERT INTO queue (student_id, queue_number, status, time_in, payment_amount, payment_for) 
                   VALUES (:student_id, :queue_number, 'waiting', NOW(), :amount, :payment_for)";
    $insertStmt = $db->prepare($insertQuery);
    $insertStmt->bindParam(':student_id', $student['student_id']);
    $insertStmt->bindParam(':queue_number', $nextQueueNumber);
    $insertStmt->bindParam(':amount', $amount);
    $paymentForString = implode(', ', $payment_for);
    if (!empty($other_specify) && in_array('others', $payment_for)) {
        $paymentForString .= " ($other_specify)";
    }
    $insertStmt->bindParam(':payment_for', $paymentForString);
    
    if ($insertStmt->execute()) {
        header('Location: student-dashboard.php?message=Queue number ' . $nextQueueNumber . ' assigned successfully!');
    } else {
        header('Location: student-dashboard.php?error=Failed to get queue number');
    }
    exit();
}

// Get current queue status (existing code)
$queueQuery = "SELECT * FROM queue WHERE student_id = :student_id AND status IN ('waiting', 'serving') ORDER BY queue_id DESC LIMIT 1";
$queueStmt = $db->prepare($queueQuery);
$queueStmt->bindParam(':student_id', $student['student_id']);
$queueStmt->execute();
$currentQueue = $queueStmt->fetch(PDO::FETCH_ASSOC);

// Get position in queue (existing code)
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - SLC College</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <style>
        .payment-slip {
            border: 2px solid #000;
            padding: 20px;
            background: white;
            max-width: 500px;
            margin: 0 auto;
        }
        .payment-slip .header {
            text-align: center;
            border-bottom: 1px solid #000;
            margin-bottom: 15px;
            padding-bottom: 10px;
        }
        .form-field {
            margin-bottom: 15px;
        }
        .form-field label {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .checkbox-group {
            margin: 10px 0;
        }
        .other-specify {
            margin-left: 25px;
            margin-top: 5px;
        }
    </style>
</head>
<body class="student-view">
    <nav class="navbar navbar-dark bg-dark bg-opacity-50">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-university me-2"></i>SLC College Student Portal
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">Welcome, <?php echo $student['name']; ?></span>
                <a class="btn btn-outline-light btn-sm" href="api/auth.php?action=logout">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h3 class="mb-0">Cashier Queue System</h3>
                    </div>
                    <div class="card-body p-4">
                        <!-- Display Messages -->
                        <?php if (isset($_GET['message'])): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($_GET['message']); ?></div>
                        <?php endif; ?>
                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                        <?php endif; ?>

                        <div id="student-queue-status" class="text-center mb-4">
                            <?php if ($currentQueue): ?>
                                <div class="alert alert-info">
                                    <h4>Your Queue Number: <span class="queue-number"><?php echo $currentQueue['queue_number']; ?></span></h4>
                                    <p class="mb-1">Status: <strong class="text-uppercase"><?php echo $currentQueue['status']; ?></strong></p>
                                    <?php if ($currentQueue['status'] === 'waiting'): ?>
                                        <p class="mb-0">Students ahead of you: <strong><?php echo $position; ?></strong></p>
                                    <?php endif; ?>
                                    <?php if ($currentQueue['status'] === 'serving'): ?>
                                        <div class="mt-2">
                                            <div class="alert alert-warning mb-0">
                                                <i class="fas fa-bell me-2"></i>
                                                You're being served! Please proceed to the cashier.
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-secondary">
                                    <h4>No Active Queue Number</h4>
                                    <p class="mb-0">Fill out the payment slip below to get a queue number.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Payment Slip Form -->
                        <?php if (!$currentQueue): ?>
                        <div class="card mb-4">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Payment Slip</h5>
                            </div>
                            <div class="card-body">
                                <div class="payment-slip">
                                    <div class="header">
                                        <h4 class="mb-1">Saint Louis College</h4>
                                        <p class="mb-1">City of San Fernando, 2500 La Union</p>
                                        <h5 class="mb-0">PAYMENT SLIP</h5>
                                    </div>
                                    
                                    <form method="POST" action="student-dashboard.php">
                                        <input type="hidden" name="action" value="submit_payment_slip">
                                        
                                        <div class="form-field">
                                            <label for="name">NAME:</label>
                                            <input type="text" class="form-control" id="name" value="<?php echo $student['name']; ?>" readonly>
                                        </div>
                                        
                                        <div class="form-field">
                                            <label for="student_id">ID NO:</label>
                                            <input type="text" class="form-control" id="student_id" value="<?php echo $student['student_id']; ?>" readonly>
                                        </div>
                                        
                                        <div class="form-field">
                                            <label for="course_year">COURSE & YEAR:</label>
                                            <input type="text" class="form-control" id="course_year" value="<?php echo $student['course'] . ' - ' . $student['year_level']; ?>" readonly>
                                        </div>
                                        
                                        <div class="form-field">
                                            <label for="amount">AMOUNT: <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="amount" name="amount" step="0.01" placeholder="0.00" required>
                                        </div>
                                        
                                        <div class="form-field">
                                            <label>IN PAYMENT OF: <span class="text-danger">*</span></label>
                                            <div class="checkbox-group">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="payment_for[]" value="Tuition Fee" id="tuition">
                                                    <label class="form-check-label" for="tuition">Tuition Fee</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="payment_for[]" value="Transcript" id="transcript">
                                                    <label class="form-check-label" for="transcript">Transcript</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="payment_for[]" value="Overdue" id="overdue">
                                                    <label class="form-check-label" for="overdue">Overdue</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="payment_for[]" value="others" id="others">
                                                    <label class="form-check-label" for="others">Others (Please specify)</label>
                                                </div>
                                                <div class="other-specify">
                                                    <input type="text" class="form-control mt-1" name="other_specify" placeholder="Specify other payment" id="otherSpecify" disabled>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="form-field">
                                            <label for="date">DATE:</label>
                                            <input type="text" class="form-control" id="date" value="<?php echo date('F d, Y'); ?>" readonly>
                                        </div>
                                        
                                        <div class="text-center mt-4">
                                            <button type="submit" class="btn btn-success btn-lg">
                                                <i class="fas fa-ticket-alt me-2"></i>Submit Payment Slip & Get Queue Number
                                            </button>
                                        </div>
                                    </form>
                                    
                                    <div class="mt-4 text-center text-muted small">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong>Reference Code</strong><br>
                                                FM-TREA-001
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Revision No.</strong><br>
                                                0
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Effectivity Date</strong><br>
                                                August 1, 2019
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Payment History -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Payment History</h5>
                            </div>
                            <div class="card-body">
                                <?php
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
                                ?>
                                
                                <?php if (count($history) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-striped">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Amount</th>
                                                    <th>Type</th>
                                                    <th>Status</th>
                                                    <th>Cashier</th>
                                                    <th>Queue #</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($history as $item): ?>
                                                    <tr>
                                                        <td><?php echo date('M d, Y H:i', strtotime($item['date'])); ?></td>
                                                        <td>₱<?php echo number_format($item['amount'], 2); ?></td>
                                                        <td><span class="badge bg-info"><?php echo ucfirst($item['payment_type']); ?></span></td>
                                                        <td><span class="badge bg-<?php echo $item['status'] === 'completed' ? 'success' : 'warning'; ?>"><?php echo ucfirst($item['status']); ?></span></td>
                                                        <td><?php echo $item['cashier_name'] ?? 'N/A'; ?></td>
                                                        <td>#<?php echo $item['queue_number']; ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center text-muted py-4">
                                        <i class="fas fa-receipt fa-3x mb-3"></i>
                                        <p>No payment history found</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-refresh every 2 minutes
        setTimeout(function() {
            location.reload();
        }, 120000);
        
        // Enable/disable other specify field
        document.getElementById('others').addEventListener('change', function() {
            document.getElementById('otherSpecify').disabled = !this.checked;
            if (!this.checked) {
                document.getElementById('otherSpecify').value = '';
            }
        });
    </script>
</body>
</html>