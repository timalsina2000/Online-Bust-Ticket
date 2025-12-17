<?php
include 'config/db.php';

$sql = "ALTER TABLE bookings ADD COLUMN transaction_uuid VARCHAR(50)";
if ($conn->query($sql) === TRUE) {
    echo "Column transaction_uuid added successfully";
} else {
    echo "Error adding column: " . $conn->error;
}
?>
