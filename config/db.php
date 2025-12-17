<?php
// Fix for session path issue on some local environments
if (session_status() == PHP_SESSION_NONE) {
    if (!file_exists(__DIR__ . '/../sessions')) {
        @mkdir(__DIR__ . '/../sessions', 0777, true);
    }
    session_save_path(__DIR__ . '/../sessions');
    // We do NOT call session_start() here because it's called in index.php etc.
    // However, the files usually include config/db.php AFTER session_start().
    // We will need to update the entry points or just fix it here and hope for the best?
    // No, session_save_path must be called BEFORE session_start.
    // Since index.php calls session_start() at line 2, and includes db.php at line 3,
    // editing this file won't help index.php unless we change index.php.
}

$host = "localhost";
$user = "root";
$pass = "";
$db_name = "bus_ticket";

// Disable fatal exception for connection to allow handling cleanly
mysqli_report(MYSQLI_REPORT_OFF);

$conn = new mysqli($host, $user, $pass, $db_name);

if ($conn->connect_error) {
    // If we are not already on setup.php, redirect
    if (basename($_SERVER['PHP_SELF']) != 'setup.php') {
        echo "<h2>Database Error</h2>";
        echo "<p>It looks like the database is missing.</p>";
        echo "<a href='setup.php' style='background: #4F46E5; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Run System Setup</a>";
        die();
    } else {
        // Let setup.php handle it
    }
}
?>
