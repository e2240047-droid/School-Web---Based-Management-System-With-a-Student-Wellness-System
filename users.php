<?php
session_start();
require_once __DIR__ . "/db.php";

// allow only admin
if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    header("Location: login.php");
    exit();
}

$msg = "";

/* =========================
   UPDATE USER
========================= */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_user"])) {

    $id = (int)$_POST["user_id"];
    $role = $_POST["role"];
    $status = $_POST["status"];

    $stmt = $conn->prepare("UPDATE users SET role = ?, status = ? WHERE id = ?");
    $stmt->bind_param("ssi", $role, $status, $id);

    if ($stmt->execute()) {
        $msg = "User updated successfully.";
    } else {
        $msg = "Failed to update user.";
    }
}

/* =========================
   DELETE USER
========================= */
if (isset($_GET["delete"])) {

    $id = (int)$_GET["delete"];

    // prevent admin from deleting own account
    if ($id == $_SESSION["user_id"]) {
        $msg = "You cannot delete your own account.";
    } else {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            header("Location: admin_users.php");
            exit();
        } else {
            $msg = "Failed to delete user.";
        }
    }
}

// load all users
$result = $conn->query("SELECT id, name, email, role, status FROM users ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Manage Users</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    background: linear-gradient(135deg,#e0f7ff,#fff0f7,#f3ffe3);
    min-height: 100vh;
    font-family: 'Segoe UI', sans-serif;
}

.cardx {
    background: white;
    border-radius: 18px;
    box-shadow: 0 12px 25px rgba(0,0,0,0.1);
}

.soft-btn {
    border-radius: 12px;
    font-weight: 700;
}
</style>
</head>

<body>

<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="admin_dashboard.php" class="btn btn-dark soft-btn">← Back</a>
        <h3 class="mb-0">👥 Manage Users</h3>
        <a href="logout.php" class="btn btn-danger soft-btn">Logout</a>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-info"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <div class="cardx p-4">

        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th width="230">Action</th>
                    </tr>
                </thead>

                <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <form method="post">

                            <td><?= (int)$row["id"] ?></td>

                            <td><?= htmlspecialchars($row["name"]) ?></td>

                            <td><?= htmlspecialchars($row["email"]) ?></td>

                            <td>
                                <select name="role" class="form-select form-select-sm">
                                    <option value="student" <?= $row["role"] == "student" ? "selected" : "" ?>>Student</option>
                                    <option value="teacher" <?= $row["role"] == "teacher" ? "selected" : "" ?>>Teacher</option>
                                    <option value="counsellor" <?= $row["role"] == "counsellor" ? "selected" : "" ?>>Counsellor</option>
                                    <option value="admin" <?= $row["role"] == "admin" ? "selected" : "" ?>>Admin</option>
                                </select>
                            </td>

                            <td>
                                <select name="status" class="form-select form-select-sm">
                                    <option value="Active" <?= $row["status"] == "Active" ? "selected" : "" ?>>Active</option>
                                    <option value="Inactive" <?= $row["status"] == "Inactive" ? "selected" : "" ?>>Inactive</option>
                                    <option value="Blocked" <?= $row["status"] == "Blocked" ? "selected" : "" ?>>Blocked</option>
                                </select>
                            </td>

                            <td>
                                <input type="hidden" name="user_id" value="<?= (int)$row["id"] ?>">

                                <button type="submit" name="update_user" class="btn btn-success btn-sm">
                                    Update
                                </button>

                                <a href="admin_users.php?delete=<?= (int)$row["id"] ?>"
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Are you sure you want to delete this user?')">
                                   Delete
                                </a>
                            </td>

                        </form>
                    </tr>
                <?php endwhile; ?>
                </tbody>

            </table>
        </div>

    </div>

</div>

</body>
</html>