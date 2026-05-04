<?php
$conn = mysqli_connect("localhost", "root", "", "food_system");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (isset($_GET['approve_id'])) {
    $id = $_GET['approve_id'];

    $sql = "UPDATE requests SET status='Approved' WHERE request_id='$id'";
    mysqli_query($conn, $sql);

    echo "Request Approved!<br><br>";
}

if (isset($_GET['reject_id'])) {
    $id = $_GET['reject_id'];

    $sql = "UPDATE requests SET status='Rejected' WHERE request_id='$id'";
    mysqli_query($conn, $sql);

    echo "Request Rejected!<br><br>";
}

$result = mysqli_query($conn, "SELECT * FROM requests");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Approve Requests</title>
</head>
<body>

<h2>Food Requests</h2>

<table border="1" cellpadding="8">
    <tr>
        <th>ID</th>
        <th>User ID</th>
        <th>Food ID</th>
        <th>Quantity</th>
        <th>Status</th>
        <th>Action</th>
    </tr>

    <?php
    
    while ($row = mysqli_fetch_assoc($result)) {
    ?>
    <tr>
        <td><?php echo $row['request_id']; ?></td>
        <td><?php echo $row['user_id']; ?></td>
        <td><?php echo $row['food_id']; ?></td>
        <td><?php echo $row['quantity']; ?></td>
        <td><?php echo $row['status']; ?></td>
        <td>
            <?php
            
            if ($row['status'] == "Pending") {
            ?>
                <a href="?approve_id=<?php echo $row['request_id']; ?>">Approve</a> |
                <a href="?reject_id=<?php echo $row['request_id']; ?>">Reject</a>
            <?php
            } else {
                echo "No action";
            }
            ?>
        </td>
    </tr>
    <?php } ?>

</table>

</body>
</html>