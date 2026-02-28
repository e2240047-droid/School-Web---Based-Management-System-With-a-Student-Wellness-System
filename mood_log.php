<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/db.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "student") {
    header("Location: login.php");
    exit();
}

$student_id = (int)($_SESSION["user_id"] ?? 0);
$name = $_SESSION["name"] ?? "Student";

$success = "";
$error = "";

// ✅ Delete own mood log
if (isset($_GET["delete"])) {
    $del_id = (int)$_GET["delete"];
    $del = $conn->prepare("DELETE FROM mood_logs WHERE id=? AND student_id=?");
    $del->bind_param("ii", $del_id, $student_id);
    $del->execute();
    header("Location: student_mood.php");
    exit();
}

// ✅ Save mood
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $mood = $_POST["mood"] ?? "";
    $note = trim($_POST["note"] ?? "");

    if ($mood === "") {
        $error = "Please select a mood.";
    } else {
        $stmt = $conn->prepare("INSERT INTO mood_logs (student_id, mood, note) VALUES (?,?,?)");
        $stmt->bind_param("iss", $student_id, $mood, $note);
        $stmt->execute();
        $success = "✅ Mood saved successfully!";
    }
}

// ✅ Load mood history
$stmt = $conn->prepare("SELECT id, mood, note, created_at FROM mood_logs WHERE student_id=? ORDER BY id DESC");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$logs = $stmt->get_result();

