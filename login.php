<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/db.php";

$error = "";

// Capture system kick flash parameters from auth.php redirects
if (isset($_GET['auth_error'])) {
    $error = "🔒 " . $_GET['auth_error'];
} elseif (isset($_GET['error'])) {
    $error = "🔒 " . $_GET['error'];
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    $stmt = $conn->prepare("SELECT id, name, password, role, status FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Verify the BCRYPT password string
        if (password_verify($password, $row["password"])) {
            
            // 🚫 CRITICAL ENTRY LOCKDOWN CHECK
            if ($row["status"] === "Blocked" || $row["status"] === "Inactive") {
                $error = "🔒 Access Denied: Your account has been suspended by the Administrator. Please contact the Faculty IT Department.";
            } else {
                // Account is healthy, establish session parameters
                $_SESSION["user_id"] = $row["id"];
                $_SESSION["name"] = $row["name"];
                $_SESSION["role"] = $row["role"];
                
                // Redirect based on role matrix
                if ($row["role"] === "admin") header("Location: admin_dashboard.php");
                elseif ($row["role"] === "teacher") header("Location: teacher_dashboard.php");
                elseif ($row["role"] === "counsellor") header("Location: counsellor_dashboard.php");
                else header("Location: student_dashboard.php");
                exit();
            }
        } else {
            $error = "❌ Invalid email or password confirmation combination.";
        }
    } else {
        $error = "❌ Account identifier code not found in our database system registries.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login - School Wellness System</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #e0f7ff, #fff0f7, #f3ffe3);
            min-height: 100vh;
        }
        .cardx {
            border: 0;
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.92);
            box-shadow: 0 12px 30px rgba(0,0,0,0.10);
        }
        .soft-btn {
            border-radius: 12px;
            font-weight: bold;
        }
    </style>
</head>
<body class="d-flex align-items-center">

<div class="container">
    <div class="row justify-content-center w-100 m-0">
        <div class="col-md-5">

            <!-- Login Card -->
            <div class="cardx shadow p-4 mx-auto" style="max-width: 450px;">

                <!-- Back Button -->
                <div class="mb-2">
                    <a href="index.php" class="btn btn-outline-secondary btn-sm soft-btn">
                        ← Back to Home
                    </a>
                </div>

                <!-- Title Section -->
                <div class="text-center mb-3">
                    <img src="logo.png" width="70" alt="Logo">
                    <h4 class="mt-2 fw-bold">Login</h4>
                    <p class="text-muted small">School Web-Based Management System</p>
                </div>

                <!-- DYNAMIC ERROR MESSAGES & LOCKDOWN NOTIFICATIONS -->
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger border-0 shadow-sm p-3 mb-3 d-flex align-items-center gap-2" style="border-radius: 12px; background-color: #fff5f5; color: #dc3545; font-size: 14px; font-weight: 600;">
                        <div><?= htmlspecialchars($error) ?></div>
                    </div>
                <?php endif; ?>

                <!-- Login Form (action changed to submit to itself) -->
                <form action="" method="POST">

                    <!-- Email -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="name@example.com" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
                    </div>

                    <!-- Password -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>

                    <!-- Login Button -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary soft-btn py-2">
                            Login
                        </button>
                    </div>

                </form>

                <!-- Links -->
                <div class="text-center mt-3">
                    <a href="forgot_password.php" class="text-decoration-none small fw-bold">Forgot Password?</a>
                </div>

                <div class="text-center mt-2">
                    <small class="text-muted">
                        Don't have an account? 
                        <a href="register.php" class="text-decoration-none fw-bold">Register</a>
                    </small>
                </div>

            </div>

        </div>
    </div>
</div>

</body>
</html>