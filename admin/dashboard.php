<?php
$s_path = __DIR__ . '/../sessions';
if (!file_exists($s_path)) { @mkdir($s_path, 0777, true); }
session_save_path($s_path);

session_start();
include '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// Stats
$buses = $conn->query("SELECT COUNT(*) as c FROM buses")->fetch_assoc()['c'];
$routes = $conn->query("SELECT COUNT(*) as c FROM routes")->fetch_assoc()['c'];
$schedules = $conn->query("SELECT COUNT(*) as c FROM schedules")->fetch_assoc()['c'];
$bookings = $conn->query("SELECT COUNT(*) as c FROM bookings")->fetch_assoc()['c'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Teraibus</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container flex justify-between items-center">
            <a href="#" class="nav-brand">Admin Panel</a>
            <div class="nav-links">
                <a href="dashboard.php" class="text-primary">Dashboard</a>
                <a href="buses.php">Buses</a>
                <a href="routes.php">Routes</a>
                <a href="schedules.php">Schedules</a>
                <a href="bookings.php">Bookings</a>
                <a href="../logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container" style="padding: 3rem 1.5rem;">
        <h2 class="mb-4">Overview</h2>
        
        <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
            <div class="card text-center">
                <h2><?php echo $buses; ?></h2>
                <p class="text-muted">Total Buses</p>
            </div>
            <div class="card text-center">
                <h2><?php echo $routes; ?></h2>
                <p class="text-muted">Routes</p>
            </div>
            <div class="card text-center">
                <h2><?php echo $schedules; ?></h2>
                <p class="text-muted">Active Schedules</p>
            </div>
            <div class="card text-center">
                <h2><?php echo $bookings; ?></h2>
                <p class="text-muted">Total Bookings</p>
            </div>
        </div>
    </div>
</body>
</html>
