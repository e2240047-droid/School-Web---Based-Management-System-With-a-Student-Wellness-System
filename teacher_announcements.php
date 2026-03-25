<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . "/db.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "teacher") {
    header("Location: login.php");
    exit();
}

$msg = "";

// create uploads folder if missing
$upload_dir = __DIR__ . "/uploads";

if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$image_name = "";

if (!empty($_FILES["image"]["name"])) {

    $image_name = time() . "_" . basename($_FILES["image"]["name"]);
    $target_file = $upload_dir . "/" . $image_name;

    move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"] ?? "");
    $message = trim($_POST["message"] ?? "");
    $category = trim($_POST["category"] ?? "General");
    $publish_date = date("Y-m-d");
    $image_name = "";

    if (!empty($_FILES["image"]["name"])) {
        $image_name = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = __DIR__ . "/uploads/" . $image_name;

        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_name = "";
        }
    }

    if ($title !== "" && $message !== "") {
        $stmt = $conn->prepare("INSERT INTO announcements (title, message, category, publish_date, image) VALUES (?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sssss", $title, $message, $category, $publish_date, $image_name);
            if ($stmt->execute()) {
                $msg = "Announcement uploaded successfully.";
            } else {
                $msg = "Database insert failed: " . $stmt->error;
            }
        } else {
            $msg = "Prepare failed: " . $conn->error;
        }
    } else {
        $msg = "Please fill in all required fields.";
    }
}

$result = $conn->query("SELECT * FROM announcements ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Teacher Announcements</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body{
      background: linear-gradient(135deg,#e0f7ff,#fff0f7,#f3ffe3);
      min-height:100vh;
      font-family:'Segoe UI',sans-serif;
    }
    .cardx{
      border:0;
      border-radius:20px;
      background:rgba(255,255,255,0.95);
      box-shadow:0 12px 25px rgba(0,0,0,0.10);
    }
    .soft-btn{
      border-radius:14px;
      font-weight:800;
    }
    .announcement-img{
      width:100%;
      max-height:180px;
      object-fit:cover;
      border-radius:12px;
    }
  </style>
</head>
<body>

<div class="container py-4" style="max-width:1000px;">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <a href="teacher_dashboard.php" class="btn btn-outline-dark soft-btn">← Back</a>
    <a href="logout.php" class="btn btn-danger soft-btn">Logout</a>
  </div>

  <div class="cardx p-4 mb-4">
    <h3 class="fw-bold mb-3">📢 Manage Announcements</h3>

    <?php if ($msg !== ""): ?>
      <div class="alert alert-info"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
      <div class="mb-3">
        <label class="form-label">Title</label>
        <input type="text" name="title" class="form-control" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Category</label>
        <select name="category" class="form-select">
          <option value="General">General</option>
          <option value="Exam">Exam</option>
          <option value="Sports">Sports</option>
          <option value="Events">Events</option>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Message</label>
        <textarea name="message" class="form-control" rows="4" required></textarea>
      </div>

      <div class="mb-3">
        <label class="form-label">Image</label>
        <input type="file" name="image" class="form-control">
      </div>

      <button type="submit" class="btn btn-primary soft-btn">Upload Announcement</button>
    </form>
  </div>

  <div class="cardx p-4">
    <h4 class="fw-bold mb-3">All Announcements</h4>

    <?php if ($result && $result->num_rows > 0): ?>
      <div class="row g-3">
        <?php while ($row = $result->fetch_assoc()): ?>
          <div class="col-md-6">
            <div class="border rounded p-3 h-100">
              <?php if (!empty($row["image"])): ?>
                <img src="uploads/<?= htmlspecialchars($row["image"]) ?>" class="announcement-img mb-3" alt="Announcement image">
              <?php endif; ?>

              <h5><?= htmlspecialchars($row["title"]) ?></h5>
              <small class="text-muted">
                <?= htmlspecialchars($row["category"]) ?> | <?= htmlspecialchars($row["publish_date"]) ?>
              </small>
              <p class="mt-2 mb-0"><?= nl2br(htmlspecialchars($row["message"])) ?></p>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    <?php else: ?>
      <div class="alert alert-secondary mb-0">No announcements found.</div>
    <?php endif; ?>
  </div>
</div>

</body>
</html>