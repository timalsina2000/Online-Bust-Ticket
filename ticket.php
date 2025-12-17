<?php
$s_path = __DIR__ . '/sessions';
if (!file_exists($s_path)) { @mkdir($s_path, 0777, true); }
session_save_path($s_path);

session_start();
include 'config/db.php';

// Fix: ticket.php used to include config/db.php after session_start checking user_id immediately.
// We need session_start before checking $_SESSION.
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$schedule_id = $_GET['schedule_id'];
$seats = $_GET['seats'];

// Fetch route info
$sql = "SELECT s.*, b.name as bus_name, r.start_location, r.end_location, r.duration_approx 
        FROM schedules s
        JOIN buses b ON s.bus_id = b.id
        JOIN routes r ON s.route_id = r.id
        WHERE s.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $schedule_id);
$stmt->execute();
$schedule = $stmt->get_result()->fetch_assoc();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Ticket - Mero Bus</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .ticket {
            border-left: 5px solid var(--secondary-color);
            position: relative;
        }
        .ticket::after {
            content: '';
            position: absolute;
            right: -10px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            background: var(--dark-bg);
            border-radius: 50%;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container flex justify-between items-center">
            <a href="index.php" class="nav-brand">Mero Bus</a>
            <a href="user/dashboard.php" class="btn btn-secondary">My Dashboard</a>
        </div>
    </nav>

    <div class="container" style="padding: 3rem 1.5rem; max-width: 600px;">
        <div class="text-center mb-4">
            <h2 style="color: var(--secondary-color);">Booking Confirmed!</h2>
            <p class="text-muted">Your ticket has been generated.</p>
        </div>

        <div class="card ticket">
            <div class="flex justify-between items-center border-bottom mb-4 pb-2" style="border-bottom: 1px dashed var(--border-color);">
                <h3><?php echo $schedule['bus_name']; ?></h3>
                <span>#<?php echo rand(10000, 99999); ?></span>
            </div>
            
            <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div>
                    <label>From</label>
                    <h4><?php echo $schedule['start_location']; ?></h4>
                    <small><?php echo date('H:i, d M', strtotime($schedule['departure_time'])); ?></small>
                </div>
                <div class="text-right" style="text-align: right;">
                    <label>To</label>
                    <h4><?php echo $schedule['end_location']; ?></h4>
                    <small><?php echo date('H:i, d M', strtotime($schedule['arrival_time'])); ?></small>
                </div>
            </div>

            <div class="flex justify-between items-center" style="background: rgba(0,0,0,0.2); padding: 1rem; border-radius: var(--radius);">
                <div>
                    <label>Passengers</label>
                    <p><?php echo $_SESSION['name']; ?></p>
                </div>
                <div class="text-right" style="text-align: right;">
                    <label>Seats</label>
                    <p><strong><?php echo $seats; ?></strong></p>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <button onclick="window.print()" class="btn btn-secondary">Print Ticket</button>
            </div>
        </div>
    </div>
</body>
</html>
