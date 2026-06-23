<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Log errors to a file
$error_log = dirname(__FILE__) . '/form_errors.log';

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

// Include database config
require_once 'config.php';

// Get form data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Log incoming data
file_put_contents($error_log, "[" . date('Y-m-d H:i:s') . "] Incoming data: " . print_r($data, true) . "\n", FILE_APPEND);

// Validate required fields
$required_fields = ['firstName', 'lastName', 'email'];

// Check if required fields are present
$missing_fields = [];
foreach ($required_fields as $field) {
    if (!isset($data[$field]) || empty(trim($data[$field]))) {
        $missing_fields[] = $field;
    }
}

if (!empty($missing_fields)) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Missing required fields',
        'missing' => $missing_fields
    ]);
    exit();
}

// Sanitize and validate email
$user_email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email address']);
    exit();
}

// Admin email (recipient)
$admin_email = 'admin@gulftp.com';

// Determine form type based on available fields
$form_type = 'General Form';
if (isset($data['users'])) {
    $form_type = 'Subscription Request';
} elseif (isset($data['service']) && isset($data['country'])) {
    $form_type = 'Contact Form';
} elseif (isset($data['company'])) {
    $form_type = 'Appointment Request';
}

// Store in database
try {
    $conn = getDBConnection();
    if ($conn) {
        $name = $conn->real_escape_string($data['firstName'] . ' ' . (isset($data['lastName']) ? $data['lastName'] : ''));
        $email = $conn->real_escape_string($user_email);
        $organisation = isset($data['company']) ? $conn->real_escape_string($data['company']) : '';
        $industry = isset($data['service']) ? $conn->real_escape_string($data['service']) : '';
        $description = isset($data['message']) ? $conn->real_escape_string($data['message']) : 'No additional details provided';
        $mobile = isset($data['phone']) ? $conn->real_escape_string($data['phone']) : '';
        
        $sql = "INSERT INTO form_submissions (name, email, mobile, organisation, industry, description, created_at)
                VALUES ('$name', '$email', '$mobile', '$organisation', '$industry', '$description', NOW())";
        
        $conn->query($sql);
        $conn->close();
        
        file_put_contents($error_log, "[" . date('Y-m-d H:i:s') . "] Data stored in database\n", FILE_APPEND);
    }
} catch (Exception $e) {
    file_put_contents($error_log, "[" . date('Y-m-d H:i:s') . "] Database error: " . $e->getMessage() . "\n", FILE_APPEND);
}

// Build email content
$email_subject = "GulfTP Form Submission - " . $form_type;

$email_body = "
<!DOCTYPE html>
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
        .orange { color: #f39223; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2 class='orange'>GulfTP Form Submission</h2>
            <p>Form Type: <strong>" . htmlspecialchars($form_type) . "</strong></p>
        </div>
        
        <div class='content'>
";

// Add form fields to email body
foreach ($data as $key => $value) {
    if ($key === 'email_to' || $key === 'fileUpload') continue; // Skip hidden fields
    
    // Format field names for display
    $display_key = ucwords(str_replace('_', ' ', $key));
    
    if (is_array($value)) {
        $value = implode(', ', $value);
    }
    
    $email_body .= "
            <div class='field'>
                <div class='field-label'>" . htmlspecialchars($display_key) . ":</div>
                <div class='field-value'>" . nl2br(htmlspecialchars($value)) . "</div>
            </div>
    ";
}

// Add submission details
$email_body .= "
            <div class='field'>
                <div class='field-label'>Submission Time:</div>
                <div class='field-value'>" . date('Y-m-d H:i:s') . " UTC</div>
            </div>
            
            <div class='field'>
                <div class='field-label'>User IP Address:</div>
                <div class='field-value'>" . htmlspecialchars($_SERVER['REMOTE_ADDR']) . "</div>
            </div>
        </div>
        
        <div class='footer'>
            <p>This is an automated message from your GulfTP website form handler.</p>
            <p>&copy; 2026 GulfTP. All Rights Reserved.</p>
        </div>
    </div>
</body>
</html>
";

// Set email headers
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
$headers .= "From: noreply@gulftp.com" . "\r\n";
$headers .= "Reply-To: " . $user_email . "\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

// Send email to admin
$mail_sent = mail($admin_email, $email_subject, $email_body, $headers);

// Also send confirmation email to user
$user_subject = "We received your GulfTP form submission";
$user_body = "
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #08664b; color: white; padding: 20px; border-radius: 5px 5px 0 0; }
        .content { background-color: #f9f9f9; padding: 20px; border: 1px solid #ddd; border-radius: 0 0 5px 5px; }
        .success { color: #28a745; }
        .footer { margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>Submission Received</h2>
        </div>
        
        <div class='content'>
            <p>Hi " . htmlspecialchars($data['firstName']) . ",</p>
            <p class='success'><strong>Thank you for submitting your form!</strong></p>
            <p>We have received your submission and our team will review it shortly. You will hear from us within 24-48 business hours.</p>
            
            <p><strong>What happens next:</strong></p>
            <ul>
                <li>Our team will review your information</li>
                <li>We will contact you at <strong>" . htmlspecialchars($user_email) . "</strong></li>
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
</html>
";

$user_headers = "MIME-Version: 1.0" . "\r\n";
$user_headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
$user_headers .= "From: admin@gulftp.com" . "\r\n";
$user_headers .= "Reply-To: admin@gulftp.com" . "\r\n";
$user_headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

mail($user_email, $user_subject, $user_body, $user_headers);

// Send response
if ($mail_sent) {
    file_put_contents($error_log, "[" . date('Y-m-d H:i:s') . "] Email sent successfully to: " . $admin_email . "\n", FILE_APPEND);
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Form submitted successfully! We will contact you shortly.'
    ]);
} else {
    file_put_contents($error_log, "[" . date('Y-m-d H:i:s') . "] Email send failed. PHP mail() function may not be configured. Error: " . error_get_last()['message'] . "\n", FILE_APPEND);
    http_response_code(500);
    echo json_encode([
        'error' => 'Form submitted but email notification failed. Your data has been saved.'
    ]);
}
?>

// Validate required fields
$required_fields = ['firstName', 'lastName', 'email'];

// Check if required fields are present
$missing_fields = [];
foreach ($required_fields as $field) {
    if (!isset($data[$field]) || empty(trim($data[$field]))) {
        $missing_fields[] = $field;
    }
}

if (!empty($missing_fields)) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Missing required fields',
        'missing' => $missing_fields
    ]);
    exit();
}

