<?php
// Simplified connection test - try all common combinations
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 Database Connection Test</h2>";
echo "<hr>";

// Test credentials - start with simplest
$credentials = [
    ['localhost', 'u852823366_gulftp_user', 'GulfTP@2024', 'u852823366_gulftp_forms', 'New Credentials (Recommended)'],
    ['localhost', 'gulftpmain', 'Gulftp1234', 'gulftpforms', 'Old Credentials Without Prefix'],
    ['localhost', 'u852823366_gulfpmain', 'Gulftp1234', 'u852823366_gulftpforms', 'Old Credentials With Prefix'],
    ['localhost', 'root', '', 'u852823366_gulftp_forms', 'Root (no password)'],
];

$found_working = false;

foreach ($credentials as [$host, $user, $pass, $db, $name]) {
    echo "<h3>Testing: $name</h3>";
    echo "<p><small>Host: $host | User: $user | DB: $db</small></p>";
    
    $conn = @new mysqli($host, $user, $pass, $db);
    
    if ($conn->connect_error) {
        echo "<p style='color: #d9534f;'><strong>❌ FAILED:</strong> " . htmlspecialchars($conn->connect_error) . "</p>";
    } else {
        echo "<p style='color: #5cb85c;'><strong>✅ SUCCESS!</strong></p>";
        $found_working = true;
        
        // Try to list tables
        $result = $conn->query("SHOW TABLES");
        if ($result) {
            echo "<p>Tables in database:</p><ul>";
            while ($row = $result->fetch_row()) {
                echo "<li><code>" . htmlspecialchars($row[0]) . "</code></li>";
            }
            echo "</ul>";
        }
        
        $conn->close();
    }
    echo "<hr>";
}

if (!$found_working) {
    echo "<p style='color: #ff7f00;'><strong>⚠️ No credentials worked!</strong></p>";
    echo "<p>Please check your Hostinger database settings:</p>";
    echo "<ol>";
    echo "<li>Go to Hostinger Control Panel > Databases</li>";
    echo "<li>Check the <strong>exact username</strong> (copy-paste it)</li>";
    echo "<li>Check the <strong>exact database name</strong> (copy-paste it)</li>";
    echo "<li>Verify the <strong>password</strong></li>";
    echo "<li>Email or upload the correct credentials</li>";
    echo "</ol>";
}
?>
