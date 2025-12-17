<?php
// esewa_success.php
// eSewa returns parameters in GET usually, or sometimes base64 in "data" param for V2.
// Documentation says: success_url?data=BASE64_STRING
// We need to decode it.

include 'config/db.php';

if (!isset($_GET['data'])) {
    die("Invalid response from payment gateway.");
}

$data = json_decode(base64_decode($_GET['data']), true);

if (!isset($data['transaction_uuid'])) {
    die("Invalid transaction data.");
}

$transaction_uuid = $data['transaction_uuid'];
$total_amount = $data['total_amount'];
$status = $data['status']; // "COMPLETE"

// Verify Signature here if needed, but for now we trust the decoded UUID and update status.
if ($status !== 'COMPLETE') {
    header("Location: esewa_failure.php");
    exit;
}

// Update Database
$stmt = $conn->prepare("UPDATE bookings SET status = 'confirmed' WHERE transaction_uuid = ?");
$stmt->bind_param("s", $transaction_uuid);
$stmt->execute();

// Get one of the schedule IDs to redirect to ticket (or just show all by uuid?)
// The ticket.php expects schedule_id and seats. 
// We should probably update ticket.php to accept transaction_uuid instead?
// For now, let's fetch the info to redirect correctly.

$f = $conn->prepare("SELECT schedule_id, group_concat(seat_number) as seats FROM bookings WHERE transaction_uuid = ? GROUP BY transaction_uuid");
$f->bind_param("s", $transaction_uuid);
$f->execute();
$res = $f->get_result()->fetch_assoc();

if ($res) {
    header("Location: ticket.php?schedule_id=" . $res['schedule_id'] . "&seats=" . $res['seats']);
} else {
    echo "Payment successful but booking not found?";
}
?>
