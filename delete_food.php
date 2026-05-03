<?php
session_start();

// Redirect if not logged in or not a donor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'donor') {
    header("Location: login.php");
    exit();
}

// Validate food item ID
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

// Fetch food item — only if it belongs to this donor
$fetch = $conn->query("SELECT * FROM Food_Items WHERE id = $food_id AND donor_id = $donor_id");
if ($fetch->num_rows === 0) {
    $conn->close();
    header("Location: food_list.php?error=notfound");
    exit();
}
$food = $fetch->fetch_assoc();

$error = "";

// Block deletion if item has active/pending requests
$check = $conn->query("SELECT COUNT(*) as cnt FROM Requests 
                        WHERE food_id = $food_id AND status IN ('pending', 'approved')");
$row = $check->fetch_assoc();
$has_active_requests = ($row['cnt'] > 0);

// Handle confirmed deletion
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['confirm_delete'])) {

    if ($has_active_requests) {
        $error = "Cannot delete: this item has active or pending requests. Please resolve them first.";
    } else {
        // Soft delete: mark as 'deleted' rather than hard-remove (preserves history)
        $sql = "UPDATE Food_Items SET status = 'deleted', updated_at = NOW() 
                WHERE id = $food_id AND donor_id = $donor_id";

        if ($conn->query($sql)) {
            $conn->close();
            header("Location: food_list.php?deleted=1");
            exit();
        } else {
            $error = "Database error: " . $conn->error;
        }
    }
}

$conn->close();

// Expiry status helper
function expiryStatus($date) {
    $days = (strtotime($date) - strtotime(date('Y-m-d'))) / 86400;
    if ($days < 0)  return ['label' => 'Expired',       'color' => '#c53030'];
    if ($days <= 2) return ['label' => 'Expires soon',  'color' => '#c05621'];
    return                 ['label' => 'Valid',          'color' => '#276749'];
}

