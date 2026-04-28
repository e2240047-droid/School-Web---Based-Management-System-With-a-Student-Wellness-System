<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "auth.php";
require_once "db.php";

// allow only admin
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

$message = "";
$edit_data = null;

// upload folder
$upload_dir = __DIR__ . "/uploads/";
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

/* =========================
   DELETE ANNOUNCEMENT
========================= */
if (isset($_GET["delete"])) {
    $id = (int)$_GET["delete"];

    // get image
    $res = $conn->query("SELECT image FROM announcements WHERE id=$id");
    $row = $res->fetch_assoc();

    if (!empty($row["image"])) {
        $path = $upload_dir . $row["image"];
        if (file_exists($path)) unlink($path);
    }

    $conn->query("DELETE FROM announcements WHERE id=$id");

    header("Location: admin_announcements.php");
    exit();
}

/* =========================
   LOAD FOR EDIT
========================= */
if (isset($_GET["edit"])) {
    $id = (int)$_GET["edit"];
    $res = $conn->query("SELECT * FROM announcements WHERE id=$id");
    $edit_data = $res->fetch_assoc();
}

/* =========================
   ADD / UPDATE
========================= */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $id = (int)($_POST["id"] ?? 0);
    $title = trim($_POST["title"]);
    $msg = trim($_POST["message"]);
    $category = $_POST["category"];
    $publish_date = date("Y-m-d");

    $imageName = $_POST["old_image"] ?? "";

    // upload new image
    if (!empty($_FILES["image"]["name"])) {

        // delete old image
        if (!empty($imageName)) {
            $old = $upload_dir . $imageName;
            if (file_exists($old)) unlink($old);
        }

        $imageName = time() . "_" . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $upload_dir . $imageName);
    }

    if ($id > 0) {
        // UPDATE
        $stmt = $conn->prepare("
            UPDATE announcements 
            SET title=?, message=?, category=?, image=? 
            WHERE id=?
        ");
        $stmt->bind_param("ssssi", $title, $msg, $category, $imageName, $id);

        $message = "Announcement updated successfully!";
    } else {
        // INSERT
        $stmt = $conn->prepare("
            INSERT INTO announcements (title, message, category, publish_date, image)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("sssss", $title, $msg, $category, $publish_date, $imageName);

        $message = "Announcement created successfully!";
    }

    $stmt->execute();
}

/* =========================
   FETCH ALL
========================= */
$result = $conn->query("SELECT * FROM announcements ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Announcements</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    background: linear-gradient(135deg,#e0f7ff,#fff0f7,#f3ffe3);
}
.cardx {
    border-radius:20px;
    background:white;
    box-shadow:0 10px 20px rgba(0,0,0,0.1);
}
.announcement-img {
    max-height:150px;
    object-fit:cover;
    width:100%;
}
</style>
</head>

<body>

<div class="container py-4" style="max-width:1000px;">

<div class="d-flex justify-content-between mb-3">
    <a href="admin_dashboard.php" class="btn btn-dark">← Back</a>
    <h4>📢 Admin Announcements</h4>
    <a href="logout.php" class="btn btn-danger">Logout</a>
</div>

<?php if($message): ?>
<div class="alert alert-success"><?= $message ?></div>
<?php endif; ?>

<!-- FORM -->
<div class="cardx p-4 mb-4">

<h5><?= $edit_data ? "Edit Announcement" : "Add Announcement" ?></h5>

<form method="post" enctype="multipart/form-data">

<input type="hidden" name="id" value="<?= $edit_data["id"] ?? 0 ?>">
<input type="hidden" name="old_image" value="<?= $edit_data["image"] ?? "" ?>">

<input class="form-control mb-2" name="title"
value="<?= $edit_data["title"] ?? "" ?>" placeholder="Title" required>

<textarea class="form-control mb-2" name="message" required><?= $edit_data["message"] ?? "" ?></textarea>

<select class="form-select mb-2" name="category">
<option>General</option>
<option>Exam</option>
<option>Sports</option>
<option>Events</option>
</select>

<input type="file" name="image" class="form-control mb-2">

<button class="btn btn-primary w-100">
<?= $edit_data ? "Update" : "Publish" ?>
</button>

</form>
</div>

<!-- LIST -->
<div class="row">

<?php while($row = $result->fetch_assoc()): ?>

<div class="col-md-6 mb-3">
<div class="card p-3">

<?php if($row["image"]): ?>
<img src="uploads/<?= $row["image"] ?>" class="announcement-img mb-2">
<?php endif; ?>

<h5><?= $row["title"] ?></h5>

<small><?= $row["category"] ?> | <?= $row["publish_date"] ?></small>

<p><?= $row["message"] ?></p>

<div class="d-flex justify-content-end gap-2">
    <a href="?edit=<?= $row["id"] ?>" class="btn btn-warning btn-sm">Edit</a>
    <a href="?delete=<?= $row["id"] ?>" 
       class="btn btn-danger btn-sm"
       onclick="return confirm('Delete this announcement?')">
       Delete
    </a>
</div>

</div>
</div>

<?php endwhile; ?>

</div>

</div>

</body>
</html>