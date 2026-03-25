<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . "/db.php";

// ✅ Protect page
if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "counsellor") {
    header("Location: login.php");
    exit();
}

$name = $_SESSION["name"] ?? "Counsellor";

// ✅ Optional stats (safe queries)
$total_students = 0;
$total_mood_logs = 0;
$total_chats = 0;

try {
    // If your tables exist, counts will work. If not, it will just stay 0.
    $q1 = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='student'");
    if ($q1) $total_students = (int)$q1->fetch_assoc()["c"];

    $q2 = $conn->query("SELECT COUNT(*) AS c FROM mood_logs");
    if ($q2) $total_mood_logs = (int)$q2->fetch_assoc()["c"];

    $q3 = $conn->query("SELECT COUNT(*) AS c FROM anonymous_chats");
    if ($q3) $total_chats = (int)$q3->fetch_assoc()["c"];
} catch (Exception $e) {
    // ignore if table not found
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Counsellor Dashboard</title>
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
    background: rgba(255,255,255,0.16);
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
    background: rgba(255,255,255,0.90);
    backdrop-filter: blur(8px);
    box-shadow:0 12px 25px rgba(0,0,0,0.10);
    border:1px solid rgba(255,255,255,0.6);
    transition:transform .15s ease, box-shadow .15s ease;
  }

  .cardx:hover{
    transform: translateY(-4px);
    box-shadow:0 18px 35px rgba(0,0,0,0.14);
  }

  .soft-btn{
    border-radius:14px;
    font-weight:800;
    position:relative;
    z-index:5;
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
    background: rgba(255,255,255,0.22);
  }
</style>
</head>

<body>
<div class="container py-4" style="max-width:1100px;">

  <!-- HERO -->
  <div class="hero mb-4 d-flex justify-content-between align-items-center">
    <div>
      <h3 class="fw-bold mb-1">🧠 Counsellor Dashboard</h3>
      <div class="small">Support students • Monitor wellness • Respond safely</div>
      <div class="mt-2 tag">Logged in as: <?= htmlspecialchars($name) ?></div>
    </div>
    <div class="text-end">
     <a href="./logout.php" class="btn btn-light soft-btn">Logout</a>
    </div>
  </div>

  <!-- QUICK ACTIONS -->
  <div class="d-flex flex-wrap gap-2 mb-3">
 <a href="counsellor_chat_list.php" class="btn btn-primary soft-btn">Anonymous Chats</a>
    <a href="counsellor_mood_insights.php" class="btn btn-success soft-btn">Mood Insights</a>
  <a href="wellness_resources.php" class="btn btn-warning">Wellness Resources</a>
    <a href="counsellor_reports.php" class="btn btn-dark soft-btn">Reports</a>
 
  </div>

  <!-- STATS -->
  <div class="row g-3 mb-3">
    <div class="col-md-4">
      <div class="cardx p-3">
        <div class="fw-bold">Total Students</div>
        <div class="big"><?= $total_students ?></div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="cardx p-3">
        <div class="fw-bold">Mood Logs</div>
        <div class="big"><?= $total_mood_logs ?></div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="cardx p-3">
        <div class="fw-bold">Anonymous Chats</div>
        <div class="big"><?= $total_chats ?></div>
      </div>
    </div>
  </div>

  <!-- INFO -->
  <div class="cardx p-4">
    <h5 class="fw-bold mb-2">✨ What you can do here</h5>
    <ul class="mb-0">
      <li>View and reply to student anonymous chats (safely).</li>
      <li>Track mood trends and identify students needing support.</li>
      <li>Share helpful wellness tips/resources to students.</li>
      <li>Generate simple reports for insights.</li>
    </ul>
    <div class="text-muted small mt-2">
      If any link shows “Not Found”, that page file isn’t created yet — tell me and I will create it.
    </div>
  </div>

</div>
</body>
</html>