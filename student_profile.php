<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/db.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "student") {
    header("Location: login.php");
    exit();
}

$student_id = (int)($_SESSION["user_id"] ?? 0);
$message = "";
$error = "";

// Generate clean secondary school options (Grade 6 to 13)
$available_grades = [];
for ($i = 6; $i <= 13; $i++) {
    $available_grades[] = "Grade " . $i;
}

/* ----------------------------------
   Profile Details Update Controller
-----------------------------------*/
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_profile'])) {
    $upd_index = trim($_POST['index_number'] ?? '');
    $upd_grade = trim($_POST['current_grade'] ?? '');
    $upd_sec   = trim($_POST['current_section'] ?? '');

    $updStmt = $conn->prepare("UPDATE users SET index_number=?, current_grade=?, current_section=? WHERE id=?");
    $updStmt->bind_param("sssi", $upd_index, $upd_grade, $upd_sec, $student_id);
    
    if ($updStmt->execute()) {
        $message = "✅ Profile details updated successfully.";
    } else {
        $error = "❌ Failed to update profile information.";
    }
}

/* ----------------------------------
   Password Update Controller
-----------------------------------*/
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_password'])) {
    $current_pass = $_POST['current_password'] ?? '';
    $new_pass     = $_POST['new_password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';

    if (empty($current_pass) || empty($new_pass) || empty($confirm_pass)) {
        $error = "All password fields are required.";
    } elseif ($new_pass !== $confirm_pass) {
        $error = "The new passwords do not match.";
    } else {
        $pwdStmt = $conn->prepare("SELECT password FROM users WHERE id = ? LIMIT 1");
        $pwdStmt->bind_param("i", $student_id);
        $pwdStmt->execute();
        $pwdRes = $pwdStmt->get_result()->fetch_assoc();

        if ($pwdRes && password_verify($current_pass, $pwdRes['password'])) {
            $newHash = password_hash($new_pass, PASSWORD_BCRYPT);
            $upStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $upStmt->bind_param("si", $newHash, $student_id);
            if ($upStmt->execute()) {
                $message = "✅ Password changed successfully.";
            } else {
                $error = "❌ Failed to update password.";
            }
        } else {
            $error = "❌ Current password is incorrect.";
        }
    }
}

