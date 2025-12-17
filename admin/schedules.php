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
    $bus_id = $_POST['bus_id'];
    $route_id = $_POST['route_id'];
    $depart = $_POST['departure_time'];
    $arrive = $_POST['arrival_time'];
    $price = $_POST['price']; // Should be DECIMAL from input
    
    // In real app, validate dates
    $stmt = $conn->prepare("INSERT INTO schedules (bus_id, route_id, departure_time, arrival_time, price) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $bus_id, $route_id, $depart, $arrive, $price);
    $stmt->execute();
}

$buses = $conn->query("SELECT * FROM buses");
$routes = $conn->query("SELECT * FROM routes");
$schedules = $conn->query("SELECT s.*, b.name as bus_name, r.start_location, r.end_location 
                           FROM schedules s 
                           JOIN buses b ON s.bus_id = b.id 
                           JOIN routes r ON s.route_id = r.id 
                           ORDER BY s.departure_time DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Schedules - Mero Bus</title>
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
                <a href="schedules.php" style="color: var(--primary-color);">Schedules</a>
                <a href="bookings.php">Bookings</a>
                <a href="../logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container" style="padding: 3rem 1.5rem;">
        <h2 class="mb-4">Manage Schedules</h2>

        <div class="grid" style="grid-template-columns: 1fr 2fr; gap: 2rem;">
            <!-- Add Form -->
            <div class="card" style="height: fit-content;">
                <h3 class="mb-4">Create Schedule</h3>
                <form method="POST">
                    <div class="form-group">
                        <label>Select Bus</label>
                        <select name="bus_id">
                            <?php while($b = $buses->fetch_assoc()): ?>
                                <option value="<?php echo $b['id']; ?>"><?php echo $b['name']; ?> (<?php echo $b['bus_number']; ?>)</option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Select Route</label>
                        <select name="route_id">
                            <?php while($r = $routes->fetch_assoc()): ?>
                                <option value="<?php echo $r['id']; ?>"><?php echo $r['start_location']; ?> -> <?php echo $r['end_location']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Departure Time</label>
                        <input type="datetime-local" name="departure_time" required>
                    </div>
                    <div class="form-group">
                        <label>Arrival Time</label>
                        <input type="datetime-local" name="arrival_time" required>
                    </div>
                    <div class="form-group">
                        <label>Price (NPR)</label>
                        <input type="number" step="0.01" name="price" required>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Add Schedule</button>
                </form>
            </div>

            <!-- List -->
            <div class="card">
                <h3 class="mb-4">Active Schedules</h3>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="text-align: left; border-bottom: 2px solid var(--border-color);">
                                <th style="padding: 1rem;">Bus</th>
                                <th style="padding: 1rem;">Route</th>
                                <th style="padding: 1rem;">Depart</th>
                                <th style="padding: 1rem;">Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $schedules->fetch_assoc()): ?>
                                <tr style="border-bottom: 1px solid var(--border-color);">
                                    <td style="padding: 1rem;"><?php echo htmlspecialchars($row['bus_name']); ?></td>
                                    <td style="padding: 1rem;">
                                        <small class="text-muted"><?php echo htmlspecialchars($row['start_location']); ?> &rarr;</small><br>
                                        <?php echo htmlspecialchars($row['end_location']); ?>
                                    </td>
                                    <td style="padding: 1rem;">
                                        <?php echo date('d M, H:i', strtotime($row['departure_time'])); ?>
                                    </td>
                                    <td style="padding: 1rem;">NPR <?php echo $row['price']; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
