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
        
        .serving {
            background-color: #d1ecf1 !important;
            border-left: 4px solid #0dcaf0 !important;
        }

        .waiting {
            background-color: #fff3cd !important;
            border-left: 4px solid #ffc107 !important;
        }

        .served {
            background-color: #d4edda !important;
            border-left: 4px solid #198754 !important;
        }

        .voided {
            background-color: #f8d7da !important;
            border-left: 4px solid #dc3545 !important;
        }

        .queue-item {
            transition: all 0.3s ease;
            border: 1px solid #dee2e6;
        }

        .queue-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .dashboard-card {
            transition: transform 0.2s;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container-fluid {
                padding: 10px;
            }
            
            .queue-number {
                font-size: 1.5rem;
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
                        <!-- Display Messages -->
                        <div id="successAlert" class="alert alert-success d-none">Payment slip submitted successfully!</div>
                        <div id="errorAlert" class="alert alert-danger d-none">Please fill all required fields.</div>

                        <!-- Queue Status -->
                        <div id="student-queue-status" class="text-center mb-4">
                            <div class="alert alert-secondary d-none" id="noQueueAlert">
                                <h4>No Active Queue Number</h4>
                                <p class="mb-0">Fill out the payment slip below to get a queue number.</p>
                            </div>
                            
                            <div class="alert alert-info d-none" id="waitingQueueAlert">
                                <h4>Your Queue Number: <span class="queue-number">#A-025</span></h4>
                                <p class="mb-1">Status: <strong class="text-uppercase">WAITING</strong></p>
                                <p class="mb-0">Students ahead of you: <strong>3</strong></p>
                            </div>
                            
                            <div class="alert alert-warning d-none" id="servingQueueAlert">
                                <h4>Your Queue Number: <span class="queue-number">#A-022</span></h4>
                                <p class="mb-1">Status: <strong class="text-uppercase">SERVING</strong></p>
                                <div class="mt-2">
                                    <div class="alert alert-warning mb-0">
                                        <i class="fas fa-bell me-2"></i>
                                        You're being served! Please proceed to the cashier.
                                    </div>
                                </div>
                            </div>
                        </div>
                        
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
                                    
                                    <form id="paymentForm">
                                        <div class="form-field">
                                            <label for="name">NAME:</label>
                                            <input type="text" class="form-control" id="name" value="John Doe" readonly>
                                        </div>
                                        
                                        <div class="form-field">
                                            <label for="student_id">ID NO:</label>
                                            <input type="text" class="form-control" id="student_id" value="2023-00123" readonly>
                                        </div>
                                        
                                        <div class="form-field">
                                            <label for="course_year">COURSE & YEAR:</label>
                                            <input type="text" class="form-control" id="course_year" value="BS Computer Science - 3rd Year" readonly>
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
                                            <input type="text" class="form-control" id="date" value="November 15, 2023" readonly>
                                        </div>
                                        
                                        <div class="text-center mt-4">
                                            <button type="button" class="btn btn-success btn-lg" onclick="submitPaymentSlip()">
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
                        
                        <!-- Payment History -->
                        <div class="card dashboard-card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Payment History</h5>
                            </div>
                            <div class="card-body">
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
                                            <tr>
                                                <td>Nov 10, 2023 14:30</td>
                                                <td>₱5,250.00</td>
                                                <td><span class="badge bg-info">Tuition</span></td>
                                                <td><span class="badge bg-success">Completed</span></td>
                                                <td>Maria Santos</td>
                                                <td>#A-018</td>
                                            </tr>
                                            <tr>
                                                <td>Oct 25, 2023 10:15</td>
                                                <td>₱350.00</td>
                                                <td><span class="badge bg-info">Transcript</span></td>
                                                <td><span class="badge bg-success">Completed</span></td>
                                                <td>Juan Dela Cruz</td>
                                                <td>#A-015</td>
                                            </tr>
                                            <tr>
                                                <td>Sep 15, 2023 09:45</td>
                                                <td>₱12,500.00</td>
                                                <td><span class="badge bg-info">Tuition</span></td>
                                                <td><span class="badge bg-success">Completed</span></td>
                                                <td>Maria Santos</td>
                                                <td>#A-012</td>
                                            </tr>
                                            <tr>
                                                <td>Aug 05, 2023 13:20</td>
                                                <td>₱750.00</td>
                                                <td><span class="badge bg-info">Library</span></td>
                                                <td><span class="badge bg-success">Completed</span></td>
                                                <td>Juan Dela Cruz</td>
                                                <td>#A-008</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>