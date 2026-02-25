<?php
session_start();
include __DIR__ . "/db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: login.php");
    exit();
}

$users = $conn->query("SELECT id, name, email, role, status, is_approved FROM users ORDER BY id DESC");
?>


<!DOCTYPE html>
<html>
<head>
  <title>Manage Users</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">

  <div class="d-flex justify-content-between align-items-center">
    <h3>Manage Users</h3>
    <a href="admin_dashboard.php" class="btn btn-secondary btn-sm">Back</a>
  </div>

  <hr>

  <table class="table table-bordered table-striped">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Role</th>
        <th>Status</th>
        <th>Approval</th>
        <th>Actions</th>
      </tr>
    </thead>

    <tbody>
      <?php while($u = $users->fetch_assoc()) { ?>
        <tr>
          <td><?php echo $u["id"]; ?></td>
          <td><?php echo htmlspecialchars($u["name"]); ?></td>
          <td><?php echo htmlspecialchars($u["email"]); ?></td>
          <td><?php echo $u["role"]; ?></td>

          <td>
            <?php if ($u["status"] == 1) { ?>
              <span class="badge bg-success">Active</span>
            <?php } else { ?>
              <span class="badge bg-danger">Blocked</span>
            <?php } ?>
          </td>

          <td>
            <?php
              if ($u["role"] == "teacher" || $u["role"] == "counsellor") {
                echo ($u["is_approved"] == 1) ? "<span class='badge bg-success'>Approved</span>"
                                             : "<span class='badge bg-warning text-dark'>Pending</span>";
              } else {
                echo "<span class='text-muted'>Not required</span>";
              }
            ?>
          </td>

          <td class="d-flex gap-2">
            <!-- Approve only teacher/counsellor -->
            <?php if (($u["role"] == "teacher" || $u["role"] == "counsellor") && $u["is_approved"] == 0) { ?>
              <a class="btn btn-sm btn-success" href="approve_user.php?id=<?php echo $u["id"]; ?>"
                 onclick="return confirm('Approve this user?');">Approve</a>
            <?php } ?>

            <!-- Toggle status -->
            <a class="btn btn-sm btn-primary" href="toggle_status.php?id=<?php echo $u["id"]; ?>"
               onclick="return confirm('Change status (active/blocked)?');">Toggle Status</a>
          </td>
        </tr>
      <?php } ?>
    </tbody>
  </table>

</div>
</body>
</html>