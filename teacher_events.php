<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/db.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "teacher") {
    header("Location: login.php");
    exit();
}

$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $event_date = $_POST["event_date"];

    $image_name = "";

    if (!empty($_FILES["image"]["name"])) {

        $target_dir = "uploads/";
        $image_name = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image_name;

        move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
    }

    $stmt = $conn->prepare("INSERT INTO events (title, description, event_date, image) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $title, $description, $event_date, $image_name);

    if ($stmt->execute()) {
        $msg = "Event uploaded successfully.";
    } else {
        $msg = "Upload failed.";
    }
}

$result = $conn->query("SELECT * FROM events ORDER BY event_date DESC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Manage Events</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{
background:linear-gradient(135deg,#e0f7ff,#fff0f7,#f3ffe3);
min-height:100vh;
font-family:'Segoe UI',sans-serif;
}

.cardx{
border-radius:20px;
background:white;
box-shadow:0 12px 25px rgba(0,0,0,0.1);
}

.soft-btn{
border-radius:14px;
font-weight:800;
}
</style>

</head>
<body>

<div class="container py-4" style="max-width:1000px;">

<a href="teacher_dashboard.php" class="btn btn-dark mb-3">← Back</a>

<div class="cardx p-4 mb-4">

<h3 class="mb-3">📅 Upload Event</h3>

<?php if($msg): ?>
<div class="alert alert-info"><?= $msg ?></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">

<div class="mb-3">
<label class="form-label">Event Title</label>
<input type="text" name="title" class="form-control" required>
</div>

<div class="mb-3">
<label class="form-label">Description</label>
<textarea name="description" class="form-control" rows="4" required></textarea>
</div>

<div class="mb-3">
<label class="form-label">Event Date</label>
<input type="date" name="event_date" class="form-control" required>
</div>

<div class="mb-3">
<label class="form-label">Event Image</label>
<input type="file" name="image" class="form-control">
</div>

<button class="btn btn-success soft-btn">Upload Event</button>

</form>
</div>

<div class="cardx p-4">

<h4 class="mb-3">All Events</h4>

<div class="row g-3">

<?php while($row = $result->fetch_assoc()): ?>

<div class="col-md-6">

<div class="border rounded p-3 h-100">

<?php if($row["image"]): ?>
<img src="uploads/<?= $row["image"] ?>" 
class="img-fluid rounded mb-2"
style="max-height:180px;object-fit:cover;">
<?php endif; ?>

<h5><?= htmlspecialchars($row["title"]) ?></h5>

<small class="text-muted">
<?= $row["event_date"] ?>
</small>

<p class="mt-2"><?= htmlspecialchars($row["description"]) ?></p>

</div>

</div>

<?php endwhile; ?>

</div>

</div>

</div>

</body>
</html>