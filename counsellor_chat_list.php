<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . "/db.php";

/* Risk Badge */
function riskBadge($risk)
{
    switch($risk)
    {
        case "High":
            return '<span class="badge bg-danger px-3 py-2">🔴 High Risk</span>';

        case "Medium":
            return '<span class="badge bg-warning text-dark px-3 py-2">🟡 Medium Risk</span>';

        default:
            return '<span class="badge bg-success px-3 py-2">🟢 Low Risk</span>';
    }
}

/* Protect Page */
if(!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") != "counsellor")
{
    header("Location: login.php");
    exit();
}

/* Get Anonymous Chats (Corrected Media Tracking Layer) */
$sql = "
SELECT
    ac.id,
    ac.student_id,
    ac.created_at,
    wr.risk_score,
    wr.risk_level,
(
    SELECT 
        CASE 
            WHEN am.message != '' AND am.message IS NOT NULL THEN am.message
            WHEN am.file_type = 'audio' THEN '🎤 Voice Note'
            WHEN am.file_type = 'image' THEN '📷 Image Attachment'
            WHEN am.file_type = 'video' THEN '🎥 Video Attachment'
            WHEN am.file_path IS NOT NULL THEN '📁 Document File'
            ELSE 'No messages yet'
        END
    FROM anonymous_messages am
    WHERE am.chat_id = ac.id
    ORDER BY am.id DESC
    LIMIT 1
) AS last_message
FROM anonymous_chats ac
LEFT JOIN wellness_risk wr
ON ac.student_id = wr.student_id
ORDER BY ac.created_at DESC
";

$result = $conn->query($sql);

$totalChats = ($result) ? $result->num_rows : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Counsellor - Anonymous Chats</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{
    background:linear-gradient(135deg,#eef5ff,#f7f9fc,#fff9ef);
    font-family:'Segoe UI',sans-serif;
}
.header{
    background:linear-gradient(90deg,#2563eb,#7c3aed,#ec4899);
    color:#fff;
    padding:30px;
    border-radius:20px;
    box-shadow:0 10px 25px rgba(0,0,0,.12);
}
.stats{
    border-radius:18px;
    background:#fff;
    box-shadow:0 6px 18px rgba(0,0,0,.08);
    padding:20px;
    text-align:center;
}
.stats h3{
    font-weight:bold;
    color:#2563eb;
}
.chat-card{
    border:none;
    border-radius:18px;
    box-shadow:0 8px 20px rgba(0,0,0,.08);
    transition:.3s;
}
.chat-card:hover{
    transform:translateY(-4px);
    box-shadow:0 12px 28px rgba(0,0,0,.14);
}
.chat-title{
    color:#1e3a8a;
    font-weight:700;
}
.chat-message{
    color:#444;
}
.chat-date{
    color:#888;
    font-size:14px;
}
.status-box{
    background:#f8f9ff;
    border-radius:15px;
    padding:15px;
}
.score{
    font-size:28px;
    font-weight:bold;
    color:#0d6efd;
}
.btn-dashboard{
    border-radius:12px;
    font-weight:600;
}
</style>
</head>
<body>

<div class="container py-4">

<div class="header mb-4">
<div class="d-flex justify-content-between align-items-center">
<div>
<h2 class="mb-1">💬 Anonymous Student Chats</h2>
<p class="mb-0">Monitor anonymous student conversations and student wellbeing.</p>
</div>
<a href="counsellor_dashboard.php" class="btn btn-light btn-dashboard">← Dashboard</a>
</div>
</div>

<div class="row mb-4">
<div class="col-md-4">
<div class="stats">
<h6>Total Anonymous Chats</h6>
<h3><?= $totalChats ?></h3>
</div>
</div>
</div>

<?php if($result && $result->num_rows>0): ?>
<?php mysqli_data_seek($result,0); ?>
<?php while($row = $result->fetch_assoc()): ?>
<a href="counsellor_chat_view.php?chat_id=<?= (int)$row['id']; ?>" class="text-decoration-none text-dark">
<div class="card chat-card mb-4">
<div class="card-body">
<div class="row align-items-center">
<div class="col-md-8">
<h5 class="chat-title">💬 Chat #<?= (int)$row['id']; ?></h5>
<p class="chat-message mb-2 fw-semibold text-secondary">
<?= htmlspecialchars(trim($row['last_message'] ?? '') ?: 'No messages yet'); ?>
</p>
<div class="chat-date">
🕒 <?= htmlspecialchars($row['created_at']); ?>
</div>
</div>
<div class="col-md-4 text-end">
<div class="status-box">
<h6 class="fw-bold text-primary">🩺 Wellness Status</h6>
<div class="score">
<?= ($row["risk_score"] !== NULL) ? (int)$row["risk_score"] : 0; ?>
</div>
<div class="mb-2">Wellness Score</div>
<?= riskBadge($row["risk_level"] ?? "Low"); ?>
</div>
</div>
</div>
</div>
</div>
</a>
<?php endwhile; ?>
<?php else: ?>
<div class="alert alert-info text-center">No anonymous chats available.</div>
<?php endif; ?>

</div>

</body>
</html>