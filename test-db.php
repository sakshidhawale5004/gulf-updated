<?php
// Simple diagnostic test - does NOT use .env or config.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 Database Connection Diagnostic Test</h2>";
echo "<hr>";

// Test different credential combinations
$test_credentials = [
    [
        'name' => 'With Hostinger Prefix (CURRENT)',
        'host' => 'localhost',
        'user' => 'u852823366_gulfpmain',
        'pass' => 'Gulftp1234',
        'db' => 'u852823366_gulftpforms'
    ],
    [
        'name' => 'Without Prefix',
        'host' => 'localhost',
        'user' => 'gulftpmain',
        'pass' => 'Gulftp1234',
        'db' => 'gulftpforms'
    ],
    [
        'name' => 'Alternative Password',
        'host' => 'localhost',
        'user' => 'u852823366_gulfpmain',
        'pass' => 'Gulftpuser123',
        'db' => 'u852823366_gulftpforms'
    ],
];

foreach ($test_credentials as $cred) {
    echo "<h3>Testing: " . $cred['name'] . "</h3>";
    echo "<p><small>User: <code>" . $cred['user'] . "</code> | DB: <code>" . $cred['db'] . "</code></small></p>";
    
    $conn = @new mysqli($cred['host'], $cred['user'], $cred['pass'], $cred['db']);
    
    if ($conn->connect_error) {
        echo "<p style='color: red;'><strong>❌ FAILED:</strong> " . $conn->connect_error . "</p>";
    } else {
        echo "<p style='color: green;'><strong>✅ SUCCESS:</strong> Connected!</p>";
        
        // Try to query the table
        $result = $conn->query("SELECT COUNT(*) as total FROM form_responses");
        if ($result) {
            $row = $result->fetch_assoc();
            echo "<p style='color: blue;'>📊 Table found! Total submissions: " . $row['total'] . "</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Table doesn't exist yet. Run a form submission to create it.</p>";
        }
        
        $conn->close();
    }
    echo "<hr>";
}

echo "<p><strong>Next steps:</strong></p>";
echo "<ol>";
echo "<li>Note which credential combination works above</li>";
echo "<li>Update <code>.env</code> with correct credentials</li>";
echo "<li>Update <code>config.php</code> fallback defaults</li>";
echo "<li>Test form submission at https://gulftp.com/contact.html</li>";
echo "</ol>";
?>
