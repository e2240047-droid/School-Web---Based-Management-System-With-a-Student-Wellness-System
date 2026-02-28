<?php
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/db.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "student") {
    header("Location: login.php");
    exit();
}

$id = (int)($_SESSION["user_id"] ?? 0);

$stmt = $conn->prepare("SELECT name, email, role, status FROM users WHERE id=? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    die("User not found in database.");
}

$name  = $user["name"];
$email = $user["email"];
$role  = $user["role"];
$status = $user["status"] ?? "Active";

// avatar letter
$avatarLetter = strtoupper(substr($name, 0, 1));
?>
<!DOCTYPE html>
<html>
<head>
  <title>My Profile</title>
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
      color: white;
      border-radius: 22px;
      padding: 22px;
      box-shadow: 0 12px 30px rgba(0,0,0,0.12);
      position: relative;
      overflow: hidden;
    }
    .hero::after{
      content:"";
      position:absolute;
      right:-60px;
      top:-60px;
      width:220px;
      height:220px;
      border-radius:50%;
      background: rgba(255,255,255,0.15);
    }
    .avatar{
      width: 86px;
      height: 86px;
      border-radius: 50%;
      display:flex;
      align-items:center;
      justify-content:center;
      font-size: 34px;
      font-weight: 900;
      color: #fff;
      background: linear-gradient(135deg,#ffd43b,#ff6b6b);
      box-shadow: 0 10px 25px rgba(0,0,0,0.18);
      border: 4px solid rgba(255,255,255,0.55);
    }
    .cardx{
      border: 0;
      border-radius: 22px;
      background: rgba(255,255,255,0.85);
      backdrop-filter: blur(8px);
      box-shadow: 0 10px 25px rgba(0,0,0,0.08);
      border: 1px solid rgba(255,255,255,0.6);
    }
    .badge-pill{
      border-radius: 999px;
      padding: 6px 12px;
      font-weight: 800;
      font-size: 12px;
    }
    .soft-btn{
      border-radius: 14px;
      font-weight: 800;
    }
    .info-item{
      padding: 12px 14px;
      border-radius: 16px;
      background: rgba(13,110,253,0.06);
      border: 1px solid rgba(13,110,253,0.10);
    }
    .info-label{
      font-size: 12px;
      color: #666;
      margin-bottom: 4px;
    }
    .info-value{
      font-weight: 800;
      font-size: 15px;
      color: #222;
      word-break: break-word;
    }
  </style>
</head>

<body>

<div class="container py-4" style="max-width: 900px;">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <a href="student_dashboard.php" class="btn btn-outline-dark soft-btn">← Back</a>
    <a href="logout.php" class="btn btn-light soft-btn">Logout</a>
  </div>

  <!-- HERO -->
  <div class="hero mb-4">
    <div class="d-flex flex-wrap align-items-center gap-3">
      <div class="avatar"><?= htmlspecialchars($avatarLetter) ?></div>
      <div>
        <h3 class="fw-bold mb-1">My Profile ✨</h3>
        <div class="small">Your account details and student wellness info</div>

        <div class="mt-2 d-flex flex-wrap gap-2">
          <span class="badge bg-warning text-dark badge-pill">🎓 Student</span>
          <span class="badge bg-success badge-pill">✅ <?= htmlspecialchars($status) ?></span>
        </div>
      </div>
    </div>
  </div>

  <!-- PROFILE CARDS -->
  <div class="row g-3">

    <!-- Details -->
    <div class="col-md-7">
      <div class="cardx p-4 h-100">
        <h5 class="fw-bold mb-3">👤 Account Details</h5>

        <div class="info-item mb-3">
          <div class="info-label">Full Name</div>
          <div class="info-value"><?= htmlspecialchars($name) ?></div>
        </div>

        <div class="info-item mb-3">
          <div class="info-label">Email</div>
          <div class="info-value"><?= htmlspecialchars($email) ?></div>
        </div>

        <div class="info-item">
          <div class="info-label">Role</div>
          <div class="info-value"><?= htmlspecialchars($role) ?></div>
        </div>

        <hr>

        <div class="d-flex flex-wrap gap-2">
          <a href="student_resources.php" class="btn btn-warning soft-btn">Wellness Resources 💛</a>
          <a href="student_chat.php" class="btn btn-primary soft-btn">Anonymous Chat 💬</a>
          <a href="student_alert.php" class="btn btn-danger soft-btn">Silent Alert 🚨</a>
          <a href="change_password.php" class="btn btn-dark soft-btn">Change Password 🔐</a>
        </div>
      </div>
    </div>

    <!-- Fun + Motivation -->
    <div class="col-md-5">
      <div class="cardx p-4 h-100">
        <h5 class="fw-bold mb-3">🌈 Daily Boost</h5>

        <div class="p-3 rounded-4 mb-3" style="background:linear-gradient(135deg,#63e6be,#74c0fc); color:#0b2e13;">
          <div class="fw-bold">Tip of the day ✅</div>
          <div class="small mt-1">
            “Take short breaks while studying — your brain learns faster!”
          </div>
        </div>

        <div class="p-3 rounded-4 mb-3" style="background:linear-gradient(135deg,#ffe066,#ffa8a8);">
          <div class="fw-bold">Mood Reminder 😊</div>
          <div class="small mt-1">Log your mood daily to track your wellness journey.</div>
          <a href="student_mood.php" class="btn btn-dark soft-btn btn-sm mt-2">Log Mood</a>
        </div>

        <div class="p-3 rounded-4" style="background:linear-gradient(135deg,#b197fc,#f783ac); color:#2b134f;">
          <div class="fw-bold">You are not alone 💜</div>
          <div class="small mt-1">If you feel stressed, talk in Anonymous Chat or send Alert.</div>
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