<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/db.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "counsellor") {
    header("Location: login.php");
    exit();
}

$chat_id = (int)($_GET["chat_id"] ?? 0);
if ($chat_id <= 0) die("Invalid chat.");

// Handle AJAX Chat Refresh Context Intercept
if (isset($_GET['ajax_fetch'])) {
    $stmt = $conn->prepare("SELECT sender_role, message, file_path, file_type, created_at FROM anonymous_messages WHERE chat_id=? ORDER BY id ASC");
    $stmt->bind_param("i", $chat_id);
    $stmt->execute();
    $messages = $stmt->get_result();
    $data = [];
    while ($row = $messages->fetch_assoc()) { $data[] = $row; }
    echo json_encode($data);
    exit();
}

// Handle POST Reply (Messages, Files, and Audio)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $text = trim($_POST["message"] ?? "");
    $file_path = null;
    $file_type = 'text';

    if (!empty($_FILES["attachment"]["name"])) {
        $target_dir = __DIR__ . "/uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $ext = strtolower(pathinfo($_FILES["attachment"]["name"], PATHINFO_EXTENSION));
        $file_name = time() . "_" . bin2hex(random_bytes(8)) . "." . $ext;
        
        if (in_array($ext, ['jpg', 'png', 'jpeg', 'gif'])) $file_type = 'image';
        elseif (in_array($ext, ['mp4', 'mov', 'avi'])) $file_type = 'video';
        elseif (in_array($ext, ['mp3', 'wav', 'ogg', 'webm', 'm4a'])) $file_type = 'audio';
        else $file_type = 'file';

        if (move_uploaded_file($_FILES["attachment"]["tmp_name"], $target_dir . $file_name)) {
            $file_path = $file_name;
        }
    }

    if ($text !== "" || $file_path !== null) {
        $stmt = $conn->prepare("INSERT INTO anonymous_messages (chat_id, sender_role, message, file_path, file_type) VALUES (?, 'counsellor', ?, ?, ?)");
        $stmt->bind_param("isss", $chat_id, $text, $file_path, $file_type);
        $stmt->execute();
    }
    header("Location: counsellor_chat_view.php?chat_id=" . $chat_id);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Counsellor Live Chat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #e5ddd5; font-family: 'Segoe UI', sans-serif; }
        .chat-container { max-width: 900px; margin: 20px auto; height: 450px; overflow-y: auto; background: #fff; padding: 20px; border-radius: 15px; border: 1px solid #ddd; }
        .bubble { max-width: 70%; padding: 12px; border-radius: 15px; margin-bottom: 15px; box-shadow: 0 1px 2px rgba(0,0,0,0.08); }
        .bubble-student { background: #d9fdd3; margin-right: auto; }
        .bubble-counsellor { background: #0084ff; color: white; margin-left: auto; }
        .media-preview { max-width: 250px; border-radius: 10px; display: block; margin-top: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.15); }
        .audio-preview { width: 100%; max-width: 250px; margin-top: 5px; display: block; }
        .recording-pulse { animation: pulse-red 1.5s infinite; background-color: #dc3545 !important; color: white !important; }
        @keyframes pulse-red { 0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); } 100% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); } }
    </style>
</head>
<body>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3><i class="bi bi-chat-heart-fill text-danger"></i> Session Chat #<?= $chat_id ?></h3>
        <a href="counsellor_chat_list.php" class="btn btn-secondary rounded-pill">Back</a>
    </div>

    <div class="chat-container mb-3" id="chatbox"></div>

    <form method="post" id="replyForm" enctype="multipart/form-data" class="card p-3 mx-auto" style="max-width: 900px; border-radius: 15px;">
        <div class="input-group">
            <input type="text" name="message" id="msgText" class="form-control" placeholder="Type or record message...">
            <button type="button" id="recordBtn" class="btn btn-outline-secondary"><i class="bi bi-mic-fill"></i></button>
            <input type="file" name="attachment" class="form-control" style="max-width: 150px;">
            <button type="submit" class="btn btn-primary"><i class="bi bi-send-fill"></i> Send</button>
        </div>
    </form>
</div>

<script>
// Recording Logic
let mediaRecorder, audioChunks = [], isRecording = false;
const recordBtn = document.getElementById('recordBtn');
const msgText = document.getElementById('msgText');

recordBtn.addEventListener('click', async () => {
    if (!isRecording) {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            mediaRecorder = new MediaRecorder(stream);
            audioChunks = [];
            mediaRecorder.ondataavailable = e => audioChunks.push(e.data);
            mediaRecorder.onstop = () => {
                const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                const formData = new FormData(document.getElementById('replyForm'));
                formData.append('attachment', audioBlob, 'voice.webm');
                fetch(window.location.href, { method: 'POST', body: formData }).then(() => window.location.reload());
            };
            mediaRecorder.start();
            isRecording = true;
            recordBtn.classList.add('recording-pulse');
            msgText.placeholder = "Recording...";
        } catch(err) {
            alert("Microphone capture access rejected by browser environment settings.");
        }
    } else {
        mediaRecorder.stop();
        isRecording = false;
        recordBtn.classList.remove('recording-pulse');
    }
});

// Real-time Refresh Core Matrix Logic
function fetchMessages() {
    fetch('counsellor_chat_view.php?chat_id=<?= $chat_id ?>&ajax_fetch=1')
        .then(res => res.json())
        .then(data => {
            const box = document.getElementById('chatbox');
            box.innerHTML = '';
            data.forEach(m => {
                let media = '';
                if (m.file_path) {
                    if (m.file_type === 'image') {
                        media = `<br><img src="uploads/${m.file_path}" class="media-preview">`;
                    } else if (m.file_type === 'audio') {
                        media = `<br><audio controls src="uploads/${m.file_path}" class="audio-preview"></audio>`;
                    } else if (m.file_type === 'video') {
                        media = `<br><video controls src="uploads/${m.file_path}" class="media-preview"></video>`;
                    } else {
                        media = `<br><div class="p-2 border rounded bg-light text-dark small mt-1"><i class="bi bi-file-earmark-arrow-down-fill text-danger"></i> <a href="uploads/${m.file_path}" download class="text-primary text-decoration-none">Download Staged Asset</a></div>`;
                    }
                }
                
                box.innerHTML += `
                    <div class="d-flex ${m.sender_role === 'student' ? '' : 'justify-content-end'}">
                        <div class="bubble ${m.sender_role === 'student' ? 'bubble-student' : 'bubble-counsellor'}">
                            <small class="d-block fw-bold opacity-75 mb-1" style="font-size: 11px;">
                                ${m.sender_role === 'student' ? 'Student (Anonymous)' : 'You (Counselor)'}
                            </small>
                            <div>${m.message}</div>
                            ${media}
                            <small class="d-block text-end opacity-75 mt-1" style="font-size: 10px;">${m.created_at}</small>
                        </div>
                    </div>`;
            });
            box.scrollTop = box.scrollHeight;
        });
}
setInterval(fetchMessages, 3000);
fetchMessages();
</script>
</body>
</html>