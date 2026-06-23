<?php
require_once '../config.php';

// Simple authentication check
session_start();

// Check if user is logged in, if not redirect to login
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

// Connect to database
$conn = getDBConnection();
if (!$conn) {
    die('Database connection failed');
}

// Get analytics data
$analytics = [];

// 1. Total submissions
$result = $conn->query("SELECT COUNT(*) as count FROM form_responses");
$analytics['total'] = $result->fetch_assoc()['count'];

// 2. Submissions by type
$result = $conn->query("SELECT form_type, COUNT(*) as count FROM form_responses GROUP BY form_type");
$analytics['by_type'] = [];
while ($row = $result->fetch_assoc()) {
    $analytics['by_type'][$row['form_type']] = $row['count'];
}

// 3. Submissions this month
$result = $conn->query("SELECT COUNT(*) as count FROM form_responses WHERE MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())");
$analytics['this_month'] = $result->fetch_assoc()['count'];

// 4. Submissions this week
$result = $conn->query("SELECT COUNT(*) as count FROM form_responses WHERE WEEK(created_at) = WEEK(NOW()) AND YEAR(created_at) = YEAR(NOW())");
$analytics['this_week'] = $result->fetch_assoc()['count'];

// 5. Submissions today
$result = $conn->query("SELECT COUNT(*) as count FROM form_responses WHERE DATE(created_at) = CURDATE()");
$analytics['today'] = $result->fetch_assoc()['count'];

// 6. Daily breakdown for last 7 days
$result = $conn->query("SELECT DATE(created_at) as date, COUNT(*) as count FROM form_responses WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY DATE(created_at) ORDER BY date");
$analytics['daily'] = [];
while ($row = $result->fetch_assoc()) {
    $analytics['daily'][$row['date']] = $row['count'];
}

// 7. Average submission time (if multiple per day)
$result = $conn->query("SELECT COUNT(*) as count, DATE(created_at) as date FROM form_responses GROUP BY DATE(created_at) HAVING count > 0");
$total_days = $result->num_rows;
$analytics['avg_per_day'] = $total_days > 0 ? round($analytics['total'] / $total_days, 2) : 0;

// 8. Most recent submissions (last 5)
$result = $conn->query("SELECT id, first_name, last_name, email, form_type, created_at FROM form_responses ORDER BY created_at DESC LIMIT 5");
$analytics['recent'] = [];
while ($row = $result->fetch_assoc()) {
    $analytics['recent'][] = $row;
}

// 9. Top companies submitting
$result = $conn->query("SELECT company, COUNT(*) as count FROM form_responses WHERE company != '' GROUP BY company ORDER BY count DESC LIMIT 10");
$analytics['top_companies'] = [];
while ($row = $result->fetch_assoc()) {
    $analytics['top_companies'][] = $row;
}

