<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Removed redundant session_start(); here as auth.php securely handles the session initialization.
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/db.php";

// Enforce teacher or admin role check
if (!isset($_SESSION["role"]) || ($_SESSION["role"] !== "teacher" && $_SESSION["role"] !== "admin")) {
    header("Location: login.php");
    exit();
}

$teacher_name = $_SESSION["name"] ?? "Instructor";
$message = "";
$error = "";

// Capture Filter States
$filter_exam_id     = isset($_GET['exam_id']) ? (int)$_GET['exam_id'] : 0;
$filter_class_grade = isset($_GET['class_grade']) ? trim($_GET['class_grade']) : "";
$filter_section     = isset($_GET['section']) ? trim($_GET['section']) : "";
$filter_subject     = isset($_GET['subject']) ? trim($_GET['subject']) : "";

// Handle Deletion Request
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $delStmt = $conn->prepare("DELETE FROM results WHERE id = ?");
    $delStmt->bind_param("i", $delete_id);
    if ($delStmt->execute()) {
        $message = "✅ Academic record successfully removed from the database.";
    } else {
        $error = "❌ Failed to delete the record.";
    }
}

if (isset($_GET['msg']) && $_GET['msg'] === 'deleted') {
    $message = "✅ Academic record successfully removed from the database.";
}

// Sri Lankan National Curriculum GPA Calculation Matrix
function getGradeAndPoints($marks) {
    if ($marks >= 75 && $marks <= 100) return ['grade' => 'A', 'points' => 4.0];
    if ($marks >= 65 && $marks < 75)   return ['grade' => 'B', 'points' => 3.0];
    if ($marks >= 55 && $marks < 65)   return ['grade' => 'C', 'points' => 2.0];
    if ($marks >= 40 && $marks < 55)   return ['grade' => 'S', 'points' => 1.0];
    return ['grade' => 'F', 'points' => 0.0];
}

// Maps Cumulative GPA into Academic Performance Classifications
function getPerformanceBand($gpa) {
    if ($gpa >= 3.7) return ['name' => 'First Class Distinction 🌟', 'class' => 'bg-success text-white'];
    if ($gpa >= 3.0) return ['name' => 'Highly Satisfactory 👍', 'class' => 'bg-info text-dark'];
    if ($gpa >= 2.0) return ['name' => 'Satisfactory Pass ✔️', 'class' => 'bg-warning text-dark'];
    return ['name' => 'Academic Review Status 🩺', 'class' => 'bg-danger text-white'];
}

function gradeBadgeColor($grade){
    $g = strtoupper($grade);
    if ($g == "A") return "bg-success";
    if ($g == "B") return "bg-primary";
    if ($g == "C") return "bg-warning text-dark";
    if ($g == "S") return "bg-secondary text-white";
    return "bg-danger";
}

/* ----------------------------------
   Fetch Master Datasets & Map Options
-----------------------------------*/
$examsList = $conn->query("SELECT id, exam_name FROM exams ORDER BY id ASC");
$grades_res = $conn->query("SELECT DISTINCT class_grade FROM results ORDER BY class_grade ASC");
$subjects_res = $conn->query("SELECT DISTINCT subject FROM results ORDER BY subject ASC");

$exam_options_map = [];
while($ex = $examsList->fetch_assoc()) {
    $eName = $ex['exam_name'];
    if (stripos($eName, 'Term 1') !== false || stripos($eName, 'Term 2') !== false || stripos($eName, 'Mid') !== false) {
        $exam_options_map['Mid Exam'] = $ex['id'];
    } elseif (stripos($eName, 'Final') !== false || stripos($eName, 'Term 3') !== false) {
        $exam_options_map['Final Exam'] = $ex['id'];
    }
}

