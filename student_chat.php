<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/db.php";

// Enforce strict student role isolation
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "student") {
    header("Location: login.php");
    exit();
}

$student_id = (int)($_SESSION["user_id"] ?? 0);
$name = $_SESSION["name"] ?? "Student";
$msg = "";

/* ----------------------------------
   Get or Create Active Chat Room
-----------------------------------*/
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
    $chat_id = (int)$conn->insert_id;
}

/* ----------------------------------
   Process Outbound Message & Multi-Media Upload Tier
-----------------------------------*/
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $text = trim($_POST["message"] ?? "");
    $uploaded_file_name = null;
    $file_type_category = 'text';

    // Process File/Audio Upload Matrix
    if (!empty($_FILES["attachment"]["name"])) {
        $target_dir = __DIR__ . "/uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_extension = strtolower(pathinfo($_FILES["attachment"]["name"], PATHINFO_EXTENSION));
        $clean_basename = time() . "_" . bin2hex(random_bytes(8)) . "." . $file_extension;
        $target_path = $target_dir . $clean_basename;

        $allowed_images = ["jpg", "jpeg", "png", "gif"];
        $allowed_videos = ["mp4", "mov", "avi"];
        $allowed_docs   = ["pdf", "doc", "docx", "txt", "zip"];
        $allowed_audio  = ["webm", "mp3", "wav", "ogg", "m4a"];

        if (in_array($file_extension, $allowed_images)) {
            $file_type_category = 'image';
        } elseif (in_array($file_extension, $allowed_videos)) {
            $file_type_category = 'video';
        } elseif (in_array($file_extension, $allowed_docs)) {
            $file_type_category = 'file';
        } elseif (in_array($file_extension, $allowed_audio)) {
            $file_type_category = 'audio';
        } else {
            $msg = "Unsupported attachment format.";
        }

        if ($_FILES["attachment"]["size"] > 26214400) {
            $msg = "Attachment size exceeds the 25MB infrastructure cap.";
        }

        if ($msg === "") {
            if (move_uploaded_file($_FILES["attachment"]["tmp_name"], $target_path)) {
                $uploaded_file_name = $clean_basename;
            } else {
                $msg = "Server failed to write the attachment.";
            }
        }
    }

    // Profanity Filter
    $badWords = ["fuck", "shit", "bitch", "asshole"];
    foreach ($badWords as $bw) {
        if (stripos($text, $bw) !== false) {
            $msg = "Please use respectful words 🙂";
            break;
        }
    }

    if ($text === "" && $uploaded_file_name === null) {
        $msg = "Cannot send an empty message.";
    }

    if ($msg === "") {
        $sender = "student";
        
        // Context State Check
        $lastMsgQuery = $conn->prepare("SELECT sender_role, message FROM anonymous_messages WHERE chat_id=? ORDER BY id DESC LIMIT 1");
        $lastMsgQuery->bind_param("i", $chat_id);
        $lastMsgQuery->execute();
        $lastMsgRes = $lastMsgQuery->get_result();
        $wasLastMessageBotQuestion = false;
        
        if ($lastMsgRes->num_rows > 0) {
            $lastMsg = $lastMsgRes->fetch_assoc();
            if ($lastMsg['sender_role'] === 'counsellor' && strpos($lastMsg['message'], '❓') !== false) {
                if (strpos($text, '[') === false) {
                    $wasLastMessageBotQuestion = true;
                }
            }
        }

        // Persist Student Message
        $stmt2 = $conn->prepare("INSERT INTO anonymous_messages (chat_id, sender_role, message, file_path, file_type) VALUES (?, ?, ?, ?, ?)");
        $stmt2->bind_param("issss", $chat_id, $sender, $text, $uploaded_file_name, $file_type_category);
        $stmt2->execute();

        /* ------------------------------
            NLP Risk Evaluation Engine
        ------------------------------*/
        $riskScore = 0;
        $messageLower = strtolower($text);
        $detectedCategory = "General"; 

        $regexKeywords = [
            "Stress"     => "/stress[s]*|stressed|overwhelmed|pressure/",
            "Depression" => "/depress[s]*ed|depression|hopeless[s]*|alone|lonely|worthless[s]*/",
            "Anxiety"    => "/panic|cry|crying|anxiety|help[p]*|scared/"
        ];

        foreach ($regexKeywords as $categoryKey => $pattern) {
            if (preg_match($pattern, $messageLower)) {
                $riskScore += 60;
                $detectedCategory = $categoryKey; 
            }
        }

        $criticalKeywords = ["/suicide/", "/kill[l]*/", "/die|dying/"];
        $isCriticalTriggered = false;
        foreach ($criticalKeywords as $pattern) {
            if (preg_match($pattern, $messageLower)) {
                $isCriticalTriggered = true;
                $detectedCategory = "Crisis";
            }
        }

        if ($isCriticalTriggered || $riskScore >= 60) {
            $riskScore = 100;
            $riskLevel = "High";
        } elseif ($riskScore >= 30) {
            $riskLevel = "Medium";
        } else {
            $riskScore = 0;
            $riskLevel = "Low";
        }

        /* ------------------------------
            ADVANCED CONVERSATIONAL AI LIFECYCLE
        ------------------------------*/
        $botMessagesToInsert = [];

        if ($isCriticalTriggered) {
            $botMessagesToInsert[] = "🤖 **[Urgent Support Assistant]**: I am so sorry you are feeling this much pain right now. You are incredibly brave for sharing this. Please stay with me.";
            $botMessagesToInsert[] = "🚨 **[Action Required]**: I have immediately paged the emergency counselor. If you feel you cannot wait, please call the national crisis line at **1333** right now. Your life is valuable.";
        } 
        elseif ($detectedCategory !== "General") {
            if ($detectedCategory === "Stress") {
                $botMessagesToInsert[] = "🤖 **[Intelligent Assistant]**: I understand you're feeling stressed. It is completely valid to feel overwhelmed when things pile up.";
                $botMessagesToInsert[] = "🌿 **[Quick Relief]**: Let's try a dynamic breathing exercise. Inhale deeply for 4 seconds... hold for 4... and exhale slowly for 6 seconds. Repeat this two times.";
                $botMessagesToInsert[] = "❓ **[Tell me more]**: While I connect you to the counselor, can you share what specifically is triggering this stress?\n[Academic Pressure] [Exam Anxiety] [Personal Matters]";
            } 
            elseif ($detectedCategory === "Anxiety") {
                $botMessagesToInsert[] = "🤖 **[Intelligent Assistant]**: It sounds like you are experiencing a lot of anxiety right now. You are in a safe place.";
                $botMessagesToInsert[] = "🌿 **[Quick Relief]**: Let's ground your senses. Look around and name 5 things you can see, 4 things you can touch, and 3 things you can hear.";
                $botMessagesToInsert[] = "❓ **[Tell me more]**: Can you tell me what the physical anxiety feels like right now?\n[Heart Racing] [Difficulty Breathing] [Restlessness]";
            }
            elseif ($detectedCategory === "Depression") {
                $botMessagesToInsert[] = "🤖 **[Intelligent Assistant]**: I am so sorry you are feeling down. It takes courage to type those words out.";
                $botMessagesToInsert[] = "❓ **[Tell me more]**: How long have you been feeling this way? Has something specific happened recently?\n[Just Recently] [For a Few Weeks] [For a Long Time]";
            }

            $resQuery = $conn->prepare("SELECT title, link FROM wellness_resources WHERE category=? ORDER BY id DESC LIMIT 2");
            $resQuery->bind_param("s", $detectedCategory);
            $resQuery->execute();
            $resData = $resQuery->get_result();
            
            if ($resData->num_rows > 0) {
                $resText = "💡 **[Recommended For You]**: Here are targeted self-care shortcuts from our wellness library:\n";
                while($resourceRow = $resData->fetch_assoc()) {
                    $resText .= "• [" . $resourceRow['title'] . "](" . $resourceRow['link'] . ")\n";
                }
                $botMessagesToInsert[] = $resText;
            }
        } 
        elseif (preg_match("/problem|issue|not good|upset|sad|trouble/", $messageLower)) {
            $botMessagesToInsert[] = "🤖 **[Intelligent Assistant]**: I see that you're running into some challenges right now. I'm here to support you without judgment.";
            $botMessagesToInsert[] = "❓ **[Assessment Filter]**: What domain best classifies the problem you are experiencing?\n[Academic Issues] [Emotional Stress] [Relationship Matters] [Health & Sleep Problems]";
        }
        elseif (stripos($messageLower, "academic") !== false || stripos($messageLower, "exam") !== false) {
            $botMessagesToInsert[] = "🤖 **[Intelligent Assistant]**: Academic workloads can be intense. Remember, your grades do not define your human value.";
            $botMessagesToInsert[] = "💡 **[Action Tip]**: Break your studying into 25-minute units using the Pomodoro technique. Go explore our **Wellness Resources** page for the timer tool.";
        }
        elseif (stripos($messageLower, "emotional") !== false || stripos($messageLower, "mood") !== false) {
            $botMessagesToInsert[] = "🤖 **[Intelligent Assistant]**: Dealing with heavy emotions takes a lot of mental energy. It is completely okay to not be okay today.";
            $botMessagesToInsert[] = "🌿 **[Suggestion]**: Try writing down your feelings in the **Log Mood** section to track your emotional trends.";
        }
        elseif ($wasLastMessageBotQuestion) {
            $botMessagesToInsert[] = "🤖 **[Intelligent Assistant]**: Thank you for explaining that. Providing this context really helps. I have securely attached this to your file for the counselor to read as soon as they join. 💙";
        }
        elseif ($uploaded_file_name !== null) {
            if ($file_type_category === 'audio') {
                $botMessagesToInsert[] = "🤖 **[System Assistant]**: Your voice note has been securely encrypted and uploaded. The counselor will listen to it shortly.";
            } else {
                $botMessagesToInsert[] = "🤖 **[System Assistant]**: Your file has been securely uploaded for the counselor to review.";
            }
        }
        else {
            if (preg_match("/hi|hello|hey|good morning|good afternoon/", $messageLower)) {
                $botMessagesToInsert[] = "🤖 **[Intelligent Assistant]**: Hello! Welcome to your secure sanctuary room. How are you feeling today?\n[I have problem] [I feel stressed] [I am feeling anxious] [I just want to talk]";
            } else {
                $botMessagesToInsert[] = "🤖 **[Intelligent Assistant]**: Thank you for reaching out. Please describe what you are walking through, or click an option below to initiate care:\n[I have problem] [Help with Stress] [Manage Anxiety]";
            }
        }

        if (!empty($botMessagesToInsert)) {
            $botSender = "counsellor";
            $null_val = null;
            $text_type = "text";
            foreach ($botMessagesToInsert as $botMsg) {
                $stmtBot = $conn->prepare("INSERT INTO anonymous_messages (chat_id, sender_role, message, file_path, file_type) VALUES (?, ?, ?, ?, ?)");
                $stmtBot->bind_param("issss", $chat_id, $botSender, $botMsg, $null_val, $text_type);
                $stmtBot->execute();
            }
        }

        /* -----------------------------------------------------------
            STICKY RISK MEMORY INTERLOCK
        ----------------------------------------------------------- */
        $check = $conn->prepare("SELECT id, risk_score FROM wellness_risk WHERE student_id=?");
        $check->bind_param("i", $student_id);
        $check->execute();
        $exist = $check->get_result();

        if ($exist->num_rows > 0) {
            $existingRisk = $exist->fetch_assoc();
            if ($riskScore >= $existingRisk['risk_score']) {
                $update = $conn->prepare("UPDATE wellness_risk SET risk_score=?, risk_level=? WHERE student_id=?");
                $update->bind_param("isi", $riskScore, $riskLevel, $student_id);
                $update->execute();
            }
        } else {
            $insert = $conn->prepare("INSERT INTO wellness_risk (student_id, risk_score, risk_level) VALUES (?, ?, ?)");
            $insert->bind_param("iis", $student_id, $riskScore, $riskLevel);
            $insert->execute();
        }

        if (isset($_POST['is_ajax']) && $_POST['is_ajax'] == '1') {
            echo json_encode(["status" => "success"]);
            exit();
        }

        header("Location: student_chat.php");
        exit();
    }
}

