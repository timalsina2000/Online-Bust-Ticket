<?php
$s_path = __DIR__ . '/sessions';
if (!file_exists($s_path)) { @mkdir($s_path, 0777, true); }
session_save_path($s_path);

session_start();
include 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$schedule_id = $_GET['schedule_id'];

// Get Schedule Details
$sql = "SELECT s.*, b.name as bus_name, b.total_seats, r.start_location, r.end_location 
        FROM schedules s
        JOIN buses b ON s.bus_id = b.id
        JOIN routes r ON s.route_id = r.id
        WHERE s.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $schedule_id);
$stmt->execute();
$schedule = $stmt->get_result()->fetch_assoc();

if (!$schedule) {
    die("Invalid Schedule");
}

// Get Booked Seats
$booked_seats = [];
$b_sql = "SELECT seat_number FROM bookings WHERE schedule_id = ? AND status='confirmed'";
$b_stmt = $conn->prepare($b_sql);
$b_stmt->bind_param("i", $schedule_id);
$b_stmt->execute();
$b_result = $b_stmt->get_result();
while($row = $b_result->fetch_assoc()) {
    $booked_seats[] = $row['seat_number'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Select Seats - Mero Bus</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .bus-layout {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            max-width: 300px;
            margin: 0 auto;
            background: #2D3748;
            padding: 2rem;
            border-radius: 1rem;
        }
        .seat {
            width: 50px;
            height: 50px;
            background: #4A5568;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
        }
        .seat.booked {
            background: #EF4444;
            cursor: not-allowed;
            pointer-events: none;
        }
        .seat.selected {
            background: #10B981;
            color: white;
        }
        .seat:hover:not(.booked) {
            background: #4F46E5;
        }
        .aisle {
            grid-column: span 1; /* Space for aisle */
            pointer-events: none;
        }
        /* Layout logic: 2 seats, aisle gap (handle with CSS grid logic better or simple loop) */
        /* Let's try 2 - aisle - 2 */
        .bus-layout-container {
             display: grid;
             grid-template-columns: repeat(5, 1fr); /* 2 seats, 1 aisle, 2 seats */
             gap: 10px;
        }
        
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container flex justify-between items-center">
            <a href="index.php" class="nav-brand">Mero Bus</a>
        </div>
    </nav>

    <div class="container" style="padding: 3rem 1.5rem;">
        <h2 class="text-center mb-4">Select Your Seats</h2>
        <p class="text-center text-muted mb-4">
            <?php echo $schedule['start_location'] . ' to ' . $schedule['end_location']; ?> | 
            Price: NPR <?php echo $schedule['price']; ?>/seat
        </p>

        <div class="flex justify-between" style="max-width: 800px; margin: 0 auto;">
            <!-- Bus Layout -->
            <div class="card">
                <h4 class="text-center mb-4">Front</h4>
                <div class="bus-layout-container">
                    <?php 
                    $total_seats = $schedule['total_seats'];
                    // Assuming 4 seats per row (2 + 2)
                    for($i = 1; $i <= $total_seats; $i++) {
                        $seat_num = $i; // Simple numeric seat numbers for demo
                        $is_booked = in_array($seat_num, $booked_seats);
                        
                        // Add aisle every 2 seats if we strictly follow 2-aisle-2, but simpler grid is easier
                        // Let's just do a simple grid, 
                        // If i % 4 == 3, maybe add an aisle div? 
                        
                        if ($i > 1 && ($i-1) % 2 == 0 && ($i-1) % 4 != 0) {
                             echo '<div class="aisle"></div>';
                        }

                        $class = $is_booked ? 'seat booked' : 'seat';
                        echo "<div class='$class' data-seat='$seat_num'>$seat_num</div>";
                    }
                    ?>
                </div>
            </div>

            <!-- Booking Summary -->
            <div class="card" style="width: 300px; height: fit-content;">
                <h3>Booking Summary</h3>
                <div id="selected-seats-display" class="my-4">
                    <p class="text-muted">No seats selected</p>
                </div>
                <div class="flex justify-between mb-4">
                    <span>Total:</span>
                    <strong id="total-price">NPR 0</strong>
                </div>
                
                <form action="process_booking.php" method="POST">
                    <input type="hidden" name="schedule_id" value="<?php echo $schedule_id; ?>">
                    <input type="hidden" name="selected_seats" id="selected-seats-input">
                    <div class="form-group">
                        <label>Passenger Name</label>
                        <input type="text" name="passenger_name" required value="<?php echo $_SESSION['name'] ?? ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>Age</label>
                        <input type="number" name="passenger_age" required>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;" id="checkout-btn" disabled>Proceed to Pay</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const pricePerSeat = <?php echo $schedule['price']; ?>;
        const seats = document.querySelectorAll('.seat:not(.booked)');
        let selectedSeats = [];

        seats.forEach(seat => {
            seat.addEventListener('click', () => {
                const seatNum = seat.getAttribute('data-seat');
                if(selectedSeats.includes(seatNum)) {
                    selectedSeats = selectedSeats.filter(s => s !== seatNum);
                    seat.classList.remove('selected');
                } else {
                    selectedSeats.push(seatNum);
                    seat.classList.add('selected');
                }
                updateSummary();
            });
        });

        function updateSummary() {
            const display = document.getElementById('selected-seats-display');
            const input = document.getElementById('selected-seats-input');
            const totalDisplay = document.getElementById('total-price');
            const btn = document.getElementById('checkout-btn');

            if(selectedSeats.length === 0) {
                display.innerHTML = '<p class="text-muted">No seats selected</p>';
                btn.disabled = true;
                totalDisplay.innerText = 'NPR 0';
            } else {
                display.innerHTML = '<p>' + selectedSeats.join(', ') + '</p>';
                btn.disabled = false;
                totalDisplay.innerText = 'NPR ' + (selectedSeats.length * pricePerSeat);
            }
            input.value = selectedSeats.join(',');
        }
    </script>
</body>
</html>
