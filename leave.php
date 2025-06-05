<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION["user_id"]) || !isset($_GET["group_id"])) {
    header("Location: group.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$group_id = $_GET["group_id"];

// Remove user from group
$stmt = $conn->prepare("DELETE FROM group_members WHERE user_id = ? AND group_id = ?");
$stmt->bind_param("ii", $user_id, $group_id);
$stmt->execute();

header("Location: group.php");
exit();
?>