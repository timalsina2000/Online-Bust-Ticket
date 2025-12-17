<?php
$s_path = __DIR__ . '/../sessions';
if (!file_exists($s_path)) { @mkdir($s_path, 0777, true); }
session_save_path($s_path);

session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get Bookings
$sql = "SELECT b.*, s.departure_time, r.start_location, r.end_location, bus.name as bus_name 
        FROM bookings b
        JOIN schedules s ON b.schedule_id = s.id
        JOIN routes r ON s.route_id = r.id
        JOIN buses bus ON s.bus_id = bus.id
        WHERE b.user_id = ?
        ORDER BY b.booking_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bookings = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Dashboard - BusLive</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container flex justify-between items-center">
            <a href="../index.php" class="nav-brand">BusLive</a>
            <div class="nav-links">
                <a href="../index.php">Home</a>
                <a href="../logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container" style="padding: 3rem 1.5rem;">
        <h2 class="mb-4">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></h2>
        
        <div class="mb-4">
            <div class="card">
                <h3>Find a Bus</h3>
                <form action="../search.php" method="GET" class="flex gap-4 items-center flex-wrap mt-4">
                    <div class="form-group" style="flex: 1; margin-bottom: 0;">
                        <label>From</label>
                        <select name="from" required>
                            <option value="">Select Origin</option>
                            <?php 
                            // Fetch distinct start locations
                            $starts = $conn->query("SELECT DISTINCT start_location FROM routes");
                            while($s = $starts->fetch_assoc()) {
                                echo "<option value='".$s['start_location']."'>".$s['start_location']."</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group" style="flex: 1; margin-bottom: 0;">
                        <label>To</label>
                        <select name="to" required>
                            <option value="">Select Destination</option>
                            <?php 
                            // Fetch distinct end locations
                            $ends = $conn->query("SELECT DISTINCT end_location FROM routes");
                            while($e = $ends->fetch_assoc()) {
                                echo "<option value='".$e['end_location']."'>".$e['end_location']."</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <!-- Date is optional per request -->
                    <button type="submit" class="btn btn-primary" style="height: 50px; margin-top: auto;">Search</button>
                </form>
            </div>
        </div>

        <div class="grid" style="grid-template-columns: 1fr 3fr; gap: 2rem;">
            <!-- Sidebar / Profile Summary -->
            <div>
                <div class="card">
                    <h3>Profile</h3>
                    <p class="text-muted mt-4">Name: <?php echo htmlspecialchars($_SESSION['name']); ?></p>
                </div>
            </div>

            <!-- Main Content / Bookings -->
            <div>
                <h3 class="mb-4">My Bookings</h3>
                <?php if($bookings->num_rows > 0): ?>
                    <div class="grid gap-4">
                        <?php while($row = $bookings->fetch_assoc()): ?>
                            <div class="card flex justify-between items-center">
                                <div>
                                    <h4><?php echo htmlspecialchars($row['start_location'] . ' → ' . $row['end_location']); ?></h4>
                                    <p class="text-muted"><?php echo date('d M Y, H:i', strtotime($row['departure_time'])); ?></p>
                                    <small><?php echo $row['bus_name']; ?> | Seat: <?php echo $row['seat_number']; ?></small>
                                </div>
                                <div class="text-right">
                                    <span class="btn btn-secondary" style="font-size: 0.8rem; padding: 0.5rem;"><?php echo strtoupper($row['status']); ?></span>
                                    <br>
                                    <a href="../ticket.php?schedule_id=<?php echo $row['schedule_id']; ?>&seats=<?php echo $row['seat_number']; ?>" style="font-size: 0.9rem; color: var(--primary-color); display: inline-block; margin-top: 0.5rem;">View Ticket</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="card text-center">
                        <p class="text-muted">No bookings found.</p>
                        <a href="../index.php" class="btn btn-primary mt-4">Book Now</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
