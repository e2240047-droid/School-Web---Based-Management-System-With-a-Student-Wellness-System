<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Suppress starting session if already active globally
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/db.php";

$message = "";
$error = "";
$email = isset($_GET['email']) ? trim($_GET['email']) : "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $new_password = $_POST["password"] ?? "";
    $form_email = $_POST["email"] ?? "";

    // CORRECTED: Infrastructure Password Matrix Validator (8+ Characters, Lower, Upper, Digit, Special)
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $new_password)) {
        $error = "❌ Password does not meet secure framework policy requirements.";
    } else {
        // Securely hash the validated password string
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashed_password, $form_email);
        
        if ($stmt->execute()) {
            $message = "✅ Password has been successfully updated. Redirecting...";
            header("refresh:2;url=login.php");
        } else {
            $error = "❌ Failed to reset password. Please try again later.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Reset Password</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
  <style>
    body {
      background: linear-gradient(135deg, #e0f7ff, #fff0f7, #f3ffe3);
      min-height: 100vh;
      font-family: 'Segoe UI', sans-serif;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .cardx {
      border: 0;
      border-radius: 22px;
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      box-shadow: 0 12px 35px rgba(0,0,0,0.08);
      max-width: 450px;
      width: 100%;
      padding: 35px;
      border: 1px solid rgba(255,255,255,0.7);
    }
    .soft-btn {
      border-radius: 12px;
      font-weight: 700;
      padding: 12px;
      background: #0ea5e9;
      border: none;
      color: white;
      transition: all 0.2s ease;
    }
    .soft-btn:hover {
      background: #0284c7;
      transform: translateY(-1px);
    }
    .form-control {
      border-radius: 10px;
      padding: 12px;
    }
    /* Real-time Validator Checklist Styles */
    .rule-item {
      font-size: 13px;
      color: #64748b;
      font-weight: 600;
      transition: color 0.2s ease;
    }
    .rule-item i {
      margin-right: 6px;
    }
    .rule-item.valid {
      color: #10b981 !important;
    }
  </style>
</head>
<body>

<div class="cardx">
  <h4 class="fw-bold text-center text-dark mb-4">Reset Password</h4>

  <?php if ($message): ?>
    <div class="alert alert-success border-0 small fw-bold mb-3"><?= $message ?></div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="alert alert-danger border-0 small fw-bold mb-3"><?= $error ?></div>
  <?php endif; ?>

  <form method="POST" action="" id="resetForm">
    <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">

    <div class="mb-3">
      <label class="form-label small fw-bold text-muted">New Password</label>
      <div class="position-relative">
        <input type="password" name="password" id="passwordInput" class="form-control" placeholder="Enter secure password" required>
      </div>
    </div>

    <!-- ADVANCED USER EXPERIENCE: Real-time validation checklist tracking system -->
    <div class="p-3 bg-light rounded-3 mb-4 border">
      <div class="fw-bold text-dark small mb-2">Password Requirements:</div>
      <div class="rule-item mb-1" id="rule-length"><i class="bi bi-circle"></i> At least 8 characters</div>
      <div class="rule-item mb-1" id="rule-upper"><i class="bi bi-circle"></i> Upper & lowercase letters (A-Z, a-z)</div>
      <div class="rule-item mb-1" id="rule-number"><i class="bi bi-circle"></i> At least one number (0-9)</div>
      <div class="rule-item" id="rule-special"><i class="bi bi-circle"></i> Special character (@, $, !, %, etc.)</div>
    </div>

    <button type="submit" class="btn soft-btn w-100 mb-3"><i class="bi bi-shield-lock-fill me-2"></i> Reset Password</button>
    
    <div class="text-center">
      <a href="login.php" class="text-decoration-none text-secondary small fw-bold"><i class="bi bi-arrow-left"></i> Back to Login</a>
    </div>
  </form>
</div>

<script>
const passwordInput = document.getElementById('passwordInput');

// CORRECTED: Criteria rule components mapped to verification layers
const rules = {
    length: { regex: /.{8,}/, element: document.getElementById('rule-length') },
    upper: { regex: /(?=.*[a-z])(?=.*[A-Z])/, element: document.getElementById('rule-upper') },
    number: { regex: /(?=.*\d)/, element: document.getElementById('rule-number') },
    special: { regex: /(?=.*[\W_])/, element: document.getElementById('rule-special') }
};

passwordInput.addEventListener('input', () => {
    const val = passwordInput.value;
    
    // Cycle checking loops updating badge matrix tokens natively
    for (const key in rules) {
        const rule = rules[key];
        if (rule.regex.test(val)) {
            rule.element.classList.add('valid');
            rule.element.querySelector('i').className = "bi bi-check-circle-fill text-success";
        } else {
            rule.element.classList.remove('valid');
            rule.element.querySelector('i').className = "bi bi-circle";
        }
    }
});
</script>

</body>
</html>