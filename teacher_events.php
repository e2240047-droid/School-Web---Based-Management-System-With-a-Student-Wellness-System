<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/db.php";

// allow only teacher access
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "teacher") {
    header("Location: login.php");
    exit();
}

$msg = "";
$edit_event = null;

// delete event
if (isset($_GET["delete"])) {
    $id = (int)$_GET["delete"];

    $stmt = $conn->prepare("SELECT image FROM events WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $event = $stmt->get_result()->fetch_assoc();

    if ($event && !empty($event["image"])) {
        $image_path = "uploads/" . $event["image"];

        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }

    $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: teacher_events.php");
    exit();
}

// load event for edit
if (isset($_GET["edit"])) {
    $id = (int)$_GET["edit"];

    $stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit_event = $stmt->get_result()->fetch_assoc();
}

// add or update event
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $event_id = (int)($_POST["event_id"] ?? 0);
    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $event_date = $_POST["event_date"];

    $image_name = $_POST["old_image"] ?? "";

    if (!empty($_FILES["image"]["name"])) {
        $target_dir = "uploads/";

        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        if (!empty($image_name)) {
            $old_path = $target_dir . $image_name;

            if (file_exists($old_path)) {
                unlink($old_path);
            }
        }

        $image_name = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image_name;

        move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
    }

    if ($event_id > 0) {
        $stmt = $conn->prepare("
            UPDATE events
            SET title = ?, description = ?, event_date = ?, image = ?
            WHERE id = ?
        ");
        $stmt->bind_param("ssssi", $title, $description, $event_date, $image_name, $event_id);

        if ($stmt->execute()) {
            header("Location: teacher_events.php");
            exit();
        } else {
            $msg = "Failed to update event.";
        }
    } else {
        $stmt = $conn->prepare("
            INSERT INTO events (title, description, event_date, image)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("ssss", $title, $description, $event_date, $image_name);

        if ($stmt->execute()) {
            $msg = "Event uploaded successfully.";
        } else {
            $msg = "Upload failed.";
        }
    }
}

// get all events
$result = $conn->query("SELECT * FROM events ORDER BY event_date DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Events</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #e0f7ff, #fff0f7, #f3ffe3);
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

        .event-img {
            width: 100%;
            max-height: 180px;
            object-fit: cover;
        }
    </style>
</head>

<body>

<div class="container py-4" style="max-width:1000px;">

    <a href="teacher_dashboard.php" class="btn btn-dark mb-3">← Back</a>

    <div class="cardx p-4 mb-4">

        <h3 class="mb-3">
            <?= $edit_event ? "✏️ Edit Event" : "📅 Upload Event" ?>
        </h3>

        <?php if ($msg): ?>
            <div class="alert alert-info"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">

            <input type="hidden" name="event_id" value="<?= $edit_event ? (int)$edit_event["id"] : 0 ?>">
            <input type="hidden" name="old_image" value="<?= $edit_event ? htmlspecialchars($edit_event["image"]) : "" ?>">

            <div class="mb-3">
                <label class="form-label">Event Title</label>
                <input type="text" name="title" class="form-control"
                       value="<?= $edit_event ? htmlspecialchars($edit_event["title"]) : "" ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4" required><?= $edit_event ? htmlspecialchars($edit_event["description"]) : "" ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Event Date</label>
                <input type="date" name="event_date" class="form-control"
                       value="<?= $edit_event ? htmlspecialchars($edit_event["event_date"]) : "" ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Event Image</label>
                <input type="file" name="image" class="form-control">

                <?php if ($edit_event && !empty($edit_event["image"])): ?>
                    <div class="mt-2">
                        <small class="text-muted">Current image:</small><br>
                        <img src="uploads/<?= htmlspecialchars($edit_event["image"]) ?>"
                             class="rounded mt-1"
                             style="max-height:100px; object-fit:cover;">
                    </div>
                <?php endif; ?>
            </div>

            <button class="btn btn-success soft-btn">
                <?= $edit_event ? "Update Event" : "Upload Event" ?>
            </button>

            <?php if ($edit_event): ?>
                <a href="teacher_events.php" class="btn btn-secondary soft-btn ms-2">Cancel</a>
            <?php endif; ?>

        </form>
    </div>

    <div class="cardx p-4">

        <h4 class="mb-3">All Events</h4>

        <div class="row g-3">

            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>

                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">

                            <?php if (!empty($row["image"])): ?>
                                <img src="uploads/<?= htmlspecialchars($row["image"]) ?>"
                                     class="img-fluid rounded mb-2 event-img">
                            <?php endif; ?>

                            <h5><?= htmlspecialchars($row["title"]) ?></h5>

                            <small class="text-muted">
                                <?= htmlspecialchars($row["event_date"]) ?>
                            </small>

                            <p class="mt-2"><?= htmlspecialchars($row["description"]) ?></p>

                            <div class="d-flex justify-content-end gap-2 mt-3">
                                <a href="teacher_events.php?edit=<?= (int)$row["id"] ?>"
                                   class="btn btn-warning btn-sm soft-btn">
                                    Edit
                                </a>

                                <a href="teacher_events.php?delete=<?= (int)$row["id"] ?>"
                                   class="btn btn-danger btn-sm soft-btn"
                                   onclick="return confirm('Are you sure you want to delete this event?')">
                                    Delete
                                </a>
                            </div>

                        </div>
                    </div>

                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-muted">No events found.</p>
            <?php endif; ?>

        </div>
    </div>

</div>

</body>
</html>