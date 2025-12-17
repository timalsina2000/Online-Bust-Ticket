<?php
session_start();
if($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: index.php");
    exit;
}

$schedule_id = $_POST['schedule_id'];
$selected_seats = $_POST['selected_seats']; // comma separated
$passenger_name = $_POST['passenger_name'];
$passenger_age = $_POST['passenger_age'];

// Simple validation
if(empty($selected_seats)) {
    die("No seats selected");
}

$seats_array = explode(',', $selected_seats);
$count = count($seats_array);

// In a real app, we would re-fetch price from DB here to be secure.
include 'config/db.php';
$stmt = $conn->prepare("SELECT price FROM schedules WHERE id = ?");
$stmt->bind_param("i", $schedule_id);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$total_price = $res['price'] * $count;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment - BusLive</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container center">
            <a href="#" class="nav-brand">Secure Payment</a>
        </div>
    </nav>

    <div class="container" style="padding: 3rem 1.5rem; display: flex; justify-content: center;">
        <div class="card" style="max-width: 500px; width: 100%;">
            <h2 class="mb-4">Payment Details</h2>
            <div class="flex justify-between mb-4" style="border-bottom: 1px solid var(--border-color); padding-bottom: 1rem;">
                <span>Seats Selected</span>
                <strong><?php echo $selected_seats; ?></strong>
            </div>
            <div class="flex justify-between mb-4">
                <span>Total Amount</span>
                <strong style="font-size: 1.5rem; color: var(--primary-color);">$<?php echo $total_price; ?></strong>
            </div>

            <form action="process_booking.php" method="POST">
                <input type="hidden" name="schedule_id" value="<?php echo $schedule_id; ?>">
                <input type="hidden" name="selected_seats" value="<?php echo $selected_seats; ?>">
                <input type="hidden" name="passenger_name" value="<?php echo $passenger_name; ?>">
                <input type="hidden" name="passenger_age" value="<?php echo $passenger_age; ?>">
                
                <div class="form-group">
                    <label>Card Number (Simulation)</label>
                    <input type="text" value="4444 4444 4444 4444" readonly style="background: rgba(0,0,0,0.1);">
                </div>
                <div class="flex gap-4">
                     <div class="form-group">
                        <label>Expiry</label>
                        <input type="text" value="12/26" readonly style="background: rgba(0,0,0,0.1);">
                    </div>
                     <div class="form-group">
                        <label>CVV</label>
                        <input type="text" value="123" readonly style="background: rgba(0,0,0,0.1);">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">Pay Now</button>
            </form>
        </div>
    </div>
</body>
</html>