// Sanitize and validate email
$user_email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email address']);
    exit();
}

// Admin email (recipient)
$admin_email = 'admin@gulftp.com';

// Determine form type based on available fields
$form_type = 'General Form';
if (isset($data['users'])) {
    $form_type = 'Subscription Request';
} elseif (isset($data['service']) && isset($data['country'])) {
    $form_type = 'Contact Form';
} elseif (isset($data['whatsapp'])) {
    $form_type = 'Contact Form';
}

// Build email content
$email_subject = "GulfTP Form Submission - " . $form_type;

$email_body = "
<!DOCTYPE html>
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
        .success { color: #28a745; }
        .orange { color: #f39223; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2 class='orange'>GulfTP Form Submission</h2>
            <p>Form Type: <strong>" . htmlspecialchars($form_type) . "</strong></p>
        </div>
        
        <div class='content'>
";

// Add form fields to email body
foreach ($data as $key => $value) {
    if ($key === 'email_to') continue; // Skip the hidden email field
    
    // Format field names for display
    $display_key = ucwords(str_replace('_', ' ', $key));
    
    if (is_array($value)) {
        $value = implode(', ', $value);
    }
    
    $email_body .= "
            <div class='field'>
                <div class='field-label'>" . htmlspecialchars($display_key) . ":</div>
                <div class='field-value'>" . nl2br(htmlspecialchars($value)) . "</div>
            </div>
    ";
}

// Add submission details
$email_body .= "
            <div class='field'>
                <div class='field-label'>Submission Time:</div>
                <div class='field-value'>" . date('Y-m-d H:i:s') . " UTC</div>
            </div>
            
            <div class='field'>
                <div class='field-label'>User IP Address:</div>
                <div class='field-value'>" . htmlspecialchars($_SERVER['REMOTE_ADDR']) . "</div>
            </div>
        </div>
        
        <div class='footer'>
            <p>This is an automated message from your GulfTP website form handler.</p>
            <p>&copy; 2026 GulfTP. All Rights Reserved.</p>
        </div>
    </div>
</body>
</html>
";

// Set email headers
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
$headers .= "From: " . $user_email . "\r\n";
$headers .= "Reply-To: " . $user_email . "\r\n";

// Send email to admin
$mail_sent = mail($admin_email, $email_subject, $email_body, $headers);

// Also send confirmation email to user
$user_subject = "We received your GulfTP form submission";
$user_body = "
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #08664b; color: white; padding: 20px; border-radius: 5px 5px 0 0; }
        .content { background-color: #f9f9f9; padding: 20px; border: 1px solid #ddd; border-radius: 0 0 5px 5px; }
        .success { color: #28a745; }
        .footer { margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>Submission Received</h2>
        </div>
        
        <div class='content'>
            <p>Hi " . htmlspecialchars($data['firstName']) . ",</p>
            <p class='success'><strong>Thank you for submitting your form!</strong></p>
            <p>We have received your submission and our team will review it shortly. You will hear from us within 24-48 business hours.</p>
            
            <p><strong>What happens next:</strong></p>
            <ul>
                <li>Our team will review your information</li>
                <li>We will contact you at <strong>" . htmlspecialchars($user_email) . "</strong></li>
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
</html>
";

$user_headers = "MIME-Version: 1.0" . "\r\n";
$user_headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
$user_headers .= "From: admin@gulftp.com" . "\r\n";
$user_headers .= "Reply-To: admin@gulftp.com" . "\r\n";

mail($user_email, $user_subject, $user_body, $user_headers);

// Send response
if ($mail_sent) {
    file_put_contents($error_log, "[" . date('Y-m-d H:i:s') . "] Email sent successfully to: " . $admin_email . "\n", FILE_APPEND);
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Form submitted successfully! We will contact you shortly.'
    ]);
} else {
    file_put_contents($error_log, "[" . date('Y-m-d H:i:s') . "] Email send failed. Error: " . error_get_last()['message'] . "\n", FILE_APPEND);
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to send form. Please try again.'
    ]);
}
?>
