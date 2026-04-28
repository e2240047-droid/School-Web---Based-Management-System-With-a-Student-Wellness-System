<?php
// show errors for development
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . "/db.php";

// allow only teacher
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "teacher") {
    header("Location: login.php");
    exit();
}

$msg = "";

// create uploads folder if not exists
$upload_dir = __DIR__ . "/uploads/";
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// add announcement
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"]);
    $message = trim($_POST["message"]);
    $category = $_POST["category"] ?? "General";
    $publish_date = date("Y-m-d");

    $image_name = "";

    // upload image if selected
    if (!empty($_FILES["image"]["name"])) {
        $image_name = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $upload_dir . $image_name;

        move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
    }

    if ($title !== "" && $message !== "") {
        $stmt = $conn->prepare("
            INSERT INTO announcements (title, message, category, publish_date, image)
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->bind_param("sssss", $title, $message, $category, $publish_date, $image_name);

        if ($stmt->execute()) {
            $msg = "Announcement uploaded successfully.";
        } else {
            $msg = "Upload failed.";
        }
    } else {
        $msg = "Please fill all fields.";
    }
}

// fetch announcements
$result = $conn->query("SELECT * FROM announcements ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Teacher Announcements</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg,#e0f7ff,#fff0f7,#f3ffe3);
            min-height: 100vh;
            font-family: 'Segoe UI', sans-serif;
        }

        .cardx {
            border-radius: 20px;
            background: white;
            box-shadow: 0 12px 25px rgba(0,0,0,0.1);
        }

        .soft-btn {
            border-radius: 14px;
            font-weight: 800;
        }

        .announcement-img {
            width: 100%;
            max-height: 180px;
            object-fit: cover;
            border-radius: 12px;
        }
    </style>
</head>

<body>

<div class="container py-4" style="max-width:1000px;">

    <!-- TOP BAR -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <!-- Go Back Button -->
        <a href="teacher_dashboard.php" class="btn btn-dark soft-btn">
            ← Go Back
        </a>

        <!-- Page Title -->
        <h4 class="fw-bold mb-0">📢 Announcements</h4>

        <!-- Logout Button -->
        <a href="logout.php" class="btn btn-danger soft-btn">
            Logout
        </a>

    </div>

    <!-- ADD ANNOUNCEMENT -->
    <div class="cardx p-4 mb-4">

        <h5 class="mb-3">Add New Announcement</h5>

        <?php if ($msg): ?>
            <div class="alert alert-info">
                <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">

            <div class="mb-3">
                <label>Title</label>
                <input type="text" name="title" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Category</label>
                <select name="category" class="form-select">
                    <option value="General">General</option>
                    <option value="Exam">Exam</option>
                    <option value="Sports">Sports</option>
                    <option value="Events">Events</option>
                </select>
            </div>

            <div class="mb-3">
                <label>Message</label>
                <textarea name="message" class="form-control" rows="4" required></textarea>
            </div>

            <div class="mb-3">
                <label>Image</label>
                <input type="file" name="image" class="form-control">
            </div>

            <button class="btn btn-primary soft-btn">
                Upload
            </button>

        </form>
    </div>

    <!-- SHOW ANNOUNCEMENTS -->
    <div class="cardx p-4">

        <h5 class="mb-3">All Announcements</h5>

        <div class="row g-3">

            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>

                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">

                            <?php if (!empty($row["image"])): ?>
                                <img src="uploads/<?= htmlspecialchars($row["image"]) ?>" class="announcement-img mb-2">
                            <?php endif; ?>

                            <h5><?= htmlspecialchars($row["title"]) ?></h5>

                            <small class="text-muted d-block mb-2">
                                <?= htmlspecialchars($row["category"]) ?> • <?= htmlspecialchars($row["publish_date"]) ?>
                            </small>

                            <p><?= nl2br(htmlspecialchars($row["message"])) ?></p>

                        </div>
                    </div>

                <?php endwhile; ?>
            <?php else: ?>
                <div class="alert alert-secondary">
                    No announcements available.
                </div>
            <?php endif; ?>

        </div>

    </div>

</div>

</body>
</html>