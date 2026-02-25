<?php
include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $token = $_POST["token"];
    $password = $_POST["password"];

    if (strlen($password) < 6) {
        die("Password must be at least 6 characters.");
    }

    $stmt = $conn->prepare("SELECT id, reset_expiry FROM users WHERE reset_token=?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {

        $user = $result->fetch_assoc();

        if (strtotime($user["reset_expiry"]) < time()) {
            die("Reset link expired.");
        }

        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $update = $conn->prepare("UPDATE users SET password=?, reset_token=NULL, reset_expiry=NULL WHERE id=?");
        $update->bind_param("si", $hashed, $user["id"]);
        $update->execute();

        echo "Password updated successfully! <a href='login.php'>Login</a>";

    } else {
        echo "Invalid reset token.";
    }
}
?>