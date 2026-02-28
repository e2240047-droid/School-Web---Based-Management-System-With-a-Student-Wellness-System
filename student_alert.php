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
$success = "";
$error = "";

if ($student_id <= 0) {
    die("Invalid session. Please login again.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $alert_type = $_POST["alert_type"] ?? "";
    $message = trim($_POST["message"] ?? "");

    if ($alert_type === "") {
        $error = "Please select an alert type.";
    } else {
        $stmt = $conn->prepare("INSERT INTO silent_alerts (student_id, alert_type, message) VALUES (?,?,?)");
        if (!$stmt) {
            die("SQL prepare failed: " . $conn->error);
        }
        $stmt->bind_param("iss", $student_id, $alert_type, $message);
        $stmt->execute();
        $success = "✅ Alert sent successfully. A counsellor/admin will review it soon.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Silent Alert</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4" style="max-width:650px;">
  <a href="student_resources.php" class="btn btn-outline-dark">← Back</a>

  <div class="p-3 mt-3 rounded text-white" style="background:linear-gradient(90deg,#dc3545,#fd7e14,#ffc107);">
    <h3 class="fw-bold mb-1">🚨 Silent Alert</h3>
    <p class="mb-0 small">Use this if you need urgent help. This is private.</p>
  </div>

  <?php if($success): ?>
    <div class="alert alert-success mt-3"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <?php if($error): ?>
    <div class="alert alert-danger mt-3"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="card shadow-sm p-4 mt-3">
    <form method="post">

      <label class="form-label fw-bold">Select Alert Type</label>
      <select name="alert_type" class="form-select mb-3" required>
        <option value="">-- Choose --</option>
        <option value="Bullying">Bullying</option>
        <option value="Feeling unsafe">Feeling unsafe</option>
        <option value="Very stressed">Very stressed</option>
        <option value="Anxiety / panic">Anxiety / panic</option>
        <option value="Need counsellor support">Need counsellor support</option>
        <option value="Other">Other</option>
      </select>

      <label class="form-label fw-bold">Optional Message</label>
      <textarea name="message" class="form-control mb-3" rows="4"
        placeholder="Explain what happened (optional)"></textarea>

      <button class="btn btn-danger w-100">Send Silent Alert</button>

      <div class="text-muted small mt-3">
        If this is a real emergency, contact a trusted adult immediately.
      </div>
    </form>
  </div>
</div>

</body>
</html>