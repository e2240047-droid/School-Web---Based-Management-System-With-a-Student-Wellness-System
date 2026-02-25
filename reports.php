<?php
session_start();
include __DIR__ . "/db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: login.php");
    exit();
}

// Safe counts (if tables exist)
function safeCount($conn, $table) {
    $check = $conn->query("SHOW TABLES LIKE '$table'");
    if ($check && $check->num_rows == 1) {
        $row = $conn->query("SELECT COUNT(*) AS c FROM $table")->fetch_assoc();
        return $row['c'];
    }
    return 0;
}

$mood_logs = safeCount($conn, "mood_log");
$alerts    = safeCount($conn, "wellness_alert");
$reports   = safeCount($conn, "reports");
?>

<!DOCTYPE html>
<html>
<head>
  <title>Reports & Insights</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">

  <div class="d-flex justify-content-between align-items-center">
    <h3>Reports & Insights</h3>
    <a href="admin_dashboard.php" class="btn btn-secondary btn-sm">Back</a>
  </div>

  <hr>

  <div class="row g-3">
    <div class="col-md-4">
      <div class="card shadow-sm p-3">
        <div class="fw-bold">Mood Logs</div>
        <div class="display-6"><?php echo $mood_logs; ?></div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card shadow-sm p-3">
        <div class="fw-bold">Wellness Alerts</div>
        <div class="display-6"><?php echo $alerts; ?></div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card shadow-sm p-3">
        <div class="fw-bold">Generated Reports</div>
        <div class="display-6"><?php echo $reports; ?></div>
      </div>
    </div>
  </div>

  <div class="mt-4 text-muted">
    If these numbers show 0, it means you haven't created/inserted data in those tables yet.
  </div>

</div>

</body>
</html>