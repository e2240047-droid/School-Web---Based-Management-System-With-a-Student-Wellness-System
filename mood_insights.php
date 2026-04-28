<?php
// admin_mood_insights.php
session_start();

require_once __DIR__ . "/db.php";

ini_set('display_errors', 1);
error_reporting(E_ALL);

// allow only admin
if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    header("Location: login.php");
    exit();
}

// default values
$total = 0;
$happy = 0;
$excited = 0;
$calm = 0;
$sad = 0;
$stressed = 0;
$angry = 0;

// student mood page uses mood_logs table
$table = "mood_logs";

// check table exists
$check = $conn->query("SHOW TABLES LIKE '$table'");
$table_exists = ($check && $check->num_rows > 0);

if ($table_exists) {

    // total logs
    $resTotal = $conn->query("SELECT COUNT(*) AS total FROM mood_logs");
    $total = (int)($resTotal->fetch_assoc()["total"] ?? 0);

    // count each mood
    $counts = $conn->query("
        SELECT mood, COUNT(*) AS count_value
        FROM mood_logs
        GROUP BY mood
    ");

    if ($counts) {
        while ($row = $counts->fetch_assoc()) {

            $mood = strtolower(trim($row["mood"]));
            $count = (int)$row["count_value"];

            if ($mood == "happy") {
                $happy = $count;
            } elseif ($mood == "excited") {
                $excited = $count;
            } elseif ($mood == "calm") {
                $calm = $count;
            } elseif ($mood == "sad") {
                $sad = $count;
            } elseif ($mood == "stressed") {
                $stressed = $count;
            } elseif ($mood == "angry") {
                $angry = $count;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Mood Insights</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg,#e0f7ff,#fff0f7,#f3ffe3);
            min-height: 100vh;
            font-family: 'Segoe UI', sans-serif;
        }

        .hero {
            background: linear-gradient(90deg,#0d6efd,#6f42c1,#d63384);
            color: white;
            border-radius: 22px;
            padding: 20px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.12);
        }

        .cardx {
            border: 0;
            border-radius: 20px;
            background: white;
            box-shadow: 0 12px 25px rgba(0,0,0,0.10);
            padding: 20px;
        }

        .soft-btn {
            border-radius: 14px;
            font-weight: 800;
        }
    </style>
</head>

<body>

<div class="container py-4" style="max-width:1100px;">

    <!-- top buttons -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="admin_dashboard.php" class="btn btn-dark soft-btn">← Back</a>
        <a href="logout.php" class="btn btn-danger soft-btn">Logout</a>
    </div>

    <!-- heading -->
    <div class="hero mb-4">
        <h3 class="fw-bold mb-1">📊 Student Mood Log Summary</h3>
        <div class="small">Admin overview of students’ mood logs</div>
    </div>

    <?php if (!$table_exists): ?>

        <div class="alert alert-warning">
            <b>mood_logs table not found.</b><br>
            Please check your database table name.
        </div>

    <?php else: ?>

        <div class="row g-3">

            <div class="col-md-3">
                <div class="cardx">
                    <div class="fw-bold">Total Logs</div>
                    <div class="display-6"><?= $total ?></div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="cardx">
                    <div class="fw-bold">Happy 😊</div>
                    <div class="display-6"><?= $happy ?></div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="cardx">
                    <div class="fw-bold">Excited 🤩</div>
                    <div class="display-6"><?= $excited ?></div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="cardx">
                    <div class="fw-bold">Calm 😌</div>
                    <div class="display-6"><?= $calm ?></div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="cardx">
                    <div class="fw-bold">Sad 😢</div>
                    <div class="display-6"><?= $sad ?></div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="cardx">
                    <div class="fw-bold">Stressed 😰</div>
                    <div class="display-6"><?= $stressed ?></div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="cardx">
                    <div class="fw-bold">Angry 😡</div>
                    <div class="display-6"><?= $angry ?></div>
                </div>
            </div>

        </div>

    <?php endif; ?>

</div>

</body>
</html>