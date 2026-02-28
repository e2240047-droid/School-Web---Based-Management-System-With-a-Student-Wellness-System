<?php
require_once "auth.php";
require_once "db.php";

if ($_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = $_POST["title"];
    $msg = $_POST["message"];
    $category = $_POST["category"];

    $imageName = null;

    if (!empty($_FILES["image"]["name"])) {
        $imageName = time() . "_" . $_FILES["image"]["name"];
        move_uploaded_file($_FILES["image"]["tmp_name"], "uploads/" . $imageName);
    }

    $stmt = $conn->prepare("INSERT INTO announcements (title, message, publish_date, category, image) VALUES (?, ?, CURDATE(), ?, ?)");
    $stmt->bind_param("ssss", $title, $msg, $category, $imageName);
    $stmt->execute();

    $message = "Announcement created successfully!";
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Add Announcement</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5" style="max-width:600px;">
<h3>Add Announcement</h3>

<?php if($message): ?>
<div class="alert alert-success"><?= $message ?></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">

<input class="form-control mb-3" name="title" placeholder="Title" required>

<textarea class="form-control mb-3" name="message" placeholder="Message" required></textarea>

<select class="form-select mb-3" name="category">
  <option value="General">General</option>
  <option value="Exam">Exam</option>
  <option value="Sports">Sports</option>
  <option value="Events">Events</option>
</select>

<input type="file" name="image" class="form-control mb-3">

<button class="btn btn-primary w-100">Publish</button>
</form>
</div>

</body>
</html>