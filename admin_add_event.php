<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . "/db.php";

if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    header("Location: login.php");
    exit();
}

$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $event_date = $_POST["event_date"] ?? "";

    if ($title === "" || $description === "" || $event_date === "") {
        $msg = "All fields are required.";
    } else {

        $imageName = null;

        // ✅ Upload image (optional)
        if (!empty($_FILES["image"]["name"])) {

            // create uploads folder if not exist
            $uploadDir = __DIR__ . "/uploads/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $imageName = time() . "_" . basename($_FILES["image"]["name"]);
            move_uploaded_file($_FILES["image"]["tmp_name"], $uploadDir . $imageName);
        }

        $stmt = $conn->prepare("INSERT INTO events (title, description, event_date, image) VALUES (?,?,?,?)");
        $stmt->bind_param("ssss", $title, $description, $event_date, $imageName);
        $stmt->execute();

        $msg = "✅ Event added successfully!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Add Event</title>
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
    }
    .cardx{
      border:0;
      border-radius: 20px;
      background: rgba(255,255,255,0.90);
      backdrop-filter: blur(8px);
      box-shadow: 0 12px 25px rgba(0,0,0,0.10);
      border: 1px solid rgba(255,255,255,0.6);
    }
    .soft-btn{
      border-radius: 14px;
      font-weight: 800;
    }
  </style>
</head>

<body>

<div class="container py-4" style="max-width: 850px;">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <a href="admin_dashboard.php" class="btn btn-outline-dark soft-btn">← Back</a>
    <a href="logout.php" class="btn btn-danger soft-btn">Logout</a>
  </div>

  <div class="hero mb-4">
    <h3 class="fw-bold mb-1">🎉 Add New Event</h3>
    <div class="small">Create school events students can view 🌟</div>
  </div>

  <?php if($msg): ?>
    <div class="alert alert-info"><?= htmlspecialchars($msg) ?></div>
  <?php endif; ?>

  <div class="cardx p-4">
    <form method="post" enctype="multipart/form-data">

      <div class="mb-3">
        <label class="form-label fw-bold">Event Title</label>
        <input type="text" name="title" class="form-control" required>
      </div>

      <div class="mb-3">
        <label class="form-label fw-bold">Event Description</label>
        <textarea name="description" class="form-control" rows="4" required></textarea>
      </div>

      <div class="mb-3">
        <label class="form-label fw-bold">Event Date</label>
        <input type="date" name="event_date" class="form-control" required>
      </div>

      <div class="mb-3">
        <label class="form-label fw-bold">Event Image (optional)</label>
        <input type="file" name="image" class="form-control" accept="image/*">
      </div>

      <button class="btn btn-success w-100 soft-btn">✅ Add Event</button>
    </form>
  </div>

</div>

</body>
</html>