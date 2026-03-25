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

$result = $conn->query("
    SELECT ac.id, ac.created_at,
           (
             SELECT message
             FROM anonymous_messages am
             WHERE am.chat_id = ac.id
             ORDER BY am.id DESC
             LIMIT 1
           ) AS last_message
    FROM anonymous_chats ac
    ORDER BY ac.id DESC
");
?>
<!DOCTYPE html>
<html>
<head>
  <title>Anonymous Chats</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Anonymous Chats</h3>
    <a href="counsellor_dashboard.php" class="btn btn-secondary">Back</a>
  </div>

  <?php if ($result && $result->num_rows > 0): ?>
    <div class="list-group">
      <?php while ($row = $result->fetch_assoc()): ?>
        <a href="counsellor_chat_view.php?chat_id=<?= (int)$row['id'] ?>" class="list-group-item list-group-item-action">
          <strong>Chat #<?= (int)$row['id'] ?></strong><br>
          <small><?= htmlspecialchars($row['last_message'] ?? 'No messages yet') ?></small>
        </a>
      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <div class="alert alert-info">No chats found.</div>
  <?php endif; ?>
</div>
</body>
</html>