/* ----------------------------------
   Fetch Current Profile Data
-----------------------------------*/
$stmt = $conn->prepare("SELECT name, email, index_number, current_grade, current_section, role, status FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();

if (!$profile) {
    die("User profile not found.");
}

$status = $profile["status"] ?? "Active";
$first_letter = strtoupper(substr($profile['name'], 0, 1));

/* ----------------------------------
   ADVANCED UNIQUE FEATURE: Dynamic Academic Summary
-----------------------------------*/
$total_subjects = 0;
$cumulative_gpa = 0.00;

$summaryQuery = $conn->prepare("
    SELECT grade FROM results WHERE student_id = ?
");
$summaryQuery->bind_param("i", $student_id);
$summaryQuery->execute();
$summaryResult = $summaryQuery->get_result();

if ($summaryResult->num_rows > 0) {
    $total_points = 0;
    while ($row = $summaryResult->fetch_assoc()) {
        $g = strtoupper($row['grade']);
        $points = 0;
        if ($g == 'A') $points = 4;
        elseif ($g == 'B') $points = 3;
        elseif ($g == 'C') $points = 2;
        elseif ($g == 'S') $points = 1;
        
        $total_points += $points;
        $total_subjects++;
    }
    if ($total_subjects > 0) {
        $cumulative_gpa = round($total_points / $total_subjects, 2);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>My Profile</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
  <style>
    body { background: #f4f7fb; font-family: Arial, sans-serif; }
    .cardx { border: 0; border-radius: 16px; background: white; box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08); overflow: hidden; }
    .hero { background: #0d6efd; color: white; border-radius: 16px 16px 0 0; padding: 30px; }
    .profile-avatar { width: 75px; height: 75px; border-radius: 50%; background: white; color: #0d6efd; font-size: 34px; font-weight: bold; display: flex; align-items: center; justify-content: center; }
    .soft-btn { border-radius: 10px; padding: 10px 18px; }
    .data-box { background: #f8f9fa; border-left: 4px solid #0d6efd; border-radius: 10px; padding: 14px 18px; height: 100%; }
    .analytics-box { background: #fdf8ff; border-left: 4px solid #6f42c1; border-radius: 10px; padding: 14px 18px; height: 100%; }
    .section-title { margin-top: 25px; margin-bottom: 15px; font-weight: bold; color: #212529; }
  </style>
</head>
<body>

<div class="container py-4" style="max-width: 950px;">

  <!-- TOP NAVIGATION -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <a href="student_dashboard.php" class="btn btn-outline-secondary soft-btn">Back</a>
    <a href="logout.php" class="btn btn-danger soft-btn px-4">Logout</a>
  </div>

  <!-- MAIN PROFILE CONTAINER -->
  <div class="cardx mb-4">
      
      <!-- HERO SECTION -->
      <div class="hero d-flex align-items-center gap-4 flex-wrap flex-md-nowrap">
        <div class="profile-avatar">
            <?= htmlspecialchars($first_letter) ?>
        </div>
        <div>
            <h2 class="fw-bold mb-1">My Profile</h2>
            <p class="mb-0 opacity-75">View your account details and academic summaries</p>
        </div>
      </div>

      <!-- NOTIFICATIONS -->
      <div class="p-4 pb-0">
          <?php if ($message): ?><div class="alert alert-success border-0 py-3 mb-2 text-success fw-bold"><?= $message ?></div><?php endif; ?>
          <?php if ($error): ?><div class="alert alert-danger border-0 py-3 mb-2 text-danger fw-bold"><?= $error ?></div><?php endif; ?>
      </div>

      <div class="row p-4 g-4">
          
          <!-- LEFT COLUMN: ACCOUNT DATA -->
          <div class="col-lg-7">
              <form method="POST" action="">
                  <div class="row g-3">
                      
                      <!-- Read-Only Fields -->
                      <div class="col-md-6">
                          <div class="data-box">
                              <label class="small fw-bold text-muted mb-1">Full Name</label>
                              <span class="fw-bold text-dark fs-5 d-block"><?= htmlspecialchars($profile['name'] ?? 'N/A') ?></span>
                          </div>
                      </div>
                      <div class="col-md-6">
                          <div class="data-box">
                              <label class="small fw-bold text-muted mb-1">Email Address</label>
                              <span class="fw-semibold text-dark text-break d-block mt-1"><?= htmlspecialchars($profile['email'] ?? 'N/A') ?></span>
                          </div>
                      </div>
                      
                      <div class="col-md-6">
                          <div class="data-box">
                              <label class="small fw-bold text-muted mb-1">Role</label>
                              <span class="fw-bold text-dark d-block text-capitalize mt-1"><?= htmlspecialchars($profile['role'] ?? 'Student') ?></span>
                          </div>
                      </div>
                      <div class="col-md-6">
                          <div class="data-box">
                              <label class="small fw-bold text-muted mb-1">Account Status</label>
                              <span class="fw-bold text-success d-block mt-1"><?= htmlspecialchars($status) ?></span>
                          </div>
                      </div>

                      <!-- Editable Academic Information -->
                      <div class="col-md-12 mt-4">
                          <h6 class="fw-bold text-secondary border-bottom pb-2">Academic Information</h6>
                      </div>

                      <div class="col-md-4">
                          <label class="form-label small fw-bold text-secondary">Index Number</label>
                          <input type="text" name="index_number" class="form-control" style="border-radius: 10px;" value="<?= htmlspecialchars($profile['index_number'] ?? '') ?>" placeholder="e.g. 10245">
                      </div>

                      <div class="col-md-4">
                          <label class="form-label small fw-bold text-secondary">Grade</label>
                          <select name="current_grade" class="form-select" style="border-radius: 10px;">
                              <option value="">Select</option>
                              <?php foreach ($available_grades as $gradeOption): ?>
                                  <option value="<?= htmlspecialchars($gradeOption) ?>" <?= ($profile['current_grade'] === $gradeOption) ? 'selected' : '' ?>><?= htmlspecialchars($gradeOption) ?></option>
                              <?php endforeach; ?>
                          </select>
                      </div>

                      <div class="col-md-4">
                          <label class="form-label small fw-bold text-secondary">Section</label>
                          <select name="current_section" class="form-select" style="border-radius: 10px;">
                              <option value="">Select</option>
                              <option value="A" <?= ($profile['current_section'] === 'A') ? 'selected' : '' ?>>A</option>
                              <option value="B" <?= ($profile['current_section'] === 'B') ? 'selected' : '' ?>>B</option>
                              <option value="C" <?= ($profile['current_section'] === 'C') ? 'selected' : '' ?>>C</option>
                          </select>
                      </div>

                      <div class="col-md-12 mt-2 text-end">
                          <button type="submit" name="update_profile" class="btn btn-primary soft-btn px-4">
                              Save Profile Details
                          </button>
                      </div>
                  </div>
              </form>

              <!-- UNIQUE ADVANCED FEATURE: ACADEMIC LEGER SUMMARY -->
              <h5 class="section-title"><i class="bi bi-cpu-fill text-purple"></i> Academic Statistics & Authentication</h5>
              <div class="row g-2">
                  <div class="col-md-6">
                      <div class="analytics-box">
                          <label class="small fw-bold text-purple mb-1">Total Modules Tracked</label>
                          <span class="fw-bold text-dark fs-5 d-block"><?= $total_subjects ?> Modules</span>
                      </div>
                  </div>
                  <div class="col-md-6">
                      <div class="analytics-box">
                          <label class="small fw-bold text-purple mb-1">Overall Cumulative GPA</label>
                          <span class="fw-bold text-dark fs-5 d-block"><?= number_format($cumulative_gpa, 2) ?> / 4.00</span>
                      </div>
                  </div>
                  <div class="col-md-12">
                      <div class="analytics-box">
                          <label class="small fw-bold text-purple mb-1">Digital Transcript Security Token</label>
                          <span class="fw-mono text-muted small d-block text-break">
                              <i class="bi bi-shield-check text-success"></i> Verified ID: <?= md5($student_id . $profile['email']) ?>
                          </span>
                      </div>
                  </div>
              </div>
          </div>

          <!-- RIGHT COLUMN: CHANGE PASSWORD -->
          <div class="col-lg-5 border-start-md">
              <div class="p-2">
                  <h5 class="fw-bold mb-2 text-dark">Change Password</h5>
                  <p class="small text-muted mb-4">Keep your account secure by updating your password regularly.</p>
                  
                  <form method="POST" action="">
                      <div class="mb-3">
                          <label class="form-label small fw-bold text-secondary">Current Password</label>
                          <input type="password" name="current_password" class="form-control py-2" style="border-radius: 10px;" required>
                      </div>
                      <div class="mb-3">
                          <label class="form-label small fw-bold text-secondary">New Password</label>
                          <input type="password" name="new_password" class="form-control py-2" style="border-radius: 10px;" minlength="6" required>
                      </div>
                      <div class="mb-4">
                          <label class="form-label small fw-bold text-secondary">Confirm New Password</label>
                          <input type="password" name="confirm_password" class="form-control py-2" style="border-radius: 10px;" minlength="6" required>
                      </div>

                      <button type="submit" name="update_password" class="btn btn-dark soft-btn w-100 py-2.5">
                          Update Password
                      </button>
                  </form>
              </div>
          </div>

      </div>
  </div>

  <div class="footer-text">
      Student Wellness Management System
  </div>

</div>
</body>
</html>