<?php
session_start();
include "../db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: ../login.php");
    exit();
}

$id = intval($_GET["id"]);

$stmt = $conn->prepare("UPDATE users SET is_approved=1 WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: users.php");
exit();
?>