/* ----------------------------------
   Bulk Data Processing Sync Tier
-----------------------------------*/
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['save_bulk_marks'])) {
    $posted_exam_id    = (int)$_POST['form_exam_id'];
    $posted_class      = trim($_POST['form_class_grade']);
    $posted_section    = trim($_POST['form_section']);
    $posted_subject    = trim($_POST['form_subject']);
    $marks_array       = $_POST['marks'] ?? [];

    if ($posted_exam_id === 0 || $posted_class === "" || $posted_section === "" || $posted_subject === "") {
        $error = "Filter validation scope expired. Please reload the register fields.";
    } else {
        $conn->begin_transaction();
        try {
            foreach ($marks_array as $student_key_id => $score_input) {
                if ($score_input === "") continue; 

                $score = (int)$score_input;
                if ($score >= 0 && $score <= 100) {
                    $metricData = getGradeAndPoints($score);
                    $grade = $metricData['grade'];

                    $chk = $conn->prepare("SELECT id FROM results WHERE student_id=? AND exam_id=? AND class_grade=? AND section=? AND subject=?");
                    $chk->bind_param("iisss", $student_key_id, $posted_exam_id, $posted_class, $posted_section, $posted_subject);
                    $chk->execute();
                    $exist = $chk->get_result();

                    if ($exist->num_rows > 0) {
                        $row = $exist->fetch_assoc();
                        $up = $conn->prepare("UPDATE results SET marks=?, grade=? WHERE id=?");
                        $up->bind_param("isi", $score, $grade, $row['id']);
                        $up->execute();
                    } else {
                        $ins = $conn->prepare("INSERT INTO results (student_id, exam_id, class_grade, section, subject, marks, grade) VALUES (?, ?, ?, ?, ?, ?, ?)");
                        $ins->bind_param("iisssis", $student_key_id, $posted_exam_id, $posted_class, $posted_section, $posted_subject, $score, $grade);
                        $ins->execute();
                    }
                }
            }
            $conn->commit();
            $message = "✅ Student scholastic records successfully synchronized and updated.";
        } catch (Exception $e) {
            $conn->rollback();
            $error = "❌ Database Transaction Failure: " . $e->getMessage();
        }
    }
}

/* ----------------------------------
   Fetch Active Student Performance Pool
-----------------------------------*/
$students_data = [];
$class_average_marks = 0;
$pass_count = 0;

if ($filter_class_grade !== "" && $filter_section !== "" && $filter_subject !== "") {
    $stmtSt = $conn->prepare("SELECT id, name, index_number FROM users WHERE role='student' ORDER BY name ASC");
    $stmtSt->execute();
    $resSt = $stmtSt->get_result();
    
    $total_marks = 0;
    while ($r = $resSt->fetch_assoc()) {
        $scoreQuery = $conn->prepare("SELECT id, marks, grade FROM results WHERE student_id=? AND exam_id=? AND class_grade=? AND section=? AND subject=? LIMIT 1");
        $scoreQuery->bind_param("iisss", $r['id'], $filter_exam_id, $filter_class_grade, $filter_section, $filter_subject);
        $scoreQuery->execute();
        $scoreRes = $scoreQuery->get_result()->fetch_assoc();
        
        $r['result_id']     = $scoreRes ? $scoreRes['id'] : null;
        $r['current_marks'] = $scoreRes ? $scoreRes['marks'] : "";
        $r['current_grade'] = $scoreRes ? $scoreRes['grade'] : "";
        
        $students_data[] = $r;

        if ($scoreRes) {
            $total_marks += (int)$scoreRes['marks'];
            if (strtoupper($scoreRes['grade']) !== 'F' && (int)$scoreRes['marks'] >= 35) {
                $pass_count++;
            }
        }
    }
    
    $total_students = count($students_data);
    if ($total_students > 0) {
        $class_average_marks = round($total_marks / $total_students, 1);
    }
}

