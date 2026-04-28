<?php
// show errors (for development only)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// include authentication and database connection
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/db.php";

// check if the user is a logged-in student
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "student") {
    header("Location: login.php");
    exit();
}

// get logged-in student details from session
$student_id = (int)($_SESSION["user_id"] ?? 0);
$name = $_SESSION["name"] ?? "Student";

// variables to show messages
$success = "";
$error = "";

/* =========================
   DELETE MOOD LOG
========================= */
if (isset($_GET["delete"])) {
    $del_id = (int)$_GET["delete"];

    // delete only the selected log belonging to this student
    $stmt = $conn->prepare("DELETE FROM mood_logs WHERE id=? AND student_id=?");
    $stmt->bind_param("ii", $del_id, $student_id);
    $stmt->execute();

    // refresh page after deletion
    header("Location: student_mood.php");
    exit();
}

/* =========================
   SAVE NEW MOOD
========================= */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $mood = $_POST["mood"] ?? "";
    $note = trim($_POST["note"] ?? "");

    // check if mood is selected
    if ($mood === "") {
        $error = "Please select a mood.";
    } else {
        // insert mood into database
        $stmt = $conn->prepare("INSERT INTO mood_logs (student_id, mood, note) VALUES (?,?,?)");
        $stmt->bind_param("iss", $student_id, $mood, $note);
        $stmt->execute();

        $success = "✅ Mood saved successfully!";
    }
}

/* =========================
   LOAD MOOD HISTORY
========================= */
$stmt = $conn->prepare("SELECT id, mood, note, created_at FROM mood_logs WHERE student_id=? ORDER BY id DESC");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$logs = $stmt->get_result();

/* =========================
   FUNCTION: GET BADGE COLOR
========================= */
function moodBadge($mood){
    $m = strtolower($mood);

    if ($m == "happy") return "bg-success";
    if ($m == "excited") return "bg-primary";
    if ($m == "calm") return "bg-info text-dark";
    if ($m == "sad") return "bg-secondary";
    if ($m == "angry") return "bg-danger";
    if ($m == "stressed") return "bg-warning text-dark";

    return "bg-dark"; // default color
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Mood & Wellness Log</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap for layout -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="container py-4" style="max-width: 980px;">

  <!-- top navigation -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <a href="student_dashboard.php" class="btn btn-outline-dark">← Back</a>
    <div class="small text-muted">
        Hi, <b><?= htmlspecialchars($name) ?></b> 😊
    </div>
  </div>

  <!-- page header -->
  <h3 class="mb-4">📝 Mood & Wellness Log</h3>

  <!-- success / error messages -->
  <?php if($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <?php if($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="row">

    <!-- LEFT: ADD MOOD -->
    <div class="col-md-5">
      <div class="card p-3">

        <h5>Select Your Mood</h5>

        <!-- mood form -->
        <form method="post">
          <input type="hidden" name="mood" id="moodInput">

          <!-- mood buttons -->
          <div class="row g-2 mt-2">
            <div class="col-6"><button type="button" onclick="setMood('Happy')" class="btn btn-success w-100">😊 Happy</button></div>
            <div class="col-6"><button type="button" onclick="setMood('Excited')" class="btn btn-primary w-100">🤩 Excited</button></div>
            <div class="col-6"><button type="button" onclick="setMood('Calm')" class="btn btn-info w-100">😌 Calm</button></div>
            <div class="col-6"><button type="button" onclick="setMood('Stressed')" class="btn btn-warning w-100">😰 Stressed</button></div>
            <div class="col-6"><button type="button" onclick="setMood('Sad')" class="btn btn-secondary w-100">😢 Sad</button></div>
            <div class="col-6"><button type="button" onclick="setMood('Angry')" class="btn btn-danger w-100">😡 Angry</button></div>
          </div>

          <!-- note input -->
          <div class="mt-3">
            <label>Optional Note</label>
            <textarea name="note" class="form-control" rows="3"></textarea>
          </div>

          <!-- submit button -->
          <button class="btn btn-primary mt-3 w-100">Save Mood</button>
        </form>

      </div>
    </div>

    <!-- RIGHT: MOOD HISTORY -->
    <div class="col-md-7">
      <div class="card p-3">

        <h5>📅 Mood History</h5>

        <!-- if no logs -->
        <?php if($logs->num_rows === 0): ?>
          <div class="text-muted">No mood logs yet.</div>
        <?php endif; ?>

        <!-- show logs -->
        <?php while($row = $logs->fetch_assoc()): ?>
          <div class="border rounded p-2 mt-2">

            <div class="d-flex justify-content-between">
              <span class="badge <?= moodBadge($row["mood"]) ?>">
                <?= htmlspecialchars($row["mood"]) ?>
              </span>

              <!-- delete button -->
              <a href="?delete=<?= (int)$row["id"] ?>"
                 class="btn btn-sm btn-outline-danger"
                 onclick="return confirm('Delete this mood log?')">
                 Delete
              </a>
            </div>

            <!-- note -->
            <div class="mt-2">
              <?= $row["note"] ? nl2br(htmlspecialchars($row["note"])) : "<span class='text-muted'>No note</span>" ?>
            </div>

            <!-- date -->
            <div class="small text-muted mt-1">
              🕒 <?= htmlspecialchars($row["created_at"]) ?>
            </div>

          </div>
        <?php endwhile; ?>

      </div>
    </div>

  </div>

</div>

<script>
// set mood when button is clicked
function setMood(mood){
  document.getElementById("moodInput").value = mood;

  // simple alert to confirm selection
  alert("Mood selected: " + mood);
}
</script>

</body>
</html>