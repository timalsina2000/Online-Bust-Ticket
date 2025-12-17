<?php
// setup.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$user = "root";
$pass = ""; // Assumption for XAMPP/Default
$dbname = "bus_ticket";

echo "<h1>System Setup</h1>";

// 1. Connect without DB
$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) {
    die("<p style='color:red'>Connection failed: " . $conn->connect_error . " (Check if MySQL is running)</p>");
}

// 2. Create Database
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    echo "<p style='color:green'>Database '$dbname' checks out (created or exists).</p>";
} else {
    die("<p style='color:red'>Error creating database: " . $conn->error . "</p>");
}

// 3. Select DB
$conn->select_db($dbname);

// 4. Import SQL from file
$sqlFile = 'database.sql';
if (file_exists($sqlFile)) {
    $script = file_get_contents($sqlFile);
    // Split by semicolons for basic multi-query execution
    $queries = explode(';', $script);
    
    $errors = 0;
    foreach ($queries as $query) {
        $query = trim($query);
        if (!empty($query)) {
            if (!$conn->query($query)) {
                echo "<p style='color:orange'>Warning on query: " . substr($query, 0, 50) . "... <br>Error: " . $conn->error . "</p>";
                $errors++;
            }
        }
    }
    
    if ($errors === 0) {
        echo "<p style='color:green'>Tables imported successfully!</p>";
    } else {
        echo "<p style='color:orange'>Import completed with some warnings (tables might already exist).</p>";
    }
} else {
    echo "<p style='color:red'>Error: database.sql not found.</p>";
}

// 5. Insert Admin (Helper for user)
// Check if admin exists
$res = $conn->query("SELECT * FROM admins WHERE username='admin'");
if ($res->num_rows == 0) {
    $pass = password_hash('admin', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO admins (username, password) VALUES ('admin', '$pass')");
    echo "<p style='color:green'>Created default admin (User: admin, Pass: admin)</p>";
}

echo "<h3>Setup Complete!</h3>";
echo "<a href='index.php'>Go to Homepage</a>";
?>
