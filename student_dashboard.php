<?php
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "student") {
    header("Location: login.php");
    exit();
}
?>

<h2>Welcome <?php echo htmlspecialchars($_SESSION["name"]); ?></h2>
<a href="logout.php">Logout</a>