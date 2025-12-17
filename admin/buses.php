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
    $name = $_POST['name'];
    $number = $_POST['bus_number'];
    $type = $_POST['type'];
    $seats = $_POST['total_seats'];
    
    $stmt = $conn->prepare("INSERT INTO buses (name, bus_number, type, total_seats) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $name, $number, $type, $seats);
    $stmt->execute();
}

$buses = $conn->query("SELECT * FROM buses ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Buses - Mero Bus</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container flex justify-between items-center">
            <a href="#" class="nav-brand">Admin Panel</a>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="buses.php" style="color: var(--primary-color);">Buses</a>
                <a href="routes.php">Routes</a>
                <a href="schedules.php">Schedules</a>
                <a href="bookings.php">Bookings</a>
                <a href="../logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container" style="padding: 3rem 1.5rem;">
        <div class="flex justify-between items-center mb-4">
            <h2>Manage Buses</h2>
        </div>

        <div class="grid" style="grid-template-columns: 1fr 2fr; gap: 2rem;">
            <!-- Add Bus Form -->
            <div class="card" style="height: fit-content;">
                <h3 class="mb-4">Add New Bus</h3>
                <form method="POST">
                    <div class="form-group">
                        <label>Bus Name</label>
                        <input type="text" name="name" required placeholder="e.g. GreenLine Express">
                    </div>
                    <div class="form-group">
                        <label>Bus Number</label>
                        <input type="text" name="bus_number" required placeholder="e.g. ABC-123">
                    </div>
                    <div class="form-group">
                        <label>Type</label>
                        <select name="type">
                            <option>AC Sleeper</option>
                            <option>AC Seater</option>
                            <option>Non-AC Seater</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Total Seats</label>
                        <input type="number" name="total_seats" value="40" required>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Add Bus</button>
                </form>
            </div>

            <!-- List Buses -->
            <div class="card">
                <h3 class="mb-4">Bus List</h3>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="text-align: left; border-bottom: 2px solid var(--border-color);">
                                <th style="padding: 1rem;">Name</th>
                                <th style="padding: 1rem;">Number</th>
                                <th style="padding: 1rem;">Type</th>
                                <th style="padding: 1rem;">Seats</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $buses->fetch_assoc()): ?>
                                <tr style="border-bottom: 1px solid var(--border-color);">
                                    <td style="padding: 1rem;"><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td style="padding: 1rem;"><?php echo htmlspecialchars($row['bus_number']); ?></td>
                                    <td style="padding: 1rem;"><?php echo htmlspecialchars($row['type']); ?></td>
                                    <td style="padding: 1rem;"><?php echo $row['total_seats']; ?></td>
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
