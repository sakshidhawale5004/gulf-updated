<?php
session_start();

// Check if already logged in
if (isset($_SESSION['admin_logged_in'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    // Simple hardcoded admin credentials (in production, use database)
    // Default: admin / admin@2024
    $adminUsername = 'admin';
    $adminPassword = 'admin@2024';
    
    if ($username === $adminUsername && $password === $adminPassword) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header('Location: dashboard.php');
        exit();
    } else {
        $error = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - GulfTP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-green: #08664b;
            --primary-orange: #f39223;
        }
        
        body {
            background: linear-gradient(135deg, var(--primary-green), #054834);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h2 {
            color: var(--primary-green);
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .login-header p {
            color: #666;
            font-size: 14px;
        }
        
        .form-control {
            border-radius: 6px;
            border: 1px solid #e0e0e0;
            padding: 12px 15px;
            margin-bottom: 15px;
            font-size: 14px;
        }
        
        .form-control:focus {
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 0.2rem rgba(243, 146, 35, 0.25);
        }
        
        .btn-login {
            background: linear-gradient(135deg, var(--primary-green), #054834);
            border: none;
            padding: 12px;
            font-weight: 600;
            border-radius: 6px;
            width: 100%;
            color: white;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(8, 102, 75, 0.3);
            color: white;
        }
        
        .alert {
            border-radius: 6px;
            margin-bottom: 20px;
        }
        
        .login-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #999;
        }
        
        .icon-lock {
            text-align: center;
            margin-bottom: 20px;
            color: var(--primary-green);
            font-size: 48px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="icon-lock">
                <i class="fa-solid fa-lock"></i>
            </div>
            <h2>Admin Login</h2>
            <p>GulfTP Management Dashboard</p>
        </div>

        <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i>
            <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label" style="color: #333; font-weight: 600;">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Enter username" required>
            </div>

            <div class="mb-3">
                <label class="form-label" style="color: #333; font-weight: 600;">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter password" required>
            </div>

            <button type="submit" class="btn btn-login">
                <i class="fa-solid fa-sign-in-alt me-2"></i> Sign In
            </button>
        </form>

        <div class="login-footer">
            <p style="margin: 0; color: #999;">
                <i class="fa-solid fa-info-circle me-1"></i>
                Demo Credentials:<br>
                Username: <strong>admin</strong><br>
                Password: <strong>admin@2024</strong>
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
