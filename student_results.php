<?php
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/db.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "student") {
    header("Location: login.php");
    exit();
}

$student_id = (int)($_SESSION["user_id"] ?? 0);

$stmt = $conn->prepare("
  SELECT subject, marks, grade
  FROM results
  WHERE student_id=?
  ORDER BY id DESC
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

$total = 0;
$count = 0;

function gradeColor($grade){
    $g = strtoupper($grade);
    if ($g == "A" || $g == "A+") return "bg-success";
    if ($g == "B") return "bg-primary";
    if ($g == "C") return "bg-warning text-dark";
    if ($g == "D") return "bg-secondary";
    return "bg-danger";
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>My Exam Results</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body{
      background: linear-gradient(135deg,#e0f7ff,#fff0f7,#f3ffe3);
      min-height: 100vh;
      font-family: 'Segoe UI', sans-serif;
    }
    .hero{
      background: linear-gradient(90deg,#0d6efd,#6f42c1,#d63384);
      color:white;
      border-radius: 22px;
      padding: 20px;
      box-shadow: 0 12px 30px rgba(0,0,0,0.12);
    }
    .cardx{
      border: 0;
      border-radius: 20px;
      background: rgba(255,255,255,0.88);
      backdrop-filter: blur(8px);
      box-shadow: 0 10px 25px rgba(0,0,0,0.08);
      border: 1px solid rgba(255,255,255,0.6);
      transition: transform .15s ease;
    }
    .cardx:hover{
      transform: translateY(-4px);
    }
    .soft-btn{
      border-radius: 14px;
      font-weight: 800;
    }
    .marks-circle{
      width: 70px;
      height: 70px;
      border-radius: 50%;
      display:flex;
      align-items:center;
      justify-content:center;
      font-weight: 900;
      font-size: 20px;
      color:white;
      background: linear-gradient(135deg,#74c0fc,#4dabf7);
      box-shadow: 0 6px 18px rgba(0,0,0,0.15);
    }
    .summary{
      border-radius: 20px;
      background: linear-gradient(135deg,#63e6be,#74c0fc);
      padding: 18px;
      color:#0b2e13;
      font-weight:600;
      box-shadow: 0 10px 25px rgba(0,0,0,0.10);
    }
  </style>
</head>

<body>
<div class="container py-4" style="max-width:1000px;">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <a href="student_dashboard.php" class="btn btn-outline-dark soft-btn">← Back</a>
  </div>

  <div class="hero mb-4">
    <h3 class="fw-bold mb-1">📊 My Exam Results</h3>
    <div class="small">Track your academic progress and celebrate your achievements 🎉</div>
  </div>

  <?php if($result->num_rows === 0): ?>
      <div class="alert alert-info">No results available yet.</div>
  <?php endif; ?>

  <div class="row g-3">

    <?php while($row = $result->fetch_assoc()): 
        $total += $row["marks"];
        $count++;
    ?>
      <div class="col-md-6">
        <div class="cardx p-4 d-flex justify-content-between align-items-center">
          
          <div>
            <h5 class="fw-bold mb-1"><?= htmlspecialchars($row["subject"]) ?></h5>
            <span class="badge <?= gradeColor($row["grade"]) ?> px-3 py-2">
              Grade: <?= htmlspecialchars($row["grade"]) ?>
            </span>
          </div>

          <div class="marks-circle">
            <?= (int)$row["marks"] ?>
          </div>

        </div>
      </div>
    <?php endwhile; ?>

  </div>

  <?php if($count > 0): 
      $average = round($total / $count, 2);
  ?>
  <div class="summary mt-4">
    <div class="row text-center">
      <div class="col-md-4">
        <div>Total Subjects</div>
        <div class="fs-4"><?= $count ?></div>
      </div>
      <div class="col-md-4">
        <div>Total Marks</div>
        <div class="fs-4"><?= $total ?></div>
      </div>
      <div class="col-md-4">
        <div>Average</div>
        <div class="fs-4"><?= $average ?></div>
      </div>
    </div>
  </div>

  <div class="text-center mt-4">
    <h5 class="fw-bold text-success">
      <?= $average >= 75 ? "🌟 Excellent Work! Keep it up!" :
         ($average >= 50 ? "👏 Good Job! Keep Improving!" :
         "💪 Don't Give Up! You Can Do Better!") ?>
    </h5>
  </div>

  <?php endif; ?>

</div>
</body>
</html>