<!DOCTYPE html>
<html>
<head><title>PHP Test</title></head>
<body style="font-family: monospace; padding: 20px;">
<h1>PHP Execution Test</h1>
<?php
echo "<p style='color: green; font-size: 20px;'><strong>✓ PHP IS WORKING!</strong></p>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Server: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p>Current Time: " . date('Y-m-d H:i:s') . "</p>";

// Test required functions
$tests = [
    'session_start' => function_exists('session_start'),
    'curl_init' => function_exists('curl_init'),
    'mysqli_connect' => function_exists('mysqli_connect'),
    'header' => function_exists('header'),
    'json_encode' => function_exists('json_encode')
];

echo "<h2>Required Functions:</h2><ul>";
foreach ($tests as $func => $exists) {
    $status = $exists ? '<span style="color:green">✓</span>' : '<span style="color:red">✗</span>';
    echo "<li>$status $func</li>";
}
echo "</ul>";

// Test file access
echo "<h2>File System:</h2>";
echo "<p>Current directory: " . __DIR__ . "</p>";
echo "<p>Files: " . (is_readable('keys.php') ? '✓ keys.php found' : '✗ keys.php missing') . "</p>";

?>
<p><strong>If you see this green message above, PHP is working correctly!</strong></p>
</body>
</html>
