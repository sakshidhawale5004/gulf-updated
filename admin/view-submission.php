<?php
require_once '../config.php';

session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

$submissionId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($submissionId === 0) {
    header('Location: dashboard.php');
    exit();
}

$conn = getDBConnection();
if (!$conn) {
    die('Database connection failed');
}

$query = "SELECT * FROM form_responses WHERE id = $submissionId";
$result = $conn->query($query);
$submission = $result->fetch_assoc();
$conn->close();

if (!$submission) {
    header('Location: dashboard.php');
    exit();
}

$formData = json_decode($submission['form_data'], true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Submission - GulfTP Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-green: #08664b;
            --primary-orange: #f39223;
        }
        
        body {
            background-color: #f8f9fa;
        }
        
        .topbar {
            background: white;
            border-bottom: 1px solid #e9ecef;
            padding: 20px 30px;
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
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-header h5 {
            margin: 0;
            color: var(--primary-green);
            font-weight: 600;
        }
        
        .detail-group {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .detail-group:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .detail-label {
            font-weight: 600;
            color: var(--primary-green);
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }
        
        .detail-value {
            color: #333;
            font-size: 15px;
            word-break: break-word;
        }
        
        .badge-type {
            display: inline-block;
            padding: 6px 12px;
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
        
        .btn-back {
            background: white;
            border: 1px solid #e9ecef;
            color: #333;
            padding: 8px 15px;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .btn-back:hover {
            background: #f8f9fa;
            color: var(--primary-green);
        }
    </style>
</head>
<body>
    <!-- Topbar -->
    <div class="topbar">
        <h4 style="margin: 0;">
            <a href="dashboard.php" class="btn-back me-2">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            View Submission
        </h4>
        <a href="logout.php" class="btn-back">
            <i class="fa-solid fa-sign-out-alt me-2"></i> Logout
        </a>
    </div>

    <!-- Content -->
    <div class="main-content">
        <div class="row">
            <div class="col-lg-8">
                <!-- Main Details -->
                <div class="card">
                    <div class="card-header">
                        <h5>Submission Details</h5>
                        <?php
                        $type = $submission['form_type'];
                        $badgeClass = 'badge-contact';
                        if ($type === 'Subscription') $badgeClass = 'badge-subscription';
                        elseif ($type === 'Appointment') $badgeClass = 'badge-appointment';
                        elseif ($type === 'Demo') $badgeClass = 'badge-demo';
                        ?>
                        <span class="badge-type <?php echo $badgeClass; ?>"><?php echo $type; ?></span>
                    </div>
                    <div class="card-body p-4">
                        <div class="detail-group">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="detail-label">First Name</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($submission['first_name']); ?></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="detail-label">Last Name</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($submission['last_name']); ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="detail-group">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="detail-label">Email</div>
                                    <div class="detail-value">
                                        <a href="mailto:<?php echo htmlspecialchars($submission['email']); ?>">
                                            <?php echo htmlspecialchars($submission['email']); ?>
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="detail-label">Phone</div>
                                    <div class="detail-value">
                                        <?php echo htmlspecialchars($submission['phone']); ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="detail-group">
                            <div class="detail-label">Company</div>
                            <div class="detail-value"><?php echo htmlspecialchars($submission['company']); ?></div>
                        </div>

                        <div class="detail-group">
                            <div class="detail-label">Service / Transaction Type</div>
                            <div class="detail-value"><?php echo htmlspecialchars($submission['service']); ?></div>
                        </div>

                        <?php if (!empty($submission['country'])): ?>
                        <div class="detail-group">
                            <div class="detail-label">Country</div>
                            <div class="detail-value"><?php echo htmlspecialchars($submission['country']); ?></div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($submission['users'])): ?>
                        <div class="detail-group">
                            <div class="detail-label">Users</div>
                            <div class="detail-value"><?php echo htmlspecialchars($submission['users']); ?></div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($submission['message'])): ?>
                        <div class="detail-group">
                            <div class="detail-label">Message</div>
                            <div class="detail-value"><?php echo nl2br(htmlspecialchars($submission['message'])); ?></div>
                        </div>
                        <?php endif; ?>

                        <div class="detail-group" style="border-bottom: none;">
                            <div class="detail-label">IP Address</div>
                            <div class="detail-value"><?php echo htmlspecialchars($submission['ip_address']); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Sidebar -->
                <div class="card">
                    <div class="card-header">
                        <h5>Info</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="detail-group">
                            <div class="detail-label">Submission ID</div>
                            <div class="detail-value">#<?php echo $submission['id']; ?></div>
                        </div>

                        <div class="detail-group">
                            <div class="detail-label">Submitted On</div>
                            <div class="detail-value">
                                <?php echo date('M d, Y \a\t h:i A', strtotime($submission['created_at'])); ?>
                            </div>
                        </div>

                        <div class="detail-group" style="border-bottom: none;">
                            <div class="detail-label">Status</div>
                            <div class="detail-value">
                                <span class="badge bg-success">
                                    <i class="fa-solid fa-check-circle me-1"></i> Received
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card">
                    <div class="card-header">
                        <h5>Actions</h5>
                    </div>
                    <div class="card-body p-4">
                        <a href="mailto:<?php echo htmlspecialchars($submission['email']); ?>" class="btn btn-primary w-100 mb-2">
                            <i class="fa-solid fa-envelope me-2"></i> Send Email
                        </a>
                        <a href="dashboard.php" class="btn btn-secondary w-100">
                            <i class="fa-solid fa-arrow-left me-2"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
