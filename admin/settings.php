<?php
require_once '../config.php';

// Simple authentication check
session_start();

// Check if user is logged in, if not redirect to login
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if ($action === 'change_password') {
        $current_password = isset($_POST['current_password']) ? $_POST['current_password'] : '';
        $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
        $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
        
        // Verify current password (demo: admin@2024)
        if ($current_password === 'admin@2024') {
            if ($new_password === $confirm_password && !empty($new_password)) {
                if (strlen($new_password) < 6) {
                    $message = 'Password must be at least 6 characters long';
                    $message_type = 'danger';
                } else {
                    // In production, you would save this to database
                    // For now, just show success message
                    $message = 'Password changed successfully! (Note: This change is not persisted in demo mode)';
                    $message_type = 'success';
                }
            } else {
                $message = 'New passwords do not match or are empty';
                $message_type = 'danger';
            }
        } else {
            $message = 'Current password is incorrect';
            $message_type = 'danger';
        }
    }
}

// Get database statistics
$conn = getDBConnection();
if ($conn) {
    $result = $conn->query("SELECT COUNT(*) as count FROM form_responses");
    $db_submissions = $result->fetch_assoc()['count'];
    
    $result = $conn->query("SELECT table_name, round(((data_length + index_length) / 1024 / 1024), 2) as size_mb FROM information_schema.TABLES WHERE table_schema = DATABASE() AND table_name = 'form_responses'");
    $db_size = $result->fetch_assoc();
    
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - GulfTP Admin</title>
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
        }
        
        .card-header h5 {
            margin: 0;
            color: var(--primary-green);
            font-weight: 600;
        }
        
        .settings-section {
            margin-bottom: 30px;
        }
        
        .settings-section h6 {
            color: var(--primary-green);
            font-weight: 600;
            margin-bottom: 15px;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 1px;
        }
        
        .setting-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 6px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .setting-item.highlight {
            background: #f0f7ff;
            border-left: 4px solid #0066cc;
        }
        
        .form-control, .form-select {
            border-radius: 6px;
            border: 1px solid #e9ecef;
        }
        
        .form-control:focus {
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 0.2rem rgba(243, 146, 35, 0.25);
        }
        
        .btn-save {
            background-color: var(--primary-green);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-save:hover {
            background-color: #054834;
            color: white;
        }
        
        .info-box {
            background: white;
            padding: 15px;
            border-left: 4px solid var(--primary-orange);
            border-radius: 4px;
            margin-bottom: 15px;
        }
        
        .info-box strong {
            color: var(--primary-green);
        }
        
        .alert {
            border-radius: 6px;
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
                <a href="analytics.php">
                    <i class="fa-solid fa-chart-bar me-2"></i> Analytics
                </a>
                <a href="settings.php" class="active">
                    <i class="fa-solid fa-cog me-2"></i> Settings
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
                <h4 style="margin: 0;">Settings</h4>
                <div class="topbar-right">
                    <span>Welcome, Admin</span>
                    <i class="fa-solid fa-user-circle" style="font-size: 24px;"></i>
                </div>
            </div>

            <!-- Content -->
            <div class="main-content">
                <!-- Success/Error Messages -->
                <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                    <i class="fa-solid fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <!-- System Information -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fa-solid fa-info-circle me-2"></i>System Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="settings-section">
                            <h6>Application Details</h6>
                            <div class="info-box">
                                <strong>Application Name:</strong> GulfTP
                            </div>
                            <div class="info-box">
                                <strong>Version:</strong> 1.0.0
                            </div>
                            <div class="info-box">
                                <strong>Environment:</strong> <?php echo defined('APP_ENV') ? APP_ENV : 'Production'; ?>
                            </div>
                            <div class="info-box">
                                <strong>URL:</strong> <?php echo defined('APP_URL') ? APP_URL : 'https://gulftp.com'; ?>
                            </div>
                        </div>

                        <div class="settings-section">
                            <h6>Server Information</h6>
                            <div class="info-box">
                                <strong>PHP Version:</strong> <?php echo phpversion(); ?>
                            </div>
                            <div class="info-box">
                                <strong>Server OS:</strong> <?php echo php_uname('s'); ?>
                            </div>
                            <div class="info-box">
                                <strong>Current Date/Time:</strong> <?php echo date('Y-m-d H:i:s'); ?>
                            </div>
                        </div>

                        <div class="settings-section">
                            <h6>Database Information</h6>
                            <div class="info-box">
                                <strong>Total Submissions:</strong> <?php echo isset($db_submissions) ? $db_submissions : 'N/A'; ?>
                            </div>
                            <div class="info-box">
                                <strong>Database Size:</strong> 
                                <?php 
                                if (isset($db_size) && $db_size['size_mb']) {
                                    echo $db_size['size_mb'] . ' MB';
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Account Settings -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fa-solid fa-lock me-2"></i>Account Settings</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="change_password">
                            
                            <div class="settings-section">
                                <h6>Change Admin Password</h6>
                                
                                <div class="mb-3">
                                    <label class="form-label">Current Password</label>
                                    <input type="password" class="form-control" name="current_password" required placeholder="Enter your current password">
                                    <small class="text-muted">Demo: admin@2024</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">New Password</label>
                                    <input type="password" class="form-control" name="new_password" required placeholder="Enter new password (min 6 characters)">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" name="confirm_password" required placeholder="Confirm new password">
                                </div>
                                
                                <button type="submit" class="btn btn-save">
                                    <i class="fa-solid fa-save me-2"></i>Change Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Email Configuration -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fa-solid fa-envelope me-2"></i>Email Configuration</h5>
                    </div>
                    <div class="card-body">
                        <div class="settings-section">
                            <h6>Email Settings</h6>
                            <div class="info-box highlight">
                                <strong>Admin Email:</strong> admin@gulftp.com
                            </div>
                            <div class="info-box highlight">
                                <strong>Noreply Email:</strong> noreply@gulftp.com
                            </div>
                            <div class="info-box highlight">
                                <strong>Support Phone:</strong> +971 581711600
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fa-solid fa-info-circle me-2"></i>
                            Email settings are configured in the <code>.env</code> file. To change email addresses, update the environment configuration.
                        </div>
                    </div>
                </div>

                <!-- Payment Configuration -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fa-solid fa-credit-card me-2"></i>Payment Configuration</h5>
                    </div>
                    <div class="card-body">
                        <div class="settings-section">
                            <h6>Payment Settings</h6>
                            <div class="info-box highlight">
                                <strong>Payment Amount:</strong> 750 AED
                            </div>
                            <div class="info-box highlight">
                                <strong>Currency:</strong> AED (UAE Dirhams)
                            </div>
                            <div class="info-box">
                                <strong>Payment Gateway:</strong> Stripe
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fa-solid fa-info-circle me-2"></i>
                            Payment settings including Stripe keys are configured in the <code>.env</code> file. Never expose sensitive keys in code.
                        </div>
                    </div>
                </div>

                <!-- Database Maintenance -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fa-solid fa-database me-2"></i>Database Maintenance</h5>
                    </div>
                    <div class="card-body">
                        <div class="settings-section">
                            <h6>Maintenance Tasks</h6>
                            
                            <div class="info-box">
                                <div>
                                    <strong>Backup Database</strong>
                                    <p class="text-muted small mb-2">Download a backup of your submissions</p>
                                </div>
                                <button class="btn btn-sm btn-outline-primary" disabled>
                                    <i class="fa-solid fa-download me-1"></i>Coming Soon
                                </button>
                            </div>

                            <div class="info-box">
                                <div>
                                    <strong>Export to CSV</strong>
                                    <p class="text-muted small mb-2">Export all submissions as CSV file</p>
                                </div>
                                <button class="btn btn-sm btn-outline-primary" disabled>
                                    <i class="fa-solid fa-file-csv me-1"></i>Coming Soon
                                </button>
                            </div>

                            <div class="info-box">
                                <div>
                                    <strong>Clear Old Submissions</strong>
                                    <p class="text-muted small mb-2">Delete submissions older than 90 days</p>
                                </div>
                                <button class="btn btn-sm btn-outline-danger" disabled>
                                    <i class="fa-solid fa-trash me-1"></i>Coming Soon
                                </button>
                            </div>
                        </div>

                        <div class="alert alert-warning">
                            <i class="fa-solid fa-exclamation-triangle me-2"></i>
                            <strong>Note:</strong> Database maintenance features are coming soon. Please perform manual backups regularly.
                        </div>
                    </div>
                </div>

                <!-- Admin Features -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fa-solid fa-star me-2"></i>Available Features</h5>
                    </div>
                    <div class="card-body">
                        <div class="settings-section">
                            <h6>Dashboard Features</h6>
                            <ul style="list-style: none; padding: 0;">
                                <li class="mb-2">
                                    <i class="fa-solid fa-check text-success me-2"></i>
                                    <strong>Dashboard:</strong> Quick overview with statistics
                                </li>
                                <li class="mb-2">
                                    <i class="fa-solid fa-check text-success me-2"></i>
                                    <strong>Submissions List:</strong> View all form submissions with pagination
                                </li>
                                <li class="mb-2">
                                    <i class="fa-solid fa-check text-success me-2"></i>
                                    <strong>Analytics:</strong> Detailed reports and charts
                                </li>
                                <li class="mb-2">
                                    <i class="fa-solid fa-check text-success me-2"></i>
                                    <strong>Filtering:</strong> Filter by form type
                                </li>
                                <li class="mb-2">
                                    <i class="fa-solid fa-check text-success me-2"></i>
                                    <strong>Details View:</strong> View complete submission information
                                </li>
                                <li class="mb-2">
                                    <i class="fa-solid fa-check text-success me-2"></i>
                                    <strong>Email Integration:</strong> Send emails from submissions
                                </li>
                            </ul>
                        </div>

                        <div class="settings-section">
                            <h6>Coming Soon</h6>
                            <ul style="list-style: none; padding: 0;">
                                <li class="mb-2">
                                    <i class="fa-solid fa-hourglass-half text-warning me-2"></i>
                                    Database backups & exports
                                </li>
                                <li class="mb-2">
                                    <i class="fa-solid fa-hourglass-half text-warning me-2"></i>
                                    Advanced search functionality
                                </li>
                                <li class="mb-2">
                                    <i class="fa-solid fa-hourglass-half text-warning me-2"></i>
                                    Email templates customization
                                </li>
                                <li class="mb-2">
                                    <i class="fa-solid fa-hourglass-half text-warning me-2"></i>
                                    User role management
                                </li>
                                <li class="mb-2">
                                    <i class="fa-solid fa-hourglass-half text-warning me-2"></i>
                                    Automated reports
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Help & Support -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fa-solid fa-question-circle me-2"></i>Help & Support</h5>
                    </div>
                    <div class="card-body">
                        <div class="settings-section">
                            <h6>Getting Help</h6>
                            <div class="info-box">
                                <strong>Admin Email:</strong> admin@gulftp.com
                            </div>
                            <div class="info-box">
                                <strong>Phone:</strong> +971 581711600
                            </div>
                            <div class="info-box">
                                <strong>Website:</strong> https://gulftp.com
                            </div>
                        </div>

                        <div class="alert alert-light border">
                            <i class="fa-solid fa-book me-2"></i>
                            For detailed documentation, please refer to the documentation files included with your installation.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
