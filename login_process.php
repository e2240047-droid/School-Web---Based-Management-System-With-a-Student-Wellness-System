<?php
session_start();
include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($email) || empty($password)) {
        die("All fields are required.");
    }

    // Explicitly query credentials out of persistence layer using safe prepared binding statements
    $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify cryptographic crypt signature match
        if (!password_verify($password, $user['password'])) {
            die("Invalid password.");
        }

        // Secure session tracking matrix from fixation hijacking exploits
        session_regenerate_id(true);

        $_SESSION["user_id"] = $user["id"];
        $_SESSION["name"] = $user["name"];
        $_SESSION["role"] = $user["role"]; // Role extracted dynamically from DB row directly!

        // Asynchronous routing based entirely on database role string context attributes
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
            default:
                die("Unauthorized security context exception.");
        }
        exit();

    } else {
        die("User not found.");
    }
}
?>