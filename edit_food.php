<?php
session_start();

// Redirect if not logged in or not a donor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'donor') {
    header("Location: login.php");
    exit();
}

// Validate food item ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: food_list.php");
    exit();
}

$food_id  = (int) $_GET['id'];
$donor_id = (int) $_SESSION['user_id'];

// Database connection
$host     = "localhost";
$db_name  = "food_redistribution";
$username = "root";
$password = "";

$conn = new mysqli($host, $username, $password, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$success = "";
$errors  = [];

// Fetch existing food item — only if it belongs to this donor
$fetch = $conn->query("SELECT * FROM Food_Items WHERE id = $food_id AND donor_id = $donor_id");
if ($fetch->num_rows === 0) {
    $conn->close();
    header("Location: food_list.php?error=notfound");
    exit();
}
$food = $fetch->fetch_assoc();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $food_name       = trim($conn->real_escape_string($_POST['food_name']));
    $category        = trim($conn->real_escape_string($_POST['category']));
    $quantity        = (int) $_POST['quantity'];
    $unit            = trim($conn->real_escape_string($_POST['unit']));
    $expiry_date     = trim($conn->real_escape_string($_POST['expiry_date']));
    $description     = trim($conn->real_escape_string($_POST['description']));
    $pickup_location = trim($conn->real_escape_string($_POST['pickup_location']));
    $status          = trim($conn->real_escape_string($_POST['status']));

    // Validation
    if (empty($food_name))       $errors[] = "Food name is required.";
    if (empty($category))        $errors[] = "Category is required.";
    if ($quantity <= 0)          $errors[] = "Quantity must be greater than zero.";
    if (empty($unit))            $errors[] = "Unit is required.";
    if (empty($expiry_date))     $errors[] = "Expiry date is required.";
    if (empty($pickup_location)) $errors[] = "Pickup location is required.";

    $allowed_statuses = ['available', 'reserved', 'collected'];
    if (!in_array($status, $allowed_statuses)) {
        $errors[] = "Invalid status selected.";
    }

    if (empty($errors)) {
        $sql = "UPDATE Food_Items SET
                    food_name       = '$food_name',
                    category        = '$category',
                    quantity        = $quantity,
                    unit            = '$unit',
                    expiry_date     = '$expiry_date',
                    description     = '$description',
                    pickup_location = '$pickup_location',
                    status          = '$status',
                    updated_at      = NOW()
                WHERE id = $food_id AND donor_id = $donor_id";

        if ($conn->query($sql)) {
            $success = "Food item updated successfully!";
            // Refresh $food with updated values
            $fetch = $conn->query("SELECT * FROM Food_Items WHERE id = $food_id");
            $food  = $fetch->fetch_assoc();
        } else {
            $errors[] = "Database error: " . $conn->error;
        }
    } else {
        // Keep posted values in $food for repopulation
        $food['food_name']       = $_POST['food_name'];
        $food['category']        = $_POST['category'];
        $food['quantity']        = $_POST['quantity'];
        $food['unit']            = $_POST['unit'];
        $food['expiry_date']     = $_POST['expiry_date'];
        $food['description']     = $_POST['description'];
        $food['pickup_location'] = $_POST['pickup_location'];
        $food['status']          = $_POST['status'];
    }
}

$conn->close();

