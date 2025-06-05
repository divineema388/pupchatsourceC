<?php
// Database credentials (UPDATE THESE)
$host = "sql108.infinityfree.com"; // e.g., sql123.infinityfree.com
$username = "if0_38272387";
$password = "pupchat1";
$database = "if0_38272387_pupnot";

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>