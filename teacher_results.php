<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/db.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "teacher") {
    header("Location: login.php");
    exit();
}

$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $student_name = trim($_POST["student_name"]);
    $subject = trim($_POST["subject"]);
    $marks = trim($_POST["marks"]);
    $grade = trim($_POST["grade"]);
    $term = trim($_POST["term"]);

    $stmt = $conn->prepare("INSERT INTO results (student_name, subject, marks, grade, term) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiss", $student_name, $subject, $marks, $grade, $term);

    if ($stmt->execute()) {
        $msg = "Result added successfully.";
    } else {
        $msg = "Failed to add result. Check your database fields.";
    }
}

$result = $conn->query("SELECT * FROM results ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Results</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">

<style>
body{
  background: linear-gradient(135deg,#dff6ff,#f9e7ff,#fff4d6,#e9ffd9);
  min-height:100vh;
  font-family:'Segoe UI',sans-serif;
  color:#1f2937;
}

.page-wrap{
  max-width:1100px;
  margin:auto;
  padding:30px 15px;
}

.top-bar{
  background: linear-gradient(90deg,#2563eb,#7c3aed,#ec4899);
  color:white;
  border-radius:24px;
  padding:22px 28px;
  box-shadow:0 15px 35px rgba(0,0,0,0.12);
  margin-bottom:25px;
}

.top-bar h2{
  margin:0;
  font-weight:800;
}

.top-bar p{
  margin:6px 0 0;
  color:rgba(255,255,255,0.88);
}

.back-btn{
  border-radius:14px;
  font-weight:700;
  padding:10px 18px;
}

.cardx{
  border:none;
  border-radius:24px;
  background:rgba(255,255,255,0.92);
  box-shadow:0 15px 35px rgba(0,0,0,0.10);
  overflow:hidden;
}

.card-head{
  padding:18px 24px;
  color:white;
  font-weight:800;
  font-size:1.2rem;
}

.card-head.results-form{
  background:linear-gradient(90deg,#06b6d4,#3b82f6);
}

.card-head.results-list{
  background:linear-gradient(90deg,#8b5cf6,#ec4899);
}

.card-body-custom{
  padding:24px;
}

.form-label{
  font-weight:700;
  color:#334155;
}

.form-control, .form-select{
  border-radius:14px;
  padding:12px 14px;
  border:1px solid #dbe2ea;
  box-shadow:none !important;
}

.form-control:focus, .form-select:focus{
  border-color:#7c3aed;
  box-shadow:0 0 0 0.2rem rgba(124,58,237,0.15) !important;
}

.soft-btn{
  border:none;
  border-radius:14px;
  font-weight:800;
  padding:12px 20px;
}

.btn-save{
  background:linear-gradient(90deg,#16a34a,#22c55e);
  color:white;
}

.btn-save:hover{
  background:linear-gradient(90deg,#15803d,#16a34a);
  color:white;
}

.result-card{
  border:none;
  border-radius:20px;
  padding:20px;
  height:100%;
  color:#1f2937;
  box-shadow:0 10px 24px rgba(0,0,0,0.08);
  transition:0.3s ease;
}

.result-card:hover{
  transform:translateY(-6px);
}

.result-blue{ background:linear-gradient(135deg,#eff6ff,#dbeafe); }
.result-pink{ background:linear-gradient(135deg,#fdf2f8,#fce7f3); }
.result-green{ background:linear-gradient(135deg,#ecfdf5,#d1fae5); }
.result-yellow{ background:linear-gradient(135deg,#fffbeb,#fef3c7); }
.result-purple{ background:linear-gradient(135deg,#f5f3ff,#ede9fe); }
.result-orange{ background:linear-gradient(135deg,#fff7ed,#fed7aa); }

.result-title{
  font-size:1.1rem;
  font-weight:800;
  margin-bottom:10px;
}

.result-meta{
  font-size:0.95rem;
  margin-bottom:6px;
}

.badge-grade{
  display:inline-block;
  padding:8px 14px;
  border-radius:999px;
  font-weight:800;
  font-size:0.9rem;
  margin-top:10px;
}

.grade-a{ background:#dcfce7; color:#166534; }
.grade-b{ background:#dbeafe; color:#1d4ed8; }
.grade-c{ background:#fef3c7; color:#92400e; }
.grade-d{ background:#fee2e2; color:#b91c1c; }
.grade-default{ background:#e5e7eb; color:#374151; }

.empty-box{
  text-align:center;
  padding:30px;
  background:#f8fafc;
  border-radius:18px;
  color:#64748b;
}

.alert{
  border-radius:14px;
  font-weight:600;
}

@media (max-width:768px){
  .top-bar{
    text-align:center;
  }
}
</style>
</head>
<body>

<div class="page-wrap">

  <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
    <a href="teacher_dashboard.php" class="btn btn-dark back-btn">
      <i class="bi bi-arrow-left"></i> Back to Dashboard
    </a>
  </div>

  <div class="top-bar">
    <h2><i class="bi bi-bar-chart-fill me-2"></i>Manage Student Results</h2>
    <p>Add, organize, and view student academic performance in a colorful dashboard.</p>
  </div>

  <?php if($msg): ?>
    <div class="alert alert-info"><?= htmlspecialchars($msg) ?></div>
  <?php endif; ?>

  <div class="cardx mb-4">
    <div class="card-head results-form">
      <i class="bi bi-plus-circle-fill me-2"></i>Add New Result
    </div>

    <div class="card-body-custom">
      <form method="post">
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Student Name</label>
            <input type="text" name="student_name" class="form-control" required>
          </div>

          <div class="col-md-6 mb-3">
            <label class="form-label">Subject</label>
            <input type="text" name="subject" class="form-control" required>
          </div>

          <div class="col-md-4 mb-3">
            <label class="form-label">Marks</label>
            <input type="number" name="marks" class="form-control" required>
          </div>

          <div class="col-md-4 mb-3">
            <label class="form-label">Grade</label>
            <select name="grade" class="form-select" required>
              <option value="">Select Grade</option>
              <option value="A">A</option>
              <option value="B">B</option>
              <option value="C">C</option>
              <option value="D">D</option>
              <option value="F">F</option>
            </select>
          </div>

          <div class="col-md-4 mb-3">
            <label class="form-label">Term</label>
            <input type="text" name="term" class="form-control" placeholder="Term 1 / Term 2 / Final" required>
          </div>
        </div>

        <button type="submit" class="btn soft-btn btn-save">
          <i class="bi bi-check-circle-fill me-2"></i>Save Result
        </button>
      </form>
    </div>
  </div>

  <div class="cardx">
    <div class="card-head results-list">
      <i class="bi bi-collection-fill me-2"></i>All Results
    </div>

    <div class="card-body-custom">
      <div class="row g-4">

        <?php
        $colors = ["result-blue","result-pink","result-green","result-yellow","result-purple","result-orange"];
        $i = 0;
        ?>

        <?php if ($result && $result->num_rows > 0): ?>
          <?php while($row = $result->fetch_assoc()): ?>
            <?php
              $cardColor = $colors[$i % count($colors)];
              $i++;

              $gradeClass = "grade-default";
              if ($row["grade"] === "A") $gradeClass = "grade-a";
              elseif ($row["grade"] === "B") $gradeClass = "grade-b";
              elseif ($row["grade"] === "C") $gradeClass = "grade-c";
              elseif ($row["grade"] === "D" || $row["grade"] === "F") $gradeClass = "grade-d";
            ?>

            <div class="col-md-6 col-lg-4">
              <div class="result-card <?= $cardColor ?>">
                <div class="result-title">
                  <i class="bi bi-person-circle me-2"></i>
                  <?= htmlspecialchars($row["student_name"]) ?>
                </div>

                <div class="result-meta">
                  <strong>Subject:</strong> <?= htmlspecialchars($row["subject"]) ?>
                </div>

                <div class="result-meta">
                  <strong>Marks:</strong> <?= htmlspecialchars($row["marks"]) ?>
                </div>

                <div class="result-meta">
                  <strong>Term:</strong> <?= htmlspecialchars($row["term"]) ?>
                </div>

                <span class="badge-grade <?= $gradeClass ?>">
                  Grade <?= htmlspecialchars($row["grade"]) ?>
                </span>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <div class="col-12">
            <div class="empty-box">
              <i class="bi bi-inboxes-fill fs-1 d-block mb-2"></i>
              No results found yet.
            </div>
          </div>
        <?php endif; ?>

      </div>
    </div>
  </div>

</div>

</body>
</html>