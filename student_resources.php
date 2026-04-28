<?php
// include authentication
require_once __DIR__ . "/auth.php";

// check if student is logged in
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "student") {
    header("Location: login.php");
    exit();
}

// get student name
$name = $_SESSION["name"] ?? "Student";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Wellness Resources</title>

  <!-- basic page setup -->
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    /* page background */
    body {
      background: linear-gradient(135deg, #e0f7ff, #fff0f7, #f3ffe3);
      min-height: 100vh;
      font-family: 'Segoe UI', Arial, sans-serif;
      margin: 0;
    }

    /* top header */
    .header {
      background: linear-gradient(90deg, #0d6efd, #6f42c1, #d63384);
      color: white;
      padding: 15px 0;
      box-shadow: 0 6px 18px rgba(0,0,0,0.12);
    }

    .page-title {
      font-size: 20px;
      font-weight: 700;
    }

    /* welcome section */
    .hero {
      background: rgba(255,255,255,0.85);
      border-radius: 18px;
      padding: 24px;
      box-shadow: 0 8px 22px rgba(0,0,0,0.08);
      margin-bottom: 24px;
    }

    /* resource cards */
    .card-box {
      background: white;
      border-radius: 16px;
      padding: 22px;
      box-shadow: 0 8px 22px rgba(0,0,0,0.08);
      height: 100%;
      display: flex;
      flex-direction: column;
    }

    .card-box h5 {
      font-weight: 700;
      margin-bottom: 10px;
    }

    .card-box p,
    .card-box li {
      color: #444;
      font-size: 15px;
    }

    .card-box ul {
      margin-bottom: 18px;
    }

    .card-box .btn {
      margin-top: auto;
    }

    /* buttons */
    .btn {
      border-radius: 12px;
      font-weight: 700;
      padding: 10px 14px;
    }

    /* emergency section */
    .help-box {
      background: white;
      border-radius: 16px;
      padding: 22px;
      box-shadow: 0 8px 22px rgba(0,0,0,0.08);
    }
  </style>
</head>

<body>

<!-- header -->
<div class="header">
  <div class="container d-flex justify-content-between align-items-center">
    <div class="page-title">Wellness Resources</div>
    <div>Hello, <b><?= htmlspecialchars($name) ?></b> 👋</div>
  </div>
</div>

<div class="container py-4">

  <!-- back button -->
  <a href="student_dashboard.php" class="btn btn-secondary mb-3">← Back</a>

  <!-- page introduction -->
  <div class="hero">
    <h3>Student Wellness Zone 🌈</h3>
    <p class="mb-0">Find simple resources to relax, focus, and get support when needed.</p>
  </div>

  <!-- resources -->
  <div class="row g-4">

    <!-- breathing -->
    <div class="col-12 col-md-6 col-lg-4">
      <div class="card-box">
        <h5>Breathing Exercise 🫁</h5>
        <p>Simple steps to calm your mind.</p>
        <ul>
          <li>Inhale for 4 seconds</li>
          <li>Hold for 4 seconds</li>
          <li>Exhale for 6 seconds</li>
        </ul>
        <a href="https://www.youtube.com/results?search_query=breathing+exercise"
           target="_blank"
           class="btn btn-danger w-100">Watch Video</a>
      </div>
    </div>

    <!-- study -->
    <div class="col-12 col-md-6 col-lg-4">
      <div class="card-box">
        <h5>Study Tips 🎯</h5>
        <p>Helpful tips to improve your focus.</p>
        <ul>
          <li>Study for 25 minutes</li>
          <li>Take a 5 minute break</li>
          <li>Keep distractions away</li>
        </ul>
        <a href="https://www.youtube.com/results?search_query=pomodoro+timer"
           target="_blank"
           class="btn btn-success w-100">Start Timer</a>
      </div>
    </div>

    <!-- music -->
    <div class="col-12 col-md-6 col-lg-4">
      <div class="card-box">
        <h5>Relaxing Music 🎧</h5>
        <p>Listen to calm music while studying or relaxing.</p>
        <ul>
          <li>Lo-fi music</li>
          <li>Nature sounds</li>
          <li>Calm background music</li>
        </ul>
        <a href="https://www.youtube.com/results?search_query=lofi+music"
           target="_blank"
           class="btn btn-primary w-100">Play Music</a>
      </div>
    </div>

    <!-- confidence -->
    <div class="col-12 col-md-6">
      <div class="card-box">
        <h5>Confidence Tips 💪</h5>
        <p>Small positive habits can build confidence.</p>
        <ul>
          <li>Write your achievements</li>
          <li>Think positively</li>
          <li>Learn from mistakes</li>
        </ul>
        <a href="student_mood.php" class="btn btn-secondary w-100">Write Feelings</a>
      </div>
    </div>

    <!-- mental health -->
    <div class="col-12 col-md-6">
      <div class="card-box">
        <h5>Mental Health 🧠</h5>
        <p>Simple habits that support your well-being.</p>
        <ul>
          <li>Sleep well</li>
          <li>Eat healthy food</li>
          <li>Talk to someone you trust</li>
        </ul>
        <a href="student_chat.php" class="btn btn-info w-100 text-white">Talk to Counsellor</a>
      </div>
    </div>

    <!-- emergency -->
    <div class="col-12">
      <div class="help-box d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
          <h5 class="mb-1">Need help immediately?</h5>
          <p class="mb-0 text-muted">Talk to a teacher, parent, or counsellor. Do not stay alone with fear.</p>
        </div>
        <a href="student_alert.php" class="btn btn-danger">Send Alert 🚨</a>
      </div>
    </div>

  </div>

</div>

</body>
</html>