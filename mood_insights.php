<?php
// mood_insights.php
session_start();
require_once __DIR__ . "/db.php";

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    header("Location: login.php");
    exit();
}

// ✅ Check if mood_log table exists
$check = $conn->query("SHOW TABLES LIKE 'mood_log'");
$table_exists = ($check && $check->num_rows > 0);

// Default stats
$total = 0;
$happy = 0; $sad = 0; $stressed = 0; $angry = 0; $neutral = 0;

if ($table_exists) {
    // ✅ Check the correct column name (mood or mood_type)
    $colCheck = $conn->query("SHOW COLUMNS FROM mood_log LIKE 'mood'");
    $moodCol = ($colCheck && $colCheck->num_rows > 0) ? "mood" : "mood_type";

    $resTotal = $conn->query("SELECT COUNT(*) AS c FROM mood_log");
    $total = (int)(($resTotal->fetch_assoc()["c"] ?? 0));

    $counts = $conn->query("SELECT $moodCol AS mood, COUNT(*) AS c FROM mood_log GROUP BY $moodCol");
    if ($counts) {
        while ($r = $counts->fetch_assoc()) {
            $m = strtolower(trim($r["mood"] ?? ""));
            $c = (int)($r["c"] ?? 0);

            if ($m === "happy") $happy = $c;
            elseif ($m === "sad") $sad = $c;
            elseif ($m === "stressed") $stressed = $c;
            elseif ($m === "angry") $angry = $c;
            elseif ($m === "neutral") $neutral = $c;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Mood Insights</title>
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
      border-radius:22px;
      padding:18px;
      box-shadow:0 12px 30px rgba(0,0,0,0.12);
    }
    .cardx{
      border:0;
      border-radius:20px;
      background: rgba(255,255,255,0.90);
      box-shadow:0 12px 25px rgba(0,0,0,0.10);
      padding:18px;
    }
  </style>
</head>
<body>

<div class="container py-4" style="max-width:1100px;">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <a href="admin_dashboard.php" class="btn btn-outline-dark fw-bold">← Back</a>
    <a href="logout.php" class="btn btn-danger fw-bold">Logout</a>
  </div>

  <div class="hero mb-4">
    <h3 class="fw-bold mb-1">📊 Mood Insights</h3>
    <div class="small">Overview of students’ mood logs (summary only)</div>
  </div>

  <?php if (!$table_exists): ?>
    <div class="alert alert-warning">
      <b>mood_log table not found.</b><br>
      Create the table first (or rename your table to <code>mood_log</code>).
    </div>
  <?php else: ?>
    <div class="row g-3">
      <div class="col-md-3"><div class="cardx"><div class="fw-bold">Total Logs</div><div class="display-6"><?= (int)$total ?></div></div></div>
      <div class="col-md-3"><div class="cardx"><div class="fw-bold">Happy 😊</div><div class="display-6"><?= (int)$happy ?></div></div></div>
      <div class="col-md-3"><div class="cardx"><div class="fw-bold">Neutral 😐</div><div class="display-6"><?= (int)$neutral ?></div></div></div>
      <div class="col-md-3"><div class="cardx"><div class="fw-bold">Sad 😔</div><div class="display-6"><?= (int)$sad ?></div></div></div>

      <div class="col-md-4"><div class="cardx"><div class="fw-bold">Stressed 😣</div><div class="display-6"><?= (int)$stressed ?></div></div></div>
      <div class="col-md-4"><div class="cardx"><div class="fw-bold">Angry 😡</div><div class="display-6"><?= (int)$angry ?></div></div></div>
      <div class="col-md-4"><div class="cardx"><div class="fw-bold">Other</div><div class="small text-muted">Moods not in this list may exist in DB but are not shown here.</div></div></div>
    </div>
  <?php endif; ?>

</div>
</body>
</html>