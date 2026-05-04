<?php
$conn=new mysqli('localhost','root','','food_redistribution');
if($conn->connect_error) die("DB Error: ".$conn->connect_error);
$conn->set_charset("utf8mb4");
?>
