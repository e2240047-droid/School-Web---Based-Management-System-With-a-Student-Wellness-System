<?php
// include required files (login check + database connection)
require_once "auth.php";
require_once "db.php";

// make sure only students can access this page
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "student") {
    header("Location: login.php");
    exit();
}

// get current user's id from session
$user_id = $_SESSION["user_id"] ?? 0;

// prepare SQL to get user details safely
$stmt = $conn->prepare("SELECT name, email, role, status FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();

// fetch result
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// if no user found, stop execution
if (!$user) {
    die("User not found.");
}

// assign values to variables (easy to use in HTML)
$name   = $user["name"];
$email  = $user["email"];
$role   = $user["role"];
$status = $user["status"] ?? "Active";

// get first letter of name for profile icon
$first_letter = strtoupper(substr($name, 0, 1));
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Profile</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap for quick styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* simple page background */
        body {
            background: #f4f7fb;
            font-family: Arial, sans-serif;
        }

        /* center container */
        .page-box {
            max-width: 950px;
            margin: 40px auto;
        }

        /* top buttons area */
        .top-bar {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        /* main card */
        .profile-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        /* blue header section */
        .profile-header {
            background: #0d6efd;
            color: white;
            padding: 30px;
            display: flex;
            align-items: center;
            gap: 20px;
        }

        /* circle icon with first letter */
        .profile-icon {
            width: 75px;
            height: 75px;
            border-radius: 50%;
            background: white;
            color: #0d6efd;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 34px;
            font-weight: bold;
        }

        /* body section */
        .profile-body {
            padding: 30px;
        }

        /* each info block */
        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #0d6efd;
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 15px;
        }

        /* label text */
        .info-label {
            font-size: 13px;
            color: #6c757d;
        }

        /* value text */
        .info-value {
            font-size: 16px;
            font-weight: 600;
            color: #212529;
        }

        /* section title */
        .action-title {
            margin-top: 25px;
            margin-bottom: 15px;
            font-weight: bold;
        }

        /* buttons styling */
        .action-btn {
            padding: 10px 18px;
            border-radius: 10px;
        }

        /* footer text */
        .footer-text {
            text-align: center;
            color: #6c757d;
            margin-top: 18px;
            font-size: 14px;
        }
    </style>
</head>

<body>

<div class="container page-box">

    <!-- top navigation buttons -->
    <div class="top-bar">
        <a href="student_dashboard.php" class="btn btn-outline-secondary">Back</a>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>

    <!-- main profile card -->
    <div class="profile-card">

        <!-- header section -->
        <div class="profile-header">

            <!-- profile icon -->
            <div class="profile-icon">
                <?= htmlspecialchars($first_letter) ?>
            </div>

            <!-- heading text -->
            <div>
                <h2 class="mb-1">My Profile</h2>
                <p class="mb-0">View your account details and wellness options</p>
            </div>
        </div>

        <!-- profile information -->
        <div class="profile-body">

            <div class="row">

                <!-- name -->
                <div class="col-md-6">
                    <div class="info-box">
                        <div class="info-label">Full Name</div>
                        <div class="info-value"><?= htmlspecialchars($name) ?></div>
                    </div>
                </div>

                <!-- email -->
                <div class="col-md-6">
                    <div class="info-box">
                        <div class="info-label">Email Address</div>
                        <div class="info-value"><?= htmlspecialchars($email) ?></div>
                    </div>
                </div>

                <!-- role -->
                <div class="col-md-6">
                    <div class="info-box">
                        <div class="info-label">Role</div>
                        <div class="info-value"><?= htmlspecialchars($role) ?></div>
                    </div>
                </div>

                <!-- status -->
                <div class="col-md-6">
                    <div class="info-box">
                        <div class="info-label">Account Status</div>
                        <div class="info-value text-success">
                            <?= htmlspecialchars($status) ?>
                        </div>
                    </div>
                </div>

            </div>

            <!-- quick action buttons -->
            <h5 class="action-title">Quick Actions</h5>

            <div class="d-flex flex-wrap gap-2">
                <a href="student_resources.php" class="btn btn-warning action-btn">Resources</a>
                <a href="student_chat.php" class="btn btn-primary action-btn">Anonymous Chat</a>
                <a href="student_alert.php" class="btn btn-danger action-btn">Silent Alert</a>
                <a href="change_password.php" class="btn btn-dark action-btn">Change Password</a>
            </div>

        </div>
    </div>

    <!-- footer -->
    <div class="footer-text">
        Student Wellness Management System
    </div>

</div>

</body>
</html>