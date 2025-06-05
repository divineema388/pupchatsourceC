<?php
include("db_config.php");

if (isset($_POST["table"])) {
    $table = $conn->real_escape_string($_POST["table"]);
    $result = $conn->query("SELECT * FROM $table");

    if ($result->num_rows > 0) {
        echo "<table class='table table-bordered'><tr>";
        $fields = $result->fetch_fields();
        foreach ($fields as $field) {
            echo "<th>{$field->name}</th>";
        }
        echo "<th>Actions</th></tr>";

        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $key => $value) {
                echo "<td contenteditable='true' onBlur='updateData(\"$table\", \"$key\", \"$value\", this)'>$value</td>";
            }
            echo "<td><button class='btn btn-danger btn-sm' onclick='deleteRow(\"$table\", \"$row[id]\")'>Delete</button></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='text-warning'>No records found.</p>";
    }
}
?>