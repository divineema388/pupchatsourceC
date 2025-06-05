<?php
include("db_config.php");

if (isset($_POST["table"], $_POST["column"], $_POST["id"], $_POST["value"])) {
    $table = $conn->real_escape_string($_POST["table"]);
    $column = $conn->real_escape_string($_POST["column"]);
    $id = $conn->real_escape_string($_POST["id"]);
    $value = $conn->real_escape_string($_POST["value"]);

    $query = "UPDATE $table SET $column='$value' WHERE id='$id'";
    if ($conn->query($query)) {
        echo "Updated!";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>