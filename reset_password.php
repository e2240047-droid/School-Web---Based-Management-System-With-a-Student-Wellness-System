<?php
if (!isset($_GET['token'])) {
    die("Invalid request.");
}
?>

<form action="reset_process.php" method="POST">
    <input type="hidden" name="token" value="<?php echo $_GET['token']; ?>">
    <h3>Reset Password</h3>
    <input type="password" name="password" placeholder="New Password" required>
    <button type="submit">Update Password</button>
</form>