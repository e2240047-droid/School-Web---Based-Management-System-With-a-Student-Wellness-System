<?php
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/db.php";
require_role(["student"]);

if($_SERVER["REQUEST_METHOD"]==="POST"){
  $msg = trim($_POST["message"] ?? "");
  if($msg=="") die("Message required");
  $stmt=$conn->prepare("INSERT INTO anonymous_chat(student_id,message) VALUES(?,?)");
  $stmt->bind_param("is",$_SESSION["user_id"],$msg);
  $stmt->execute();
  header("Location: chat.php"); exit();
}

$res=$conn->query("SELECT message,created_at FROM anonymous_chat ORDER BY id DESC LIMIT 50");
?>
<!doctype html><html><head>
<title>Anonymous Chat</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="bg-light">
<div class="container py-4">
  <div class="d-flex justify-content-between">
    <h3>💬 Anonymous Chat</h3>
    <a class="btn btn-secondary" href="student_dashboard.php">Back</a>
  </div>

  <form method="post" class="card p-3 mt-3 shadow-sm">
    <label class="form-label">Your message (anonymous)</label>
    <textarea class="form-control" name="message" rows="3" required></textarea>
    <button class="btn btn-primary mt-2">Send</button>
  </form>

  <?php while($r=$res->fetch_assoc()): ?>
    <div class="card mt-2 shadow-sm">
      <div class="card-body">
        <b>Anonymous:</b> <?= nl2br(htmlspecialchars($r["message"])) ?>
        <div><small class="text-muted"><?= $r["created_at"] ?></small></div>
      </div>
    </div>
  <?php endwhile; ?>
</div>
</body></html>