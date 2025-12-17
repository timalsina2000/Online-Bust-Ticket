<?php
$s_path = __DIR__ . '/sessions';
if (!file_exists($s_path)) { @mkdir($s_path, 0777, true); }
session_save_path($s_path);

session_start();
include 'config/db.php';

$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$date = $_GET['date'] ?? '';

// Build query dynamically
$sql = "SELECT s.*, b.name as bus_name, b.type as bus_type, b.total_seats, r.start_location, r.end_location, r.duration_approx 
        FROM schedules s
        JOIN buses b ON s.bus_id = b.id
        JOIN routes r ON s.route_id = r.id
        WHERE r.start_location LIKE ? AND r.end_location LIKE ?";

$params = ["ss", "%$from%", "%$to%"];

if (!empty($date)) {
    $sql .= " AND DATE(s.departure_time) = ?";
    $params[0] .= "s";
    $params[] = $date;
} else {
    // If no date, show only future buses
    $sql .= " AND s.departure_time >= NOW()";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param(...$params);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Results - BusLive</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container flex justify-between items-center">
            <a href="index.php" class="nav-brand">BusLive</a>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="user/dashboard.php">Dashboard</a>
                    <a href="logout.php" class="btn btn-primary" style="padding: 0.5rem 1rem;">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary" style="padding: 0.5rem 1rem;">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container" style="padding: 3rem 1.5rem;">
        <h2 class="mb-4">Results for <?php echo htmlspecialchars($from); ?> to <?php echo htmlspecialchars($to); ?></h2>
        
        <?php if($result->num_rows > 0): ?>
            <div class="grid gap-4">
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="card flex justify-between items-center flex-wrap">
                        <div>
                            <h3><?php echo htmlspecialchars($row['bus_name']); ?></h3>
                            <p class="text-muted"><?php echo htmlspecialchars($row['bus_type']); ?></p>
                            <div style="margin-top: 0.5rem;">
                                <strong><?php echo date('H:i', strtotime($row['departure_time'])); ?></strong> 
                                <span class="text-muted" style="margin: 0 1rem;">--- <?php echo htmlspecialchars($row['duration_approx']); ?> ---</span>
                                <strong><?php echo date('H:i', strtotime($row['arrival_time'])); ?></strong>
                            </div>
                        </div>
                        <div class="text-center" style="min-width: 150px;">
                            <h3 style="color: var(--secondary-color);">$<?php echo $row['price']; ?></h3>
                            <p class="text-muted mb-4"><?php echo $row['total_seats']; ?> Seats</p>
                            <a href="booking.php?schedule_id=<?php echo $row['id']; ?>" class="btn btn-primary">Select Seats</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="card text-center">
                <h3>No buses found for this route/date.</h3>
                <p class="text-muted">Try searching for different dates or locations.</p>
                <a href="index.php" class="btn btn-secondary mt-4">Go Back</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
