<?php
// Database configuration
$servername = "sql108.infinityfree.com";  // Your server name (usually localhost)
$username = "if0_38272387";  // Your database username
$password = "pupchat1";  // Your database password
$dbname = "if0_38272387_LoginApp";  // Your database name
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8
$conn->set_charset("utf8");

// Function to sanitize input - only declare if not already declared
if (!function_exists('sanitize_input')) {
    function sanitize_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
}
?><?php
// Database configuration
$servername = "sql108.infinityfree.com";  // Your server name (usually localhost)
$username = "if0_38272387";  // Your database username
$password = "pupchat1";  // Your database password
$dbname = "if0_38272387_LoginApp";  // Your database name
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8
$conn->set_charset("utf8");

// Function to sanitize input - only declare if not already declared
if (!function_exists('sanitize_input')) {
    function sanitize_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
}
?>