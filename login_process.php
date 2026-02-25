<?php
session_start();
include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $role = $_POST["role"];

    if (empty($email) || empty($password) || empty($role)) {
        die("All fields are required.");
    }

    $stmt = $conn->prepare("SELECT id,name,password,role FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {

        $user = $result->fetch_assoc();

        // Verify password
        if (!password_verify($password, $user['password'])) {
            die("Invalid password.");
        }

        // Verify role
        if ($role !== $user['role']) {
            die("Incorrect role selected.");
        }

        // Secure session
        session_regenerate_id(true);

        $_SESSION["user_id"] = $user["id"];
        $_SESSION["name"] = $user["name"];
        $_SESSION["role"] = $user["role"];

        // Redirect
        switch ($user["role"]) {
            case "student":
                header("Location: student_dashboard.php");
                break;
            case "teacher":
                header("Location: teacher_dashboard.php");
                break;
            case "counsellor":
                header("Location: counsellor_dashboard.php");
                break;
            case "admin":
                header("Location: admin_dashboard.php");
                break;
        }
        exit();

    } else {
        die("User not found.");
    }
}
?>