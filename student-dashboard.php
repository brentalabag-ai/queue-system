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
    <link href="css/student-style.css" rel="stylesheet">
</head>
<body class="student-view">
    <nav class="navbar navbar-dark bg-dark bg-opacity-50">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-university me-2"></i>SLC College Student Portal
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">Welcome, <?php echo htmlspecialchars($student['name']); ?></span>
                <a class="btn btn-outline-light btn-sm" href="api/auth.php?action=logout">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow dashboard-card">
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

                        <!-- Queue Status -->
                        <div id="student-queue-status" class="text-center mb-4">
                            <?php if ($currentQueue): ?>
                                <?php if ($currentQueue['status'] === 'waiting'): ?>
                                    <div class="alert alert-info waiting-alert">
                                        <h4>Your Queue Number: <span class="queue-number">#<?php echo $currentQueue['queue_number']; ?></span></h4>
                                        <p class="mb-1">Status: <strong class="text-uppercase">WAITING</strong></p>
                                        <p class="mb-0">Students ahead of you: <strong><?php echo $position; ?></strong></p>
                                    </div>
                                <?php elseif ($currentQueue['status'] === 'serving'): ?>
                                    <div class="alert alert-warning serving-alert">
                                        <h4>Your Queue Number: <span class="queue-number">#<?php echo $currentQueue['queue_number']; ?></span></h4>
                                        <p class="mb-1">Status: <strong class="text-uppercase">SERVING</strong></p>
                                        <div class="mt-2">
                                            <div class="alert alert-warning mb-0">
                                                <i class="fas fa-bell me-2"></i>
                                                You're being served! Please proceed to the cashier.
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="alert alert-secondary">
                                    <h4>No Active Queue Number</h4>
                                    <p class="mb-0">Click the button below to fill out payment slip and get queue number.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Get Queue Number Button - Only show if no active queue -->
                        <?php if (!$currentQueue): ?>
                        <div class="d-grid gap-2 mb-4">
                            <a href="payment-slip.php" class="btn btn-success btn-lg">
                                <i class="fas fa-ticket-alt me-2"></i>Get Queue Number
                            </a>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Payment History -->
                        <div class="card dashboard-card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Payment History</h5>
                            </div>
                            <div class="card-body">
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
                                                        <td>
                                                            <span class="badge bg-<?php 
                                                                echo match($item['status']) {
                                                                    'completed' => 'success',
                                                                    'cancelled' => 'danger',
                                                                    default => 'warning'
                                                                }; 
                                                            ?>">
                                                                <?php echo ucfirst($item['status']); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($item['cashier_name'] ?? 'N/A'); ?></td>
                                                        <td>#<?php echo htmlspecialchars($item['queue_number']); ?></td>
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