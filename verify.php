<?php
include "db.php";

if (!isset($_GET['token'])) die("Token missing");

$token = $_GET['token'];

$stmt = $conn->prepare("SELECT id FROM users WHERE verification_token=?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $stmt2 = $conn->prepare("UPDATE users SET is_verified=1, verification_token=NULL WHERE verification_token=?");
    $stmt2->bind_param("s", $token);
    $stmt2->execute();

    echo "✅ Email Verified! <a href='login.php'>Login</a>";
} else {
    echo "❌ Invalid link";
}
?>