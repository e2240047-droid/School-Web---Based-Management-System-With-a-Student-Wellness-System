<?php
// admin_dashboard.php
session_start();
require_once __DIR__ . "/db.php";

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    header("Location: login.php");
    exit();
}

// Stats (safe defaults if queries fail)
$total_users = 0;
$total_students = 0;
$total_teachers = 0;
$total_counsellors = 0;

$r = $conn->query("SELECT COUNT(*) AS c FROM users");
if ($r) $total_users = (int)($r->fetch_assoc()['c'] ?? 0);

$r = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='student'");
if ($r) $total_students = (int)($r->fetch_assoc()['c'] ?? 0);

$r = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='teacher'");
if ($r) $total_teachers = (int)($r->fetch_assoc()['c'] ?? 0);

$r = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='counsellor'");
if ($r) $total_counsellors = (int)($r->fetch_assoc()['c'] ?? 0);
?>
<!DOCTYPE html>
<html>
<head>
  <title>Admin Dashboard</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body{
      background: linear-gradient(135deg,#e0f7ff,#fff0f7,#f3ffe3);
      min-height:100vh;
      font-family:'Segoe UI', sans-serif;
    }
    .hero{
      background: linear-gradient(90deg,#0d6efd,#6f42c1,#d63384);
      color:white;
      border-radius: 22px;
      padding: 18px;
      box-shadow: 0 12px 30px rgba(0,0,0,0.12);
    }
    .cardx{
      border:0;
      border-radius: 20px;
      background: rgba(255,255,255,0.88);
      backdrop-filter: blur(8px);
      box-shadow: 0 10px 25px rgba(0,0,0,0.08);
      border: 1px solid rgba(255,255,255,0.6);
    }
    .soft-btn{
      border-radius: 14px;
      font-weight: 800;
    }
    .stat{
      font-size: 34px;
      font-weight: 900;
    }
  </style>
</head>
<body>

<div class="container py-4" style="max-width: 1100px;">

  <div class="hero w-100 d-flex justify-content-between align-items-center mb-3">
    <div>
      <h3 class="fw-bold mb-1">🛠 Admin Dashboard</h3>
      <div class="small">Manage users, announcements, events, and reports</div>
    </div>
    <div class="text-end">
      <div class="small">Hi, <b><?= htmlspecialchars($_SESSION["name"] ?? "Admin") ?></b> 👋</div>
      <a href="logout.php" class="btn btn-light soft-btn btn-sm mt-2">Logout</a>
    </div>
  </div>

  <!-- Quick buttons -->
  <div class="d-flex flex-wrap gap-2 mb-3">
    <a href="users.php" class="btn btn-primary soft-btn">Manage Users</a>
    <a href="admin_add_announcement.php" class="btn btn-warning soft-btn">Add Announcement</a>
    <a href="admin_add_event.php" class="btn btn-success soft-btn">Add Event</a>
    <a href="mood_insights.php" class="btn btn-dark soft-btn">Mood Insights</a>
  </div>

  <!-- Stats cards -->
  <div class="row g-3">
    <div class="col-md-3">
      <div class="cardx p-3">
        <div class="fw-bold">Total Users</div>
        <div class="stat"><?= (int)$total_users ?></div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="cardx p-3">
        <div class="fw-bold">Students</div>
        <div class="stat"><?= (int)$total_students ?></div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="cardx p-3">
        <div class="fw-bold">Teachers</div>
        <div class="stat"><?= (int)$total_teachers ?></div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="cardx p-3">
        <div class="fw-bold">Counsellors</div>
        <div class="stat"><?= (int)$total_counsellors ?></div>
      </div>
    </div>
  </div>

</div>
</body>
</html>