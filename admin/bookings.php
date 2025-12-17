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

$bookings = $conn->query("SELECT b.*, u.name as user_name, u.email, s.departure_time, bus.name as bus_name, r.start_location, r.end_location 
                          FROM bookings b
                          JOIN users u ON b.user_id = u.id
                          JOIN schedules s ON b.schedule_id = s.id
                          JOIN buses bus ON s.bus_id = bus.id
                          JOIN routes r ON s.route_id = r.id
                          ORDER BY b.booking_date DESC");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Bookings - Mero Bus</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container flex justify-between items-center">
            <a href="#" class="nav-brand">Admin Panel</a>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="buses.php">Buses</a>
                <a href="routes.php">Routes</a>
                <a href="schedules.php">Schedules</a>
                <a href="bookings.php" style="color: var(--primary-color);">Bookings</a>
                <a href="../logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container" style="padding: 3rem 1.5rem;">
        <h2 class="mb-4">All Bookings</h2>

        <div class="card">
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; border-bottom: 2px solid var(--border-color);">
                            <th style="padding: 1rem;">ID</th>
                            <th style="padding: 1rem;">User</th>
                            <th style="padding: 1rem;">Trip</th>
                            <th style="padding: 1rem;">Date</th>
                            <th style="padding: 1rem;">Seat</th>
                            <th style="padding: 1rem;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $bookings->fetch_assoc()): ?>
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td style="padding: 1rem;">#<?php echo $row['id']; ?></td>
                                <td style="padding: 1rem;">
                                    <?php echo htmlspecialchars($row['passenger_name']); ?><br>
                                    <small class="text-muted">By: <?php echo htmlspecialchars($row['user_name']); ?></small>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php echo htmlspecialchars($row['start_location'] . ' -> ' . $row['end_location']); ?><br>
                                    <small class="text-muted"><?php echo $row['bus_name']; ?></small>
                                </td>
                                <td style="padding: 1rem;"><?php echo date('d M, H:i', strtotime($row['departure_time'])); ?></td>
                                <td style="padding: 1rem;"><?php echo $row['seat_number']; ?></td>
                                <td style="padding: 1rem;">
                                    <span class="btn btn-secondary" style="font-size: 0.8rem; padding: 0.25rem 0.5rem;"><?php echo $row['status']; ?></span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
