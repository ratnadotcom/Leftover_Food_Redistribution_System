<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$conn    = new mysqli("localhost", "root", "", "food_redistribution");
$food_id = (int)$_GET['id'];

// Fetch food item
$result = $conn->query("SELECT * FROM Food_Items WHERE id = $food_id");
if ($result->num_rows == 0) { header("Location: food_list.php"); exit(); }
$food = $result->fetch_assoc();

$msg = "";

// Handle deletion
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sql = "DELETE FROM Food_Items WHERE id = $food_id";
    if ($conn->query($sql)) {
        header("Location: food_list.php?msg=deleted");
        exit();
    } else {
        $msg = "Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Delete Food</title></head>
<body>
<h2>Delete Food Item</h2>
<?php if ($msg) echo "<p>$msg</p>"; ?>
<p>Are you sure you want to delete <strong><?= htmlspecialchars($food['food_name']) ?></strong>?</p>
<ul>
    <li>Category: <?= htmlspecialchars($food['category']) ?></li>
    <li>Quantity: <?= $food['quantity'] ?> <?= $food['unit'] ?></li>
    <li>Expiry: <?= $food['expiry_date'] ?></li>
    <li>Location: <?= htmlspecialchars($food['pickup_location']) ?></li>
</ul>
<form method="POST">
    <button type="submit">Yes, Delete</button>
    <a href="food_list.php">Cancel</a>
</form>
</body>
</html>
