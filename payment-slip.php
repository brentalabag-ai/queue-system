<?php
require_once "api/payment-slip-B.php"
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Slip - SLC College</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/slip-style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark bg-opacity-50">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-university me-2"></i>SLC College Student Portal
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">Welcome, <?php echo htmlspecialchars($student['name']); ?></span>
                <a class="btn btn-outline-light btn-sm me-2" href="student-dashboard.php">
                    <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="payment-slip-container">
            <div class="payment-slip">
                <div class="header">
                    <h3 class="mb-1">Saint Louis College</h3>
                    <p class="mb-1">City of San Fernando, 2500 La Union</p>
                    <h4 class="mb-0">PAYMENT SLIP</h4>
                </div>
                
                <form id="paymentForm" method="POST" action="api/queue.php">
                    <input type="hidden" name="action" value="request_queue">
                    <input type="hidden" name="student_id" value="<?php echo $student['student_id']; ?>">
                    
                    <div class="form-field">
                        <label>NAME:</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($student['name']); ?>" readonly>
                    </div>
                    
                    <div class="form-field">
                        <label>ID NO:</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($student['student_id']); ?>" readonly>
                    </div>
                    
                    <div class="form-field">
                        <label>COURSE & YEAR:</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($student['course'] ?? 'Not specified') . ' - ' . htmlspecialchars($student['year_level'] ?? 'Not specified'); ?>" readonly>
                    </div>
                    
                    <div class="form-field">
                        <label for="amount">AMOUNT: <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="amount" name="amount" step="0.01" placeholder="0.00" required>
                    </div>
                    
                    <div class="form-field">
                        <label>IN PAYMENT OF: <span class="text-danger">*</span></label>
                        <div class="checkbox-group">
                            <div class="form-check">
                                <input class="form-check-input payment-type" type="checkbox" name="payment_for[]" value="tuition" id="tuition">
                                <label class="form-check-label" for="tuition">Tuition Fee</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input payment-type" type="checkbox" name="payment_for[]" value="transcript" id="transcript">
                                <label class="form-check-label" for="transcript">Transcript</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input payment-type" type="checkbox" name="payment_for[]" value="overdue" id="overdue">
                                <label class="form-check-label" for="overdue">Overdue</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input payment-type" type="checkbox" name="payment_for[]" value="others" id="others">
                                <label class="form-check-label" for="others">Others (Please specify)</label>
                            </div>
                            <div class="other-specify">
                                <input type="text" class="form-control mt-1" name="other_specify" placeholder="Specify other payment" id="otherSpecify" disabled>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-field">
                        <label>DATE:</label>
                        <input type="text" class="form-control" value="<?php echo date('F d, Y'); ?>" readonly>
                    </div>
                    
                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-ticket-alt me-2"></i>Submit & Get Queue Number
                        </button>
                        <a href="student-dashboard.php" class="btn btn-secondary btn-lg ms-2">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </div>
                </form>
                
                <div class="reference-section">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <strong>Reference Code</strong><br>
                            FM-TREA-001
                        </div>
                        <div class="col-md-4">
                            <strong>Revision No.</strong><br>
                            0
                        </div>
                        <div class="col-md-4">
                            <strong>Effectivity Date</strong><br>
                            August 1, 2019
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/slip.js"></script>
</body>
</html>