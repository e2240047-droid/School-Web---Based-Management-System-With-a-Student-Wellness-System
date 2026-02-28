<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/db.php";

if ($_SESSION["role"] !== "student") {
    header("Location: login.php");
    exit();
}

$student_id = (int)$_SESSION["user_id"];
$errorMsg = "";

// Get or create chat
$stmt = $conn->prepare("SELECT id FROM anonymous_chats WHERE student_id=? LIMIT 1");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$chatRes = $stmt->get_result();

if ($chatRes->num_rows === 1) {
    $chat_id = (int)$chatRes->fetch_assoc()["id"];
} else {
    $ins = $conn->prepare("INSERT INTO anonymous_chats (student_id) VALUES (?)");
    $ins->bind_param("i", $student_id);
    $ins->execute();
    $chat_id = mysqli_insert_id($conn);
}

// Send message
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $text = trim($_POST["message"] ?? "");

    if ($text === "") {
        $errorMsg = "Message cannot be empty.";
    } else {
        $sender = "student";
        $stmt2 = $conn->prepare("INSERT INTO anonymous_messages (chat_id, sender_role, message) VALUES (?,?,?)");
        $stmt2->bind_param("iss", $chat_id, $sender, $text);
        $stmt2->execute();

        header("Location: student_chat.php");
        exit();
    }
}

// Load messages
$stmt3 = $conn->prepare("SELECT sender_role, message, created_at FROM anonymous_messages WHERE chat_id=? ORDER BY id ASC");
$stmt3->bind_param("i", $chat_id);
$stmt3->execute();
$messages = $stmt3->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Anonymous Chat</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
    <a href="student_dashboard.php">← Back</a>
    <h4 class="mt-2">Anonymous Chat</h4>
    <p class="text-muted small">Your identity is hidden. Be respectful.</p>

    <?php if ($errorMsg): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($errorMsg) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm p-3 mb-3" style="max-height: 420px; overflow-y:auto;">
        <?php if ($messages->num_rows === 0): ?>
            <div class="text-muted">No messages yet. Start the conversation.</div>
        <?php endif; ?>

        <?php while ($m = $messages->fetch_assoc()): ?>
            <?php $isStudent = ($m["sender_role"] === "student"); ?>
            <div class="d-flex mb-2 <?= $isStudent ? "justify-content-end" : "justify-content-start" ?>">
                <div class="p-2 rounded" style="max-width:75%; background:#e9ecef;">
                    <div class="small fw-bold mb-1">
                        <?= $isStudent ? "You (Anonymous)" : "Counsellor" ?>
                    </div>
                    <div><?= nl2br(htmlspecialchars($m["message"])) ?></div>
                    <div class="text-muted small mt-1"><?= htmlspecialchars($m["created_at"]) ?></div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <form method="post" class="card shadow-sm p-3">
        <label class="form-label">Type your message</label>
        <textarea name="message" class="form-control mb-3" rows="3" required></textarea>
        <button class="btn btn-primary">Send</button>
    </form>
</div>

</body>
</html>