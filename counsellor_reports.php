<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . "/db.php";

if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "counsellor") {
    header("Location: login.php");
    exit();
}

$total_students = 0;
$total_mood_logs = 0;
$total_chats = 0;
$total_messages = 0;

$q1 = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='student'");
if ($q1) {
    $total_students = (int)$q1->fetch_assoc()["c"];
}

$q2 = $conn->query("SELECT COUNT(*) AS c FROM mood_logs");
if ($q2) {
    $total_mood_logs = (int)$q2->fetch_assoc()["c"];
}

$q3 = $conn->query("SELECT COUNT(*) AS c FROM anonymous_chats");
if ($q3) {
    $total_chats = (int)$q3->fetch_assoc()["c"];
}

$q4 = $conn->query("SELECT COUNT(*) AS c FROM anonymous_messages");
if ($q4) {
    $total_messages = (int)$q4->fetch_assoc()["c"];
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Counsellor Reports</title>
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
      padding:18px 20px;
      box-shadow:0 12px 30px rgba(0,0,0,0.12);
    }
    .cardx{
      border:0;
      border-radius:22px;
      background: rgba(255,255,255,0.90);
      backdrop-filter: blur(8px);
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
  </style>
</head>
<body>
<div class="container py-4" style="max-width:1100px;">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <a href="counsellor_dashboard.php" class="btn btn-outline-dark soft-btn">← Back</a>
    <a href="logout.php" class="btn btn-danger soft-btn">Logout</a>
  </div>

  <div class="hero mb-4">
    <h3 class="fw-bold mb-1">📊 Counsellor Reports</h3>
    <div class="small">Quick summary of students, moods, and anonymous chats.</div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="cardx p-3">
        <div class="fw-bold">Total Students</div>
        <div class="big"><?= $total_students ?></div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="cardx p-3">
        <div class="fw-bold">Mood Logs</div>
        <div class="big"><?= $total_mood_logs ?></div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="cardx p-3">
        <div class="fw-bold">Anonymous Chats</div>
        <div class="big"><?= $total_chats ?></div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="cardx p-3">
        <div class="fw-bold">Total Messages</div>
        <div class="big"><?= $total_messages ?></div>
      </div>
    </div>
  </div>

  <div class="cardx p-4">
    <h5 class="fw-bold mb-3">Report Summary</h5>
    <ul class="mb-0">
      <li>Total registered students: <strong><?= $total_students ?></strong></li>
      <li>Total mood logs submitted: <strong><?= $total_mood_logs ?></strong></li>
      <li>Total anonymous chat threads: <strong><?= $total_chats ?></strong></li>
      <li>Total anonymous messages exchanged: <strong><?= $total_messages ?></strong></li>
    </ul>
  </div>

</div>
</body>
</html>