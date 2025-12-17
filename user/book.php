
<?php
include '../config/db.php';
$buses = $conn->query("SELECT * FROM buses");
?>
<h3>Book Ticket</h3>
<form method="post">
<select name="bus">
<?php while($b = $buses->fetch_assoc()) { ?>
<option value="<?= $b['id'] ?>"><?= $b['name'] ?> - <?= $b['price'] ?></option>
<?php } ?>
</select>
<button>Book</button>
</form>
