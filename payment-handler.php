<?php
require 'config.php';

header('Content-Type: application/json');

$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Get Stripe Secret Key from environment
$stripe_secret_key = STRIPE_SECRET_KEY;

// Validate input
if (!isset($data['firstName'], $data['email'], $data['amount'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit();
}

// Validate email
$email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email']);
    exit();
}

// Save to database first
try {
    $conn = getDBConnection();
    if ($conn) {
        $firstName = $conn->real_escape_string($data['firstName']);
        $lastName = isset($data['lastName']) ? $conn->real_escape_string($data['lastName']) : '';
        $phone = isset($data['phone']) ? $conn->real_escape_string($data['phone']) : '';
        $company = isset($data['company']) ? $conn->real_escape_string($data['company']) : '';
        $country = isset($data['country']) ? $conn->real_escape_string($data['country']) : '';
        $transactionType = isset($data['transactionType']) ? $conn->real_escape_string($data['transactionType']) : '';
        $message = isset($data['message']) ? $conn->real_escape_string($data['message']) : '';
        $amount = (int)$data['amount'];
        
        // Get IP address
        $ip = isset($_SERVER['HTTP_CF_CONNECTING_IP']) ? $_SERVER['HTTP_CF_CONNECTING_IP'] : $_SERVER['REMOTE_ADDR'];
        $ip = $conn->real_escape_string($ip);
        
        // Store in form_responses table with payment status
        $sql = "INSERT INTO form_responses (form_type, first_name, last_name, email, phone, company, service, country, message, ip_address, amount, payment_status)
                VALUES ('Payment', '$firstName', '$lastName', '$email', '$phone', '$company', '$transactionType', '$country', '$message', '$ip', '$amount', 'pending')";
        
        $conn->query($sql);
        $conn->close();
    }
} catch (Exception $e) {
    // Continue anyway
}

// Return Stripe public key for client-side setup
echo json_encode([
    'success' => true,
    'stripePublicKey' => STRIPE_PUBLISHABLE_KEY,
    'amount' => $data['amount'],
    'email' => $email,
    'firstName' => $data['firstName'],
    'message' => 'Ready to process payment'
]);
?>
