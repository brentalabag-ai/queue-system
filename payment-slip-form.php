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
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Student Dashboard - SLC College</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        <style>
            .student-view {
                min-height: 100vh;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            }

            .queue-number {
                font-size: 2rem;
                font-weight: bold;
                color: #0d6efd;
            }

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

            .dashboard-card {
                transition: transform 0.2s;
            }

            .dashboard-card:hover {
                transform: translateY(-5px);
            }

            /* Custom modal implementation to avoid Bootstrap conflicts */
            .custom-modal {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 1050;
                align-items: center;
                justify-content: center;
            }

            .custom-modal.show {
                display: flex !important;
            }

            .custom-modal-content {
                background: white;
                border-radius: 0.3rem;
                width: 90%;
                max-width: 500px;
                animation: modalFadeIn 0.3s ease;
            }

            @keyframes modalFadeIn {
                from {
                    opacity: 0;
                    transform: translateY(-50px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        </style>
    </head>

    <body class="student-view">

        <!-- Navigation Bar -->
        <nav class="navbar navbar-dark bg-dark bg-opacity-50">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">
                    <i class="fas fa-university me-2"></i>SLC College Student Portal
                </a>
                <div class="navbar-nav ms-auto">
                    <span class="navbar-text me-3">Welcome, John Doe</span>
                    <a class="btn btn-outline-light btn-sm" href="#" onclick="logout()">Logout</a>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="card shadow dashboard-card">
                        <div class="card-header bg-primary text-white text-center">
                            <h3 class="mb-0">Cashier Queue System</h3>
                        </div>
                        <div class="card-body p-4">

                            <!-- Payment Slip Form -->
                            <div class="card mb-4 dashboard-card" id="paymentSlipCard">
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
                                            <!--  action="api/queue.php" -->
                                        <form id="paymentForm" method="POST" action="student-dashboard.php">
                                        <input type="hidden" name="action" value="submit_payment_slip">

                                            <div class="form-field">
                                                <label for="name">NAME:</label>
                                                <input type="text" class="form-control" id="name" value="<?php echo $student['name']; ?>">
                                            </div>

                                            <div class="form-field">
                                                <label for="student_id">ID NO:</label>
                                                <input type="text" class="form-control" id="student_id" value="<?php echo $student['student_id']; ?>" readonly>
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
                                                <div class="d-grid gap-2 mt-4">
                                                    <!-- Go Back redirects home -->
                                                    <button class="btn btn-secondary btn-lg" type="button" onclick="window.location.href='index.html'">
                                                        <i class="fas fa-home me-2"></i> Go Back
                                                    </button>
                                                    <!-- Confirm opens modal -->
                                                    <button class="btn btn-success btn-lg" type="submit" id="confirmBtn">
                                                        <i class="fas fa-ticket-alt me-2"></i>Confirm
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                        <!-- Buttons -->
                                        
                                    </div>
                                </div>
                            </div>

                            <!-- Custom Modal (replacing Bootstrap modal) -->
                            <div class="custom-modal" id="customModal">
                                <div class="custom-modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Confirm Payment</h5>
                                        <button type="button" class="btn-close" onclick="closeModal()"></button>
                                    </div>
                                    <div class="modal-body">
                                        Are you sure you want to submit this payment slip?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                                        <button type="button" class="btn btn-primary" id="modalSubmitBtn">Submit</button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // Enable "Others" text field when checkbox is checked
            const othersCheckbox = document.getElementById('others');
            const otherSpecify = document.getElementById('otherSpecify');
            const customModal = document.getElementById('customModal');

            othersCheckbox.addEventListener('change', () => {
                otherSpecify.disabled = !othersCheckbox.checked;
                if (!othersCheckbox.checked) {
                    otherSpecify.value = '';
                }
            });

            // Simple modal functions
            function showModal() {
                customModal.classList.add('show');
                document.body.style.overflow = 'hidden'; // Prevent background scrolling
            }

            function closeModal() {
                customModal.classList.remove('show');
                document.body.style.overflow = ''; // Restore scrolling
            }

            // Confirm button click handler
            document.getElementById('confirmBtn').addEventListener('click', function(e) {
                e.preventDefault();

                // Validate required fields
                const amount = document.getElementById('amount').value;
                const checkedPayments = document.querySelectorAll('input[name="payment_for[]"]:checked');

                if (!amount || amount <= 0) {
                    alert('Please enter a valid amount');
                    return;
                }

                if (checkedPayments.length === 0) {
                    alert('Please select at least one payment option');
                    return;
                }

                // Check if "Others" is checked but not specified
                if (othersCheckbox.checked && !otherSpecify.value.trim()) {
                    alert('Please specify the "Others" payment option');
                    return;
                }

                // Show modal if validation passes
                showModal();
            });

            // Modal submit handler
            document.getElementById('modalSubmitBtn').addEventListener('click', function() {
                // Get form data
                const amount = document.getElementById('amount').value;
                const paymentFor = [];
                document.querySelectorAll('input[name="payment_for[]"]:checked').forEach(checkbox => {
                    if (checkbox.value === 'others') {
                        paymentFor.push('Others: ' + document.getElementById('otherSpecify').value);
                    } else {
                        paymentFor.push(checkbox.value);
                    }
                });

                // Show success message
                alert('Payment submitted successfully!\nAmount: ₱' + amount + '\nFor: ' + paymentFor.join(', '));

                // Close modal
                closeModal();

                // Reset form
                document.getElementById('paymentForm').reset();
                document.getElementById('otherSpecify').disabled = true;
            });

            // Close modal when clicking outside
            customModal.addEventListener('click', function(e) {
                if (e.target === customModal) {
                    closeModal();
                }
            });

            // Close modal with Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && customModal.classList.contains('show')) {
                    closeModal();
                }
            });

            // Logout function
            function logout() {
                if (confirm('Are you sure you want to logout?')) {
                    alert('Logged out successfully');
                }
            }

        </script>
    </body>
</html>