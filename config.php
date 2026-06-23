<?php
// Load environment variables from .env file
function loadEnv() {
    // Try multiple possible locations for .env file
    $possible_paths = [
        dirname(__FILE__) . '/.env',
        dirname(dirname(__FILE__)) . '/.env',
        $_SERVER['DOCUMENT_ROOT'] . '/.env',
        $_SERVER['DOCUMENT_ROOT'] . '/gulftp/.env'
    ];
    
    foreach ($possible_paths as $envFile) {
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                // Skip comments
                if (strpos($line, '#') === 0) continue;
                
                // Parse KEY=VALUE
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    
                    // Remove quotes if present
                    if (in_array($value[0] ?? null, ['"', "'"])) {
                        $value = substr($value, 1, -1);
                    }
                    
                    $_ENV[$key] = $value;
                    putenv("$key=$value");
                }
            }
            break;
        }
    }
}

// Load environment variables
loadEnv();

// Database Configuration with defaults
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_USER', $_ENV['DB_USER'] ?? 'u852823366_gulftp_user');
define('DB_PASS', $_ENV['DB_PASS'] ?? 'GulfTP@2024');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'u852823366_gulftp_forms');

// Stripe Configuration
define('STRIPE_SECRET_KEY', $_ENV['STRIPE_SECRET_KEY'] ?? '');
define('STRIPE_PUBLISHABLE_KEY', $_ENV['STRIPE_PUBLISHABLE_KEY'] ?? '');

// Email Configuration
define('ADMIN_EMAIL', $_ENV['ADMIN_EMAIL'] ?? 'admin@gulftp.com');
define('NOREPLY_EMAIL', $_ENV['NOREPLY_EMAIL'] ?? 'noreply@gulftp.com');
define('PHONE', $_ENV['PHONE'] ?? '+971 581711600');

// Application Configuration
define('APP_NAME', $_ENV['APP_NAME'] ?? 'GulfTP');
define('APP_URL', $_ENV['APP_URL'] ?? 'https://gulftp.com');
define('APP_ENV', $_ENV['APP_ENV'] ?? 'production');
define('DEBUG', $_ENV['DEBUG'] === 'true');

// Payment Configuration
define('PAYMENT_AMOUNT', (int)($_ENV['PAYMENT_AMOUNT'] ?? 750));
define('PAYMENT_CURRENCY', $_ENV['PAYMENT_CURRENCY'] ?? 'AED');

// Create database connection
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        return null;
    }
    
    return $conn;
}

// Initialize database tables
function initializeDatabase() {
    $conn = getDBConnection();
    if (!$conn) return false;
    
    // Create form_responses table for admin dashboard
    $sql = "CREATE TABLE IF NOT EXISTS form_responses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        form_type VARCHAR(50),
        first_name VARCHAR(255),
        last_name VARCHAR(255),
        email VARCHAR(255),
        phone VARCHAR(50),
        company VARCHAR(255),
        service VARCHAR(255),
        country VARCHAR(255),
        users VARCHAR(50),
        message LONGTEXT,
        form_data JSON,
        ip_address VARCHAR(45),
        stripe_session_id VARCHAR(255),
        payment_status VARCHAR(50) DEFAULT 'pending',
        amount DECIMAL(10, 2) DEFAULT 0.00,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_email (email),
        INDEX idx_created_at (created_at),
        INDEX idx_form_type (form_type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $result = $conn->query($sql);
    $conn->close();
    
    return $result;
}

// Initialize on first load
initializeDatabase();
?>

