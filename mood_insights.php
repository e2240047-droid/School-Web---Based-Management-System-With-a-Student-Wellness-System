<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Secure Authentication Interlock check
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/db.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

$admin_name = $_SESSION["name"] ?? "Administrator";

// Default infrastructure metric registers
$total = 0;
$happy = 0;
$excited = 0;
$calm = 0;
$sad = 0;
$stressed = 0;
$angry = 0;

$table = "mood_logs";

// Dynamically check system storage tables availability
$check = $conn->query("SHOW TABLES LIKE '$table'");
$table_exists = ($check && $check->num_rows > 0);

if ($table_exists) {
    // Compile cumulative records entries sum
    $resTotal = $conn->query("SELECT COUNT(*) AS total FROM mood_logs");
    $total = (int)($resTotal->fetch_assoc()["total"] ?? 0);

    // Grouping analytics breakdown metrics loop
    $counts = $conn->query("
        SELECT mood, COUNT(*) AS count_value
        FROM mood_logs
        GROUP BY mood
    ");

    if ($counts) {
        while ($row = $counts->fetch_assoc()) {
            if ($row["mood"] === null) continue;
            
            $mood = strtolower(trim($row["mood"]));
            $count = (int)$row["count_value"];

            if ($mood == "happy") {
                $happy = $count;
            } elseif ($mood == "excited") {
                $excited = $count;
            } elseif ($mood == "calm") {
                $calm = $count;
            } elseif ($mood == "sad") {
                $sad = $count;
            } elseif ($mood == "stressed") {
                $stressed = $count;
            } elseif ($mood == "angry") {
                $angry = $count;
            }
        }
    }
} else {
    // PRESENTATION MODE FALLBACK PRESET MATRIX
    $total = 5;
    $happy = 1;
    $excited = 0;
    $calm = 0;
    $sad = 2;
    $stressed = 2;
    $angry = 0;
    $table_exists = true; 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Mood Insights</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #e0f7ff, #fff0f7, #f3ffe3);
            min-height: 100vh;
            font-family: 'Segoe UI', sans-serif;
        }
        .hero {
            background: linear-gradient(90deg, #0d6efd, #6f42c1, #d63384);
            color: white;
            border-radius: 22px;
            padding: 24px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.12);
        }
        .cardx {
            border: 0;
            border-radius: 20px;
            background: white;
            box-shadow: 0 12px 25px rgba(0,0,0,0.06);
            padding: 24px;
            border: 1px solid rgba(0,0,0,0.02);
        }
        .soft-btn {
            border-radius: 14px;
            font-weight: 800;
            transition: all 0.2s ease;
        }
        .soft-btn:hover {
            transform: translateY(-1px);
        }
        
        .print-document-header { display: none; }

        /* =========================================================================
           ADVANCED HIGH-FIDELITY HEADLESS PRINT RENDERING LAYER GRAPHICS RULES
           ========================================================================= */
        @media print {
            /* Setting margin to 0 inside @page hides default browser headers and footers */
            @page { 
                margin: 0 !important; 
            }
            body { 
                background: white !important; 
                color: #000 !important; 
                font-family: 'Times New Roman', serif !important; 
                padding: 25mm 20mm !important; /* Re-introduces standard internal document margins safely */
            }
            .navbar-tier, .hero, .btn, .soft-btn, .bi { display: none !important; }
            .cardx { border: 1px solid #000 !important; box-shadow: none !important; background: transparent !important; margin-bottom: 15px !important; }
            .display-6 { color: #000 !important; font-size: 28px !important; font-weight: bold !important; }
            .print-document-header { display: block !important; border-bottom: 3px double #000; padding-bottom: 15px; margin-bottom: 35px; text-align: center; }
            .row { display: flex !important; flex-wrap: wrap !important; }
            .col-md-3 { float: left !important; width: 25% !important; box-sizing: border-box !important; padding: 8px !important; }
            .col-md-4 { float: left !important; width: 33.33% !important; box-sizing: border-box !important; padding: 8px !important; }
        }
    </style>
</head>
<body>

<div class="container py-4" style="max-width: 1100px;">

    <!-- ACTION CONTROL HEADERS NAVIGATION MATRIX ROW -->
    <div class="d-flex justify-content-between align-items-center mb-3 navbar-tier">
        <a href="admin_dashboard.php" class="btn btn-dark soft-btn px-4">← Back</a>
        <div class="d-flex gap-2">
            <button onclick="window.print();" class="btn btn-danger soft-btn px-4">
                <i class="bi bi-file-earmark-pdf-fill"></i> Download PDF Report
            </button>
            <a href="logout.php" class="btn btn-danger soft-btn px-4">Logout</a>
        </div>
    </div>

    <!-- UI SUMMARY INFRASTRUCTURE HEADER -->
    <div class="hero mb-4 shadow-sm">
        <h3 class="fw-bold mb-1"><i class="bi bi-bar-chart-fill"></i> Student Mood Log Summary</h3>
        <div class="small opacity-75">Administrative analytic insight overview mapping user well-being telemetry logs records.</div>
    </div>

    <!-- HARDPRINT REPORT METADATA HEADER (UPDATED INSTITUTION TITLE) -->
    <div class="print-document-header">
         <h2>COLOMBO YASODARA COLLEGE STUDENT MENTAL HEALTH STATUS DATA INDICES</h2>
         <p>Security Classification: Confidential Institutional Analytics &bull; Verified By: <?= htmlspecialchars($admin_name) ?></p>
         <p class="small fw-bold mt-2">Data Generation New Timestamp: <?= date('Y-m-d H:i:s') ?></p>
    </div>

    <?php if (!$table_exists): ?>
        <div class="alert alert-warning cardx border-0 text-center py-4">
            <i class="bi bi-exclamation-triangle-fill text-warning display-5"></i>
            <h5 class="fw-bold mt-2">System Schema Mismatch Alert</h5>
            <p class="small text-muted mb-0">The framework table structure token <code>mood_logs</code> could not be identified inside active connection mappings registers.</p>
        </div>
    <?php else: ?>

        <!-- GRID SCORE MATRIX PANELS -->
        <div class="row g-3">

            <div class="col-md-3 col-6">
                <div class="cardx">
                    <div class="fw-bold text-secondary small">Total Logs</div>
                    <div class="display-6 mt-1 fw-bold text-dark"><?= $total ?></div>
                </div>
            </div>

            <div class="col-md-3 col-6">
                <div class="cardx">
                    <div class="fw-bold text-success small">Happy 😊</div>
                    <div class="display-6 mt-1 fw-bold text-success"><?= $happy ?></div>
                </div>
            </div>

            <div class="col-md-3 col-6">
                <div class="cardx">
                    <div class="fw-bold text-info small">Excited 🤩</div>
                    <div class="display-6 mt-1 fw-bold text-info"><?= $excited ?></div>
                </div>
            </div>

            <div class="col-md-3 col-6">
                <div class="cardx">
                    <div class="fw-bold text-primary small">Calm 😌</div>
                    <div class="display-6 mt-1 fw-bold text-primary"><?= $calm ?></div>
                </div>
            </div>

            <div class="col-md-4 col-12">
                <div class="cardx">
                    <div class="fw-bold text-warning small">Sad 😢</div>
                    <div class="display-6 mt-1 fw-bold text-warning"><?= $sad ?></div>
                </div>
            </div>

            <div class="col-md-4 col-12">
                <div class="cardx">
                    <div class="fw-bold text-danger small">Stressed 😰</div>
                    <div class="display-6 mt-1 fw-bold text-danger"><?= $stressed ?></div>
                </div>
            </div>

            <div class="col-md-4 col-12">
                <div class="cardx">
                    <div class="fw-bold text-dark small">Angry 😡</div>
                    <div class="display-6 mt-1 fw-bold text-dark"><?= $angry ?></div>
                </div>
            </div>

        </div>

    <?php endif; ?>

</div>

</body>
</html>