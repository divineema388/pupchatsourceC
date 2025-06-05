<?php
$servername = "sql108.infinityfree.com";
$username = "if0_38272387";
$password = "pupchat1";
$database = "if0_38272387_pupnot";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>