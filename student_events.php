<?php
require_once "auth.php";
require_once "db.php";
if ($_SESSION["role"] !== "student") { header("Location: login.php"); exit(); }

$list = $conn->query("SELECT title, event_date FROM events ORDER BY event_date ASC");
?>
<!DOCTYPE html>
<html>
<head>
  <title>Events</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <a href="student_dashboard.php">← Back</a>
  <h4 class="mt-2">Events</h4>

  <div class="card shadow-sm p-3">
    <table class="table table-sm mb-0">
      <thead><tr><th>Title</th><th>Date</th></tr></thead>
      <tbody>
      <?php while($e = $list->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($e["title"]) ?></td>
          <td><?= htmlspecialchars($e["event_date"]) ?></td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>

</div>
</body>
</html>