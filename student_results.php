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
$student_name = $_SESSION["name"] ?? "Student";

// 1. Generate clean Sri Lankan secondary school options (Grade 6 to 13)
$available_grades = [];
for ($i = 6; $i <= 13; $i++) {
    $available_grades[] = "Grade " . $i;
}

// 2. Fetch options directly from database to cleanly map dynamic primary keys
$examsQuery = $conn->query("SELECT id, exam_name FROM exams ORDER BY id ASC");
$exam_options_map = [];
while($ex = $examsQuery->fetch_assoc()) {
    $eName = $ex['exam_name'];
    if (stripos($eName, 'Term 1') !== false || stripos($eName, 'Term 2') !== false || stripos($eName, 'Mid') !== false) {
        $exam_options_map['Mid Exam'] = $ex['id'];
    } elseif (stripos($eName, 'Final') !== false || stripos($eName, 'Term 3') !== false) {
        $exam_options_map['Final Exam'] = $ex['id'];
    }
}

// Determine selected filters from GET parameters (Defaulting to Grade 6 and Mid Exam map)
$selected_grade = isset($_GET['class_grade']) ? trim($_GET['class_grade']) : "Grade 6";
$selected_exam_label = isset($_GET['exam_name']) ? trim($_GET['exam_name']) : "Mid Exam";

// Grab the accurate numeric primary key ID mapped to your selected label text
$selected_exam_id = isset($exam_options_map[$selected_exam_label]) ? (int)$exam_options_map[$selected_exam_label] : 0;

// 3. Fetch targeted results based on the true relational numeric ID match
$subjects = [];
$termNameDisplay = $selected_exam_label;
$studentSection = "";

