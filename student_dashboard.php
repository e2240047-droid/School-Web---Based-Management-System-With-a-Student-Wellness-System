<?php
// include authentication and database files
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/db.php";

// check if user is logged in as student
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "student") {
    header("Location: login.php");
    exit();
}

// get student name from session
$name = $_SESSION["name"] ?? "Student";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Student Dashboard</title>

  <!-- basic page setup -->
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- bootstrap for layout and styling -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    /* main background style */
    body {
      background: linear-gradient(135deg, #e0f7ff, #fff0f7, #f3ffe3);
      min-height: 100vh;
      font-family: 'Segoe UI', sans-serif;
      margin: 0;
    }

    /* top navigation bar */
    .topbar {
      background: linear-gradient(90deg, #0d6efd, #6f42c1, #d63384);
      color: white;
      padding: 14px 0;
      box-shadow: 0 6px 18px rgba(0,0,0,0.12);
    }

    /* system title */
    .brand {
      font-size: 20px;
      font-weight: 700;
    }

    /* logout button */
    .logout-btn {
      border-radius: 12px;
      font-weight: 700;
      padding: 7px 15px;
    }

    /* welcome section box */
    .hero {
      background: rgba(255,255,255,0.82);
      border-radius: 22px;
      padding: 28px;
      box-shadow: 0 10px 25px rgba(0,0,0,0.08);
      margin-bottom: 28px;
    }

    /* dashboard cards */
    .cardx {
      background: white;
      border-radius: 18px;
      overflow: hidden;
      box-shadow: 0 8px 22px rgba(0,0,0,0.09);
      height: 100%;
    }

    /* card header */
    .cardTop {
      color: white;
      font-weight: 700;
      padding: 16px 18px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    /* card content area */
    .cardBody {
      padding: 18px;
    }

    /* buttons */
    .btn {
      border-radius: 12px;
      font-weight: 700;
      padding: 10px 12px;
    }

    /* color styles */
    .g-blue { background: linear-gradient(135deg, #74c0fc, #4dabf7); }
    .g-green { background: linear-gradient(135deg, #51cf66, #2f9e44); }
    .g-purple { background: linear-gradient(135deg, #b197fc, #845ef7); }
    .g-pink { background: linear-gradient(135deg, #ffa8a8, #f06595); }
    .g-orange { background: linear-gradient(135deg, #ff8787, #fa5252); }
    .g-red { background: linear-gradient(135deg, #ff6b6b, #ffd93d); }
  </style>
</head>

<body>

<!-- top navigation bar -->
<div class="topbar">
  <div class="container d-flex justify-content-between align-items-center">

    <!-- system title -->
    <div class="brand">🎓 Student Dashboard</div>

    <!-- user info and logout -->
    <div class="d-flex align-items-center gap-3">
      <span>Hi, <b><?= htmlspecialchars($name) ?></b> 👋</span>
      <a href="logout.php" class="btn btn-light btn-sm logout-btn">Logout</a>
    </div>

  </div>
</div>

<div class="container py-4">

  <!-- welcome section -->
  <div class="hero">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">

      <!-- welcome text -->
      <div>
        <h2>Welcome back 👋</h2>
        <p>Track your mood, chat safely, and stay updated with school activities.</p>
      </div>

      <!-- quick actions -->
      <div class="d-flex gap-2">
        <a href="student_mood.php" class="btn btn-primary">Log Mood 😊</a>
        <a href="student_alert.php" class="btn btn-danger">Silent Alert 🚨</a>
      </div>

    </div>
  </div>

  <!-- dashboard feature cards -->
  <div class="row g-4">

    <!-- announcements -->
    <div class="col-12 col-md-6 col-lg-4">
      <div class="cardx">
        <div class="cardTop g-blue">Announcements 📢</div>
        <div class="cardBody">
          <p>See latest school updates.</p>
          <a href="student_announcements.php" class="btn btn-primary w-100">View Updates</a>
        </div>
      </div>
    </div>

    <!-- events -->
    <div class="col-12 col-md-6 col-lg-4">
      <div class="cardx">
        <div class="cardTop g-green">Events 📅</div>
        <div class="cardBody">
          <p>Check upcoming events.</p>
          <a href="student_events.php" class="btn btn-success w-100">View Events</a>
        </div>
      </div>
    </div>

    <!-- results -->
    <div class="col-12 col-md-6 col-lg-4">
      <div class="cardx">
        <div class="cardTop g-purple">Results 📊</div>
        <div class="cardBody">
          <p>View your exam results.</p>
          <a href="student_results.php" class="btn btn-secondary w-100">View Results</a>
        </div>
      </div>
    </div>

    <!-- wellness -->
    <div class="col-12 col-md-6 col-lg-4">
      <div class="cardx">
        <div class="cardTop g-pink">Wellness 💛</div>
        <div class="cardBody">
          <p>Access wellness resources.</p>
          <a href="student_resources.php" class="btn btn-warning w-100">Explore</a>
        </div>
      </div>
    </div>

    <!-- chat -->
    <div class="col-12 col-md-6 col-lg-4">
      <div class="cardx">
        <div class="cardTop g-red">Chat 💬</div>
        <div class="cardBody">
          <p>Chat privately with counsellor.</p>
          <a href="student_chat.php" class="btn btn-danger w-100">Start Chat</a>
        </div>
      </div>
    </div>

    <!-- profile -->
    <div class="col-12 col-md-6 col-lg-4">
      <div class="cardx">
        <div class="cardTop g-orange">Profile 👤</div>
        <div class="cardBody">
          <p>View your personal details.</p>
          <a href="student_profile.php" class="btn btn-dark w-100">View Profile</a>
        </div>
      </div>
    </div>

  </div>

  <!-- footer -->
  <div class="text-center text-muted small mt-4">
    Made with 💙 for student well-being
  </div>

</div>

</body>
</html>