<?php
require_once __DIR__ . "/db.php";

// check if an admin already exists
$adminExists = false;
$res = $conn->query("SELECT id FROM users WHERE role='admin' LIMIT 1");
if ($res && $res->num_rows > 0) {
    $adminExists = true;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Register - School Wellness System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container">
  <div class="row justify-content-center align-items-center vh-100">
    <div class="col-md-5">

      <div class="card shadow p-4">
        <div class="text-center mb-3">
          <img src="logo.png" width="70">
          <h4 class="mt-2">Register</h4>
          <p class="text-muted">Create your account</p>
        </div>

        <form action="register_process.php" method="POST">

          <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
          </div>

          <div class="mb-3">
           <select name="role" class="form-select" required>
  <option value="student">Student</option>
  <option value="teacher">Teacher</option>
  <option value="counsellor">Counsellor</option>

  <?php if (!$adminExists): ?>
    <option value="admin">Admin (Only one time)</option>
  <?php endif; ?>
</select>

          <div class="d-grid">
            <button type="submit" class="btn btn-success">Create Account</button>
          </div>

        </form>

        <div class="text-center mt-3">
          <small>Already have an account? <a href="login.php">Login</a></small>
        </div>

      </div>

    </div>
  </div>
</div>

</body>
</html>