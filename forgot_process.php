<?php
include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email.");
    }

    $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {

        $token = bin2hex(random_bytes(32));
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $update = $conn->prepare("UPDATE users SET reset_token=?, reset_expiry=? WHERE email=?");
        $update->bind_param("sss", $token, $expiry, $email);
        $update->execute();

        // 🔹 EASY METHOD (no email)
        $link = "http://localhost/School%20Web%20-%20Based%20Management%20System%20With%20a%20Student%20Wellness%20System/reset_password.php?token=$token";

        echo "Click below to reset password:<br>";
        echo "<a href='$link'>Reset Password</a>";

    } else {
        echo "Email not found.";
    }
}
?>