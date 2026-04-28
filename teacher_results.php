<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/db.php";

// allow only teacher
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "teacher") {
    header("Location: login.php");
    exit();
}

$msg = "";

// get all students for dropdown
$students = $conn->query("SELECT id, name FROM users WHERE role='student' ORDER BY name ASC");

// get all exams for dropdown
$exams = $conn->query("SELECT id, exam_name FROM exams ORDER BY id DESC");

// save result
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $student_id = (int)$_POST["student_id"];
    $exam_id = (int)$_POST["exam_id"];
    $subject = trim($_POST["subject"]);
    $marks = (int)$_POST["marks"];
    $grade = trim($_POST["grade"]);

    // insert result without term
    $stmt = $conn->prepare("
        INSERT INTO results (student_id, exam_id, subject, marks, grade)
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->bind_param("iisis", $student_id, $exam_id, $subject, $marks, $grade);

    if ($stmt->execute()) {
        $msg = "Result added successfully.";
    } else {
        $msg = "Failed to add result.";
    }
}

// get results with student name and exam name
$result = $conn->query("
    SELECT results.*, users.name AS student_name, exams.exam_name
    FROM results
    JOIN users ON results.student_id = users.id
    JOIN exams ON results.exam_id = exams.id
    ORDER BY results.id DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Results</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
</head>

<body class="bg-light">

<div class="container py-4">

    <a href="teacher_dashboard.php" class="btn btn-dark mb-3">
        <i class="bi bi-arrow-left"></i> Back to Dashboard
    </a>

    <div class="card shadow p-4 mb-4">
        <h3>Manage Student Results</h3>
        <p class="text-muted">Add and view student academic results.</p>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-info"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <!-- Add Result Form -->
    <div class="card shadow p-4 mb-4">
        <h5>Add New Result</h5>

        <form method="post">
            <div class="row">

                <!-- student dropdown -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Student Name</label>
                    <select name="student_id" class="form-select" required>
                        <option value="">Select Student</option>

                        <?php while ($s = $students->fetch_assoc()): ?>
                            <option value="<?= (int)$s["id"] ?>">
                                <?= htmlspecialchars($s["name"]) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- exam dropdown -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Exam</label>
                    <select name="exam_id" class="form-select" required>
                        <option value="">Select Exam</option>

                        <?php while ($e = $exams->fetch_assoc()): ?>
                            <option value="<?= (int)$e["id"] ?>">
                                <?= htmlspecialchars($e["exam_name"]) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Subject</label>
                    <input type="text" name="subject" class="form-control" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Marks</label>
                    <input type="number" name="marks" class="form-control" required>
                </div>

                <div class="col-md-6 mb-3">
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

            </div>

            <button type="submit" class="btn btn-success">
                Save Result
            </button>
        </form>
    </div>

    <!-- Results List -->
    <div class="card shadow p-4">
        <h5>All Results</h5>

        <?php if ($result && $result->num_rows > 0): ?>
            <div class="table-responsive mt-3">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Student</th>
                            <th>Exam</th>
                            <th>Subject</th>
                            <th>Marks</th>
                            <th>Grade</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row["student_name"]) ?></td>
                                <td><?= htmlspecialchars($row["exam_name"]) ?></td>
                                <td><?= htmlspecialchars($row["subject"]) ?></td>
                                <td><?= htmlspecialchars($row["marks"]) ?></td>
                                <td><?= htmlspecialchars($row["grade"]) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-muted mt-3">No results found yet.</p>
        <?php endif; ?>

    </div>

</div>

</body>
</html>