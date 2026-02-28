<?php
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/db.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "student") {
    header("Location: login.php");
    exit();
}

$name = $_SESSION["name"] ?? "Student";
?>
<!DOCTYPE html>
<html>
<head>
  <title>Student Dashboard</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body{
      background: linear-gradient(135deg,#e0f7ff,#fff0f7,#f3ffe3);
      min-height: 100vh;
      font-family: 'Segoe UI', sans-serif;
    }
    .topbar{
      background: linear-gradient(90deg,#0d6efd,#6f42c1,#d63384);
      color:white;
      padding: 14px 0;
      box-shadow: 0 10px 25px rgba(0,0,0,0.12);
    }
    .brand{
      font-size: 20px;
      font-weight: 700;
      letter-spacing: 0.3px;
    }
    .pill{
      background: rgba(255,255,255,0.20);
      border: 1px solid rgba(255,255,255,0.25);
      padding: 6px 12px;
      border-radius: 999px;
      font-size: 13px;
    }
    .logout-btn{
      border-radius: 12px;
      font-weight: 700;
    }

    .hero{
      border-radius: 22px;
      padding: 20px;
      background: rgba(255,255,255,0.70);
      backdrop-filter: blur(8px);
      box-shadow: 0 10px 30px rgba(0,0,0,0.08);
      border: 1px solid rgba(255,255,255,0.6);
    }
    .hero h2{
      margin:0;
      font-weight: 800;
    }
    .hero small{
      color: #555;
    }

    .cardx{
      border: 0;
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 10px 25px rgba(0,0,0,0.08);
      transition: transform .15s ease, box-shadow .15s ease;
      background: rgba(255,255,255,0.85);
      backdrop-filter: blur(6px);
      border: 1px solid rgba(255,255,255,0.6);
    }
    .cardx:hover{
      transform: translateY(-5px);
      box-shadow: 0 14px 30px rgba(0,0,0,0.12);
    }
    .cardTop{
      padding: 14px 16px;
      color: white;
      font-weight: 800;
      display:flex;
      align-items:center;
      justify-content: space-between;
    }
    .icon{
      font-size: 26px;
      filter: drop-shadow(0 2px 6px rgba(0,0,0,0.20));
    }
    .mini{
      color:#666;
      font-size: 13px;
    }
    .soft-btn{
      border-radius: 14px;
      font-weight: 800;
    }

    .g1{ background: linear-gradient(135deg,#ff6b6b,#ffd93d); }
    .g2{ background: linear-gradient(135deg,#74c0fc,#4dabf7); }
    .g3{ background: linear-gradient(135deg,#51cf66,#2f9e44); }
    .g4{ background: linear-gradient(135deg,#b197fc,#845ef7); }
    .g5{ background: linear-gradient(135deg,#ff8787,#fa5252); }
    .g6{ background: linear-gradient(135deg,#63e6be,#20c997); }
    .g7{ background: linear-gradient(135deg,#ffa8a8,#f06595); }

    /* Small bounce animation */
    @keyframes pop {
      0%{ transform: scale(0.98); opacity: 0.6; }
      100%{ transform: scale(1); opacity: 1; }
    }
    .animate-pop{ animation: pop .25s ease; }

  </style>
</head>

<body>

<!-- TOP BAR -->
<div class="topbar">
  <div class="container d-flex justify-content-between align-items-center">
    <div class="brand">🎓 Student Dashboard</div>

    <div class="d-flex gap-2 align-items-center">
      <div class="pill">Hi, <b><?= htmlspecialchars($name) ?></b> 👋</div>
      <a href="logout.php" class="btn btn-light logout-btn btn-sm">Logout</a>
    </div>
  </div>
</div>

<div class="container py-4">

  <!-- HERO -->
  <div class="hero mb-4 animate-pop">
    <div class="d-flex flex-wrap justify-content-between align-items-center">
      <div>
        <h2>Welcome back 🌈</h2>
        <small>Track your mood, chat safely, and stay updated with school activities.</small>
      </div>
      <div class="mt-3 mt-md-0 d-flex gap-2">
        <a href="student_mood.php" class="btn btn-primary soft-btn">Log Mood 😊</a>
        <a href="student_chat.php" class="btn btn-warning soft-btn">Anonymous Chat 💬</a>
        <a href="student_alert.php" class="btn btn-danger soft-btn">Silent Alert 🚨</a>
      </div>
    </div>
  </div>

  <!-- CARDS -->
  <div class="row g-3">

    <div class="col-md-3">
      <div class="cardx h-100 animate-pop">
        <div class="cardTop g2">
          <span>Announcements</span>
          <span class="icon">📢</span>
        </div>
        <div class="p-3">
          <div class="mini">See school updates</div>
          <a href="student_announcements.php" class="btn btn-primary w-100 soft-btn mt-3">Open</a>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="cardx h-100 animate-pop">
        <div class="cardTop g3">
          <span>Events</span>
          <span class="icon">📅</span>
        </div>
        <div class="p-3">
          <div class="mini">Upcoming school events</div>
          <a href="student_events.php" class="btn btn-success w-100 soft-btn mt-3">Open</a>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="cardx h-100 animate-pop">
        <div class="cardTop g4">
          <span>My Results</span>
          <span class="icon">📊</span>
        </div>
        <div class="p-3">
          <div class="mini">Check exam results</div>
          <a href="student_results.php" class="btn btn-secondary w-100 soft-btn mt-3">Open</a>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="cardx h-100 animate-pop">
        <div class="cardTop g6">
          <span>Mood Logs</span>
          <span class="icon">📝</span>
        </div>
        <div class="p-3">
          <div class="mini">View your mood history</div>
          <a href="student_mood.php" class="btn btn-info text-white w-100 soft-btn mt-3">Open</a>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="cardx h-100 animate-pop">
        <div class="cardTop g7">
          <span>Wellness Resources</span>
          <span class="icon">💛</span>
        </div>
        <div class="p-3">
          <div class="mini">Tips, music, videos</div>
          <a href="student_resources.php" class="btn btn-warning w-100 soft-btn mt-3">Open</a>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="cardx h-100 animate-pop">
        <div class="cardTop g1">
          <span>Anonymous Chat</span>
          <span class="icon">🕶️</span>
        </div>
        <div class="p-3">
          <div class="mini">Safe & private support chat</div>
          <a href="student_chat.php" class="btn btn-danger w-100 soft-btn mt-3">Open</a>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="cardx h-100 animate-pop">
        <div class="cardTop g5">
          <span>My Profile</span>
          <span class="icon">👤</span>
        </div>
        <div class="p-3">
          <div class="mini">View your details</div>
          <a href="student_profile.php" class="btn btn-dark w-100 soft-btn mt-3">Open</a>
        </div>
      </div>
    </div>

  </div>

  <div class="text-center text-muted small mt-4">
    Made with 💙 for student well-being
  </div>

</div>

</body>
</html>