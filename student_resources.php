<?php
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/db.php";

// allow only student
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "student") {
    header("Location: login.php");
    exit();
}

// get student name
$name = $_SESSION["name"] ?? "Student";

// ✅ get counsellor uploaded resources
$uploaded = $conn->query("SELECT * FROM wellness_resources ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Wellness Resources</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">

  <style>
    body {
      background: linear-gradient(135deg, #e0f7ff, #fff0f7, #f3ffe3);
      font-family: 'Segoe UI', sans-serif;
      min-height: 100vh;
      margin: 0;
    }

    /* Top Navigation Bar */
    .topbar {
      background: linear-gradient(90deg, #0d6efd, #6f42c1, #d63384);
      color: white;
      padding: 14px 0;
      box-shadow: 0 6px 18px rgba(0,0,0,0.12);
    }
    .brand { font-size: 20px; font-weight: 700; }
    
    /* Hero Title */
    .hero-title {
      font-weight: 800;
      color: #311b92;
      border-left: 6px solid #d63384;
      padding-left: 12px;
      margin-bottom: 24px;
      margin-top: 10px;
    }

    /* Professional Card Styling */
    .card-box {
      background: rgba(255, 255, 255, 0.95);
      border-radius: 18px;
      padding: 24px;
      box-shadow: 0 10px 25px rgba(0,0,0,0.05);
      height: 100%;
      border: 1px solid rgba(255,255,255,0.6);
      display: flex;
      flex-direction: column;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .card-box:hover {
      transform: translateY(-4px);
      box-shadow: 0 14px 28px rgba(0,0,0,0.1);
    }

    .card-box h5 {
      font-weight: 800;
      color: #212529;
      margin-bottom: 15px;
    }

    /* Custom Clean List */
    ul.custom-list {
      padding-left: 0;
      list-style: none;
      margin-bottom: 25px;
    }
    
    ul.custom-list li {
      margin-bottom: 10px;
      position: relative;
      padding-left: 28px;
      color: #495057;
      font-weight: 600;
      font-size: 15px;
    }
    
    ul.custom-list li::before {
      content: '\F26A'; /* Bootstrap Check Icon */
      font-family: 'bootstrap-icons';
      position: absolute;
      left: 0;
      color: #0d6efd;
      font-size: 16px;
      font-weight: bold;
    }

    /* Standardized Buttons */
    .soft-btn {
      border-radius: 12px;
      font-weight: 700;
      padding: 10px;
      margin-top: auto; /* Pushes button to the very bottom */
    }

    .section-divider {
      margin-top: 40px;
      margin-bottom: 20px;
      font-weight: 800;
      color: #495057;
      display: flex;
      align-items: center;
      gap: 10px;
    }
  </style>
</head>

<body>

<!-- TOP NAVIGATION -->
<div class="topbar">
  <div class="container d-flex justify-content-between align-items-center">
    <div class="brand">🎓 Wellness Resources</div>
    <div class="d-flex align-items-center gap-3">
      <span>Hi, <b><?= htmlspecialchars($name) ?></b> 👋</span>
    </div>
  </div>
</div>

<div class="container py-4" style="max-width: 1100px;">

  <div class="mb-2">
    <a href="student_dashboard.php" class="btn btn-outline-dark" style="border-radius: 12px; font-weight: 600;">
      <i class="bi bi-arrow-left"></i> Back to Dashboard
    </a>
  </div>

  <h3 class="hero-title">Student Wellness Zone 🌈</h3>

  <!-- ================= STATIC RESOURCES GRID ================= -->
  <div class="row g-4">

    <div class="col-lg-4 col-md-6">
      <div class="card-box">
        <h5><i class="bi bi-lungs-fill text-danger me-2"></i> Breathing Exercise</h5>
        <ul class="custom-list">
          <li>Inhale for 4 seconds</li>
          <li>Hold for 4 seconds</li>
          <li>Exhale for 6 seconds</li>
        </ul>
        <a href="https://www.youtube.com/results?search_query=breathing+exercise" class="btn btn-danger w-100 soft-btn" target="_blank">Watch Guide</a>
      </div>
    </div>

    <div class="col-lg-4 col-md-6">
      <div class="card-box">
        <h5><i class="bi bi-bullseye text-success me-2"></i> Study Tips</h5>
        <ul class="custom-list">
          <li>Study blocks of 25 min</li>
          <li>Take 5 min breaks</li>
          <li>Eliminate all distractions</li>
        </ul>
        <a href="https://www.youtube.com/results?search_query=pomodoro" class="btn btn-success w-100 soft-btn" target="_blank">Use Pomodoro Timer</a>
      </div>
    </div>

    <div class="col-lg-4 col-md-6">
      <div class="card-box">
        <h5><i class="bi bi-headphones text-primary me-2"></i> Relaxation Music</h5>
        <ul class="custom-list">
          <li>Lo-fi ambient beats</li>
          <li>Nature and rain sounds</li>
          <li>Deep focus calm music</li>
        </ul>
        <a href="https://www.youtube.com/results?search_query=lofi" class="btn btn-primary w-100 soft-btn" target="_blank">Play Audio</a>
      </div>
    </div>

    <div class="col-lg-6 col-md-6">
      <div class="card-box">
        <h5><i class="bi bi-lightning-charge-fill text-warning me-2"></i> Build Confidence</h5>
        <ul class="custom-list">
          <li>Practice positive affirmations</li>
          <li>Write down daily goals</li>
          <li>Learn something new daily</li>
        </ul>
        <a href="student_mood.php" class="btn btn-dark w-100 soft-btn">Write Your Feelings</a>
      </div>
    </div>

    <div class="col-lg-6 col-md-12">
      <div class="card-box">
        <h5><i class="bi bi-heart-pulse-fill text-info me-2"></i> Mental Health Check</h5>
        <ul class="custom-list">
          <li>Ensure 8 hours of sleep</li>
          <li>Maintain a healthy diet</li>
          <li>Talk to someone you trust</li>
        </ul>
        <a href="student_chat.php" class="btn btn-info w-100 soft-btn text-white">Start Anonymous Chat</a>
      </div>
    </div>

  </div>

  <!-- ================= DYNAMIC COUNSELLOR RESOURCES ================= -->
  <h4 class="section-divider"><i class="bi bi-bookmark-star-fill text-warning"></i> Counsellor Official Resources</h4>

  <div class="row g-4 mb-5">
    <?php if ($uploaded && $uploaded->num_rows > 0): ?>
      <?php while($row = $uploaded->fetch_assoc()): ?>
        
        <div class="col-lg-4 col-md-6">
          <div class="card-box">
            <h5 class="text-dark mb-1"><?= htmlspecialchars($row["title"]) ?></h5>
            <span class="badge bg-secondary mb-3 w-auto" style="align-self: flex-start;">
              <?= htmlspecialchars($row["category"]) ?>
            </span>
            
            <p class="text-muted small flex-grow-1" style="line-height: 1.6;">
              <?= nl2br(htmlspecialchars($row["content"])) ?>
            </p>
            
            <?php if (!empty($row["link"])): ?>
              <a href="<?= htmlspecialchars($row["link"]) ?>" target="_blank" class="btn btn-dark w-100 soft-btn mt-3">
                <i class="bi bi-link-45deg"></i> Open Resource
              </a>
            <?php else: ?>
               <!-- Invisible spacer to keep alignment if no link exists -->
               <div class="mt-auto"></div>
            <?php endif; ?>
          </div>
        </div>

      <?php endwhile; ?>
    <?php else: ?>
      
      <div class="col-12">
        <div class="alert alert-light text-center py-5 border rounded-4 shadow-sm" style="background: rgba(255,255,255,0.8);">
          <i class="bi bi-folder2-open display-4 text-muted d-block mb-3"></i>
          <h5 class="text-muted fw-bold">No official resources uploaded yet.</h5>
          <p class="text-muted small">Check back later for updates from your school counsellor.</p>
        </div>
      </div>

    <?php endif; ?>
  </div>

  <!-- Footer -->
  <div class="text-center text-muted small pb-4">
    Student Wellness Management System
  </div>

</div>

</body>
</html>