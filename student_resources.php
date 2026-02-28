<?php
require_once __DIR__ . "/auth.php";

if ($_SESSION["role"] !== "student") {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Wellness Resources</title>
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
      border-radius: 18px;
      padding: 22px;
      box-shadow: 0 10px 25px rgba(0,0,0,0.12);
    }
    .cardx{
      border: 0;
      border-radius: 18px;
      overflow: hidden;
      box-shadow: 0 10px 25px rgba(0,0,0,0.08);
      transition: transform .15s ease;
    }
    .cardx:hover{ transform: translateY(-4px); }
    .tag{
      display: inline-block;
      font-size: 12px;
      padding: 3px 10px;
      border-radius: 30px;
      background: rgba(255,255,255,0.25);
      color: #fff;
    }
    .mini{
      font-size: 13px;
      color: #555;
    }
    .soft-btn{
      border-radius: 12px;
      font-weight: 600;
    }
    .grad1{ background: linear-gradient(135deg,#ff6b6b,#ffd93d); }
    .grad2{ background: linear-gradient(135deg,#51cf66,#2f9e44); }
    .grad3{ background: linear-gradient(135deg,#74c0fc,#4dabf7); }
    .grad4{ background: linear-gradient(135deg,#b197fc,#845ef7); }
    .grad5{ background: linear-gradient(135deg,#ffa8a8,#ff8787); }
    .grad6{ background: linear-gradient(135deg,#63e6be,#20c997); }
    .card-top{
      color: white;
      padding: 16px 16px 10px;
    }
    .card-body{
      padding: 16px;
    }
    .emoji{
      font-size: 26px;
      margin-right: 8px;
    }
  </style>
</head>

<body>

<div class="container py-4">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <a href="student_dashboard.php" class="btn btn-outline-dark soft-btn">← Back</a>
    <div class="small text-muted">Hello, <?= htmlspecialchars($_SESSION["name"]) ?> 👋</div>
  </div>

  <!-- HERO -->
  <div class="hero mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center">
      <div>
        <div class="tag mb-2">Student Wellness Zone</div>
        <h3 class="fw-bold mb-1">Welcome to Wellness Resources 🌈</h3>
        <p class="mb-0">Colorful tips, relaxing tools, and study motivation just for you.</p>
      </div>
      <div class="mt-3 mt-md-0">
        <a href="student_mood.php" class="btn btn-light soft-btn me-2">Log Mood 😊</a>
        <a href="student_chat.php" class="btn btn-warning soft-btn">Anonymous Chat 💬</a>
      </div>
    </div>
  </div>

  <div class="row g-3">

    <!-- Card 1 -->
    <div class="col-md-4">
      <div class="cardx h-100">
        <div class="card-top grad1">
          <span class="emoji">🫁</span><span class="fw-bold">Breathing for Calm</span>
          <div class="mini text-white-50 mt-1">Quick stress reset in 30 seconds</div>
        </div>
        <div class="card-body">
          <ol class="mb-3">
            <li>Inhale 4 seconds</li>
            <li>Hold 4 seconds</li>
            <li>Exhale 6 seconds</li>
            <li>Repeat 3 times</li>
          </ol>
          <a class="btn btn-danger w-100 soft-btn" target="_blank"
             href="https://www.youtube.com/results?search_query=breathing+exercise+for+students">
            Watch Breathing Video ▶
          </a>
        </div>
      </div>
    </div>

    <!-- Card 2 -->
    <div class="col-md-4">
      <div class="cardx h-100">
        <div class="card-top grad2">
          <span class="emoji">🎯</span><span class="fw-bold">Focus & Study</span>
          <div class="mini text-white-50 mt-1">Study smarter, not harder</div>
        </div>
        <div class="card-body">
          <ul class="mb-3">
            <li>Pomodoro: 25 min study + 5 min break</li>
            <li>Keep phone away</li>
            <li>Drink water and sit straight</li>
          </ul>
          <a class="btn btn-success w-100 soft-btn" target="_blank"
             href="https://www.youtube.com/results?search_query=pomodoro+study+timer">
            Open Pomodoro Timer ⏳
          </a>
        </div>
      </div>
    </div>

    <!-- Card 3 -->
    <div class="col-md-4">
      <div class="cardx h-100">
        <div class="card-top grad3">
          <span class="emoji">🎧</span><span class="fw-bold">Relaxing Music</span>
          <div class="mini text-white-50 mt-1">Calm music for study and sleep</div>
        </div>
        <div class="card-body">
          <p class="mb-3">Try Lo-fi beats or nature sounds when you feel stressed.</p>
          <a class="btn btn-primary w-100 soft-btn" target="_blank"
             href="https://www.youtube.com/results?search_query=lofi+study+music">
            Play Lo-fi 🎵
          </a>
        </div>
      </div>
    </div>

    <!-- Card 4 -->
    <div class="col-md-6">
      <div class="cardx h-100">
        <div class="card-top grad4">
          <span class="emoji">💪</span><span class="fw-bold">Confidence Booster</span>
          <div class="mini text-white-50 mt-1">Small steps = big success</div>
        </div>
        <div class="card-body">
          <ul class="mb-3">
            <li>Write 3 things you did well today ✅</li>
            <li>Talk positively to yourself 🗣️</li>
            <li>Try again tomorrow — mistakes are learning 🎓</li>
          </ul>
          <a class="btn btn-secondary w-100 soft-btn" href="student_mood.php">
            Write Today’s Feelings ✍️
          </a>
        </div>
      </div>
    </div>

    <!-- Card 5 -->
    <div class="col-md-6">
      <div class="cardx h-100">
        <div class="card-top grad6">
          <span class="emoji">🧠</span><span class="fw-bold">Mental Health Tips</span>
          <div class="mini text-white-50 mt-1">Simple habits for daily peace</div>
        </div>
        <div class="card-body">
          <ul class="mb-3">
            <li>Sleep 7–8 hours 💤</li>
            <li>Eat healthy snacks 🍎</li>
            <li>Take breaks from social media 📵</li>
            <li>Talk to counsellor when needed 💛</li>
          </ul>
          <a class="btn btn-info w-100 soft-btn text-white" href="student_chat.php">
            Talk Anonymously 💬
          </a>
        </div>
      </div>
    </div>

    <!-- Card 6: Emergency -->
    <div class="col-12">
      <div class="cardx">
        <div class="card-top grad5">
          <span class="emoji">🚨</span><span class="fw-bold">Need Help Immediately?</span>
          <div class="mini text-white-50 mt-1">If you feel unsafe or overwhelmed</div>
        </div>
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center">
          <div class="mb-2">
            <div class="fw-bold">Talk to a trusted adult.</div>
            <div class="mini">Counsellor / Teacher / Parent — don’t stay alone with fear.</div>
          </div>
          <a href="student_alert.php" class="btn btn-danger soft-btn">
            Send Silent Alert 🚨
          </a>
        </div>
      </div>
      <div class="text-muted small mt-2">
        (If you haven’t created <b>student_alert.php</b> yet, tell me — I will build it next.)
      </div>
    </div>

  </div>

</div>

</body>
</html>