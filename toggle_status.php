<?php
session_start();
include __DIR__ . "/db.php";

// Only admin allowed
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: login.php");
    exit();
}

// Get ID safely
$id = isset($_GET["id"]) ? intval($_GET["id"]) : 0;

if ($id > 0) {

    // Toggle status (1 -> 0, 0 -> 1)
    $stmt = $conn->prepare("UPDATE users SET status = IF(status=1,0,1) WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: users.php");
exit();
?>