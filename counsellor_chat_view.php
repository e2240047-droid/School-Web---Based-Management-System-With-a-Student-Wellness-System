<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . "/db.php";

if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "counsellor") {
    header("Location: login.php");
    exit();
}

$chat_id = (int)($_GET["chat_id"] ?? 0);
if ($chat_id <= 0) {
    die("Invalid chat.");
}

$check = $conn->prepare("SELECT id FROM anonymous_chats WHERE id=?");
$check->bind_param("i", $chat_id);
$check->execute();
$exists = $check->get_result();

if ($exists->num_rows === 0) {
    die("Chat not found.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $message = trim($_POST["message"] ?? "");
    if ($message !== "") {
        $sender = "counsellor";
        $stmt = $conn->prepare("INSERT INTO anonymous_messages (chat_id, sender_role, message) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $chat_id, $sender, $message);
        $stmt->execute();
        header("Location: counsellor_chat_view.php?chat_id=" . $chat_id);
        exit();
    }
}

$stmt = $conn->prepare("SELECT sender_role, message, created_at FROM anonymous_messages WHERE chat_id=? ORDER BY id ASC");
$stmt->bind_param("i", $chat_id);
$stmt->execute();
$messages = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
  <title>Chat View</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Chat #<?= $chat_id ?></h3>
    <a href="counsellor_chat_list.php" class="btn btn-secondary">Back</a>
  </div>

  <div class="card p-3 mb-3">
    <?php if ($messages->num_rows > 0): ?>
      <?php while ($m = $messages->fetch_assoc()): ?>
        <div class="mb-2">
          <strong><?= htmlspecialchars($m["sender_role"]) ?>:</strong>
          <?= nl2br(htmlspecialchars($m["message"])) ?><br>
          <small class="text-muted"><?= htmlspecialchars($m["created_at"]) ?></small>
        </div>
        <hr>
      <?php endwhile; ?>
    <?php else: ?>
      <p>No messages yet.</p>
    <?php endif; ?>
  </div>

  <form method="post" class="card p-3">
    <label class="form-label">Reply</label>
    <textarea name="message" class="form-control mb-2" rows="3" required></textarea>
    <button type="submit" class="btn btn-primary">Send</button>
  </form>
</div>
</body>
</html>