<?php
$conn = mysqli_connect("localhost", "root", "", "food_system");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (isset($_POST['submit'])) {

    $user_id = $_POST['user_id'];
    $food_id = $_POST['food_id'];
    $quantity = $_POST['quantity'];

    
if ($user_id == "" || $food_id == "" || $quantity == "") {
        echo "All fields are required!";
    } else {

        
        $sql = "INSERT INTO requests (user_id, food_id, quantity, status)
                VALUES ('$user_id', '$food_id', '$quantity', 'Pending')";

if (mysqli_query($conn, $sql)) {
            echo "Request submitted successfully!";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Request Food</title>
</head>
<body>

<h2>Request Food</h2>

<form method="POST">

    <label>User ID:</label><br>
    <input type="text" name="user_id"><br><br>

    <label>Food ID:</label><br>
    <input type="text" name="food_id"><br><br>

    <label>Quantity:</label><br>
    <input type="number" name="quantity"><br><br>

    <input type="submit" name="submit" value="Request Food">

</form>

</body>
</html>