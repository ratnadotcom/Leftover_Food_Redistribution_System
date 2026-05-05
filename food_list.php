<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$conn = new mysqli("localhost", "root", "", "food_redistribution");

// Filter inputs
$category = isset($_GET['category']) ? $conn->real_escape_string($_GET['category']) : '';
$search   = isset($_GET['search'])   ? $conn->real_escape_string($_GET['search'])   : '';

// Deletion success message
$msg = isset($_GET['msg']) && $_GET['msg'] == 'deleted' ? "Food item deleted." : "";

// Build query — show only available items
$sql = "SELECT * FROM Food_Items WHERE status = 'available'";
if ($category) $sql .= " AND category = '$category'";
if ($search)   $sql .= " AND food_name LIKE '%$search%'";
$sql .= " ORDER BY expiry_date ASC";

$result = $conn->query($sql);

// Get all categories for filter dropdown
$cats = $conn->query("SELECT DISTINCT category FROM Food_Items WHERE status = 'available'");
?>
<!DOCTYPE html>
<html>
<head><title>Available Food Items</title></head>
<body>

<h2>Available Food Items</h2>
<?php if ($msg) echo "<p><b>$msg</b></p>"; ?>

<!-- Filter Form -->
<form method="GET">
    <input type="text" name="search" placeholder="Search food name..." value="<?= htmlspecialchars($search) ?>">
    <select name="category">
        <option value="">All Categories</option>
        <?php while ($row = $cats->fetch_assoc()): ?>
            <option <?= $category == $row['category'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($row['category']) ?>
            </option>
        <?php endwhile; ?>
    </select>
    <button type="submit">Filter</button>
    <a href="food_list.php">Clear</a>
    <?php if ($_SESSION['role'] == 'donor'): ?>
        &nbsp;|&nbsp; <a href="add_food.php">+ Add Food Item</a>
    <?php endif; ?>
</form>

<br>

<!-- Food Items Table -->
<?php if ($result->num_rows > 0): ?>
<table border="1" cellpadding="8" cellspacing="0">
    <tr>
        <th>#</th>
        <th>Food Name</th>
        <th>Category</th>
        <th>Quantity</th>
        <th>Expiry Date</th>
        <th>Pickup Location</th>
        <th>Actions</th>
    </tr>
    <?php $i = 1; while ($food = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $i++ ?></td>
        <td><?= htmlspecialchars($food['food_name']) ?></td>
        <td><?= htmlspecialchars($food['category']) ?></td>
        <td><?= $food['quantity'] ?> <?= $food['unit'] ?></td>
        <td><?= $food['expiry_date'] ?></td>
        <td><?= htmlspecialchars($food['pickup_location']) ?></td>
        <td>
            <?php if ($_SESSION['role'] == 'donor'): ?>
                <a href="edit_food.php?id=<?= $food['id'] ?>">Edit</a> |
                <a href="delete_food.php?id=<?= $food['id'] ?>" onclick="return confirm('Delete this item?')">Delete</a>
            <?php elseif ($_SESSION['role'] == 'receiver'): ?>
                <a href="request_food.php?id=<?= $food['id'] ?>">Request</a>
            <?php endif; ?>
        </td>
    </tr>
    <?php endwhile; ?>
</table>
<?php else: ?>
    <p>No available food items found.</p>
<?php endif; ?>

</body>
</html>