$expiry = expiryStatus($food['expiry_date']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Food Item — Food Redistribution System</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f4f8;
            color: #2d3748;
            min-height: 100vh;
            padding: 2rem 1rem;
        }

        .container { max-width: 560px; margin: 0 auto; }

        .breadcrumb {
            font-size: 0.82rem;
            color: #a0aec0;
            margin-bottom: 1.5rem;
        }

        .breadcrumb a { color: #2D7A4F; text-decoration: none; }
        .breadcrumb a:hover { text-decoration: underline; }

        /* Danger confirmation card */
        .confirm-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            overflow: hidden;
        }

        .danger-header {
            background: #fff5f5;
            border-bottom: 1px solid #fed7d7;
            padding: 1.5rem 2rem;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .danger-icon {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            background: #fed7d7;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
        }

        .danger-header h1 {
            font-size: 1.15rem;
            font-weight: 700;
            color: #9b2c2c;
        }

        .danger-header p {
            font-size: 0.85rem;
            color: #c53030;
            margin-top: 3px;
        }

        .card-body { padding: 1.75rem 2rem; }

        /* Food item preview */
        .food-preview {
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.5rem;
        }

        .food-preview h2 {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 0.75rem;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.6rem 1.5rem;
        }

        .detail-item { font-size: 0.85rem; }
        .detail-label { color: #718096; font-size: 0.78rem; margin-bottom: 2px; }
        .detail-value { color: #2d3748; font-weight: 600; }

        /* Warning box for active requests */
        .warning-box {
            background: #fffbeb;
            border: 1px solid #f6e05e;
            border-radius: 8px;
            padding: 0.85rem 1rem;
            margin-bottom: 1.25rem;
            font-size: 0.87rem;
            color: #744210;
        }

        .warning-box strong { display: block; margin-bottom: 3px; }

        .alert-error {
            background: #fff5f5;
            border: 1px solid #feb2b2;
            border-radius: 8px;
            color: #9b2c2c;
            padding: 0.85rem 1rem;
            font-size: 0.88rem;
            margin-bottom: 1.25rem;
        }

        /* Soft-delete notice */
        .info-note {
            background: #ebf8ff;
            border: 1px solid #90cdf4;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-size: 0.82rem;
            color: #2a4365;
            margin-bottom: 1.5rem;
        }

        .btn-row {
            display: flex;
            gap: 0.75rem;
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

        .btn-danger {
            background: #e53e3e;
            color: #fff;
        }

        .btn-danger:hover { background: #c53030; }

        .btn-danger:disabled {
            background: #fed7d7;
            color: #c53030;
            cursor: not-allowed;
            opacity: 0.7;
        }

        .btn-secondary {
            background: #edf2f7;
            color: #4a5568;
        }

        .btn-secondary:hover { background: #e2e8f0; }

        @media (max-width: 480px) {
            .detail-grid { grid-template-columns: 1fr; }
            .card-body { padding: 1.25rem; }
            .danger-header { padding: 1.25rem; }
        }
    </style>
</head>
<body>
<div class="container">

    <div class="breadcrumb">
        <a href="dashboard.php">Dashboard</a> &rsaquo;
        <a href="food_list.php">Food Items</a> &rsaquo;
        <a href="edit_food.php?id=<?= $food_id ?>">Edit Item</a> &rsaquo;
        Delete
    </div>

    <div class="confirm-card">

        <div class="danger-header">
            <div class="danger-icon">&#9888;</div>
            <div>
                <h1>Delete Food Item</h1>
                <p>This action cannot be undone. Please review before confirming.</p>
            </div>
        </div>

        <div class="card-body">

            <?php if ($error): ?>
                <div class="alert-error">&#9888; <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($has_active_requests): ?>
                <div class="warning-box">
                    <strong>&#128274; Deletion blocked</strong>
                    This food item has one or more <strong>pending or approved requests</strong>.
                    Please cancel or resolve those requests before deleting this item.
                    <br><br>
                    <a href="food_requests.php?food_id=<?= $food_id ?>" style="color:#744210;font-weight:700;">
                        View related requests &rarr;
                    </a>
                </div>
            <?php endif; ?>

            <!-- Food item summary -->
            <div class="food-preview">
                <h2><?= htmlspecialchars($food['food_name']) ?></h2>
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Category</div>
                        <div class="detail-value"><?= htmlspecialchars($food['category']) ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Quantity</div>
                        <div class="detail-value"><?= htmlspecialchars($food['quantity']) ?> <?= htmlspecialchars($food['unit']) ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Expiry Date</div>
                        <div class="detail-value" style="color:<?= $expiry['color'] ?>">
                            <?= date('d M Y', strtotime($food['expiry_date'])) ?>
                            &nbsp;(<?= $expiry['label'] ?>)
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Current Status</div>
                        <div class="detail-value"><?= ucfirst(htmlspecialchars($food['status'])) ?></div>
                    </div>
                    <div class="detail-item" style="grid-column: 1 / -1;">
                        <div class="detail-label">Pickup Location</div>
                        <div class="detail-value"><?= htmlspecialchars($food['pickup_location']) ?></div>
                    </div>
                    <?php if (!empty($food['description'])): ?>
                    <div class="detail-item" style="grid-column: 1 / -1;">
                        <div class="detail-label">Notes</div>
                        <div class="detail-value" style="font-weight:400;"><?= htmlspecialchars($food['description']) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="info-note">
                &#8505;&nbsp; This item will be marked as <strong>deleted</strong> in the database.
                Historical records and completed delivery data linked to it will be preserved.
            </div>

            <form method="POST" action="delete_food.php?id=<?= $food_id ?>">
                <div class="btn-row">
                    <?php if ($has_active_requests): ?>
                        <button type="button" class="btn btn-danger" disabled>&#128465; Delete Item</button>
                    <?php else: ?>
                        <button type="submit" name="confirm_delete" value="1" class="btn btn-danger">
                            &#128465; Yes, Delete This Item
                        </button>
                    <?php endif; ?>
                    <a href="food_list.php" class="btn btn-secondary">&#8592; Go Back</a>
                </div>
            </form>

        </div>
    </div>

</div>
</body>
</html>
