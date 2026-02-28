<?php
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/db.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "student") {
    header("Location: login.php");
    exit();
}

$result = $conn->query("SELECT title, message, publish_date FROM announcements ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
  <title>School Announcements</title>
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
      transition: transform .15s ease, box-shadow .15s ease;
    }
    .cardx:hover{
      transform: translateY(-4px);
      box-shadow: 0 14px 30px rgba(0,0,0,0.12);
    }
    .soft-btn{
      border-radius: 14px;
      font-weight: 800;
    }
    .date-badge{
      font-size: 12px;
      font-weight: 700;
      padding: 6px 12px;
      border-radius: 999px;
      background: linear-gradient(135deg,#74c0fc,#4dabf7);
      color:white;
    }
    .new-badge{
      font-size: 11px;
      padding: 4px 10px;
      border-radius: 999px;
      background: linear-gradient(135deg,#ff6b6b,#fa5252);
      color:white;
      margin-left: 8px;
    }
  </style>
</head>

<body>
<div class="container py-4" style="max-width:1000px;">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <a href="student_dashboard.php" class="btn btn-outline-dark soft-btn">← Back</a>
  </div>

  <!-- HERO HEADER -->
  <div class="hero mb-4">
    <h3 class="fw-bold mb-1">📢 School Announcements</h3>
    <div class="small">Stay updated with important school news and updates 🌟</div>
  </div>

  <?php if($result->num_rows === 0): ?>
      <div class="alert alert-info">No announcements available.</div>
  <?php endif; ?>

  <div class="row g-3">

  <?php 
  $counter = 0;
  while($row = $result->fetch_assoc()): 
      $counter++;
  ?>
    <div class="col-md-6">
      <div class="cardx p-4 h-100">

        <div class="d-flex justify-content-between align-items-start mb-2">
          <h5 class="fw-bold mb-0">
            <?= htmlspecialchars($row["title"]) ?>
            <?php if($counter <= 2): ?>
              <span class="new-badge">NEW</span>
            <?php endif; ?>
          </h5>
          <span class="date-badge">
            <?= htmlspecialchars($row["publish_date"]) ?>
          </span>
        </div>

        <p class="mt-3 mb-0">
          <?= nl2br(htmlspecialchars($row["message"])) ?>
        </p>

      </div>
    </div>
  <?php endwhile; ?>

  </div>

  <div class="text-center text-muted small mt-4">
    Check announcements regularly to stay informed and prepared 🌈
  </div>

</div>
</body>
</html>