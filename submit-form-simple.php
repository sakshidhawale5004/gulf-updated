<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Get form data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validate required fields
if (!isset($data['firstName']) || empty(trim($data['firstName']))) {
    http_response_code(400);
    echo json_encode(['error' => 'First name is required']);
    exit();
}

if (!isset($data['email']) || empty(trim($data['email']))) {
    http_response_code(400);
    echo json_encode(['error' => 'Email is required']);
    exit();
}

// Validate email
$email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email address']);
    exit();
}

// CORRECT Database credentials with Hostinger prefix
$db_host = 'localhost';
$db_user = 'u852823366_gulftp_user';
$db_pass = 'GulfTP@2024';
$db_name = 'u852823366_gulftp_forms';

// Connect to database
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

// Determine form type
if (isset($data['service']) && isset($data['country'])) {
    $form_type = 'Contact';
} elseif (isset($data['users'])) {
    $form_type = 'Subscription';
} else {
    $form_type = 'Appointment';
}

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

$conn->query($create_table);

// Insert into database
$sql = "INSERT INTO form_responses (form_type, first_name, last_name, email, phone, company, service, country, users, message, form_data, ip_address)
        VALUES ('$form_type', '$firstName', '$lastName', '$email', '$phone', '$company', '$service', '$country', '$users', '$message', '$form_data', '$ip')";

if ($conn->query($sql) === TRUE) {
    $conn->close();
    
    // Send email to admin
    $admin_email = 'admin@gulftp.com';
    $email_subject = "GulfTP Form Submission - $form_type";
    
    $email_body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #08664b; color: white; padding: 20px; border-radius: 5px 5px 0 0; }
            .content { background-color: #f9f9f9; padding: 20px; border: 1px solid #ddd; border-radius: 0 0 5px 5px; }
            .field { margin-bottom: 15px; }
            .field-label { font-weight: bold; color: #08664b; margin-bottom: 5px; }
            .field-value { padding: 10px; background-color: white; border-left: 3px solid #f39223; }
            .footer { margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>GulfTP Form Submission</h2>
                <p>Form Type: <strong>$form_type</strong></p>
            </div>
            <div class='content'>
                <div class='field'>
                    <div class='field-label'>Name:</div>
                    <div class='field-value'>$firstName $lastName</div>
                </div>
                <div class='field'>
                    <div class='field-label'>Email:</div>
                    <div class='field-value'>$email</div>
                </div>
                <div class='field'>
                    <div class='field-label'>Phone:</div>
                    <div class='field-value'>$phone</div>
                </div>
                <div class='field'>
                    <div class='field-label'>Company:</div>
                    <div class='field-value'>$company</div>
                </div>";
    
    if (!empty($service)) {
        $email_body .= "
                <div class='field'>
                    <div class='field-label'>Service:</div>
                    <div class='field-value'>$service</div>
                </div>";
    }
    
    if (!empty($country)) {
        $email_body .= "
                <div class='field'>
                    <div class='field-label'>Country:</div>
                    <div class='field-value'>$country</div>
                </div>";
    }
    
    if (!empty($users)) {
        $email_body .= "
                <div class='field'>
                    <div class='field-label'>Users:</div>
                    <div class='field-value'>$users</div>
                </div>";
    }
    
    if (!empty($message)) {
        $email_body .= "
                <div class='field'>
                    <div class='field-label'>Message:</div>
                    <div class='field-value'>$message</div>
                </div>";
    }
    
    $email_body .= "
                <div class='field'>
                    <div class='field-label'>Submission Time:</div>
                    <div class='field-value'>" . date('Y-m-d H:i:s') . " UTC</div>
                </div>
                <div class='field'>
                    <div class='field-label'>IP Address:</div>
                    <div class='field-value'>$ip</div>
                </div>
            </div>
            <div class='footer'>
                <p>This is an automated message from GulfTP website.</p>
                <p>&copy; 2026 GulfTP. All Rights Reserved.</p>
            </div>
        </div>
    </body>
    </html>";
    
    // Email headers
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: noreply@gulftp.com\r\n";
    $headers .= "Reply-To: $email\r\n";
    
    // Send email
    @mail($admin_email, $email_subject, $email_body, $headers);
    
    // Send confirmation to user
    $user_subject = "We received your GulfTP form submission";
    $user_body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #08664b; color: white; padding: 20px; border-radius: 5px 5px 0 0; }
            .content { background-color: #f9f9f9; padding: 20px; border: 1px solid #ddd; border-radius: 0 0 5px 5px; }
            .footer { margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #666; }
            .success { color: #28a745; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Submission Received</h2>
            </div>
            <div class='content'>
                <p>Hi $firstName,</p>
                <p class='success'>Thank you for submitting your form!</p>
                <p>We have received your submission and our team will review it shortly. You will hear from us within 24-48 business hours.</p>
                <p><strong>What happens next:</strong></p>
                <ul>
                    <li>Our team will review your information</li>
                    <li>We will contact you at <strong>$email</strong></li>
                    <li>We'll discuss your requirements and provide a tailored solution</li>
                </ul>
                <p>If you have any urgent questions, please reach out to us at <strong>admin@gulftp.com</strong> or call <strong>+971 581711600</strong>.</p>
                <p>Best regards,<br><strong>GulfTP Team</strong></p>
            </div>
            <div class='footer'>
                <p>&copy; 2026 GulfTP. All Rights Reserved.</p>
            </div>
        </div>
    </body>
    </html>";
    
    $user_headers = "MIME-Version: 1.0\r\n";
    $user_headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $user_headers .= "From: admin@gulftp.com\r\n";
    $user_headers .= "Reply-To: admin@gulftp.com\r\n";
    
    @mail($email, $user_subject, $user_body, $user_headers);
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Thank you! Your submission has been received. We will contact you shortly.',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} else {
    $conn->close();
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save your submission: ' . $conn->error]);
}
?>
