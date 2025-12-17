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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $start = $_POST['start'];
    $end = $_POST['end'];
    $duration = $_POST['duration'];
    
    $stmt = $conn->prepare("INSERT INTO routes (start_location, end_location, duration_approx) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $start, $end, $duration);
    $stmt->execute();
}

$routes = $conn->query("SELECT * FROM routes ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Routes - BusLive</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container flex justify-between items-center">
            <a href="#" class="nav-brand">Admin Panel</a>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="buses.php">Buses</a>
                <a href="routes.php" style="color: var(--primary-color);">Routes</a>
                <a href="schedules.php">Schedules</a>
                <a href="bookings.php">Bookings</a>
                <a href="../logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container" style="padding: 3rem 1.5rem;">
        <div class="flex justify-between items-center mb-4">
            <h2>Manage Routes</h2>
        </div>

        <div class="grid" style="grid-template-columns: 1fr 2fr; gap: 2rem;">
            <!-- Add Route Form -->
            <div class="card" style="height: fit-content;">
                <h3 class="mb-4">Add New Route</h3>
                <form method="POST">
                    <div class="form-group">
                        <label>Start Location</label>
                        <input type="text" name="start" required placeholder="e.g. New York">
                    </div>
                    <div class="form-group">
                        <label>End Location</label>
                        <input type="text" name="end" required placeholder="e.g. Washington DC">
                    </div>
                    <div class="form-group">
                        <label>Duration (Approx)</label>
                        <input type="text" name="duration" required placeholder="e.g. 4h 30m">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Add Route</button>
                </form>
            </div>

            <!-- List Routes -->
            <div class="card">
                <h3 class="mb-4">Route List</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; border-bottom: 2px solid var(--border-color);">
                            <th style="padding: 1rem;">From</th>
                            <th style="padding: 1rem;">To</th>
                            <th style="padding: 1rem;">Duration</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $routes->fetch_assoc()): ?>
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td style="padding: 1rem;"><?php echo htmlspecialchars($row['start_location']); ?></td>
                                <td style="padding: 1rem;"><?php echo htmlspecialchars($row['end_location']); ?></td>
                                <td style="padding: 1rem;"><?php echo htmlspecialchars($row['duration_approx']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
