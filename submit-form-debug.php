<?php
// DEBUG VERSION - Shows detailed errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Log all requests
$log = fopen('submit-form-debug.log', 'a');
fwrite($log, "\n\n=== NEW REQUEST ===\n");
fwrite($log, "Time: " . date('Y-m-d H:i:s') . "\n");
fwrite($log, "Method: " . $_SERVER['REQUEST_METHOD'] . "\n");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    fwrite($log, "Result: OPTIONS (preflight)\n");
    fclose($log);
    exit();
}

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    fwrite($log, "Result: ERROR - Method not allowed\n");
    fclose($log);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Get form data
$input = file_get_contents('php://input');
fwrite($log, "Raw Input: " . substr($input, 0, 200) . "...\n");

$data = json_decode($input, true);
fwrite($log, "Decoded Data: " . json_encode($data) . "\n");

// Validate required fields
if (!isset($data['firstName']) || empty(trim($data['firstName']))) {
    fwrite($log, "Result: ERROR - First name missing\n");
    fclose($log);
    http_response_code(400);
    echo json_encode(['error' => 'First name is required']);
    exit();
}

if (!isset($data['email']) || empty(trim($data['email']))) {
    fwrite($log, "Result: ERROR - Email missing\n");
    fclose($log);
    http_response_code(400);
    echo json_encode(['error' => 'Email is required']);
    exit();
}

// Validate email
$email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite($log, "Result: ERROR - Invalid email\n");
    fclose($log);
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email address']);
    exit();
}

fwrite($log, "Validation: PASSED\n");

// HARDCODED credentials for debugging
$db_host = 'localhost';
$db_user = 'u852823366_gulftp_user';
$db_pass = 'GulfTP@2024';
$db_name = 'u852823366_gulftp_forms';

fwrite($log, "Connecting to: $db_host | User: $db_user | DB: $db_name\n");

// Connect to database
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    fwrite($log, "Result: ERROR - DB Connection Failed: " . $conn->connect_error . "\n");
    fclose($log);
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

fwrite($log, "DB Connection: SUCCESS\n");

// Determine form type
if (isset($data['service']) && isset($data['country'])) {
    $form_type = 'Contact';
} elseif (isset($data['users'])) {
    $form_type = 'Subscription';
} else {
    $form_type = 'Appointment';
}

fwrite($log, "Form Type: $form_type\n");

// Prepare data
$firstName = $conn->real_escape_string($data['firstName']);
$lastName = isset($data['lastName']) ? $conn->real_escape_string($data['lastName']) : '';
$email = $conn->real_escape_string($email);
$phone = isset($data['phone']) ? $conn->real_escape_string($data['phone']) : '';
$company = isset($data['company']) ? $conn->real_escape_string($data['company']) : '';
$message = isset($data['message']) ? $conn->real_escape_string($data['message']) : '';
$service = isset($data['service']) ? $conn->real_escape_string($data['service']) : '';
$country = isset($data['country']) ? $conn->real_escape_string($data['country']) : '';
$users = isset($data['users']) ? $conn->real_escape_string($data['users']) : '';
$form_data = $conn->real_escape_string(json_encode($data));

// Get IP address
$ip = isset($_SERVER['HTTP_CF_CONNECTING_IP']) ? $_SERVER['HTTP_CF_CONNECTING_IP'] : $_SERVER['REMOTE_ADDR'];
$ip = $conn->real_escape_string($ip);

// Create table if it doesn't exist
$create_table = "CREATE TABLE IF NOT EXISTS form_responses (
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
    INDEX idx_form_type (form_type),
    INDEX idx_email (email),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

fwrite($log, "Creating table if not exists...\n");
if (!$conn->query($create_table)) {
    fwrite($log, "Table creation error: " . $conn->error . "\n");
}

// Insert into database
$sql = "INSERT INTO form_responses (form_type, first_name, last_name, email, phone, company, service, country, users, message, form_data, ip_address)
        VALUES ('$form_type', '$firstName', '$lastName', '$email', '$phone', '$company', '$service', '$country', '$users', '$message', '$form_data', '$ip')";

fwrite($log, "Executing Insert...\n");

if ($conn->query($sql) === TRUE) {
    fwrite($log, "Result: SUCCESS - Data inserted\n");
    $conn->close();
    fclose($log);
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Thank you! Your submission has been received. We will contact you shortly.',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} else {
    fwrite($log, "Result: ERROR - Insert failed: " . $conn->error . "\n");
    fclose($log);
    $conn->close();
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save your submission: ' . $conn->error]);
}
?>
