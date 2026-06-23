<?php
// Ultra-simple dashboard with maximum error handling
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Set error handler to catch all errors
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    echo "<h2>PHP Error</h2>";
    echo "<p><strong>Error:</strong> $errstr</p>";
    echo "<p><strong>File:</strong> $errfile (Line $errline)</p>";
    echo "<p><strong>Code:</strong> $errno</p>";
    exit;
});

try {
    // Database connection
    $db_host = 'localhost';
    $db_user = 'u852823366_gulftp_user';
    $db_pass = 'GulfTP@2024';
    $db_name = 'u852823366_gulftp_forms';

    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Set charset
    $conn->set_charset("utf8mb4");

    // Get submissions
    $submissions = array();
    $totalRows = 0;

    $countQuery = "SELECT COUNT(*) as total FROM form_responses";
    $countResult = $conn->query($countQuery);
    
    if ($countResult) {
        $countRow = $countResult->fetch_assoc();
        $totalRows = $countRow['total'];
    }

    $query = "SELECT * FROM form_responses ORDER BY created_at DESC LIMIT 20";
    $result = $conn->query($query);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $submissions[] = $row;
        }
    }

} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - GulfTP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-green: #08664b;
            --primary-orange: #f39223;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sidebar {
            background: linear-gradient(135deg, var(--primary-green), #054834);
            min-height: 100vh;
            padding: 20px;
            color: white;
        }
        
        .sidebar .logo {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 30px;
            color: white;
        }
        
        .sidebar-menu a {
            display: block;
            padding: 12px 15px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 8px;
            transition: all 0.3s ease;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background-color: var(--primary-orange);
            color: white;
        }
        
        .topbar {
            background: white;
            border-bottom: 1px solid #e9ecef;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .main-content {
            padding: 30px;
        }
        
        .card {
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .card-header {
            background: white;
            border-bottom: 1px solid #e9ecef;
            padding: 20px;
        }
        
        .card-header h5 {
            margin: 0;
            color: var(--primary-green);
            font-weight: 600;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .stat-card h6 {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .stat-card .number {
            font-size: 32px;
            font-weight: bold;
            color: var(--primary-green);
        }
        
        .badge-type {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-contact {
            background-color: #e7f3ff;
            color: #0066cc;
        }
        
        .badge-subscription {
            background-color: #e7ffe7;
            color: #00cc00;
        }
        
        .badge-appointment {
            background-color: #fff3e7;
            color: #ff9800;
        }
        
        .btn-view {
            padding: 5px 10px;
            font-size: 12px;
            border-radius: 4px;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        thead {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar" style="width: 250px;">
            <div class="logo">
                <i class="fa-solid fa-gauge"></i> GulfTP Admin
            </div>
            <div class="sidebar-menu">
                <a href="dashboard-simple.php" class="active">
                    <i class="fa-solid fa-chart-line me-2"></i> Dashboard
                </a>
                <a href="submissions.php">
                    <i class="fa-solid fa-inbox me-2"></i> All Submissions
                </a>
                <a href="analytics.php">
                    <i class="fa-solid fa-chart-bar me-2"></i> Analytics
                </a>
                <hr style="background-color: rgba(255,255,255,0.2);">
                <a href="javascript:void(0)">
                    <i class="fa-solid fa-sign-out-alt me-2"></i> Logout
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div style="flex: 1;">
            <!-- Topbar -->
            <div class="topbar">
                <h4 style="margin: 0;">Dashboard</h4>
                <div style="display: flex; gap: 20px; align-items: center;">
                    <span>Welcome, Admin</span>
                    <i class="fa-solid fa-user-circle" style="font-size: 24px;"></i>
                </div>
            </div>

            <!-- Content -->
            <div class="main-content">
                <!-- Success Message -->
                <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
                    <i class="fa-solid fa-check-circle me-2"></i>
                    <strong>✅ Database Connected Successfully!</strong>
                </div>

                <!-- Stats -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <h6>Total Submissions</h6>
                            <div class="number"><?php echo $totalRows; ?></div>
                        </div>
                    </div>
                </div>

                <!-- Recent Submissions -->
                <div class="card">
                    <div class="card-header">
                        <h5>Recent Submissions</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Type</th>
                                    <th>Company</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($submissions) > 0): ?>
                                    <?php foreach ($submissions as $submission): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($submission['first_name'] . ' ' . $submission['last_name']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($submission['email']); ?></td>
                                        <td>
                                            <?php
                                            $type = $submission['form_type'];
                                            $badgeClass = 'badge-contact';
                                            if ($type === 'Subscription') $badgeClass = 'badge-subscription';
                                            elseif ($type === 'Appointment') $badgeClass = 'badge-appointment';
                                            ?>
                                            <span class="badge-type <?php echo $badgeClass; ?>"><?php echo $type; ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($submission['company']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($submission['created_at'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <i class="fa-solid fa-inbox"></i> No submissions yet
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$conn->close();
?>
