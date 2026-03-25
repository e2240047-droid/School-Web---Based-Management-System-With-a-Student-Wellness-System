<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/db.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "teacher") {
    header("Location: login.php");
    exit();
}

$name = $_SESSION["name"] ?? "Teacher";

$total_students = 0;
$total_announcements = 0;
$total_events = 0;
$total_results = 0;

try {
    $q1 = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='student'");
    if ($q1) $total_students = (int)$q1->fetch_assoc()["c"];

    $q2 = $conn->query("SELECT COUNT(*) AS c FROM announcements");
    if ($q2) $total_announcements = (int)$q2->fetch_assoc()["c"];

    $q3 = $conn->query("SELECT COUNT(*) AS c FROM events");
    if ($q3) $total_events = (int)$q3->fetch_assoc()["c"];

    $q4 = $conn->query("SELECT COUNT(*) AS c FROM results");
    if ($q4) $total_results = (int)$q4->fetch_assoc()["c"];
} catch (Exception $e) {
    // ignore table errors
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Teacher Dashboard</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body{
      background: linear-gradient(135deg,#e0f7ff,#fff0f7,#f3ffe3);
      min-height:100vh;
      font-family:'Segoe UI',sans-serif;
    }
    .hero{
      background: linear-gradient(90deg,#0d6efd,#6f42c1,#d63384);
      color:white;
      border-radius:22px;
      padding:20px;
      box-shadow:0 12px 30px rgba(0,0,0,0.12);
      position:relative;
      overflow:hidden;
    }
    .hero::after{
      content:"";
      position:absolute;
      right:-70px;
      top:-70px;
      width:240px;
      height:240px;
      border-radius:50%;
      background:rgba(255,255,255,0.16);
      pointer-events:none;
      z-index:1;
    }
    .hero > *{
      position:relative;
      z-index:2;
    }
    .cardx{
      border:0;
      border-radius:22px;
      background:rgba(255,255,255,0.92);
      box-shadow:0 12px 25px rgba(0,0,0,0.10);
      border:1px solid rgba(255,255,255,0.6);
    }
    .soft-btn{
      border-radius:14px;
      font-weight:800;
    }
    .big{
      font-size:34px;
      font-weight:900;
    }
    .tag{
      display:inline-block;
      padding:6px 12px;
      border-radius:999px;
      font-size:12px;
      font-weight:800;
      background:rgba(255,255,255,0.22);
    }
  </style>
</head>
<body>
<div class="container py-4" style="max-width:1100px;">

  <div class="hero mb-4 d-flex justify-content-between align-items-center">
    <div>
      <h3 class="fw-bold mb-1">👩‍🏫 Teacher Dashboard</h3>
      <div class="small">Manage announcements, events, and student results</div>
      <div class="mt-2 tag">Logged in as: <?= htmlspecialchars($name) ?></div>
    </div>
    <div>
      <a href="logout.php" class="btn btn-light soft-btn">Logout</a>
    </div>
  </div>

  <div class="d-flex flex-wrap gap-2 mb-3">
   <a href="teacher_announcements.php" class="btn btn-primary soft-btn">Manage Announcements</a>
 <a href="teacher_events.php" class="btn btn-success soft-btn">Manage Events</a>
    <a href="teacher_results.php" class="btn btn-warning soft-btn">Manage Results</a>
  </div>

  <div class="row g-3 mb-3">
    <div class="col-md-3">
      <div class="cardx p-3">
        <div class="fw-bold">Students</div>
        <div class="big"><?= $total_students ?></div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="cardx p-3">
        <div class="fw-bold">Announcements</div>
        <div class="big"><?= $total_announcements ?></div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="cardx p-3">
        <div class="fw-bold">Events</div>
        <div class="big"><?= $total_events ?></div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="cardx p-3">
        <div class="fw-bold">Results</div>
        <div class="big"><?= $total_results ?></div>
      </div>
    </div>
  </div>

  <div class="cardx p-4">
    <h5 class="fw-bold mb-2">What teacher can do</h5>
    <ul class="mb-0">
      <li>Create and post announcements</li>
      <li>Add school events</li>
      <li>Upload student results</li>
    </ul>
  </div>

</div>
</body>
</html>