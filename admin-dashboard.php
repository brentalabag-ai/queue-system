<?php
    require_once "api/admin-dashboard-B.php"
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SLC College</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-university me-2"></i>SLC College - Admin Dashboard
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">Welcome, <?php echo $user['name']; ?></span>
                <a class="btn btn-outline-light btn-sm" href="api/auth.php?action=logout">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <div class="list-group">
                    <a href="admin-dashboard.php?section=dashboard" class="list-group-item list-group-item-action <?php echo $section === 'dashboard' ? 'active' : ''; ?>">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                    <a href="admin-dashboard.php?section=users" class="list-group-item list-group-item-action <?php echo $section === 'users' ? 'active' : ''; ?>">
                        <i class="fas fa-users me-2"></i>User Management
                    </a>
                    <a href="admin-dashboard.php?section=reports" class="list-group-item list-group-item-action <?php echo $section === 'reports' ? 'active' : ''; ?>">
                        <i class="fas fa-chart-bar me-2"></i>Reports
                    </a>
                    <a href="cashier-dashboard.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-cash-register me-2"></i>Cashier View
                    </a>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9">
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

                <?php
                switch($section) {
                    case 'dashboard':
                        include 'admin-sections/dashboard.php';
                        break;
                    case 'users':
                        include 'admin-sections/users.php';
                        break;
                    case 'reports':
                        include 'admin-sections/reports.php';
                        break;
                    default:
                        include 'admin-sections/dashboard.php';
                }
                ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>