// 10. Contact rate by service (for Contact forms)
$result = $conn->query("SELECT service, COUNT(*) as count FROM form_responses WHERE service != '' AND service IS NOT NULL GROUP BY service ORDER BY count DESC");
$analytics['services'] = [];
while ($row = $result->fetch_assoc()) {
    $analytics['services'][] = $row;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - GulfTP Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
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
        
        .topbar-right {
            display: flex;
            gap: 20px;
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
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-card .number {
            font-size: 32px;
            font-weight: bold;
            color: var(--primary-green);
        }
        
        .stat-card.orange .number {
            color: var(--primary-orange);
        }
        
        .stat-card.blue .number {
            color: #0066cc;
        }
        
        .stat-card.green .number {
            color: #00cc00;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 30px;
        }
        
        .table-responsive {
            background: white;
            border-radius: 8px;
            padding: 20px;
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
        
        .badge-payment {
            background-color: #ffe7f3;
            color: #cc0066;
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
                <a href="dashboard.php">
                    <i class="fa-solid fa-chart-line me-2"></i> Dashboard
                </a>
                <a href="submissions.php">
                    <i class="fa-solid fa-inbox me-2"></i> All Submissions
                </a>
                <a href="analytics.php" class="active">
                    <i class="fa-solid fa-chart-bar me-2"></i> Analytics
                </a>
                <hr style="background-color: rgba(255,255,255,0.2);">
                <a href="logout.php">
                    <i class="fa-solid fa-sign-out-alt me-2"></i> Logout
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div style="flex: 1;">
            <!-- Topbar -->
            <div class="topbar">
                <h4 style="margin: 0;">Analytics & Reports</h4>
                <div class="topbar-right">
                    <span>Welcome, Admin</span>
                    <i class="fa-solid fa-user-circle" style="font-size: 24px;"></i>
                </div>
            </div>

            <!-- Content -->
            <div class="main-content">
                <!-- Key Statistics -->
                <h5 class="mb-4" style="color: var(--primary-green); font-weight: 600;">Key Statistics</h5>
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <h6>Total Submissions</h6>
                            <div class="number"><?php echo $analytics['total']; ?></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card orange">
                            <h6>This Month</h6>
                            <div class="number"><?php echo $analytics['this_month']; ?></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card blue">
                            <h6>This Week</h6>
                            <div class="number"><?php echo $analytics['this_week']; ?></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card green">
                            <h6>Today</h6>
                            <div class="number"><?php echo $analytics['today']; ?></div>
                        </div>
                    </div>
                </div>

                <!-- Average & Rate -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="stat-card">
                            <h6>Average Per Day</h6>
                            <div class="number"><?php echo $analytics['avg_per_day']; ?></div>
                        </div>
                    </div>
                </div>

                <!-- Form Type Distribution Chart -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fa-solid fa-pie-chart me-2"></i>Submissions by Form Type</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="formTypeChart"></canvas>
                        </div>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Form Type</th>
                                    <th>Count</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($analytics['by_type'] as $type => $count): ?>
                                <tr>
                                    <td>
                                        <?php
                                        $badgeClass = 'badge-contact';
                                        if ($type === 'Subscription') $badgeClass = 'badge-subscription';
                                        elseif ($type === 'Appointment') $badgeClass = 'badge-appointment';
                                        elseif ($type === 'Payment') $badgeClass = 'badge-payment';
                                        ?>
                                        <span class="badge-type <?php echo $badgeClass; ?>"><?php echo $type; ?></span>
                                    </td>
                                    <td><strong><?php echo $count; ?></strong></td>
                                    <td><?php echo $analytics['total'] > 0 ? round(($count / $analytics['total']) * 100, 1) : 0; ?>%</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Daily Submissions Chart -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fa-solid fa-line-chart me-2"></i>Last 7 Days Activity</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="dailyChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Services Breakdown -->
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fa-solid fa-list me-2"></i>Top Services Requested</h5>
                            </div>
                            <div class="card-body">
                                <?php if (count($analytics['services']) > 0): ?>
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Service</th>
                                            <th>Requests</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($analytics['services'] as $service): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($service['service']); ?></td>
                                            <td>
                                                <span class="badge bg-primary"><?php echo $service['count']; ?></span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <?php else: ?>
                                <p class="text-muted text-center py-4">No service data available</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Top Companies -->
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fa-solid fa-building me-2"></i>Top Companies</h5>
                            </div>
                            <div class="card-body">
                                <?php if (count($analytics['top_companies']) > 0): ?>
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Company</th>
                                            <th>Submissions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($analytics['top_companies'] as $company): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($company['company']); ?></td>
                                            <td>
                                                <span class="badge bg-success"><?php echo $company['count']; ?></span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <?php else: ?>
                                <p class="text-muted text-center py-4">No company data available</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Submissions -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5><i class="fa-solid fa-recent me-2"></i>Recent Submissions</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background-color: #f8f9fa;">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Type</th>
                                    <th>Date & Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($analytics['recent']) > 0): ?>
                                    <?php foreach ($analytics['recent'] as $submission): ?>
                                    <tr>
                                        <td>#<?php echo $submission['id']; ?></td>
                                        <td><?php echo htmlspecialchars($submission['first_name'] . ' ' . $submission['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($submission['email']); ?></td>
                                        <td>
                                            <?php
                                            $type = $submission['form_type'];
                                            $badgeClass = 'badge-contact';
                                            if ($type === 'Subscription') $badgeClass = 'badge-subscription';
                                            elseif ($type === 'Appointment') $badgeClass = 'badge-appointment';
                                            elseif ($type === 'Payment') $badgeClass = 'badge-payment';
                                            ?>
                                            <span class="badge-type <?php echo $badgeClass; ?>"><?php echo $type; ?></span>
                                        </td>
                                        <td><?php echo date('M d, Y H:i', strtotime($submission['created_at'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">No recent submissions</td>
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
    <script>
        // Form Type Distribution Chart
        const formTypeData = {
            <?php foreach ($analytics['by_type'] as $type => $count): ?>
            '<?php echo $type; ?>': <?php echo $count; ?>,
            <?php endforeach; ?>
        };

        const formTypeChart = new Chart(document.getElementById('formTypeChart'), {
            type: 'doughnut',
            data: {
                labels: Object.keys(formTypeData),
                datasets: [{
                    data: Object.values(formTypeData),
                    backgroundColor: [
                        '#0066cc',
                        '#00cc00',
                        '#ff9800',
                        '#cc0066'
                    ],
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Daily Submissions Chart
        const dailyData = {
            <?php foreach ($analytics['daily'] as $date => $count): ?>
            '<?php echo date('M d', strtotime($date)); ?>': <?php echo $count; ?>,
            <?php endforeach; ?>
        };

        const dailyChart = new Chart(document.getElementById('dailyChart'), {
            type: 'line',
            data: {
                labels: Object.keys(dailyData),
                datasets: [{
                    label: 'Submissions',
                    data: Object.values(dailyData),
                    borderColor: '#08664b',
                    backgroundColor: 'rgba(8, 102, 75, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 5,
                    pointBackgroundColor: '#08664b',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>