if ($selected_grade !== '' && $selected_exam_id > 0) {
    $stmt = $conn->prepare("
      SELECT r.subject, r.marks, r.grade, r.section, e.exam_name
      FROM results r
      INNER JOIN exams e ON r.exam_id = e.id
      WHERE r.student_id = ? AND r.class_grade = ? AND r.exam_id = ?
      ORDER BY r.id ASC
    ");
    $stmt->bind_param("isi", $student_id, $selected_grade, $selected_exam_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while($row = $result->fetch_assoc()) {
        // Deduplication fix remains active to keep one card per subject
        $subjects[$row['subject']] = $row; 
        $studentSection = $row['section'];
    }
}

// GPA & UI Helper Functions
function getGradePoints($grade) {
    if ($grade == 'A') return 4.0;
    if ($grade == 'B') return 3.0;
    if ($grade == 'C') return 2.0;
    if ($grade == 'S') return 1.0;
    return 0.0;
}

function getPerformanceBand($gpa) {
    if ($gpa >= 3.7) return ['name' => 'Distinction Merit 🌟', 'class' => 'bg-success text-white', 'msg' => 'Outstanding academic achievement! Keep thriving.'];
    if ($gpa >= 3.0) return ['name' => 'Highly Satisfactory 👍', 'class' => 'bg-info text-dark', 'msg' => 'Great job! You are doing well.'];
    if ($gpa >= 2.0) return ['name' => 'Satisfactory Pass ✔️', 'class' => 'bg-warning text-dark', 'msg' => 'Good effort! Stay focused and keep improving.'];
    return ['name' => 'Academic Support Staged 🩺', 'class' => 'bg-danger text-white', 'msg' => 'Reach out to your teachers or the wellness counselor for guidance.'];
}

function gradeColor($grade){
    $g = strtoupper($grade);
    if ($g == "A") return "bg-success";
    if ($g == "B") return "bg-primary";
    if ($g == "C") return "bg-warning text-dark";
    if ($g == "S") return "bg-secondary text-white";
    return "bg-danger";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>My Exam Results - GPA System</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
  <style>
    body { background: linear-gradient(135deg, #e0f7ff, #fff0f7, #f3ffe3); min-height: 100vh; font-family: 'Segoe UI', sans-serif; }
    .hero { background: linear-gradient(90deg, #6f42c1, #512da8, #311b92); color: white; border-radius: 22px; padding: 20px; box-shadow: 0 12px 30px rgba(111,66,193,0.2); }
    .cardx { border: 0; border-radius: 20px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(8px); box-shadow: 0 10px 25px rgba(0,0,0,0.06); border: 1px solid rgba(255,255,255,0.6); }
    .soft-btn { border-radius: 12px; font-weight: 700; }
    .marks-circle { width: 65px; height: 65px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 20px; color: white; background: linear-gradient(135deg, #74c0fc, #4dabf7); box-shadow: 0 6px 18px rgba(0,0,0,0.15); }
    .term-title { font-weight: 800; color: #311b92; border-left: 6px solid #6f42c1; padding-left: 12px; }
    .summary-card { border-radius: 20px; background: linear-gradient(135deg, #f8f9fa, #e9ecef); padding: 20px; box-shadow: 0 8px 20px rgba(0,0,0,0.05); border: 1px solid #dee2e6; }
    
    /* Elements strictly visible only inside web layout */
    .print-table-container { display: none; }

    /* =========================================================================
       ADVANCED PRINT & PDF LAYOUT RULES
       ========================================================================= */
    @media print {
      /* REMOVE AUTOMATIC LOCALHOST URL HEADERS AND FOOTERS */
      @page {
        margin: 0 !important; /* Force margin to 0 to block browser headers/footers */
      }
      
      body {
        background: none !important;
        background-color: #ffffff !important;
        color: #000000 !important;
        font-family: 'Times New Roman', Times, serif !important;
        padding: 15mm !important; /* Add spacing back safely inside the page */
      }
      
      /* Hide web interactive view elements */
      .btn, .top-bar, #filterForm, .cardx, .row.g-3.mb-4, .term-title, .summary-card {
        display: none !important;
      }
      .container {
        max-width: 100% !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
      }
      /* Clean institutional monochrome text layout header */
      .hero {
        background: none !important;
        border: 2px solid #000000 !important;
        color: #000000 !important;
        box-shadow: none !important;
        border-radius: 0 !important;
        padding: 15px !important;
        text-align: center !important;
        margin-bottom: 20px !important;
      }
      .hero h3 { font-size: 26px !important; font-weight: bold !important; color: #000000 !important; }
      .hero h3 i { display: none; }
      
      /* Display print exclusive transcript section elements */
      .print-header-meta {
        display: block !important;
        margin-bottom: 25px !important;
        font-size: 16px !important;
      }
      .print-table-container {
        display: block !important;
        width: 100% !important;
      }
      .print-table {
        width: 100% !important;
        border-collapse: collapse !important;
        margin-bottom: 30px !important;
      }
      .print-table th, .print-table td {
        border: 1px solid #000000 !important;
        padding: 10px !important;
        text-align: left !important;
        font-size: 15px !important;
      }
      .print-table th {
        background-color: #f2f2f2 !important;
        font-weight: bold !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
      }
      .print-summary-box {
        border: 2px dashed #000000 !important;
        padding: 15px !important;
        margin-top: 20px !important;
        font-size: 16px !important;
      }
    }
    .print-header-meta { display: none; }
  </style>
</head>
<body>

<div class="container py-4" style="max-width: 900px;">
  
  <!-- INTERACTIVE ACTION BAR BUTTONS -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <a href="student_dashboard.php" class="btn btn-outline-dark soft-btn"><i class="bi bi-arrow-left"></i> Back</a>
    <?php if(!empty($subjects)): ?>
        <button onclick="window.print();" class="btn btn-primary soft-btn"><i class="bi bi-printer-fill"></i> Print Report Sheet</button>
    <?php endif; ?>
  </div>

  <div class="hero mb-4">
    <h3 class="fw-bold mb-0">Official Academic Transcript</h3>
  </div>

  <!-- PRINT EXCLUSIVE SYSTEM METADATA HEADER -->
  <div class="print-header-meta card p-3 border-dark rounded-0 bg-transparent mb-4">
      <div class="row">
          <div class="col-6 mb-2"><strong>Student Name:</strong> <?= htmlspecialchars($student_name) ?></div>
          <div class="col-6 text-end mb-2"><strong>Date Compiled:</strong> <?= date('Y-m-d') ?></div>
          <div class="col-6"><strong>Assessment Term:</strong> <?= htmlspecialchars($termNameDisplay) ?></div>
          <div class="col-6 text-end"><strong>Grade Cohort:</strong> <?= htmlspecialchars($selected_grade) ?> <?php if(!empty($studentSection)): ?>(Section: <?= htmlspecialchars($studentSection) ?>)<?php endif; ?></div>
      </div>
  </div>

  <!-- SELECTION DROP-DOWN FILTERS -->
  <div class="cardx p-4 mb-4">
      <form method="GET" action="" id="filterForm" class="row align-items-end g-3">
          
          <div class="col-md-5">
              <label class="small fw-bold text-muted d-block mb-1"><i class="bi bi-mortarboard-fill"></i> Select Class Grade:</label>
              <select name="class_grade" class="form-select fw-semibold text-primary" style="border-radius: 10px;" onchange="document.getElementById('filterForm').submit();">
                  <?php foreach ($available_grades as $gradeOption): ?>
                      <option value="<?= htmlspecialchars($gradeOption) ?>" <?= $selected_grade === $gradeOption ? 'selected' : '' ?>>
                          <?= htmlspecialchars($gradeOption) ?>
                      </option>
                  <?php endforeach; ?>
              </select>
          </div>

          <div class="col-md-5">
              <label class="small fw-bold text-muted d-block mb-1"><i class="bi bi-calendar3"></i> Select Assessment Term:</label>
              <select name="exam_name" class="form-select fw-semibold text-primary" style="border-radius: 10px;" onchange="document.getElementById('filterForm').submit();">
                  <?php foreach ($exam_options_map as $displayLabel => $mappedId): ?>
                      <option value="<?= htmlspecialchars($displayLabel) ?>" <?= $selected_exam_label === $displayLabel ? 'selected' : '' ?>>
                          <?= htmlspecialchars($displayLabel) ?>
                      </option>
                  <?php endforeach; ?>
              </select>
          </div>

          <div class="col-md-2 d-none d-md-block">
              <button type="submit" class="btn btn-dark w-100 soft-btn">View</button>
          </div>
      </form>
  </div>

  <?php if(empty($subjects)): ?>
      <div class="alert alert-info cardx border-0 text-center py-5">
        <i class="bi bi-inbox fs-1 text-secondary mb-3 d-block"></i>
        <h5 class="fw-bold mb-0">No records found for this choice.</h5>
        <p class="text-muted small mt-2">The selected marks have not been processed by the faculty yet.</p>
      </div>
  <?php else: ?>

      <h4 class="term-title mb-3 mt-2">
          <?= htmlspecialchars($termNameDisplay) ?> - <?= htmlspecialchars($selected_grade) ?> 
          <?php if(!empty($studentSection)): ?>
            <span class="badge bg-secondary ms-2 opacity-75">Section: <?= htmlspecialchars($studentSection) ?></span>
          <?php endif; ?>
      </h4>

      <!-- WEB VIEW LAYER: SUBJECT CARD DATA MATRIX -->
      <div class="row g-3 mb-4">
          <?php 
          $termPointsTotal = 0;
          $termSubjectCount = 0;
          
          foreach($subjects as $subjectName => $row): 
              $termPointsTotal += getGradePoints($row["grade"]);
              $termSubjectCount++;
          ?>
            <div class="col-md-6">
              <div class="cardx p-4 d-flex justify-content-between align-items-center">
                <div>
                  <h5 class="fw-bold mb-2 text-dark"><?= htmlspecialchars($subjectName) ?></h5>
                  <span class="badge <?= gradeColor($row["grade"]) ?> px-3 py-2 rounded-pill shadow-sm">
                    Grade: <?= htmlspecialchars($row["grade"]) ?>
                  </span>
                </div>
                <div class="marks-circle">
                  <?= (int)$row["marks"] ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
      </div>

      <!-- PRINT VIEW LAYER: CLEAN TRANSCRIPT DATA TABLE (VISIBLE ONLY IN PDF/PRINT) -->
      <div class="print-table-container">
          <table class="print-table">
              <thead>
                  <tr>
                      <th>Subject Module</th>
                      <th>Marks Obtained (0-100)</th>
                      <th>Assigned Grade</th>
                  </tr>
              </thead>
              <tbody>
                  <?php foreach($subjects as $subjectName => $row): ?>
                      <tr>
                          <td><strong><?= htmlspecialchars($subjectName) ?></strong></td>
                          <td><?= (int)$row["marks"] ?></td>
                          <td><strong><?= htmlspecialchars($row["grade"]) ?></strong></td>
                      </tr>
                  <?php endforeach; ?>
              </tbody>
          </table>
      </div>

      <!-- CUMULATIVE TRANSCRIPT METRIC DATA BOX -->
      <?php 
          $termGPA = ($termSubjectCount > 0) ? round($termPointsTotal / $termSubjectCount, 2) : 0;
          $performance = getPerformanceBand($termGPA);
      ?>
      
      <!-- Web View Summary Card -->
      <div class="summary-card">
        <div class="row align-items-center text-center text-md-start">
          <div class="col-md-4 text-center border-end mb-3 mb-md-0">
            <div class="text-muted fw-bold small text-uppercase mb-1">Cumulative GPA</div>
            <div style="font-size: 2.5rem; font-weight: 900; color: #512da8;"><?= number_format($termGPA, 2) ?></div>
          </div>
          <div class="col-md-8 ps-md-4">
            <div class="text-muted fw-bold small text-uppercase mb-2">Academic Performance Classification</div>
            <span class="badge <?= $performance['class'] ?> px-3 py-2 fs-6 rounded-pill mb-2 shadow-sm">
                <?= $performance['name'] ?>
            </span>
            <p class="text-muted small fw-semibold mb-0 mt-1">
                <i class="bi bi-info-circle-fill"></i> <?= $performance['msg'] ?>
            </p>
          </div>
        </div>
      </div>

      <!-- Print View Summary Box -->
      <div class="print-summary-box">
          <div class="row">
              <div class="col-6"><strong>Cumulative GPA:</strong> <?= number_format($termGPA, 2) ?></div>
              <div class="col-6 text-end"><strong>Performance Band:</strong> <?= strip_tags($performance['name']) ?></div>
              <div class="col-12 mt-2 small text-muted"><em>Result compilation authorized under national grading parameters.</em></div>
          </div>
      </div>

  <?php endif; ?>
</div>

</body>
</html>