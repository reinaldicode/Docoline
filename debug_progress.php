<?php
// debug_progress.php - Temporary file untuk debug data
session_start();
include('koneksi.php');

// Check if logged in
if (!isset($_SESSION['username'])) {
    die("Not logged in! <a href='index.php'>Login here</a>");
}

echo "<h2>Debug Progress Page</h2>";
echo "<hr>";

// Display session data
echo "<h3>Session Data:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

$nrp = isset($_SESSION['username']) ? $_SESSION['username'] : '';
echo "<hr>";
echo "<h3>Query Test:</h3>";

// Test query
$sql = "SELECT username, name, email, state, section FROM users WHERE username='$nrp' LIMIT 1";
echo "<p><strong>SQL:</strong> " . htmlspecialchars($sql) . "</p>";
echo "<p><strong>NRP Value:</strong> '" . htmlspecialchars($nrp) . "' (Length: " . strlen($nrp) . ")</p>";

$result = mysqli_query($link, $sql);

if (!$result) {
    echo "<p style='color:red;'><strong>Query Error:</strong> " . mysqli_error($link) . "</p>";
} else {
    echo "<p style='color:green;'>Query executed successfully</p>";
    echo "<p><strong>Rows found:</strong> " . mysqli_num_rows($result) . "</p>";
    
    if (mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);
        echo "<h4>User Data:</h4>";
        echo "<pre>";
        print_r($data);
        echo "</pre>";
        
        // Check each field
        echo "<h4>Field Check:</h4>";
        echo "<ul>";
        echo "<li>Username: " . (isset($data['username']) ? "✓ " . htmlspecialchars($data['username']) : "✗ NULL") . "</li>";
        echo "<li>Name: " . (isset($data['name']) ? "✓ " . htmlspecialchars($data['name']) : "✗ NULL") . "</li>";
        echo "<li>Email: " . (isset($data['email']) ? "✓ " . htmlspecialchars($data['email']) : "✗ NULL") . "</li>";
        echo "<li>State: " . (isset($data['state']) ? "✓ " . htmlspecialchars($data['state']) : "✗ NULL") . "</li>";
        echo "<li>Section: " . (isset($data['section']) ? "✓ " . htmlspecialchars($data['section']) : "✗ NULL") . "</li>";
        echo "</ul>";
    } else {
        echo "<p style='color:red;'>No user found with NRP: " . htmlspecialchars($nrp) . "</p>";
    }
}

echo "<hr>";
echo "<h3>Database Structure Check:</h3>";
$columns = mysqli_query($link, "SHOW COLUMNS FROM users");
echo "<pre>";
while ($col = mysqli_fetch_assoc($columns)) {
    echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
}
echo "</pre>";

echo "<hr>";
echo "<h3>All Approvers in Database:</h3>";
$all_approvers = mysqli_query($link, "SELECT username, name, section, state FROM users WHERE state='Approver' OR state='approver'");
if (mysqli_num_rows($all_approvers) > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Username</th><th>Name</th><th>Section</th><th>State</th></tr>";
    while ($app = mysqli_fetch_assoc($all_approvers)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($app['username'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($app['name'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($app['section'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($app['state'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:red;'>No approvers found in database!</p>";
}

echo "<hr>";
echo "<p><a href='progress.php'>Go to Progress Page</a> | <a href='index_login.php'>Back to Home</a></p>";
?>