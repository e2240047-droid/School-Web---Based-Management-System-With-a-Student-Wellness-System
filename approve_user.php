<?php
session_start();
require_once __DIR__ . "/db.php";

// only admin can approve
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
if ($id <= 0) {
    die("Invalid user id");
}

// update approval (change column name if yours is different)
$stmt = $conn->prepare("UPDATE users SET is_approved=1 WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: users.php");
exit();