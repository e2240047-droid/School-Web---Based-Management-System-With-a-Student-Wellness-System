<?php
require_once __DIR__ . "/db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);

    if ($email == "") {
        $message = "Please enter your email.";
    } else {

        $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            header("Location: reset_password.php?email=" . urlencode($email));
            exit();
        } else {
            $message = "Email not found.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Forgot Password</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
<div class="card p-4 shadow-sm mx-auto" style="max-width:400px;">

<h4 class="mb-3 text-center">Forgot Password</h4>

<?php if($message): ?>
<div class="alert alert-danger"><?= $message ?></div>
<?php endif; ?>

<form method="post">
    <label>Email</label>
    <input type="email" name="email" class="form-control mb-3" required>

    <button class="btn btn-primary w-100">Continue</button>
</form>

<div class="text-center mt-3">
    <a href="login.php">Back to Login</a>
</div>

</div>
</div>

</body>
</html>