function moodBadge($mood){
    $m = strtolower($mood);
    if ($m == "happy") return "bg-success";
    if ($m == "excited") return "bg-primary";
    if ($m == "calm") return "bg-info text-dark";
    if ($m == "sad") return "bg-secondary";
    if ($m == "angry") return "bg-danger";
    if ($m == "stressed") return "bg-warning text-dark";
    return "bg-dark";
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Mood & Wellness Log</title>
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
      padding: 18px;
      box-shadow: 0 12px 30px rgba(0,0,0,0.12);
      position: relative;
      overflow:hidden;
    }
    .hero::after{
      content:"";
      position:absolute;
      right:-70px;
      top:-70px;
      width:220px;
      height:220px;
      border-radius:50%;
      background: rgba(255,255,255,0.15);
    }
    .cardx{
      border: 0;
      border-radius: 22px;
      background: rgba(255,255,255,0.88);
      backdrop-filter: blur(8px);
      box-shadow: 0 12px 30px rgba(0,0,0,0.10);
      border: 1px solid rgba(255,255,255,0.6);
    }
    .soft-btn{
      border-radius: 14px;
      font-weight: 800;
    }
    .mood-btn{
      border-radius: 18px;
      padding: 12px 12px;
      font-weight: 800;
      border: 0;
      width: 100%;
      transition: transform .12s ease;
    }
    .mood-btn:hover{ transform: translateY(-3px); }
    .m1{ background: linear-gradient(135deg,#63e6be,#20c997); color:#0b2e13; }
    .m2{ background: linear-gradient(135deg,#74c0fc,#4dabf7); color:#0b2a4f; }
    .m3{ background: linear-gradient(135deg,#ffe066,#ffa94d); color:#4a2c00; }
    .m4{ background: linear-gradient(135deg,#ced4da,#868e96); color:#1b1e21; }
    .m5{ background: linear-gradient(135deg,#ffa8a8,#fa5252); color:#3b0000; }
    .m6{ background: linear-gradient(135deg,#ffec99,#fcc419); color:#3d2a00; }

    .log-item{
      border-radius: 18px;
      background: rgba(255,255,255,0.75);
      border: 1px solid rgba(0,0,0,0.05);
      padding: 14px;
      box-shadow: 0 8px 18px rgba(0,0,0,0.06);
    }
    .smallmuted{ font-size: 12px; color:#666; }
  </style>
</head>

<body>
<div class="container py-4" style="max-width: 980px;">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <a href="student_dashboard.php" class="btn btn-outline-dark soft-btn">← Back</a>
    <div class="small text-muted">Hi, <b><?= htmlspecialchars($name) ?></b> 😊</div>
  </div>

  <div class="hero mb-4">
    <h3 class="fw-bold mb-1">📝 Mood & Wellness Log</h3>
    <div class="small">Track your feelings daily and understand your wellness journey 🌈</div>
  </div>

  <?php if($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>
  <?php if($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="row g-3">

    <!-- LEFT: LOG FORM -->
    <div class="col-lg-5">
      <div class="cardx p-4">
        <h5 class="fw-bold mb-3">Choose Your Mood</h5>

        <form method="post" id="moodForm">
          <input type="hidden" name="mood" id="moodInput" value="">

          <div class="row g-2 mb-3">
            <div class="col-6">
              <button type="button" class="mood-btn m1" onclick="setMood('Happy')">😊 Happy</button>
            </div>
            <div class="col-6">
              <button type="button" class="mood-btn m2" onclick="setMood('Excited')">🤩 Excited</button>
            </div>
            <div class="col-6">
              <button type="button" class="mood-btn m3" onclick="setMood('Calm')">😌 Calm</button>
            </div>
            <div class="col-6">
              <button type="button" class="mood-btn m6" onclick="setMood('Stressed')">😰 Stressed</button>
            </div>
            <div class="col-6">
              <button type="button" class="mood-btn m4" onclick="setMood('Sad')">😢 Sad</button>
            </div>
            <div class="col-6">
              <button type="button" class="mood-btn m5" onclick="setMood('Angry')">😡 Angry</button>
            </div>
          </div>

          <div class="mb-2">
            <label class="form-label fw-bold">Optional Note</label>
            <textarea class="form-control" name="note" rows="4" placeholder="Write what made you feel this way..."></textarea>
          </div>

          <div class="d-grid mt-3">
            <button class="btn btn-primary soft-btn">Save Mood ✅</button>
          </div>

          <div class="text-muted small mt-2">
            Tip: Logging moods daily can help you notice patterns and feel better 💛
          </div>
        </form>
      </div>
    </div>

    <!-- RIGHT: HISTORY -->
    <div class="col-lg-7">
      <div class="cardx p-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h5 class="fw-bold mb-0">📅 My Mood History</h5>
          <span class="smallmuted">Latest first</span>
        </div>

        <?php if($logs->num_rows === 0): ?>
          <div class="text-muted">No mood logs yet. Add your first mood 😊</div>
        <?php endif; ?>

        <div class="d-flex flex-column gap-2 mt-3" style="max-height: 520px; overflow:auto;">
          <?php while($row = $logs->fetch_assoc()): ?>
            <div class="log-item">
              <div class="d-flex justify-content-between align-items-center">
                <span class="badge <?= moodBadge($row["mood"]) ?> px-3 py-2"><?= htmlspecialchars($row["mood"]) ?></span>
                <a class="btn btn-sm btn-outline-danger soft-btn"
                   href="student_mood.php?delete=<?= (int)$row["id"] ?>"
                   onclick="return confirm('Delete this mood log?')">
                   Delete
                </a>
              </div>

              <?php if(trim($row["note"]) !== ""): ?>
                <div class="mt-2"><?= nl2br(htmlspecialchars($row["note"])) ?></div>
              <?php else: ?>
                <div class="mt-2 text-muted small">No note</div>
              <?php endif; ?>

              <div class="smallmuted mt-2">🕒 <?= htmlspecialchars($row["created_at"]) ?></div>
            </div>
          <?php endwhile; ?>
        </div>

      </div>
    </div>

  </div>

</div>

<script>
function setMood(mood){
  document.getElementById("moodInput").value = mood;

  // small popup
  alert("Mood selected: " + mood + " ✅ Now click Save Mood!");
}
</script>

</body>
</html>