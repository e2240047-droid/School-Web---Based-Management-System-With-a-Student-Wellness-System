<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . "/db.php";

// allow only admin
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

$msg = "";
$edit_event = null;

/* =========================
   DELETE EVENT
========================= */
if (isset($_GET["delete"])) {
    $id = (int)$_GET["delete"];

    // delete image
    $res = $conn->query("SELECT image FROM events WHERE id=$id");
    if ($row = $res->fetch_assoc()) {
        if (!empty($row["image"])) {
            $path = "uploads/" . $row["image"];
            if (file_exists($path)) unlink($path);
        }
    }

    $conn->query("DELETE FROM events WHERE id=$id");

    header("Location: admin_events.php");
    exit();
}

/* =========================
   LOAD FOR EDIT
========================= */
if (isset($_GET["edit"])) {
    $id = (int)$_GET["edit"];
    $res = $conn->query("SELECT * FROM events WHERE id=$id");
    $edit_event = $res->fetch_assoc();
}

/* =========================
   ADD / UPDATE EVENT
========================= */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $event_id = (int)($_POST["event_id"] ?? 0);
    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $event_date = $_POST["event_date"];

    $imageName = $_POST["old_image"] ?? "";

    // upload new image
    if (!empty($_FILES["image"]["name"])) {

        if (!empty($imageName)) {
            $old = "uploads/" . $imageName;
            if (file_exists($old)) unlink($old);
        }

        if (!is_dir("uploads")) mkdir("uploads", 0777, true);

        $imageName = time() . "_" . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], "uploads/" . $imageName);
    }

    if ($event_id > 0) {
        // UPDATE
        $stmt = $conn->prepare("
            UPDATE events 
            SET title=?, description=?, event_date=?, image=? 
            WHERE id=?
        ");
        $stmt->bind_param("ssssi", $title, $description, $event_date, $imageName, $event_id);
        $msg = "Event updated successfully!";
    } else {
        // INSERT
        $stmt = $conn->prepare("
            INSERT INTO events (title, description, event_date, image)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("ssss", $title, $description, $event_date, $imageName);
        $msg = "Event added successfully!";
    }

    $stmt->execute();
}

/* =========================
   FETCH EVENTS
========================= */
$result = $conn->query("SELECT * FROM events ORDER BY event_date DESC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Events</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{
background:linear-gradient(135deg,#e0f7ff,#fff0f7,#f3ffe3);
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
.event-img{
width:100%;
max-height:180px;
object-fit:cover;
}
</style>
</head>

<body>

<div class="container py-4" style="max-width:1000px;">

<div class="d-flex justify-content-between mb-3">
<a href="admin_dashboard.php" class="btn btn-dark">← Back</a>
<h4>🎉 Admin Events</h4>
<a href="logout.php" class="btn btn-danger">Logout</a>
</div>

<?php if($msg): ?>
<div class="alert alert-success"><?= $msg ?></div>
<?php endif; ?>

<!-- FORM -->
<div class="cardx p-4 mb-4">

<h5><?= $edit_event ? "Edit Event" : "Add Event" ?></h5>

<form method="post" enctype="multipart/form-data">

<input type="hidden" name="event_id" value="<?= $edit_event["id"] ?? 0 ?>">
<input type="hidden" name="old_image" value="<?= $edit_event["image"] ?? "" ?>">

<input class="form-control mb-2" name="title"
value="<?= $edit_event["title"] ?? "" ?>" placeholder="Title" required>

<textarea class="form-control mb-2" name="description" required><?= $edit_event["description"] ?? "" ?></textarea>

<input type="date" name="event_date" class="form-control mb-2"
value="<?= $edit_event["event_date"] ?? "" ?>" required>

<input type="file" name="image" class="form-control mb-2">

<button class="btn btn-success w-100">
<?= $edit_event ? "Update Event" : "Add Event" ?>
</button>

</form>
</div>

<!-- LIST -->
<div class="row">

<?php while($row = $result->fetch_assoc()): ?>

<div class="col-md-6 mb-3">
<div class="card p-3">

<?php if($row["image"]): ?>
<img src="uploads/<?= $row["image"] ?>" class="event-img mb-2">
<?php endif; ?>

<h5><?= $row["title"] ?></h5>
<small><?= $row["event_date"] ?></small>

<p><?= $row["description"] ?></p>

<div class="d-flex justify-content-end gap-2">
<a href="?edit=<?= $row["id"] ?>" class="btn btn-warning btn-sm">Edit</a>
<a href="?delete=<?= $row["id"] ?>" class="btn btn-danger btn-sm"
onclick="return confirm('Delete this event?')">Delete</a>
</div>

</div>
</div>

<?php endwhile; ?>

</div>

</div>

</body>
</html>