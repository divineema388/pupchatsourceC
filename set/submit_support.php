<?php
session_start();
include "../db_connect.php"; // Include your database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST["name"]);
    $email = htmlspecialchars($_POST["email"]);
    $issue = htmlspecialchars($_POST["issue"]);
    $image = "";

    // Handle Image Upload
    if (!empty($_FILES["image"]["name"])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true); // Create directory if it doesn’t exist
        }
        $image = $target_dir . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $image);
    }

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO support_issues (name, email, issue, image) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $issue, $image);

    if ($stmt->execute()) {
        echo "<script>alert('Your issue has been submitted! Support will contact you soon.'); window.location.href='support.html';</script>";
    } else {
        echo "<script>alert('Error submitting your issue. Try again.'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
}
?>