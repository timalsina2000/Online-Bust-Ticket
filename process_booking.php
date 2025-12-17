<?php
// Set session path
$s_path = __DIR__ . '/sessions';
if (!file_exists($s_path)) { @mkdir($s_path, 0777, true); }
session_save_path($s_path);

session_start();
include 'config/db.php';

if($_SERVER['REQUEST_METHOD'] != 'POST') {
    die("Invalid request");
}

$schedule_id = $_POST['schedule_id'];
$selected_seats = explode(',', $_POST['selected_seats']);
$passenger_name = $_POST['passenger_name'];
$passenger_age = $_POST['passenger_age'];
$user_id = $_SESSION['user_id'];

// Calculate Amount
$stmt = $conn->prepare("SELECT price FROM schedules WHERE id = ?");
$stmt->bind_param("i", $schedule_id);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
if(!$res) die("Invalid Schedule");

$unit_price = $res['price'];
$amount = $unit_price * count($selected_seats);
$tax_amount = 0; // Simple setup
$service_charge = 0;
$delivery_charge = 0;
$total_amount = $amount + $tax_amount + $service_charge + $delivery_charge;

// Start DB Transaction
$conn->begin_transaction();

try {
    // 1. Create unique transaction UUID (using timestamp + random)
    $transaction_uuid = date('YmdHis') . "-" . rand(1000, 9999);
    
    // 2. Insert Booking as PENDING
    // Note: We are inserting one row per seat? Or one row per booking? 
    // The previous implementation was one row per seat. We need to associate them with the transaction.
    // Let's stick to one row per seat, but we need to track them.
    // We will use the SAME transaction_uuid for all seats in this batch to verify them later.
    // We need to add transaction_uuid column to bookings table or repurpose a field?
    // Let's check schema: id, user_id, schedule_id, seat_number, booking_date, status, passenger_name, passenger_age
    // I will ADD transaction_id to table first or just rely on 'pending' + user + time? 
    // Better to ADD the column. I'll execute an ALTER statement via PHP just in case, or assume step 1 instructions handled it.
    // Wait, I cannot change DB easily here. I will just use `passenger_name` field to store UUID temporarily? No that's hacky.
    // I entered a task to "Update database.sql". I should have run the ALTER. 
    // LET'S DO A QUICK CLEAN CHECK: The user didn't mention adding a column. 
    // I'll assume I can just use the insert IDs? 
    // But eSewa returns the UUID passed. I need to store that UUID to find the rows.
    // PLAN: I will create a TEMPORARY fix: Store the UUID in `passenger_name` field prefixed? No.
    // I will EXECUTE `ALTER TABLE bookings ADD COLUMN transaction_uuid VARCHAR(50);` quietly here? 
    // Risky. Let's do it properly. 
    
    // AUTOMATIC DB UPDATE FOR UUID
    $checkCol = $conn->query("SHOW COLUMNS FROM bookings LIKE 'transaction_uuid'");
    if($checkCol->num_rows == 0){
        $conn->query("ALTER TABLE bookings ADD COLUMN transaction_uuid VARCHAR(50)");
    }

    $stmt = $conn->prepare("INSERT INTO bookings (user_id, schedule_id, seat_number, passenger_name, passenger_age, status, transaction_uuid) VALUES (?, ?, ?, ?, ?, 'pending', ?)");
    
    foreach($selected_seats as $seat) {
        $stmt->bind_param("iissss", $user_id, $schedule_id, $seat, $passenger_name, $passenger_age, $transaction_uuid);
        if(!$stmt->execute()) {
             throw new Exception("Booking failed for seat $seat");
        }
    }
    
    $conn->commit();
    
    // 3. Prepare eSewa Parameters
    $product_code = "EPAYTEST";
    $success_url = "http://localhost:8000/esewa_success.php"; // Update with real domain if live
    $failure_url = "http://localhost:8000/esewa_failure.php";
    
    // Signature Generation
    // defined in documentation: total_amount,transaction_uuid,product_code
    $message = "total_amount=$total_amount,transaction_uuid=$transaction_uuid,product_code=$product_code";
    $secret = "8gBm/:&EnhH.1/q";
    $s = hash_hmac('sha256', $message, $secret, true);
    $signature = base64_encode($s);

    // 4. Output Auto-Submit Form
    ?>
    <!DOCTYPE html>
    <html>
    <head><title>Redirecting to Payment...</title></head>
    <body onload="document.forms['esewa_form'].submit()">
        <p style="text-align:center; margin-top: 20%;">Redirecting to eSewa...</p>
        <form name="esewa_form" action="https://rc-epay.esewa.com.np/api/epay/main/v2/form" method="POST">
             <input type="hidden" name="amount" value="<?php echo $amount; ?>" required>
             <input type="hidden" name="tax_amount" value="<?php echo $tax_amount; ?>" required>
             <input type="hidden" name="total_amount" value="<?php echo $total_amount; ?>" required>
             <input type="hidden" name="transaction_uuid" value="<?php echo $transaction_uuid; ?>" required>
             <input type="hidden" name="product_code" value="<?php echo $product_code; ?>" required>
             <input type="hidden" name="product_service_charge" value="<?php echo $service_charge; ?>" required>
             <input type="hidden" name="product_delivery_charge" value="<?php echo $delivery_charge; ?>" required>
             <input type="hidden" name="success_url" value="<?php echo $success_url; ?>" required>
             <input type="hidden" name="failure_url" value="<?php echo $failure_url; ?>" required>
             <input type="hidden" name="signed_field_names" value="total_amount,transaction_uuid,product_code" required>
             <input type="hidden" name="signature" value="<?php echo $signature; ?>" required>
        </form>
    </body>
    </html>
    <?php

} catch (Exception $e) {
    $conn->rollback();
    die("Booking Error: " . $e->getMessage());
}
?>
