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
<html lang="en">
<head>
  <title>Student Dashboard</title>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body{
      background: linear-gradient(135deg,#e0f7ff,#fff0f7,#f3ffe3);
      min-height: 100vh;
      font-family: 'Segoe UI', sans-serif;
      overflow-x: hidden;
    }

    /* =========================
       BACKGROUND FLOATING BLOBS
    ========================== */
    .bg-blobs{
      position: fixed;
      inset: 0;
      pointer-events: none;
      z-index: 0;
      overflow: hidden;
    }
    .blob{
      position: absolute;
      width: 340px;
      height: 340px;
      border-radius: 50%;
      filter: blur(35px);
      opacity: 0.40;
      animation: blobFloat 14s ease-in-out infinite;
      transform: translate3d(0,0,0);
    }
    .blob.b1{ background: rgba(13,110,253,0.35); top: -80px; left: -80px; animation-delay: 0s; }
    .blob.b2{ background: rgba(214,51,132,0.28); bottom: -120px; right: -80px; animation-delay: 2s; }
    .blob.b3{ background: rgba(32,201,151,0.25); top: 45%; left: 60%; animation-delay: 4s; width: 280px; height: 280px; }

    @keyframes blobFloat{
      0%   { transform: translate(0px,0px) scale(1); }
      50%  { transform: translate(40px,-30px) scale(1.08); }
      100% { transform: translate(0px,0px) scale(1); }
    }

    /* Content above blobs */
    .page{
      position: relative;
      z-index: 1;
    }

    /* =========================
         TOP BAR ANIMATIONS
    ========================== */
    .topbar{
      background: linear-gradient(90deg,#0d6efd,#6f42c1,#d63384);
      color:#fff;
      padding: 14px 0;
      box-shadow: 0 10px 25px rgba(0,0,0,0.12);
      position: sticky;
      top: 0;
      z-index: 20;
      animation: topDrop 550ms ease-out;
    }
    @keyframes topDrop{
      from{ transform: translateY(-16px); opacity: 0; }
      to  { transform: translateY(0); opacity: 1; }
    }
    .brand{
      font-size: 20px;
      font-weight: 800;
      letter-spacing: 0.3px;
      display:flex;
      align-items:center;
      gap:10px;
    }
    .pill{
      background: rgba(255,255,255,0.20);
      border: 1px solid rgba(255,255,255,0.25);
      padding: 6px 12px;
      border-radius: 999px;
      font-size: 13px;
      white-space: nowrap;
      transition: transform .2s ease, background .2s ease;
    }
    .pill:hover{
      transform: translateY(-2px);
      background: rgba(255,255,255,0.26);
    }
    .logout-btn{
      border-radius: 12px;
      font-weight: 800;
      padding: 7px 14px;
      transition: transform .2s ease, box-shadow .2s ease;
    }
    .logout-btn:hover{
      transform: translateY(-2px);
      box-shadow: 0 10px 18px rgba(0,0,0,0.18);
    }

    /* =========================
             HERO
    ========================== */
    .hero{
      border-radius: 22px;
      padding: 22px;
      background: rgba(255,255,255,0.72);
      backdrop-filter: blur(8px);
      box-shadow: 0 10px 30px rgba(0,0,0,0.08);
      border: 1px solid rgba(255,255,255,0.60);
      animation: heroIn .8s ease-out both;
    }
    @keyframes heroIn{
      from{ opacity: 0; transform: translateY(18px); }
      to  { opacity: 1; transform: translateY(0); }
    }
    .hero h2{
      margin:0;
      font-weight: 900;
      letter-spacing: 0.2px;
      color:#212529;
      animation: titlePop .9s ease-out both;
    }
    @keyframes titlePop{
      0%   { opacity: 0; transform: translateY(18px); }
      60%  { opacity: 1; transform: translateY(-2px); }
      100% { opacity: 1; transform: translateY(0); }
    }
    .hero small{
      color:#555;
      display: inline-block;
      animation: subtitleFade 1.1s ease-out both;
      animation-delay: .15s;
    }
    @keyframes subtitleFade{
      from{ opacity: 0; transform: translateY(10px); }
      to  { opacity: 1; transform: translateY(0); }
    }

    .hero-actions .btn{
      border-radius: 14px;
      font-weight: 900;
      padding: 10px 16px;
      transition: transform .22s ease, box-shadow .22s ease, filter .22s ease;
      animation: actionsIn .9s ease-out both;
      animation-delay: .12s;
    }
    @keyframes actionsIn{
      from{ opacity: 0; transform: translateY(12px); }
      to  { opacity: 1; transform: translateY(0); }
    }
    .hero-actions .btn:hover{
      transform: translateY(-3px) scale(1.03);
      box-shadow: 0 12px 22px rgba(0,0,0,0.14);
      filter: brightness(1.02);
    }
    .hero-actions .btn:active{
      transform: translateY(-1px) scale(1.01);
    }

    /* =========================
              CARDS
    ========================== */
    .cardx{
      border: 0;
      border-radius: 20px;
      overflow: hidden;
      height: 100%;
      background: rgba(255,255,255,0.86);
      backdrop-filter: blur(6px);
      border: 1px solid rgba(255,255,255,0.60);
      box-shadow: 0 10px 25px rgba(0,0,0,0.08);
      transition: transform .18s ease, box-shadow .18s ease;
      display:flex;
      flex-direction: column;
      transform: translateY(0);
      animation: cardEnter .65s ease-out both;
    }
    @keyframes cardEnter{
      from{ opacity: 0; transform: translateY(14px); }
      to  { opacity: 1; transform: translateY(0); }
    }

    /* stagger delays */
    .d1{ animation-delay: .05s; }
    .d2{ animation-delay: .10s; }
    .d3{ animation-delay: .15s; }
    .d4{ animation-delay: .20s; }
    .d5{ animation-delay: .25s; }
    .d6{ animation-delay: .30s; }

    .cardx:hover{
      transform: translateY(-6px);
      box-shadow: 0 16px 34px rgba(0,0,0,0.12);
    }

    .cardTop{
      padding: 14px 16px;
      color:#fff;
      font-weight: 900;
      display:flex;
      align-items:center;
      justify-content: space-between;
    }
    .icon{
      font-size: 26px;
      filter: drop-shadow(0 2px 6px rgba(0,0,0,0.20));
      transition: transform .25s ease;
    }
    .cardx:hover .icon{
      transform: rotate(-6deg) scale(1.08);
    }
    .mini{
      color:#666;
      font-size: 13px;
      margin-top: 2px;
    }
    .cardBody{
      padding: 16px;
      display:flex;
      flex-direction: column;
      gap: 12px;
      flex: 1;
    }
    .soft-btn{
      border-radius: 14px;
      font-weight: 900;
      padding: 10px 12px;
      margin-top: auto; /* pushes button to bottom for equal alignment */
      transition: transform .2s ease, box-shadow .2s ease;
    }
    .soft-btn:hover{
      transform: translateY(-2px);
      box-shadow: 0 10px 18px rgba(0,0,0,0.12);
    }
    .soft-btn:active{
      transform: translateY(-1px);
    }

    /* =========================
           GRADIENT HEADERS
    ========================== */
    .g1{ background: linear-gradient(135deg,#ff6b6b,#ffd93d); }
    .g2{ background: linear-gradient(135deg,#74c0fc,#4dabf7); }
    .g3{ background: linear-gradient(135deg,#51cf66,#2f9e44); }
    .g4{ background: linear-gradient(135deg,#b197fc,#845ef7); }
    .g5{ background: linear-gradient(135deg,#ff8787,#fa5252); }
    .g6{ background: linear-gradient(135deg,#63e6be,#20c997); }
    .g7{ background: linear-gradient(135deg,#ffa8a8,#f06595); }

    /* =========================
        REDUCED MOTION SUPPORT
    ========================== */
    @media (prefers-reduced-motion: reduce){
      *{ animation: none !important; transition: none !important; }
      .blob{ display:none !important; }
    }
  </style>
</head>

<body>

  <!-- floating background blobs -->
  <div class="bg-blobs" aria-hidden="true">
    <div class="blob b1"></div>
    <div class="blob b2"></div>
    <div class="blob b3"></div>
  </div>

  <div class="page">

    <!-- TOP BAR -->
    <div class="topbar">
      <div class="container d-flex justify-content-between align-items-center">
        <div class="brand">🎓 <span>Student Dashboard</span></div>

        <div class="d-flex gap-2 align-items-center">
          <div class="pill">Hi, <b><?= htmlspecialchars($name) ?></b> 👋</div>
          <a href="logout.php" class="btn btn-light logout-btn btn-sm">Logout</a>
        </div>
      </div>
    </div>

    <div class="container py-4">

      <!-- HERO -->
      <div class="hero mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
          <div>
            <h2>Welcome back 👋</h2>
            <small>Track your mood, chat safely, and stay updated with school activities.</small>
          </div>

          <div class="hero-actions d-flex flex-wrap gap-2">
            <a href="student_mood.php" class="btn btn-primary">Log Mood 😊</a>
            <a href="student_alert.php" class="btn btn-danger">Silent Alert 🚨</a>
          </div>
        </div>
      </div>

      <!-- CARDS (Perfect 3x2 Grid) -->
      <div class="row g-3">

        <div class="col-12 col-md-6 col-lg-4">
          <div class="cardx d1">
            <div class="cardTop g2">
              <span>Announcements</span>
              <span class="icon">📢</span>
            </div>
            <div class="cardBody">
              <div class="mini">See school updates</div>
              <a href="student_announcements.php" class="btn btn-primary w-100 soft-btn">Open</a>
            </div>
          </div>
        </div>

        <div class="col-12 col-md-6 col-lg-4">
          <div class="cardx d2">
            <div class="cardTop g3">
              <span>Events</span>
              <span class="icon">📅</span>
            </div>
            <div class="cardBody">
              <div class="mini">Upcoming school events</div>
              <a href="student_events.php" class="btn btn-success w-100 soft-btn">Open</a>
            </div>
          </div>
        </div>

        <div class="col-12 col-md-6 col-lg-4">
          <div class="cardx d3">
            <div class="cardTop g4">
              <span>My Results</span>
              <span class="icon">📊</span>
            </div>
            <div class="cardBody">
              <div class="mini">Check exam results</div>
              <a href="student_results.php" class="btn btn-secondary w-100 soft-btn">Open</a>
            </div>
          </div>
        </div>

        <div class="col-12 col-md-6 col-lg-4">
          <div class="cardx d4">
            <div class="cardTop g7">
              <span>Wellness Resources</span>
              <span class="icon">💛</span>
            </div>
            <div class="cardBody">
              <div class="mini">Tips, music, videos</div>
              <a href="student_resources.php" class="btn btn-warning w-100 soft-btn">Open</a>
            </div>
          </div>
        </div>

        <div class="col-12 col-md-6 col-lg-4">
          <div class="cardx d5">
            <div class="cardTop g1">
              <span>Anonymous Chat</span>
              <span class="icon">🕶️</span>
            </div>
            <div class="cardBody">
              <div class="mini">Safe & private support chat</div>
              <a href="student_chat.php" class="btn btn-danger w-100 soft-btn">Open</a>
            </div>
          </div>
        </div>

        <div class="col-12 col-md-6 col-lg-4">
          <div class="cardx d6">
            <div class="cardTop g5">
              <span>My Profile</span>
              <span class="icon">👤</span>
            </div>
            <div class="cardBody">
              <div class="mini">View your details</div>
              <a href="student_profile.php" class="btn btn-dark w-100 soft-btn">Open</a>
            </div>
          </div>
        </div>

      </div>

      <div class="text-center text-muted small mt-4">
        Made with 💙 for student well-being
      </div>

    </div>
  </div>

</body>
</html>