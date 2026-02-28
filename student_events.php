<?php
require_once "auth.php";
require_once "db.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "student") {
    header("Location: login.php");
    exit();
}

$result = $conn->query("SELECT * FROM events ORDER BY event_date ASC");
?>

<!DOCTYPE html>
<html>
<head>
<title>School Events</title>
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
  border-radius:25px;
  padding:22px;
  box-shadow:0 12px 30px rgba(0,0,0,0.15);
}
.cardx{
  border:0;
  border-radius:22px;
  background:rgba(255,255,255,0.9);
  box-shadow:0 12px 25px rgba(0,0,0,0.08);
  transition:all .2s ease;
}
.cardx:hover{
  transform:translateY(-5px);
  box-shadow:0 18px 35px rgba(0,0,0,0.12);
}
.date-badge{
  background:linear-gradient(135deg,#63e6be,#20c997);
  color:#083b28;
  padding:6px 14px;
  border-radius:999px;
  font-size:13px;
  font-weight:700;
}
.upcoming{
  background:linear-gradient(135deg,#ff6b6b,#fa5252);
  color:white;
  padding:5px 12px;
  border-radius:999px;
  font-size:12px;
  margin-left:8px;
}
.soft-btn{
  border-radius:14px;
  font-weight:800;
}
</style>
</head>

<body>

<div class="container py-4" style="max-width:1000px;">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <a href="student_dashboard.php" class="btn btn-outline-dark soft-btn">← Back</a>
  </div>

  <!-- HERO -->
  <div class="hero mb-4">
    <h3 class="fw-bold mb-1">🎉 School Events</h3>
    <div class="small">Explore upcoming activities, programs and celebrations 🌟</div>
  </div>

  <?php if($result->num_rows === 0): ?>
    <div class="alert alert-info">No events available.</div>
  <?php endif; ?>

  <div class="row g-4">

  <?php while($row = $result->fetch_assoc()): 
      $isUpcoming = (strtotime($row["event_date"]) >= strtotime(date("Y-m-d")));
  ?>
    <div class="col-md-6">
      <div class="card cardx p-3 h-100">

        <?php if($row["image"]): ?>
          <img src="uploads/<?= htmlspecialchars($row["image"]) ?>" 
               class="img-fluid rounded mb-3"
               style="max-height:200px; object-fit:cover;">
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-2">
          <h5 class="fw-bold mb-0">
            <?= htmlspecialchars($row["title"]) ?>
            <?php if($isUpcoming): ?>
              <span class="upcoming">UPCOMING</span>
            <?php endif; ?>
          </h5>
        </div>

        <div class="mb-2">
          <span class="date-badge">
            📅 <?= date("F d, Y", strtotime($row["event_date"])) ?>
          </span>
        </div>

        <p class="mt-2">
          <?= nl2br(htmlspecialchars($row["description"])) ?>
        </p>

      </div>
    </div>
  <?php endwhile; ?>

  </div>

  <div class="text-center text-muted small mt-4">
    Participate actively and enjoy school life 🎈✨
  </div>

</div>

</body>
</html>