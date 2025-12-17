
<?php
include '../config/db.php';
if ($_POST) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $conn->query("INSERT INTO buses (name, price) VALUES ('$name','$price')");
    echo "Bus added";
}
?>
<form method="post">
Bus Name: <input name="name"><br>
Price: <input name="price"><br>
<button>Add</button>
</form>
