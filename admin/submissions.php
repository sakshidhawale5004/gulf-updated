<?php
require_once '../config.php';

// Simple authentication check
session_start();

// Check if user is logged in, if not redirect to login
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

// Get filter parameters
$filterType = isset($_GET['type']) ? $_GET['type'] : 'all';
$filterStatus = isset($_GET['status']) ? $_GET['status'] : 'all';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 50; // More items per page for full submissions list
$offset = ($page - 1) * $perPage;

// Connect to database
$conn = getDBConnection();
if (!$conn) {
    die('Database connection failed');
}

// Build query
$whereClause = "1=1";
if ($filterType !== 'all') {
    $whereClause .= " AND form_type = '" . $conn->real_escape_string($filterType) . "'";
}

// Get total count
$countQuery = "SELECT COUNT(*) as total FROM form_responses WHERE $whereClause";
$countResult = $conn->query($countQuery);
$totalRows = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $perPage);

// Get submissions
$query = "SELECT * FROM form_responses 
          WHERE $whereClause 
          ORDER BY created_at DESC 
          LIMIT $offset, $perPage";

$result = $conn->query($query);
$submissions = [];
while ($row = $result->fetch_assoc()) {
    $submissions[] = $row;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Submissions - GulfTP Admin</title>
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
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-header h5 {
            margin: 0;
            color: var(--primary-green);
            font-weight: 600;
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
            background-color: #f3e5f5;
            color: #7b1fa2;
        }
        .badge-demo {
            background-color: #e3f2fd;
            color: #0288d1;
        }
        
        .badge-payment {
            background-color: #ffe7f3;
            color: #cc0066;
        }
        
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .btn-view {
            padding: 5px 10px;
            font-size: 12px;
            border-radius: 4px;
        }
        
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .pagination {
            justify-content: center;
            margin-top: 20px;
        }
        
        .export-btn {
            background-color: var(--primary-green);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s ease;
        }
        
        .export-btn:hover {
            background-color: #054834;
            color: white;
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
                <a href="submissions.php" class="active">
                    <i class="fa-solid fa-inbox me-2"></i> All Submissions
                </a>
                <a href="analytics.php">
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
                <h4 style="margin: 0;">All Submissions</h4>
                <div class="topbar-right">
                    <span>Welcome, Admin</span>
                    <i class="fa-solid fa-user-circle" style="font-size: 24px;"></i>
                </div>
            </div>

            <!-- Content -->
            <div class="main-content">
                <!-- Filters -->
                <div class="filter-section">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Filter by Type</label>
                            <select name="type" class="form-select">
                                <option value="all">All Types</option>
                                <option value="Contact" <?php echo $filterType === 'Contact' ? 'selected' : ''; ?>>Contact / Service Inquiry</option>
                                <option value="Subscription" <?php echo $filterType === 'Subscription' ? 'selected' : ''; ?>>Subscription Request</option>
                                <option value="Appointment" <?php echo $filterType === 'Appointment' ? 'selected' : ''; ?>>Book Appointment</option>
                                <option value="Demo" <?php echo $filterType === 'Demo' ? 'selected' : ''; ?>>Book Demo</option>
                                <option value="Search" <?php echo $filterType === 'Search' ? 'selected' : ''; ?>>Update Search</option>
                                <option value="Payment" <?php echo $filterType === 'Payment' ? 'selected' : ''; ?>>Payment</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fa-solid fa-filter me-2"></i> Filter
                            </button>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <a href="submissions.php" class="btn btn-secondary w-100">
                                <i class="fa-solid fa-redo me-2"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>

                <!-- All Submissions Table -->
                <div class="card">
                    <div class="card-header">
                        <h5>All Form Submissions (<?php echo $totalRows; ?> total)</h5>
                        <span class="badge bg-primary"><?php echo count($submissions); ?> entries on this page</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background-color: #f8f9fa;">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Type</th>
                                    <th>Company</th>
                                    <th>Phone</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($submissions) > 0): ?>
                                    <?php foreach ($submissions as $submission): ?>
                                    <tr>
                                        <td>
                                            <strong>#<?php echo $submission['id']; ?></strong>
                                        </td>
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
                                            elseif ($type === 'Demo') $badgeClass = 'badge-demo';
                                            elseif ($type === 'Payment') $badgeClass = 'badge-payment';
                                            ?>
                                            <span class="badge-type <?php echo $badgeClass; ?>"><?php echo $type; ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($submission['company']); ?></td>
                                        <td><?php echo htmlspecialchars($submission['phone']); ?></td>
                                        <td><?php echo date('M d, Y H:i', strtotime($submission['created_at'])); ?></td>
                                        <td>
                                            <a href="view-submission.php?id=<?php echo $submission['id']; ?>" class="btn btn-sm btn-primary btn-view">
                                                <i class="fa-solid fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="fa-solid fa-inbox"></i> No submissions found
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="submissions.php?page=<?php echo $i; ?>&type=<?php echo $filterType; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>

                <!-- Summary Stats -->
                <div class="row mt-5">
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h6 class="text-muted">Total Submissions</h6>
                                <h3 class="text-primary"><?php echo $totalRows; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h6 class="text-muted">This Page</h6>
                                <h3 class="text-info"><?php echo count($submissions); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h6 class="text-muted">Total Pages</h6>
                                <h3 class="text-success"><?php echo $totalPages; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h6 class="text-muted">Current Page</h6>
                                <h3 class="text-warning"><?php echo $page; ?></h3>
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
