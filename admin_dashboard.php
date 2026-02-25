<?php
session_start();
include __DIR__ . "/db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: login.php");
    exit();

}

// Stats
$total_users = $conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'];
$total_students = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='student'")->fetch_assoc()['c'];
$total_teachers = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='teacher'")->fetch_assoc()['c'];
$total_counsellors = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='counsellor'")->fetch_assoc()['c'];

$pending_staff = $conn->query("SELECT COUNT(*) AS c FROM users 
    WHERE (role='teacher' OR role='counsellor') AND is_approved=0")->fetch_assoc()['c'];
?>

<!DOCTYPE html>
<html>
<head>
  <title>Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center">
    <h3>Admin Dashboard</h3>
    <div>
      <span class="me-3">Hi, <?php echo htmlspecialchars($_SESSION["name"]); ?></span>
      <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>
  </div>

  <hr>

  <!-- Quick buttons -->
  <div class="mb-3">
<a href="users.php" class="btn btn-primary">Manage Users</a>
<a href="reports.php" class="btn btn-success">Reports & Insights</a>
  </div>

  <!-- Stats cards -->
  <div class="row g-3">
    <div class="col-md-3">
      <div class="card shadow-sm p-3">
        <div class="fw-bold">Total Users</div>
        <div class="display-6"><?php echo $total_users; ?></div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card shadow-sm p-3">
        <div class="fw-bold">Students</div>
        <div class="display-6"><?php echo $total_students; ?></div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card shadow-sm p-3">
        <div class="fw-bold">Teachers</div>
        <div class="display-6"><?php echo $total_teachers; ?></div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card shadow-sm p-3">
        <div class="fw-bold">Counsellors</div>
        <div class="display-6"><?php echo $total_counsellors; ?></div>
      </div>
    </div>

    <div class="col-md-12">
      <div class="card shadow-sm p-3">
        <div class="fw-bold">Pending Approvals (Teacher/Counsellor)</div>
        <div class="display-6"><?php echo $pending_staff; ?></div>
        <div class="text-muted">Go to “Manage Users” to approve.</div>
      </div>
    </div>
  </div>

</div>

</body>
</html>