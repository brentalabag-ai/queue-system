<?php
    require_once "api/cashier-dashboard-B.php"
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashier Dashboard - SLC College</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-cash-register me-2"></i>SLC College - Cashier Dashboard
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">Welcome, <?php echo $user['name']; ?></span>
                <a class="btn btn-outline-light btn-sm" href="api/auth.php?action=logout">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid mt-4">
        <!-- Messages -->
        <?php if (isset($_GET['message'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo htmlspecialchars($_GET['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo htmlspecialchars($_GET['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Main Queue -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Current Queue</h5>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="action" value="serve_next">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-forward me-1"></i>Serve Next
                            </button>
                        </form>
                    </div>
                    <div class="card-body">
                        <div id="queue-list">
                            <?php if (count($queue) > 0): ?>
                                <?php foreach ($queue as $item): ?>
                                    <div class="queue-item p-3 mb-3 rounded <?php echo $item['status']; ?>">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h5 class="mb-1">
                                                    <span class="badge bg-dark me-2">#<?php echo $item['queue_number']; ?></span>
                                                    <?php echo $item['student_name']; ?>
                                                </h5>
                                                <p class="mb-1 text-muted">
                                                    <i class="fas fa-graduation-cap me-1"></i>
                                                    <?php echo $item['course']; ?> - <?php echo $item['year_level']; ?>
                                                </p>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock me-1"></i>
                                                    Joined: <?php echo date('H:i:s', strtotime($item['time_in'])); ?>
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-<?php 
                                                    switch($item['status']) {
                                                        case 'waiting': echo 'warning'; break;
                                                        case 'serving': echo 'info'; break;
                                                        case 'served': echo 'success'; break;
                                                        case 'voided': echo 'danger'; break;
                                                        default: echo 'secondary';
                                                    }
                                                ?> text-uppercase"><?php echo $item['status']; ?></span>
                                                
                                                <?php if ($item['status'] === 'serving'): ?>
                                                    <div class="mt-2">
                                                        <button class="btn btn-success btn-sm me-1" data-bs-toggle="modal" data-bs-target="#paymentModal" 
                                                                data-queue-id="<?php echo $item['queue_id']; ?>" data-student-name="<?php echo $item['student_name']; ?>">
                                                            <i class="fas fa-money-bill me-1"></i>Process Payment
                                                        </button>
                                                        <form method="POST" action="api/queue.php" class="d-inline">
                                                            <input type="hidden" name="action" value="void_queue">
                                                            <input type="hidden" name="queue_id" value="<?php echo $item['queue_id']; ?>">
                                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Void this queue entry?')">
                                                                <i class="fas fa-times me-1"></i>Void
                                                            </button>
                                                        </form>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <?php if ($item['status'] === 'serving'): ?>
                                            <div class="mt-2">
                                                <div class="progress" style="height: 10px;">
                                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-info" style="width: 100%"></div>
                                                </div>
                                                <small class="text-muted">Currently being served</small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center text-muted py-5">
                                    <i class="fas fa-users-slash fa-3x mb-3"></i>
                                    <h5>No students in queue</h5>
                                    <p>All clear! No students waiting.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="col-md-4">
                <!-- Statistics -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Queue Statistics</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <div class="border rounded p-3 bg-light">
                                    <h3 class="text-primary mb-1"><?php echo $stats['total_waiting'] ?? 0; ?></h3>
                                    <small class="text-muted">Waiting</small>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="border rounded p-3 bg-light">
                                    <h3 class="text-info mb-1"><?php echo round($stats['avg_wait_time'] ?? 0); ?></h3>
                                    <small class="text-muted">Avg Wait (min)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="admin-dashboard.php?section=reports" class="btn btn-outline-primary">
                                <i class="fas fa-chart-line me-1"></i>View Reports
                            </a>
                            <button class="btn btn-outline-secondary" onclick="alert('Feature coming soon!')">
                                <i class="fas fa-pause me-1"></i>Pause Service
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Process Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="api/transactions.php">
                    <input type="hidden" name="action" value="complete_payment">
                    <input type="hidden" name="queue_id" id="modalQueueId">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Student</label>
                            <input type="text" class="form-control" id="modalStudentName" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="amount" class="form-label">Amount</label>
                            <input type="number" class="form-control" id="amount" name="amount" step="0.01" placeholder="0.00" required>
                        </div>
                        <div class="mb-3">
                            <label for="paymentType" class="form-label">Payment Type</label>
                            <select class="form-select" id="paymentType" name="payment_type" required>
                                <option value="">Select payment type</option>
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="digital">Digital Payment</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check me-1">Complete Payment</i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/cashier.js"></script>
</body>
</html>