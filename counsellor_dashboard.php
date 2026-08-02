<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . "/db.php";

// Reusable function to render consistent Bootstrap risk indicator pills
function riskBadge($risk)
{
    switch ($risk)
    {
        case "High":
            return '<span class="badge bg-danger">🔴 High Risk</span>';
        case "Medium":
            return '<span class="badge bg-warning text-dark">🟡 Medium Risk</span>';
        default:
            return '<span class="badge bg-success">🟢 Low Risk</span>';
    }
}

// Protect Page & Validate Role Bound Contexts
if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "counsellor") {
    header("Location: login.php");
    exit();
}

$name = $_SESSION["name"] ?? "Counsellor";

// Dashboard Statistics
$total_students = 0;
$total_mood_logs = 0;
$total_chats = 0;

try {
    $q1 = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='student'");
    if ($q1) {
        $total_students = (int)$q1->fetch_assoc()['c'];
    }

    $q2 = $conn->query("SELECT COUNT(*) AS c FROM mood_logs");
    if ($q2) {
        $total_mood_logs = (int)$q2->fetch_assoc()['c'];
    }

    $q3 = $conn->query("SELECT COUNT(*) AS c FROM anonymous_chats");
    if ($q3) {
        $total_chats = (int)$q3->fetch_assoc()['c'];
    }
} catch(Exception $e) {
    // Gracefully ignore or log database errors during runtime setup
}

/* ----------------------------------------------------------------------
   AI Wellness Risk Detection Query (Modified to extract student_id)
---------------------------------------------------------------------- */
$riskStudents = $conn->query("
    SELECT
        wr.student_id,
        wr.risk_score,
        wr.risk_level,
        ac.id AS chat_id
    FROM wellness_risk wr
    INNER JOIN users u ON u.id = wr.student_id
    LEFT JOIN anonymous_chats ac ON ac.student_id = wr.student_id
    WHERE u.role='student'
    ORDER BY wr.risk_score DESC
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Counsellor Dashboard</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body {
      background: linear-gradient(135deg, #e0f7ff, #fff0f7, #f3ffe3);
      min-height: 100vh;
      font-family: 'Segoe UI', sans-serif;
    }
    .hero {
      background: linear-gradient(90deg, #0d6efd, #6f42c1, #d63384);
      color: white;
      border-radius: 22px;
      padding: 20px;
      box-shadow: 0 12px 30px rgba(0,0,0,.12);
    }
    .cardx {
      border: 0;
      border-radius: 20px;
      background: white;
      box-shadow: 0 10px 25px rgba(0,0,0,.08);
    }
    .big {
      font-size: 34px;
      font-weight: bold;
    }
    .soft-btn {
      border-radius: 12px;
      font-weight: bold;
    }
    .tag {
      background: rgba(255, 255, 255, .25);
      padding: 6px 12px;
      border-radius: 30px;
      display: inline-block;
    }
  </style>
</head>

<body>
<div class="container py-4" style="max-width:1100px;">

  <!-- HERO -->
  <div class="hero mb-4 d-flex justify-content-between align-items-center">
    <div>
      <h3>🧠 Counsellor Dashboard</h3>
      <div>Support Students • Monitor Wellness • Respond Safely</div>
      <div class="mt-2 tag">Logged in as: <?= htmlspecialchars($name) ?></div>
    </div>
    <div>
      <a href="logout.php" class="btn btn-light soft-btn">Logout</a>
    </div>
  </div>

  <!-- QUICK ACTIONS shortcuts navigation matrix -->
  <div class="mb-4">
    <a href="counsellor_chat_list.php" class="btn btn-primary soft-btn">Anonymous Chats</a>
    <a href="counsellor_mood_insights.php" class="btn btn-success soft-btn">Mood Insights</a>
    <a href="wellness_resources.php" class="btn btn-warning soft-btn">Wellness Resources</a>
    <a href="counsellor_reports.php" class="btn btn-dark soft-btn">Reports</a>
  </div>

  <!-- High-level Aggregate Statistical Summary Counters -->
  <div class="row mb-4">
    <div class="col-md-4">
      <div class="cardx p-3">
        <h5>Total Students</h5>
        <div class="big"><?= $total_students ?></div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="cardx p-3">
        <h5>Mood Logs</h5>
        <div class="big"><?= $total_mood_logs ?></div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="cardx p-3">
        <h5>Anonymous Chats</h5>
        <div class="big"><?= $total_chats ?></div>
      </div>
    </div>
  </div>

  <!-- Interactive AI Wellness Risk Monitoring Board -->
  <div class="cardx p-4">
    <h4 class="mb-3">🧠 Students Requiring Attention</h4>
    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Student</th>
            <th>Risk Score</th>
            <th>Risk Level</th>
            <th class="text-center" width="160">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($riskStudents && $riskStudents->num_rows > 0): ?>
            <?php while ($row = $riskStudents->fetch_assoc()): ?>
              <tr>
                <!-- UNIFIED ANONYMOUS SYSTEM ID MASK INTERCEPT TIER -->
                <td class="fw-semibold">Anonymous Student #<?= (int)$row["student_id"] ?></td>
                <td><?= (int)$row["risk_score"] ?></td>
                <td><?= riskBadge($row["risk_level"]) ?></td>
                <td class="text-center">
                  <?php if (!empty($row["chat_id"])): ?>
                    <a href="counsellor_chat_view.php?chat_id=<?= (int)$row['chat_id'] ?>" class="btn btn-primary btn-sm soft-btn px-3">
                      💬 Open Chat
                    </a>
                  <?php else: ?>
                    <span class="text-muted small italic">No chat active</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="4" class="text-center text-danger py-3">
                No AI Wellness Risk Data Available
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>
</body>
</html>