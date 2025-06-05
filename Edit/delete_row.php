<?php
include("db_config.php");

if (isset($_POST["table"], $_POST["id"])) {
    $table = $conn->real_escape_string($_POST["table"]);
    $id = $conn->real_escape_string($_POST["id"]);

    $query = "DELETE FROM $table WHERE id='$id'";
    if ($conn->query($query)) {
        echo "Deleted!";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>