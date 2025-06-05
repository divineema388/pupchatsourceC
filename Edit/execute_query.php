<?php
include("db_config.php");

if (isset($_POST["query"])) {
    $query = $_POST["query"];
    if ($conn->multi_query($query)) {
        echo "<p class='text-success'>Query executed successfully!</p>";
    } else {
        echo "<p class='text-danger'>Error: " . $conn->error . "</p>";
    }
}
?>