// Status badge helper
function statusBadge($status) {
    $map = [
        'available' => ['bg' => '#f0fff4', 'border' => '#9ae6b4', 'color' => '#276749', 'label' => 'Available'],
        'reserved'  => ['bg' => '#fffbeb', 'border' => '#f6e05e', 'color' => '#744210', 'label' => 'Reserved'],
        'collected' => ['bg' => '#ebf8ff', 'border' => '#90cdf4', 'color' => '#2a4365', 'label' => 'Collected'],
    ];
    $s = $map[$status] ?? $map['available'];
    return "<span style=\"background:{$s['bg']};border:1px solid {$s['border']};color:{$s['color']};
            padding:3px 10px;border-radius:20px;font-size:0.78rem;font-weight:600;\">{$s['label']}</span>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Food Item — Food Redistribution System</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f4f8;
            color: #2d3748;
            min-height: 100vh;
            padding: 2rem 1rem;
        }

        .container { max-width: 680px; margin: 0 auto; }

        .page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 1.75rem;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .page-header h1 {
            font-size: 1.6rem;
            font-weight: 700;
            color: #1a202c;
        }

        .page-header p {
            font-size: 0.88rem;
            color: #718096;
            margin-top: 4px;
        }

        .breadcrumb {
            font-size: 0.82rem;
            color: #a0aec0;
            margin-bottom: 1.2rem;
        }

        .breadcrumb a { color: #2D7A4F; text-decoration: none; }
        .breadcrumb a:hover { text-decoration: underline; }

        .meta-bar {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.65rem 1rem;
            margin-bottom: 1.25rem;
            font-size: 0.82rem;
            color: #718096;
            flex-wrap: wrap;
        }

        .meta-bar strong { color: #2d3748; }

        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            padding: 2rem;
        }

        .alert {
            padding: 0.85rem 1rem;
            border-radius: 8px;
            margin-bottom: 1.25rem;
            font-size: 0.88rem;
        }

        .alert-success {
            background: #f0fff4;
            border: 1px solid #9ae6b4;
            color: #276749;
        }

        .alert-error {
            background: #fff5f5;
            border: 1px solid #feb2b2;
            color: #9b2c2c;
        }

        .alert ul { margin: 0.4rem 0 0 1.2rem; }
        .alert ul li { margin-bottom: 3px; }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-group { margin-bottom: 1.2rem; }

        label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 6px;
        }

        label .required { color: #e53e3e; margin-left: 2px; }

        input[type="text"],
        input[type="number"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 0.6rem 0.85rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.92rem;
            color: #2d3748;
            background: #f7fafc;
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }

        input:focus, select:focus, textarea:focus {
            border-color: #2D7A4F;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(45, 122, 79, 0.12);
        }

        textarea { resize: vertical; min-height: 90px; }

        .section-label {
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.07em;
            text-transform: uppercase;
            color: #a0aec0;
            margin: 1.5rem 0 1rem;
            padding-bottom: 6px;
            border-bottom: 1px solid #e2e8f0;
        }

        .btn-row {
            display: flex;
            gap: 0.75rem;
            margin-top: 0.5rem;
            flex-wrap: wrap;
        }

        .btn {
            padding: 0.65rem 1.5rem;
            border-radius: 8px;
            font-size: 0.92rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: background 0.18s, transform 0.1s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn:active { transform: scale(0.98); }

        .btn-primary { background: #2D7A4F; color: #fff; }
        .btn-primary:hover { background: #245f3e; }

        .btn-secondary { background: #edf2f7; color: #4a5568; }
        .btn-secondary:hover { background: #e2e8f0; }

        .btn-danger { background: #fff5f5; color: #c53030; border: 1.5px solid #feb2b2; }
        .btn-danger:hover { background: #fed7d7; }

        @media (max-width: 540px) {
            .form-row { grid-template-columns: 1fr; }
            .card { padding: 1.25rem; }
        }
    </style>
</head>
<body>
<div class="container">

    <div class="breadcrumb">
        <a href="dashboard.php">Dashboard</a> &rsaquo;
        <a href="food_list.php">Food Items</a> &rsaquo;
        Edit Food Item
    </div>

    <div class="page-header">
        <div>
            <h1>Edit Food Item</h1>
            <p>Update the details for this food listing.</p>
        </div>
        <?= statusBadge($food['status']) ?>
    </div>

    <!-- Read-only meta info -->
    <div class="meta-bar">
        <span>Item ID: <strong>#<?= $food_id ?></strong></span>
        <span>&bull;</span>
        <span>Listed: <strong><?= date('d M Y', strtotime($food['created_at'])) ?></strong></span>
        <?php if (!empty($food['updated_at'])): ?>
            <span>&bull;</span>
            <span>Last updated: <strong><?= date('d M Y, h:i A', strtotime($food['updated_at'])) ?></strong></span>
        <?php endif; ?>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success">&#10003; <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <strong>Please fix the following:</strong>
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card">
        <form method="POST" action="edit_food.php?id=<?= $food_id ?>" novalidate>

            <div class="section-label">Food Details</div>

            <div class="form-group">
                <label for="food_name">Food Name <span class="required">*</span></label>
                <input type="text" id="food_name" name="food_name"
                    value="<?= htmlspecialchars($food['food_name']) ?>" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="category">Category <span class="required">*</span></label>
                    <select id="category" name="category" required>
                        <option value="">-- Select category --</option>
                        <?php
                        $categories = ['Cooked Meal', 'Raw Vegetables', 'Fruits', 'Bakery', 'Dairy', 'Canned Goods', 'Beverages', 'Other'];
                        foreach ($categories as $cat) {
                            $sel = ($food['category'] === $cat) ? 'selected' : '';
                            echo "<option value=\"$cat\" $sel>$cat</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="expiry_date">Expiry Date <span class="required">*</span></label>
                    <input type="date" id="expiry_date" name="expiry_date"
                        value="<?= htmlspecialchars($food['expiry_date']) ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="quantity">Quantity <span class="required">*</span></label>
                    <input type="number" id="quantity" name="quantity" min="1"
                        value="<?= htmlspecialchars($food['quantity']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="unit">Unit <span class="required">*</span></label>
                    <select id="unit" name="unit" required>
                        <option value="">-- Select unit --</option>
                        <?php
                        $units = ['kg', 'grams', 'litres', 'pieces', 'packets', 'boxes', 'portions'];
                        foreach ($units as $u) {
                            $sel = ($food['unit'] === $u) ? 'selected' : '';
                            echo "<option value=\"$u\" $sel>$u</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="section-label">Status &amp; Pickup</div>

            <div class="form-row">
                <div class="form-group">
                    <label for="status">Status <span class="required">*</span></label>
                    <select id="status" name="status" required>
                        <?php
                        $statuses = ['available' => 'Available', 'reserved' => 'Reserved', 'collected' => 'Collected'];
                        foreach ($statuses as $val => $label) {
                            $sel = ($food['status'] === $val) ? 'selected' : '';
                            echo "<option value=\"$val\" $sel>$label</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="pickup_location">Pickup Location <span class="required">*</span></label>
                    <input type="text" id="pickup_location" name="pickup_location"
                        value="<?= htmlspecialchars($food['pickup_location']) ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="description">Additional Notes</label>
                <textarea id="description" name="description"><?= htmlspecialchars($food['description']) ?></textarea>
            </div>

            <div class="btn-row">
                <button type="submit" class="btn btn-primary">&#10003; Save Changes</button>
                <a href="food_list.php" class="btn btn-secondary">Cancel</a>
                <a href="delete_food.php?id=<?= $food_id ?>"
                   class="btn btn-danger"
                   onclick="return confirm('Are you sure you want to delete this food item? This action cannot be undone.');">
                   &#128465; Delete Item
                </a>
            </div>

        </form>
    </div>

</div>
</body>
</html>
