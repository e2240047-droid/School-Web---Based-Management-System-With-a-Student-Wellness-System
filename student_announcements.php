<?php
require_once "auth.php";
require_once "db.php";

$search = $_GET["search"] ?? "";
$category = $_GET["category"] ?? "";

$sql = "SELECT * FROM announcements WHERE 1";

if ($search != "") {
    $sql .= " AND (title LIKE '%$search%' OR message LIKE '%$search%')";
}

if ($category != "" && $category != "All") {
    $sql .= " AND category='$category'";
}

$sql .= " ORDER BY id DESC";

$result = $conn->query($sql);

// Latest announcement for popup
$latest = $conn->query("SELECT * FROM announcements ORDER BY id DESC LIMIT 1")->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
<title>School Announcements</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body { background: linear-gradient(135deg,#e0f7ff,#fff0f7,#f3ffe3); }
.cardx { border-radius:20px; box-shadow:0 10px 25px rgba(0,0,0,0.08); }
</style>
</head>

<body>
<div class="container py-4">

<h3 class="mb-3">📢 School Announcements</h3>

<!-- SEARCH + FILTER -->
<form method="get" class="row g-2 mb-4">
  <div class="col-md-5">
    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
           class="form-control" placeholder="Search announcements...">
  </div>

  <div class="col-md-3">
    <select name="category" class="form-select">
      <option value="All">All Categories</option>
      <option value="General">General</option>
      <option value="Exam">Exam</option>
      <option value="Sports">Sports</option>
      <option value="Events">Events</option>
    </select>
  </div>

  <div class="col-md-2">
    <button class="btn btn-primary w-100">Filter</button>
  </div>
</form>

<div class="row g-3">

<?php while($row = $result->fetch_assoc()): ?>
<div class="col-md-6">
<div class="card cardx p-3">

<h5><?= htmlspecialchars($row["title"]) ?></h5>
<small class="text-muted"><?= $row["publish_date"] ?> | <?= $row["category"] ?></small>

<?php if($row["image"]): ?>
<img src="uploads/<?= $row["image"] ?>" class="img-fluid rounded mt-2">
<?php endif; ?>

<p class="mt-2"><?= nl2br(htmlspecialchars($row["message"])) ?></p>

</div>
</div>
<?php endwhile; ?>

</div>
</div>

<!-- POPUP -->
<?php if($latest): ?>
<script>
window.onload = function(){
  alert("📢 Latest Announcement:\n\n<?= addslashes($latest["title"]) ?>");
};
</script>
<?php endif; ?>

</body>
</html>