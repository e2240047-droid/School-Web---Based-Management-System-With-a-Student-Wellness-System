<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/db.php";

// 1. Check if the user is logged in at all
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// 2. ADVANCED SECURITY: Re-verify the user's status from the database on every page load
$auth_user_id = (int)$_SESSION["user_id"];
$auth_stmt = $conn->prepare("SELECT status FROM users WHERE id = ? LIMIT 1");
$auth_stmt->bind_param("i", $auth_user_id);
$auth_stmt->execute();
$auth_result = $auth_stmt->get_result();

if ($auth_row = $auth_result->fetch_assoc()) {
    // If the admin changed their status to Blocked or Inactive, instantly kick them out
    if ($auth_row['status'] === 'Blocked' || $auth_row['status'] === 'Inactive') {
        
        // Destroy all session data
        $_SESSION = array();
        session_destroy();
        
        // Redirect to login with an error message
        header("Location: login.php?error=Your account has been blocked or deactivated by the Administrator.");
        exit();
    }
} else {
    // If the user was completely deleted from the database while logged in
    session_destroy();
    header("Location: login.php");
    exit();
}
?>