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

$msg = "";

// ✅ Get or create chat room for student
$stmt = $conn->prepare("SELECT id FROM anonymous_chats WHERE student_id=? LIMIT 1");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$chatRes = $stmt->get_result();

if ($chatRes->num_rows === 1) {
    $chat_id = (int)$chatRes->fetch_assoc()["id"];
} else {
    $ins = $conn->prepare("INSERT INTO anonymous_chats (student_id) VALUES (?)");
    $ins->bind_param("i", $student_id);
    $ins->execute();
    $chat_id = mysqli_insert_id($conn);
}

// ✅ Send message
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $text = trim($_POST["message"] ?? "");

    if ($text === "") {
        $msg = "Message cannot be empty.";
    } else {

        // Basic bad word filter
        $badWords = ["fuck","shit","bitch","asshole"];
        foreach ($badWords as $bw) {
            if (stripos($text, $bw) !== false) {
                $msg = "Please use respectful words 🙂";
                break;
            }
        }

        if ($msg === "") {
            $sender = "student";
            $stmt2 = $conn->prepare("INSERT INTO anonymous_messages (chat_id, sender_role, message) VALUES (?,?,?)");
            $stmt2->bind_param("iss", $chat_id, $sender, $text);
            $stmt2->execute();

            header("Location: student_chat.php");
            exit();
        }
    }
}

// ✅ Load messages
$stmt3 = $conn->prepare("SELECT sender_role, message, created_at FROM anonymous_messages WHERE chat_id=? ORDER BY id ASC");
$stmt3->bind_param("i", $chat_id);
$stmt3->execute();
$messages = $stmt3->get_result();
?>
<!DOCTYPE html>
<html>
<head>
  <title>Anonymous Chat</title>
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
      padding: 18px;
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
      background: rgba(255,255,255,0.16);
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
    .chatbox{
      max-height: 440px;
      overflow-y: auto;
      padding: 14px;
      background: rgba(255,255,255,0.55);
      border-radius: 18px;
      border: 1px solid rgba(0,0,0,0.05);
    }
    .bubble{
      max-width: 78%;
      padding: 10px 12px;
      border-radius: 18px;
      box-shadow: 0 6px 16px rgba(0,0,0,0.08);
      margin-bottom: 10px;
      word-break: break-word;
    }
    .student{
      background: linear-gradient(135deg,#63e6be,#20c997);
      color: #052b1d;
      border-bottom-right-radius: 6px;
    }
    .counsellor{
      background: linear-gradient(135deg,#74c0fc,#4dabf7);
      color: #05233d;
      border-bottom-left-radius: 6px;
    }
    .meta{
      font-size: 11px;
      opacity: 0.75;
      margin-top: 4px;
    }
    textarea{
      border-radius: 16px !important;
    }
  </style>
</head>

<body>

<div class="container py-4" style="max-width: 980px;">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <a href="student_dashboard.php" class="btn btn-outline-dark soft-btn">← Back</a>
    <div class="small text-muted">Hi, <b><?= htmlspecialchars($name) ?></b> 👋</div>
  </div>

  <div class="hero mb-4">
    <h3 class="fw-bold mb-1">🕶️ Anonymous Chat</h3>
    <div class="small">
      Your identity is hidden. Chat safely and respectfully 💛
    </div>
  </div>

  <?php if($msg): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($msg) ?></div>
  <?php endif; ?>

  <div class="cardx p-4 mb-3">
    <div class="chatbox" id="chatbox">

      <?php if ($messages->num_rows === 0): ?>
        <div class="text-muted">No messages yet. Start the conversation 😊</div>
      <?php endif; ?>

      <?php while($m = $messages->fetch_assoc()): ?>
        <?php $isStudent = ($m["sender_role"] === "student"); ?>

        <div class="d-flex <?= $isStudent ? "justify-content-end" : "justify-content-start" ?>">
          <div class="bubble <?= $isStudent ? "student" : "counsellor" ?>">
            <div class="fw-bold small">
              <?= $isStudent ? "You (Anonymous)" : "Counsellor" ?>
            </div>
            <div><?= nl2br(htmlspecialchars($m["message"])) ?></div>
            <div class="meta">🕒 <?= htmlspecialchars($m["created_at"]) ?></div>
          </div>
        </div>
      <?php endwhile; ?>

    </div>
  </div>

  <div class="cardx p-4">
    <form method="post">
      <label class="form-label fw-bold">Type your message 💬</label>
      <textarea name="message" class="form-control mb-3" rows="3" placeholder="Write here..." required></textarea>
      <button class="btn btn-primary w-100 soft-btn">Send Message 🚀</button>
    </form>

    <div class="text-muted small mt-3">
      If you feel unsafe, use <b>Silent Alert</b> from Wellness Resources 🚨
    </div>
  </div>

</div>

<script>
// Auto scroll to bottom
const chat = document.getElementById("chatbox");
chat.scrollTop = chat.scrollHeight;
</script>

</body>
</html>