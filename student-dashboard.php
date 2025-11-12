<?php
    require_once "api/student-dashboard-B.php"
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
            <div class="col-md-8">
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
                                    <p class="mb-0">Get a queue number to join the line.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Get Queue Number -->
                        <div class="d-grid gap-2 mb-4">
                            <form method="POST" action="api/queue.php">
                                <input type="hidden" name="action" value="request_queue">
                                <input type="hidden" name="student_id" value="<?php echo $student['student_id']; ?>">
                                <button type="submit" class="btn btn-success btn-lg w-100">
                                    <i class="fas fa-ticket-alt me-2"></i>Get Queue Number
                                </button>
                            </form>
                        </div>
                        
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
        // Auto-refresh every 10 seconds
        setTimeout(function() {
            location.reload();
        }, 10000);
    </script>
</body>
</html>