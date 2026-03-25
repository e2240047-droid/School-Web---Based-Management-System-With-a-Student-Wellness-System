<?php
session_start();
require_once __DIR__ . "/db.php";

if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    header("Location: login.php");
    exit();
}

$result = $conn->query("SELECT id, name, email, role, status FROM users ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Manage Users</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">

<h3>Manage Users</h3>
<a href="admin_dashboard.php" class="btn btn-secondary btn-sm mb-3">← Back</a>

<table class="table table-bordered table-striped bg-white">
<thead>
<tr>
<th>ID</th>
<th>Name</th>
<th>Email</th>
<th>Role</th>
<th>Status</th>
</tr>
</thead>

<tbody>
<?php while($row = $result->fetch_assoc()): ?>
<tr>
<td><?= $row["id"] ?></td>
<td><?= htmlspecialchars($row["name"]) ?></td>
<td><?= htmlspecialchars($row["email"]) ?></td>
<td><?= htmlspecialchars($row["role"]) ?></td>
<td><?= htmlspecialchars($row["status"]) ?></td>
</tr>
<?php endwhile; ?>
</tbody>

</table>

</div>

</body>
</html>