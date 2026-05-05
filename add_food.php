<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$conn = new mysqli("localhost", "root", "", "food_redistribution");
$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = $conn->real_escape_string($_POST['food_name']);
    $category = $conn->real_escape_string($_POST['category']);
    $qty      = (int)$_POST['quantity'];
    $unit     = $conn->real_escape_string($_POST['unit']);
    $expiry   = $conn->real_escape_string($_POST['expiry_date']);
    $location = $conn->real_escape_string($_POST['pickup_location']);
    $donor_id = (int)$_SESSION['user_id'];

    if ($name && $category && $qty > 0 && $unit && $expiry && $location) {
        $sql = "INSERT INTO Food_Items (donor_id, food_name, category, quantity, unit, expiry_date, pickup_location, status, created_at)
                VALUES ($donor_id, '$name', '$category', $qty, '$unit', '$expiry', '$location', 'available', NOW())";
        $msg = $conn->query($sql) ? "Food item added successfully!" : "Error: " . $conn->error;
    } else {
        $msg = "All fields are required.";
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Add Food</title></head>
<body>
<h2>Add Food Item</h2>
<?php if ($msg) echo "<p>$msg</p>"; ?>
<form method="POST">
    <label>Food Name: <input type="text" name="food_name" required></label><br><br>
    <label>Category:
        <select name="category">
            <option>Cooked Meal</option>
            <option>Vegetables</option>
            <option>Fruits</option>
            <option>Bakery</option>
            <option>Dairy</option>
            <option>Other</option>
        </select>
    </label><br><br>
    <label>Quantity: <input type="number" name="quantity" min="1" required></label><br><br>
    <label>Unit:
        <select name="unit">
            <option>kg</option>
            <option>grams</option>
            <option>litres</option>
            <option>pieces</option>
            <option>packets</option>
        </select>
    </label><br><br>
    <label>Expiry Date: <input type="date" name="expiry_date" required></label><br><br>
    <label>Pickup Location: <input type="text" name="pickup_location" required></label><br><br>
    <button type="submit">Add Food</button>
    <a href="food_list.php">Cancel</a>
</form>
</body>
</html>
