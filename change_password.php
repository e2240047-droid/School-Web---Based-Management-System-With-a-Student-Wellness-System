<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION["user_id"];
$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $current_password = $_POST["current_password"] ?? "";
    $new_password     = $_POST["new_password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    if ($current_password === "" || $new_password === "" || $confirm_password === "") {
        $error = "All fields are required.";
    }
    elseif ($new_password !== $confirm_password) {
        $error = "New password and confirm password do not match.";
    }
    // ✅ Strong password validation
    elseif (strlen($new_password) < 8) {
        $error = "Password must be at least 8 characters.";
    }
    elseif (!preg_match("/[A-Z]/", $new_password)) {
        $error = "Password must include at least 1 uppercase letter.";
    }
    elseif (!preg_match("/[a-z]/", $new_password)) {
        $error = "Password must include at least 1 lowercase letter.";
    }
    elseif (!preg_match("/[0-9]/", $new_password)) {
        $error = "Password must include at least 1 number.";
    }
    elseif (!preg_match("/[\W_]/", $new_password)) {
        $error = "Password must include at least 1 special character.";
    }
    else {
        // ✅ check current password
        $stmt = $conn->prepare("SELECT password FROM users WHERE id=? LIMIT 1");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if (!$row || !password_verify($current_password, $row["password"])) {
            $error = "Current password is incorrect.";
        } else {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);

            $up = $conn->prepare("UPDATE users SET password=? WHERE id=?");
            $up->bind_param("si", $hashed, $user_id);
            $up->execute();

            $message = "✅ Password updated successfully!";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Change Password</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body{
      background: linear-gradient(135deg,#e0f7ff,#fff0f7,#f3ffe3);
      min-height: 100vh;
      font-family: 'Segoe UI', sans-serif;
    }
    .cardx{
      max-width: 520px;
      margin: 0 auto;
      border: 0;
      border-radius: 22px;
      background: rgba(255,255,255,0.88);
      backdrop-filter: blur(8px);
      box-shadow: 0 12px 30px rgba(0,0,0,0.10);
      border: 1px solid rgba(255,255,255,0.6);
    }
    .hero{
      border-radius: 22px;
      background: linear-gradient(90deg,#0d6efd,#6f42c1,#d63384);
      color: white;
      padding: 18px;
      box-shadow: 0 12px 30px rgba(0,0,0,0.12);
      max-width: 520px;
      margin: 0 auto 14px;
    }
    .soft-btn{
      border-radius: 14px;
      font-weight: 800;
    }
    .rule{
      font-size: 12px;
      color: #555;
    }
  </style>
</head>
<body>

<div class="container py-4">

  <a href="student_profile.php" class="btn btn-outline-dark soft-btn mb-3">← Back</a>

  <div class="hero">
    <h4 class="fw-bold mb-1">🔐 Change Password</h4>
    <div class="small">Keep your account secure</div>
  </div>

  <div class="cardx p-4">

    <?php if($message): ?>
      <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post">
      <label class="form-label fw-bold">Current Password</label>
      <input type="password" name="current_password" class="form-control mb-3" required>

      <label class="form-label fw-bold">New Password</label>
      <input type="password" name="new_password" class="form-control mb-2" required>

      <small class="rule">
        Must be 8+ chars and include: A-Z, a-z, 0-9, special char.
      </small>

      <label class="form-label fw-bold mt-3">Confirm New Password</label>
      <input type="password" name="confirm_password" class="form-control mb-3" required>

      <button class="btn btn-primary w-100 soft-btn">Update Password</button>
    </form>

  </div>
</div>

</body>
</html>