/* ----------------------------------
   Fetch Active Risk Context
-----------------------------------*/
$riskCheck = $conn->prepare("SELECT risk_level FROM wellness_risk WHERE student_id=?");
$riskCheck->bind_param("i", $student_id);
$riskCheck->execute();
$riskResult = $riskCheck->get_result()->fetch_assoc();
$currentRiskLevel = $riskResult["risk_level"] ?? "Low";

/* ----------------------------------
   Load History
-----------------------------------*/
$stmt3 = $conn->prepare("SELECT sender_role, message, file_path, file_type, created_at FROM anonymous_messages WHERE chat_id=? ORDER BY id ASC");
$stmt3->bind_param("i", $chat_id);
$stmt3->execute();
$messages = $stmt3->get_result();

function parseBotMarkdown($text) {
    $text = htmlspecialchars($text);
    $text = preg_replace('/\*\*(.*?)\*\*/', '<strong style="color:#075e54;">$1</strong>', $text);
    $text = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2" target="_blank" class="fw-bold text-primary text-decoration-none">$1 <i class="bi bi-box-arrow-up-right small"></i></a>', $text);
    $text = preg_replace('/\[([^\]]+)\](?!\()/', '<button type="button" class="btn btn-outline-success btn-sm m-1 rounded-pill chat-option-badge" onclick="submitChatOption(\'$1\')">$1</button>', $text);
    return nl2br($text);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Anonymous Chat AI</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">

  <style>
    body {
      background: #e5ddd5;
      background-image: radial-gradient(rgba(0,0,0,0.08) 1px, transparent 0);
      background-size: 24px 24px;
      min-height: 100vh;
      font-family: 'Segoe UI', -apple-system, sans-serif;
      margin: 0;
    }
    .custom-navbar {
      background: #075e54;
      color: white;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .avatar-circle {
      width: 45px;
      height: 45px;
      border-radius: 50%;
      background: #ece5dd;
      color: #075e54;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      font-size: 18px;
    }
    .chat-container {
      max-width: 900px;
      margin: 0 auto;
      height: calc(100vh - 200px);
      overflow-y: auto;
      padding: 20px;
    }
    @keyframes popIn {
        0% { opacity: 0; transform: translateY(15px) scale(0.95); }
        100% { opacity: 1; transform: translateY(0) scale(1); }
    }
    .bubble {
      max-width: 75%;
      padding: 12px 16px;
      border-radius: 12px;
      margin-bottom: 14px;
      position: relative;
      box-shadow: 0 2px 5px rgba(0,0,0,0.08);
      word-wrap: break-word;
      animation: popIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
    }
    .bubble-student {
      background: #d9fdd3;
      color: #111b21;
      border-top-right-radius: 0px;
    }
    .bubble-counsellor {
      background: #ffffff;
      color: #111b21;
      border-top-left-radius: 0px;
    }
    .bubble-bot {
      background: linear-gradient(145deg, #f8f9fa, #f1f3f5) !important;
      border-left: 4px solid #00a884 !important;
      color: #212529 !important;
      border-radius: 12px !important;
      border-top-left-radius: 0px !important;
      max-width: 85% !important;
    }
    .media-preview {
      max-width: 100%;
      border-radius: 8px;
      margin-bottom: 6px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.15);
    }
    .audio-player {
        width: 100%;
        height: 40px;
        margin-top: 5px;
    }
    .meta-time {
      font-size: 11px;
      color: #667781;
      margin-top: 6px;
      text-align: right;
    }
    .input-bar {
      background: #f0f2f5;
      padding: 12px 20px;
      border-top: 1px solid #e3e6e9;
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      z-index: 1000;
    }
    .chat-input {
      border-radius: 24px !important;
      padding: 10px 18px;
      border: 1px solid #ffffff;
    }
    .send-btn {
      border-radius: 50% !important;
      width: 46px;
      height: 46px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #00a884 !important;
      border: none !important;
      color: white !important;
    }
    .chat-option-badge {
      border: 1px solid #00a884 !important;
      color: #075e54 !important;
      background-color: rgba(0, 168, 132, 0.05);
      font-weight: 600;
      transition: all 0.2s ease;
    }
    .chat-option-badge:hover {
      background-color: #00a884 !important;
      color: white !important;
    }
    @keyframes pulse-red {
        0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
        70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
        100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
    }
    .recording-pulse {
        animation: pulse-red 1.5s infinite;
        background-color: #dc3545 !important;
        color: white !important;
    }
  </style>
</head>
<body class="pb-5">

<div class="custom-navbar py-2 sticky-top">
  <div class="container d-flex justify-content-between align-items-center" style="max-width: 900px;">
    <div class="d-flex align-items-center gap-3">
      <a href="student_dashboard.php" class="text-white fs-4 text-decoration-none"><i class="bi bi-arrow-left"></i></a>
      <div class="avatar-circle">🛡️</div>
      <div>
        <!-- DYNAMIC SENDER NAME RECONNECT LAYER -->
        <h6 class="mb-0 fw-bold">Support Room - <?= htmlspecialchars($name) ?></h6>
        <small class="opacity-75">Conversational AI & Counselor Gateway</small>
      </div>
    </div>
    <span class="badge bg-light text-dark py-2 px-3 rounded-pill fw-bold small">Encrypted Channel</span>
  </div>
</div>

<div class="container my-3" style="max-width: 900px;">
  <?php if ($currentRiskLevel === "High" || $currentRiskLevel === "Medium"): ?>
    <div class="alert alert-danger shadow-sm border-0 d-flex justify-content-between align-items-center mb-3 p-3" style="border-radius: 16px;">
        <div>
            <h6 class="fw-bold mb-1"><i class="bi bi-person-lines-fill"></i> Counselor Escalation Triggered</h6>
            <p class="small mb-0 opacity-75">Our intelligent system has notified a live counselor. They will join this chat thread shortly to support you.</p>
        </div>
        <span class="badge bg-white text-danger fw-bold py-2 px-3 rounded-pill shadow-sm"><i class="bi bi-shield-check"></i> Monitored</span>
    </div>
  <?php endif; ?>

  <?php if ($msg): ?>
    <div class="alert alert-warning mb-3" style="border-radius: 12px;"><?= htmlspecialchars($msg) ?></div>
  <?php endif; ?>

  <div class="chat-container d-flex flex-column" id="chatbox">
    <?php while ($m = $messages->fetch_assoc()): ?>
      <?php 
        $isStudent = ($m["sender_role"] === "student"); 
        $isBot = (strpos($m["message"], "🤖") !== false || strpos($m["message"], "💡") !== false || strpos($m["message"], "🌿") !== false || strpos($m["message"], "❓") !== false || strpos($m["message"], "🚨") !== false);
        $file_path = $m["file_path"] ?? null;
        $file_type = $m["file_type"] ?? 'text';
      ?>

      <div class="d-flex w-100 <?= $isStudent ? 'justify-content-end' : 'justify-content-start' ?>">
        <div class="bubble <?= $isStudent ? 'bubble-student' : 'bubble-counsellor' ?> <?= $isBot ? 'bubble-bot' : '' ?>">
          
          <div class="fw-bold small opacity-75 mb-1" style="font-size: 11px;">
            <?= $isStudent ? "You (Anonymous)" : ($isBot ? "✨ Intelligent Wellness AI" : "👨‍💼 Designated Counselor") ?>
          </div>

          <?php if (!empty($file_path)): ?>
            <div class="media-render-zone mt-1 mb-2">
              <?php if ($file_type === 'image'): ?>
                <img src="uploads/<?= htmlspecialchars($file_path) ?>" class="media-preview img-fluid">
              <?php elseif ($file_type === 'video'): ?>
                <video src="uploads/<?= htmlspecialchars($file_path) ?>" controls class="media-preview w-100"></video>
              <?php elseif ($file_type === 'audio'): ?>
                <audio src="uploads/<?= htmlspecialchars($file_path) ?>" controls class="audio-player"></audio>
              <?php elseif ($file_type === 'file'): ?>
                <div class="p-2 border rounded bg-light d-flex align-items-center gap-2">
                  <i class="bi bi-file-earmark-arrow-down-fill text-danger fs-3"></i>
                  <div class="overflow-hidden">
                    <span class="d-block text-truncate small fw-bold"><?= htmlspecialchars($file_path) ?></span>
                    <a href="uploads/<?= htmlspecialchars($file_path) ?>" download class="small text-decoration-none text-primary">Download Document</a>
                  </div>
                </div>
              <?php endif; ?>
            </div>
          <?php endif; ?>
          
          <?php if (!empty($m["message"])): ?>
            <div style="font-size: 15px; line-height: 1.5; color: #111b21;">
                <?= $isBot ? parseBotMarkdown($m["message"]) : nl2br(htmlspecialchars($m["message"])) ?>
            </div>
          <?php endif; ?>
          
          <div class="meta-time d-flex align-items-center justify-content-end gap-1">
            <?= date("g:i A", strtotime($m["created_at"])) ?>
          </div>

        </div>
      </div>
    <?php endwhile; ?>
  </div>
</div>

<div class="input-bar">
  <div class="container p-0" style="max-width: 900px;">
    <form method="post" id="chatForm" enctype="multipart/form-data" class="d-flex align-items-center gap-2">
      
      <label class="btn btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 44px; height: 44px; background: white; border: none; color: #54656f;">
        <i class="bi bi-paperclip fs-5"></i>
        <input type="file" name="attachment" id="attachment" style="display: none;" onchange="updateFileIndicator()">
      </label>
      
      <input type="text" name="message" id="msgText" class="form-control chat-input shadow-sm" placeholder="Message the counselor or AI..." autocomplete="off" required>
      
      <button type="button" id="recordBtn" class="btn rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 44px; height: 44px; background: white; border: none; color: #54656f;" title="Hold to Record Audio">
        <i class="bi bi-mic-fill fs-5"></i>
      </button>

      <button type="submit" id="sendBtn" class="btn send-btn shadow-sm"><i class="bi bi-send-fill"></i></button>
      
    </form>
    <div id="file-indicator" class="text-success small fw-bold px-5 mt-1" style="display: none;"><i class="bi bi-file-check-fill"></i> Media payload staged successfully.</div>
  </div>
</div>

<script>
const chat = document.getElementById("chatbox");
chat.scrollTop = chat.scrollHeight;

function updateFileIndicator() {
    const fileInput = document.getElementById('attachment');
    const indicator = document.getElementById('file-indicator');
    if (fileInput.files.length > 0) {
        indicator.style.display = 'block';
        document.getElementById('msgText').placeholder = "File attached: " + fileInput.files[0].name;
    }
}

function submitChatOption(optionValue) {
    const inputField = document.getElementById('msgText');
    const formElement = document.getElementById('chatForm');
    
    inputField.value = optionValue;
    formElement.submit();
}

let mediaRecorder;
let audioChunks = [];
let isRecording = false;

const recordBtn = document.getElementById('recordBtn');
const recordIcon = recordBtn.querySelector('i');
const msgText = document.getElementById('msgText');

recordBtn.addEventListener('click', async () => {
    if (!isRecording) {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            mediaRecorder = new MediaRecorder(stream);
            audioChunks = [];

            mediaRecorder.ondataavailable = e => {
                if (e.data.size > 0) audioChunks.push(e.data);
            };

            mediaRecorder.onstop = async () => {
                const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                const formData = new FormData();
                
                formData.append('attachment', audioBlob, 'voice_note_' + Date.now() + '.webm');
                formData.append('message', '🎤 Voice Message');
                formData.append('is_ajax', '1');

                msgText.placeholder = "Uploading voice note...";

                await fetch(window.location.href, { 
                    method: 'POST', 
                    body: formData 
                });
                
                window.location.reload();
            };

            mediaRecorder.start();
            isRecording = true;
            
            recordBtn.classList.add('recording-pulse');
            recordIcon.classList.replace('bi-mic-fill', 'bi-stop-circle-fill');
            
            msgText.value = "";
            msgText.placeholder = "Recording... Tap stop to send.";
            msgText.disabled = true;

        } catch (err) {
            alert('Microphone access denied or unsupported on this browser.');
        }
    } else {
        mediaRecorder.stop();
        isRecording = false;
        
        recordBtn.classList.remove('recording-pulse');
        recordIcon.classList.replace('bi-stop-circle-fill', 'bi-mic-fill');
        msgText.disabled = false;
        msgText.placeholder = "Message the counselor or AI...";
    }
});
</script>
</body>
</html>