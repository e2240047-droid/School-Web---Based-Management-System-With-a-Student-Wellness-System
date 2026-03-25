<?php
// wellness_resources.php
session_start();
require_once __DIR__ . "/db.php";

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Counsellor (and admin optional)
$role = $_SESSION["role"] ?? "";
if (!isset($_SESSION["user_id"]) || !in_array($role, ["counsellor", "admin"])) {
    header("Location: login.php");
    exit();
}

$user_id = (int)($_SESSION["user_id"] ?? 0);

// Handle actions
$action = $_POST["action"] ?? "";
$id     = (int)($_POST["id"] ?? 0);

$title    = trim($_POST["title"] ?? "");
$category = trim($_POST["category"] ?? "General");
$content  = trim($_POST["content"] ?? "");
$link     = trim($_POST["link"] ?? "");

$msg = "";

// ADD
if ($action === "add") {
    if ($title === "" || $content === "") {
        $msg = "Title and Content are required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO wellness_resources (title, category, content, link, created_by) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $title, $category, $content, $link, $user_id);
        $stmt->execute();
        $stmt->close();
        header("Location: wellness_resources.php");
        exit();
    }
}

// UPDATE
if ($action === "update" && $id > 0) {
    if ($title === "" || $content === "") {
        $msg = "Title and Content are required.";
    } else {
        $stmt = $conn->prepare("UPDATE wellness_resources SET title=?, category=?, content=?, link=? WHERE id=?");
        $stmt->bind_param("ssssi", $title, $category, $content, $link, $id);
        $stmt->execute();
        $stmt->close();
        header("Location: wellness_resources.php");
        exit();
    }
}

// DELETE
if ($action === "delete" && $id > 0) {
    $stmt = $conn->prepare("DELETE FROM wellness_resources WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: wellness_resources.php");
    exit();
}

// Load resources
$rows = [];
$res = $conn->query("SELECT * FROM wellness_resources ORDER BY created_at DESC");
if ($res) {
    while ($r = $res->fetch_assoc()) $rows[] = $r;
}

// Editing?
$edit_id = (int)($_GET["edit"] ?? 0);
$edit_row = null;
if ($edit_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM wellness_resources WHERE id=?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Wellness Resources (Counsellor)</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body{ background: linear-gradient(135deg,#e0f7ff,#fff0f7,#f3ffe3); min-height:100vh; font-family:'Segoe UI', sans-serif; }
    .hero{ background: linear-gradient(90deg,#0d6efd,#6f42c1,#d63384); color:#fff; border-radius:22px; padding:18px; box-shadow:0 12px 30px rgba(0,0,0,0.12); }
    .cardx{ border:0; border-radius:20px; background: rgba(255,255,255,0.92); box-shadow:0 12px 25px rgba(0,0,0,0.10); padding:18px; }
    .badge-soft{ background: rgba(13,110,253,.12); color:#0d6efd; border:1px solid rgba(13,110,253,.25); }
  </style>
</head>
<body>

<div class="container py-4" style="max-width:1100px;">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <a href="counsellor_dashboard.php" class="btn btn-outline-dark fw-bold">← Back</a>
    <a href="logout.php" class="btn btn-danger fw-bold">Logout</a>
  </div>

  <div class="hero mb-4">
    <h3 class="fw-bold mb-1">💛 Wellness Resources</h3>
    <div class="small">Counsellor can add / edit / delete resources</div>
  </div>

  <?php if ($msg): ?>
    <div class="alert alert-warning"><?= htmlspecialchars($msg) ?></div>
  <?php endif; ?>

  <div class="row g-3">
    <div class="col-lg-5">
      <div class="cardx">
        <h5 class="fw-bold mb-3"><?= $edit_row ? "Edit Resource" : "Add New Resource" ?></h5>

        <form method="post">
          <input type="hidden" name="action" value="<?= $edit_row ? "update" : "add" ?>">
          <input type="hidden" name="id" value="<?= (int)($edit_row["id"] ?? 0) ?>">

          <div class="mb-2">
            <label class="form-label fw-bold">Title</label>
            <input class="form-control" name="title" value="<?= htmlspecialchars($edit_row["title"] ?? "") ?>" required>
          </div>

          <div class="mb-2">
            <label class="form-label fw-bold">Category</label>
            <input class="form-control" name="category" value="<?= htmlspecialchars($edit_row["category"] ?? "General") ?>">
          </div>

          <div class="mb-2">
            <label class="form-label fw-bold">Content</label>
            <textarea class="form-control" name="content" rows="5" required><?= htmlspecialchars($edit_row["content"] ?? "") ?></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">Link (optional)</label>
            <input class="form-control" name="link" value="<?= htmlspecialchars($edit_row["link"] ?? "") ?>">
          </div>

          <div class="d-flex gap-2">
            <button class="btn btn-primary fw-bold" type="submit">
              <?= $edit_row ? "Update" : "Add" ?>
            </button>

            <?php if ($edit_row): ?>
              <a class="btn btn-outline-secondary fw-bold" href="wellness_resources.php">Cancel</a>
            <?php endif; ?>
          </div>
        </form>
      </div>
    </div>

    <div class="col-lg-7">
      <div class="cardx">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h5 class="fw-bold mb-0">All Resources</h5>
          <span class="badge badge-soft px-3 py-2"><?= count($rows) ?> items</span>
        </div>

        <?php if (count($rows) === 0): ?>
          <div class="text-muted">No resources yet. Add your first one on the left.</div>
        <?php else: ?>
          <div class="list-group">
            <?php foreach ($rows as $r): ?>
              <div class="list-group-item rounded-3 mb-2">
                <div class="d-flex justify-content-between">
                  <div>
                    <div class="fw-bold"><?= htmlspecialchars($r["title"]) ?></div>
                    <div class="small text-muted"><?= htmlspecialchars($r["category"]) ?> • <?= htmlspecialchars($r["created_at"]) ?></div>
                  </div>
                  <div class="d-flex gap-2">
                    <a class="btn btn-sm btn-outline-primary" href="wellness_resources.php?edit=<?= (int)$r["id"] ?>">Edit</a>

                    <form method="post" onsubmit="return confirm('Delete this resource?')" style="display:inline;">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?= (int)$r["id"] ?>">
                      <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                    </form>
                  </div>
                </div>

                <div class="mt-2"><?= nl2br(htmlspecialchars($r["content"])) ?></div>

                <?php if (!empty($r["link"])): ?>
                  <div class="mt-2">
                    <a href="<?= htmlspecialchars($r["link"]) ?>" target="_blank">🔗 Open link</a>
                  </div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

      </div>
    </div>
  </div>

</div>
</body>
</html>