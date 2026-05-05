<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$conn    = new mysqli("localhost", "root", "", "food_redistribution");
$food_id = (int)$_GET['id'];
$msg     = "";

// Fetch food item
$result = $conn->query("SELECT * FROM Food_Items WHERE id = $food_id");
if ($result->num_rows == 0) { header("Location: food_list.php"); exit(); }
$food = $result->fetch_assoc();

// Handle update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = $conn->real_escape_string($_POST['food_name']);
    $category = $conn->real_escape_string($_POST['category']);
    $qty      = (int)$_POST['quantity'];
    $unit     = $conn->real_escape_string($_POST['unit']);
    $expiry   = $conn->real_escape_string($_POST['expiry_date']);
    $location = $conn->real_escape_string($_POST['pickup_location']);
    $status   = $conn->real_escape_string($_POST['status']);

    $sql = "UPDATE Food_Items SET food_name='$name', category='$category', quantity=$qty,
            unit='$unit', expiry_date='$expiry', pickup_location='$location', status='$status'
            WHERE id = $food_id";
    $msg = $conn->query($sql) ? "Updated successfully!" : "Error: " . $conn->error;

    // Refresh data
    $food = $conn->query("SELECT * FROM Food_Items WHERE id = $food_id")->fetch_assoc();
}
?>
<!DOCTYPE html>
<html>
<head><title>Edit Food</title></head>
<body>
<h2>Edit Food Item</h2>
<?php if ($msg) echo "<p>$msg</p>"; ?>
<form method="POST">
    <label>Food Name: <input type="text" name="food_name" value="<?= htmlspecialchars($food['food_name']) ?>" required></label><br><br>
    <label>Category:
        <select name="category">
            <?php foreach (['Cooked Meal','Vegetables','Fruits','Bakery','Dairy','Other'] as $c): ?>
                <option <?= $food['category']==$c ? 'selected' : '' ?>><?= $c ?></option>
            <?php endforeach; ?>
        </select>
    </label><br><br>
    <label>Quantity: <input type="number" name="quantity" value="<?= $food['quantity'] ?>" min="1" required></label><br><br>
    <label>Unit:
        <select name="unit">
            <?php foreach (['kg','grams','litres','pieces','packets'] as $u): ?>
                <option <?= $food['unit']==$u ? 'selected' : '' ?>><?= $u ?></option>
            <?php endforeach; ?>
        </select>
    </label><br><br>
    <label>Expiry Date: <input type="date" name="expiry_date" value="<?= $food['expiry_date'] ?>" required></label><br><br>
    <label>Pickup Location: <input type="text" name="pickup_location" value="<?= htmlspecialchars($food['pickup_location']) ?>" required></label><br><br>
    <label>Status:
        <select name="status">
            <?php foreach (['available','reserved','collected'] as $s): ?>
                <option <?= $food['status']==$s ? 'selected' : '' ?>><?= $s ?></option>
            <?php endforeach; ?>
        </select>
    </label><br><br>
    <button type="submit">Save Changes</button>
    <a href="food_list.php">Cancel</a>
    <a href="delete_food.php?id=<?= $food_id ?>" onclick="return confirm('Delete this item?')">Delete</a>
</form>
</body>
</html>
