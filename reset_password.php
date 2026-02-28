<?php 
require_once __DIR__ . "/db.php";

$email = $_GET["email"] ?? "";
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST["email"];
    $new_password = $_POST["new_password"];

    // ✅ Password Strength Validation
    if (strlen($new_password) < 8) {
        $message = "Password must be at least 8 characters.";
    }
    elseif (!preg_match("/[A-Z]/", $new_password)) {
        $message = "Password must include at least 1 uppercase letter.";
    }
    elseif (!preg_match("/[a-z]/", $new_password)) {
        $message = "Password must include at least 1 lowercase letter.";
    }
    elseif (!preg_match("/[0-9]/", $new_password)) {
        $message = "Password must include at least 1 number.";
    }
    elseif (!preg_match("/[\W_]/", $new_password)) {
        $message = "Password must include at least 1 special character.";
    }
    else {

        $hashed = password_hash($new_password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE users SET password=? WHERE email=?");
        $stmt->bind_param("ss", $hashed, $email);
        $stmt->execute();

        header("Location: login.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Reset Password</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
<div class="card p-4 shadow-sm mx-auto" style="max-width:400px;">

<h4 class="mb-3 text-center">Reset Password</h4>

<?php if($message): ?>
<div class="alert alert-danger"><?= $message ?></div>
<?php endif; ?>

<form method="post">
    <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">

    <label>New Password</label>
    <input type="password" name="new_password" class="form-control mb-2" required>

    <!-- ✅ Password Rules -->
    <small class="text-muted d-block mb-3">
        Password must be 8+ characters and include:
        A-Z, a-z, 0-9, and a special character.
    </small>

    <button class="btn btn-success w-100">Reset Password</button>
</form>

<div class="text-center mt-3">
    <a href="login.php">Back to Login</a>
</div>

</div>
</div>

<!-- ✅ Live Password Strength Check -->
<script>
const pass = document.querySelector('input[name="new_password"]');

pass.addEventListener("input", function () {
  const v = pass.value;
  let ok =
    v.length >= 8 &&
    /[A-Z]/.test(v) &&
    /[a-z]/.test(v) &&
    /[0-9]/.test(v) &&
    /[\W_]/.test(v);

  pass.style.borderColor = ok ? "green" : "red";
});
</script>

</body>
</html>Ctrl + `