// Advanced GPA Group Summary Calculation
$gpaReport = [];
if ($filter_exam_id > 0 && $filter_class_grade !== "" && $filter_section !== "") {
    $gpaQuery = $conn->prepare("
        SELECT 
            u.name AS student_name,
            COUNT(r.subject) AS total_subjects,
            AVG(CASE 
                WHEN r.grade = 'A' THEN 4.0
                WHEN r.grade = 'B' THEN 3.0
                WHEN r.grade = 'C' THEN 2.0
                WHEN r.grade = 'S' THEN 1.0
                ELSE 0.0
            END) AS calculated_gpa
        FROM results r
        INNER JOIN users u ON r.student_id = u.id
        WHERE r.exam_id=? AND r.class_grade=? AND r.section=?
        GROUP BY r.student_id
        ORDER BY calculated_gpa DESC
    ");
    $gpaQuery->bind_param("iss", $filter_exam_id, $filter_class_grade, $filter_section);
    $gpaQuery->execute();
    $gpaReport = $gpaQuery->get_result();
}

/* ----------------------------------
   CSV Data Export Sub-Routine Tier
-----------------------------------*/
if (isset($_GET['export_csv']) && !empty($students_data)) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=Performance_Summary_' . $filter_class_grade . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Index Number', 'Student Name', 'Marks Obtained', 'Assigned Grade']);
    foreach ($students_data as $student) {
        fputcsv($output, [$student['index_number'], $student['name'], $student['current_marks'], $student['current_grade']]);
    }
    fclose($output);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Scholastic GPA Performance Module</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
  <style>
    body { background: #f4f7fb; min-height: 100vh; font-family: 'Segoe UI', sans-serif; }
    .cardx { border: 0; border-radius: 22px; background: rgba(255, 255, 255, 0.96); backdrop-filter: blur(8px); box-shadow: 0 12px 30px rgba(0,0,0,0.05); border: 1px solid rgba(255,255,255,0.6); }
    .hero { background: linear-gradient(90deg, #0d6efd, #6f42c1, #d63384); color: white; border-radius: 22px; padding: 25px; box-shadow: 0 12px 30px rgba(13,110,253,0.15); }
    .soft-btn { border-radius: 12px; font-weight: 700; transition: all 0.2s ease; padding: 10px 18px; }
    .metric-card { background: #f8f9fa; border-left: 4px solid #6f42c1; border-radius: 10px; padding: 15px; }
    .badge-band { font-weight: 800; padding: 6px 12px; border-radius: 8px; font-size: 13px; }
    
    .print-report-header, .print-summary-box, .print-table-container { display: none; }

    /* =========================================================================
       ADVANCED HEADLESS PRINT AUTOMATION TRANSCIPT RULES
       ========================================================================= */
    @media print {
      @page { margin: 0 !important; }
      body { background: #fff !important; color: #000 !important; padding: 20mm 15mm !important; font-family: 'Times New Roman', serif !important; }
      .hero, #filterForm, .btn, .bi, .navbar, .action-buttons-container, .cardx, .col-lg-6 { display: none !important; }
      .print-report-header { display: block !important; border-bottom: 3px double #000; padding-bottom: 15px; margin-bottom: 25px; text-align: center; }
      .print-table-container { display: block !important; width: 100% !important; }
      .print-table { width: 100% !important; border-collapse: collapse !important; margin-top: 15px !important; }
      .print-table th, .print-table td { border: 1px solid #000 !important; padding: 10px !important; color: #000 !important; text-align: left; }
      .print-table th { background-color: #f2f2f2 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; font-weight: bold; }
      .print-summary-box { display: block !important; margin-top: 35px; padding: 15px; border: 2px dashed #000; }
    }
  </style>
</head>
<body>

<div class="container py-4" style="max-width: 1300px;">

  <!-- TOP ACTIONS NAVIGATION BAR -->
  <div class="d-flex justify-content-between align-items-center mb-4 navbar">
    <a href="teacher_dashboard.php" class="btn btn-outline-secondary soft-btn"><i class="bi bi-arrow-left"></i> Dashboard</a>
    <span class="text-muted fw-semibold">Logged in as: <span class="text-primary"><?= htmlspecialchars($teacher_name) ?></span></span>
  </div>

  <!-- HERO SECTION HEADER -->
  <div class="hero mb-4 shadow-sm">
    <h3 class="fw-bold mb-1"><i class="bi bi-bar-chart-fill"></i> Scholastic GPA Performance Module</h3>
    <p class="mb-0 opacity-75">Advanced metric interface for academic evaluation and progress monitoring.</p>
  </div>

  <?php if ($message): ?><div class="alert alert-success cardx border-0 py-3 mb-3 text-success fw-bold"><i class="bi bi-check-circle-fill"></i> <?= $message ?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-danger cardx border-0 py-3 mb-3 text-danger fw-bold"><i class="bi bi-exclamation-triangle-fill"></i> <?= $error ?></div><?php endif; ?>

  <!-- ACADEMIC FILTER MATRIX CARD -->
  <div class="cardx p-4 mb-4" id="filterForm">
      <h6 class="fw-bold mb-3 text-secondary"><i class="bi bi-sliders"></i> Academic Filter Matrix</h6>
      <form method="GET" action="" class="row g-2 align-items-end">
          
          <div class="col-md-3">
              <label class="small fw-bold text-muted">Term Assessment</label>
              <select name="exam_id" class="form-select" style="border-radius: 10px;" required>
                  <option value="">Select Evaluation</option>
                  <?php foreach ($exam_options_map as $labelName => $db_id): ?>
                      <option value="<?= $db_id ?>" <?= $filter_exam_id == $db_id ? 'selected' : '' ?>><?= $labelName ?></option>
                  <?php endforeach; ?>
              </select>
          </div>
          
          <div class="col-md-3">
              <label class="small fw-bold text-muted">Grade Level</label>
              <select name="class_grade" class="form-select" style="border-radius: 10px;" required>
                  <option value="">Select Cohort</option>
                  <?php for($i=6; $i<=13; $i++): ?>
                      <option value="Grade <?= $i ?>" <?= $filter_class_grade === "Grade $i" ? 'selected' : '' ?>>Grade <?= $i ?></option>
                  <?php endfor; ?>
              </select>
          </div>
          
          <div class="col-md-2">
              <label class="small fw-bold text-muted">Section Division</label>
              <select name="section" class="form-select" style="border-radius: 10px;" required>
                  <option value="">Select</option>
                  <option value="A" <?= $filter_section === 'A' ? 'selected' : '' ?>>A</option>
                  <option value="B" <?= $filter_section === 'B' ? 'selected' : '' ?>>B</option>
                  <option value="C" <?= $filter_section === 'C' ? 'selected' : '' ?>>C</option>
              </select>
          </div>

          <div class="col-md-2">
              <label class="small fw-bold text-muted">Subject Domain</label>
              <select name="subject" class="form-select" style="border-radius: 10px;" required>
                  <option value="">Select Module</option>
                  <option value="Science" <?= $filter_subject === 'Science' ? 'selected' : '' ?>>Science</option>
                  <option value="Maths" <?= $filter_subject === 'Maths' ? 'selected' : '' ?>>Maths</option>
                  <option value="ICT" <?= $filter_subject === 'ICT' ? 'selected' : '' ?>>ICT</option>
                  <option value="English" <?= $filter_subject === 'English' ? 'selected' : '' ?>>English</option>
                  <option value="Music" <?= $filter_subject === 'Music' ? 'selected' : '' ?>>Music</option>
              </select>
          </div>

          <div class="col-md-2">
              <button type="submit" class="btn btn-primary w-100 soft-btn"><i class="bi bi-cloud-arrow-down-fill"></i> Load Register</button>
          </div>
      </form>
  </div>

  <?php if ($filter_class_grade !== "" && $filter_section !== ""): ?>
  
  <!-- INTERACTIVE ACTION BAR BUTTONS FOR EXPORT -->
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pb-3 mb-3 action-buttons-container">
     <h5 class="fw-bold text-dark m-0"><i class="bi bi-grid-3x3-gap-fill text-primary"></i> Performance Ledger Dashboard</h5>
     <div class="d-flex gap-2">
        <a href="?<?= http_build_query(array_merge($_GET, ['export_csv' => '1'])) ?>" class="btn btn-success soft-btn">
           <i class="bi bi-file-earmark-excel-fill"></i> Export Excel (CSV)
        </a>
        <button onclick="window.print();" class="btn btn-dark soft-btn">
           <i class="bi bi-printer-fill"></i> Download Performance PDF
        </button>
     </div>
  </div>

  <!-- PRINT INTERFACE CAPTION BLOCK LAYER -->
  <div class="print-report-header">
     <h2>OFFICIAL ACADEMIC PERFORMANCE SUMMARY REPORT</h2>
     <p>Compiled by: <?= htmlspecialchars($teacher_name) ?> &bull; Date Printed: <?= date('Y-m-d') ?></p>
     <div class="row text-start mt-3 small fw-bold">
        <div class="col-6">Grade Cohort: <?= htmlspecialchars($filter_class_grade) ?> (Section: <?= htmlspecialchars($filter_section) ?>)</div>
        <div class="col-6 text-end">Subject Module: <?= htmlspecialchars($filter_subject) ?></div>
     </div>
  </div>

  <div class="row g-4">
      
      <!-- BULK INPUT WITH EXPLICIT ACTIONS -->
      <div class="col-lg-6">
          <div class="cardx p-4">
              <div class="d-flex justify-content-between align-items-center mb-3">
                  <h5 class="fw-bold mb-0 text-dark">📋 Evaluation Ledger: <span class="text-primary"><?= $filter_subject ?></span></h5>
                  <span class="badge bg-dark rounded-pill"><?= $filter_class_grade ?> - <?= $filter_section ?></span>
              </div>
              
              <form method="POST" action="">
                  <input type="hidden" name="form_exam_id" value="<?= $filter_exam_id ?>">
                  <input type="hidden" name="form_class_grade" value="<?= $filter_class_grade ?>">
                  <input type="hidden" name="form_section" value="<?= $filter_section ?>">
                  <input type="hidden" name="form_subject" value="<?= $filter_subject ?>">

                  <div class="table-responsive" style="max-height: 480px; overflow-y:auto;">
                      <table class="table table-hover align-middle mb-0">
                          <thead class="table-light sticky-top">
                              <tr>
                                  <th>Student Identifier</th>
                                  <th width="140" class="text-center">Marks (0-100)</th>
                                  <th width="120" class="text-center">Action</th>
                              </tr>
                          </thead>
                          <tbody>
                              <?php if (!empty($students_data)): ?>
                                  <?php foreach ($students_data as $stRow): ?>
                                      <tr>
                                          <td class="fw-semibold text-dark">
                                              <?= htmlspecialchars($stRow['name']) ?>
                                              <?php if($stRow['current_grade']): ?>
                                                  <span class="badge <?= gradeBadgeColor($stRow['current_grade']) ?> ms-2"><?= htmlspecialchars($stRow['current_grade']) ?></span>
                                              <?php endif; ?>
                                          </td>
                                          <td>
                                              <input type="number" 
                                                     name="marks[<?= $stRow['id'] ?>]" 
                                                     class="form-control text-center fw-bold text-primary" 
                                                     min="0" max="100" 
                                                     style="border-radius: 8px;"
                                                     placeholder="--" 
                                                     value="<?= htmlspecialchars($stRow['current_marks']) ?>">
                                          </td>
                                          <td class="text-center">
                                              <?php if($stRow['result_id']): ?>
                                                  <a href="?exam_id=<?= $filter_exam_id ?>&class_grade=<?= urlencode($filter_class_grade) ?>&section=<?= urlencode($filter_section) ?>&subject=<?= urlencode($filter_subject) ?>&delete_id=<?= $stRow['result_id'] ?>" 
                                                     class="btn btn-sm btn-outline-danger soft-btn py-1" 
                                                     onclick="return confirm('Confirm permanent deletion of this academic record?');">
                                                      <i class="bi bi-trash3-fill"></i>
                                                  </a>
                                              <?php else: ?>
                                                  <span class="badge bg-light text-muted border">Pending</span>
                                              <?php endif; ?>
                                          </td>
                                      </tr>
                                  <?php endforeach; ?>
                              <?php endif; ?>
                          </tbody>
                      </table>
                  </div>

                  <div class="text-end mt-3">
                      <button type="submit" name="save_bulk_marks" class="btn btn-primary soft-btn text-white px-4"><i class="bi bi-arrow-repeat"></i> Update & Sync Records</button>
                  </div>
              </form>
          </div>
      </div>

      <!-- CUMULATIVE GPA METRIC VISUALIZER -->
      <div class="col-lg-6">
          <div class="cardx p-4">
              <h5 class="fw-bold mb-1 text-dark"><i class="bi bi-diagram-3-fill text-primary"></i> Academic Performance Overview</h5>
              <p class="small text-muted mb-3">Evaluations emphasize structured credit-mapped GPA metrics.</p>
              
              <div class="table-responsive">
                  <table class="table table-hover align-middle text-center mb-0">
                      <thead class="table-dark">
                          <tr>
                              <th>Student Name</th>
                              <th>Modules Evaluated</th>
                              <th>Cumulative GPA</th>
                              <th>Classification</th>
                          </tr>
                      </thead>
                      <tbody>
                          <?php if (!empty($gpaReport) && $gpaReport->num_rows > 0): ?>
                              <?php 
                              while ($gRow = $gpaReport->fetch_assoc()): 
                                  $gpaVal = number_format($gRow['calculated_gpa'], 2);
                                  $band = getPerformanceBand($gpaVal);
                              ?>
                                  <tr>
                                      <td class="text-start fw-bold text-dark"><?= htmlspecialchars($gRow['student_name'] ?? '') ?></td>
                                      <td><span class="badge bg-secondary"><?= (int)$gRow['total_subjects'] ?> / 5</span></td>
                                      <td class="fw-bold fs-5 text-primary"><?= $gpaVal ?></td>
                                      <td>
                                          <span class="badge badge-band <?= $band['class'] ?> shadow-sm">
                                              <?= $band['name'] ?>
                                          </span>
                                      </td>
                                  </tr>
                              <?php endwhile; ?>
                          <?php else: ?>
                              <tr><td colspan="4" class="text-muted py-4">Awaiting metric input variables to compile ledger data.</td></tr>
                          <?php endif; ?>
                      </tbody>
                  </table>
              </div>
          </div>
      </div>
  </div>

  <!-- PRINT VIEW LAYER: DATA TRANSCRIPT TABLE (VISIBLE ONLY IN GENERATED PDF) -->
  <div class="print-table-container">
      <table class="print-table">
          <thead>
              <tr>
                  <th>Index Number</th>
                  <th>Student Name</th>
                  <th>Marks Obtained</th>
                  <th>Assigned Grade</th>
              </tr>
          </thead>
          <tbody>
              <?php foreach ($students_data as $student): ?>
                  <tr>
                      <td><code><?= htmlspecialchars($student['index_number']) ?></code></td>
                      <td><strong><?= htmlspecialchars($student['name']) ?></strong></td>
                      <td><?= $student['current_marks'] !== "" ? (int)$student['current_marks'] : 'N/A' ?></td>
                      <td><strong><?= $student['current_grade'] !== "" ? htmlspecialchars($student['current_grade']) : 'Pending' ?></strong></td>
                  </tr>
              <?php endforeach; ?>
          </tbody>
      </table>
  </div>

  <!-- PRINT LAYOUT ANALYTICAL METRICS SUMMARY BOX -->
  <div class="print-summary-box">
      <h5><strong>Class Analytical Summary Logs</strong></h5>
      <div class="row mt-2">
         <div class="col-4"><strong>Class Average Marks:</strong> <?= $class_average_marks ?> / 100</div>
         <div class="col-4"><strong>Total Students Enrolled:</strong> <?= count($students_data) ?></div>
         <div class="col-4 text-end"><strong>Performance Passing Rate:</strong> <?= count($students_data) > 0 ? round(($pass_count / count($students_data)) * 100, 0) : 0 ?>%</div>
      </div>
  </div>

  <?php else: ?>
      <div class="text-center text-muted cardx p-5 my-4">
          <i class="bi bi-clipboard-data-fill display-4 text-secondary"></i>
          <p class="mt-2 mb-0 fw-semibold">Please select a valid evaluation scope via the parameters above.</p>
      </div>
  <?php endif; ?>
</div>

</body>
</html>