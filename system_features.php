<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Suppress starting session if already active globally
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/db.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>System Implementation & Results</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
  <style>
    body { 
        background: #f8f9fa; 
        font-family: 'Segoe UI', sans-serif; 
        padding-bottom: 50px;
    }
    .section-title {
        font-weight: 900;
        color: #1a4314;
        text-align: center;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 30px;
        margin-top: 40px;
    }
    .module-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.06);
        overflow: hidden;
        height: 100%;
        border: 1px solid #eee;
    }
    .module-header {
        padding: 20px;
        text-align: center;
        color: white;
        font-weight: bold;
        text-transform: uppercase;
        font-size: 15px;
        letter-spacing: 0.5px;
    }
    .header-academic { background: #0d6efd; }
    .header-wellness { background: #198754; }
    .header-security { background: #6f42c1; }
    
    .module-icon-container {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: -45px auto 15px auto;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .module-icon-container i { font-size: 35px; }
    .icon-academic { color: #0d6efd; }
    .icon-wellness { color: #198754; }
    .icon-security { color: #6f42c1; }

    .feature-list {
        list-style: none;
        padding: 0 20px 20px 20px;
        margin: 0;
    }
    .feature-list li {
        padding: 12px 0;
        border-bottom: 1px dashed #e9ecef;
        display: flex;
        align-items: center;
        font-weight: 600;
        color: #333;
        font-size: 14px;
    }
    .feature-list li:last-child { border-bottom: none; }
    .feature-list li i {
        margin-right: 12px;
        font-size: 18px;
    }

    /* Results Table Styles */
    .results-container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.06);
        overflow: hidden;
        border: 1px solid #eee;
    }
    .results-header {
        background: #0d6efd;
        color: white;
        padding: 15px 20px;
        font-weight: bold;
        font-size: 18px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .results-row {
        display: flex;
        align-items: center;
        padding: 15px 20px;
        border-bottom: 1px solid #f1f1f1;
        transition: background 0.2s;
    }
    .results-row:hover { background: #f8f9fa; }
    .results-row:last-child { border-bottom: none; }
    
    .feature-name {
        flex-grow: 1;
        font-weight: 600;
        color: #333;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .feature-name i { font-size: 20px; color: #0d6efd; }
    .feature-name.wellness i { color: #198754; }
    .feature-name.security i { color: #6f42c1; }
    
    .check-icon {
        color: #198754;
        font-size: 22px;
    }
  </style>
</head>
<body>

<div class="container py-4" style="max-width: 1100px;">
    
    <!-- SYSTEM OVERVIEW INTRO -->
    <div class="text-center mb-5 mt-3">
        <h2 class="fw-black text-dark mb-3">School Web-Based Management System</h2>
        <p class="text-muted mx-auto" style="max-width: 700px; line-height: 1.6;">
            A comprehensive Student Wellness System that integrates academic administration and student well-being into a web platform. The system enables efficient school management while providing confidential wellness support and early intervention for students.
        </p>
    </div>

    <h3 class="section-title">Implementation Details</h3>

    <!-- MODULE CARDS GRID -->
    <div class="row g-4 mb-5">
        
        <!-- ACADEMIC MODULE -->
        <div class="col-md-4">
            <div class="module-card mt-4">
                <div class="module-header header-academic">Academic Module</div>
                <div class="module-icon-container"><i class="bi bi-mortarboard-fill icon-academic"></i></div>
                <ul class="feature-list">
                    <li><i class="bi bi-people-fill icon-academic"></i> Student Management</li>
                    <li><i class="bi bi-person-badge-fill icon-academic"></i> Teacher Management</li>
                    <li><i class="bi bi-clipboard2-data-fill icon-academic"></i> Examination Management</li>
                    <li><i class="bi bi-megaphone-fill icon-academic"></i> Events & Announcements</li>
                </ul>
            </div>
        </div>

        <!-- WELLNESS MODULE -->
        <div class="col-md-4">
            <div class="module-card mt-4">
                <div class="module-header header-wellness">Student Wellness Module</div>
                <div class="module-icon-container"><i class="bi bi-heart-pulse-fill icon-wellness"></i></div>
                <ul class="feature-list">
                    <li><i class="bi bi-emoji-smile-fill icon-wellness"></i> Mood Tracking</li>
                    <!-- CORRECTED CHAT FEATURE TITLE -->
                    <li><i class="bi bi-chat-square-dots-fill icon-wellness"></i> Anonymous AI/Counselor Chat</li>
                    <li><i class="bi bi-bell-fill icon-wellness"></i> Silent Alert System</li>
                    <li><i class="bi bi-speedometer2 icon-wellness"></i> Counsellor Dashboard</li>
                    <li><i class="bi bi-book-half icon-wellness"></i> Wellness Resources</li>
                </ul>
            </div>
        </div>

        <!-- SECURITY MODULE -->
        <div class="col-md-4">
            <div class="module-card mt-4">
                <div class="module-header header-security">Security</div>
                <div class="module-icon-container"><i class="bi bi-shield-lock-fill icon-security"></i></div>
                <ul class="feature-list">
                    <li><i class="bi bi-person-check-fill icon-security"></i> User Authentication</li>
                    <li><i class="bi bi-diagram-3-fill icon-security"></i> Role-Based Access Control (RBAC)</li>
                    <li><i class="bi bi-key-fill icon-security"></i> Password Encryption (bcrypt)</li>
                    <li><i class="bi bi-check2-square icon-security"></i> Input Validation</li>
                </ul>
            </div>
        </div>

    </div>

    <h3 class="section-title">Results</h3>

    <!-- IMPLEMENTED FEATURES RESULTS TABLE -->
    <div class="results-container">
        <div class="results-header">
            <i class="bi bi-gear-fill"></i> Implemented Features
        </div>
        
        <div class="row m-0">
            <!-- Left Column: Academic & Core Features -->
            <div class="col-md-6 p-0 border-end">
                <div class="results-row">
                    <div class="feature-name"><i class="bi bi-person-fill-lock"></i> User Registration & Login System</div>
                    <i class="bi bi-check-circle-fill check-icon"></i>
                </div>
                <div class="results-row">
                    <div class="feature-name"><i class="bi bi-people-fill"></i> Student Management</div>
                    <i class="bi bi-check-circle-fill check-icon"></i>
                </div>
                <div class="results-row">
                    <div class="feature-name"><i class="bi bi-person-badge-fill"></i> Teacher Management</div>
                    <i class="bi bi-check-circle-fill check-icon"></i>
                </div>
                <div class="results-row">
                    <div class="feature-name"><i class="bi bi-clipboard2-data-fill"></i> Examination Management</div>
                    <i class="bi bi-check-circle-fill check-icon"></i>
                </div>
                <div class="results-row">
                    <div class="feature-name"><i class="bi bi-megaphone-fill"></i> Events & Announcements</div>
                    <i class="bi bi-check-circle-fill check-icon"></i>
                </div>
            </div>

            <!-- Right Column: Wellness & Security Features -->
            <div class="col-md-6 p-0">
                <div class="results-row">
                    <div class="feature-name wellness"><i class="bi bi-emoji-smile-fill"></i> Mood Tracking System</div>
                    <i class="bi bi-check-circle-fill check-icon"></i>
                </div>
                <div class="results-row">
                    <!-- CORRECTED CHAT FEATURE TITLE -->
                    <div class="feature-name wellness"><i class="bi bi-chat-square-dots-fill"></i> Anonymous AI/Counselor Chat</div>
                    <i class="bi bi-check-circle-fill check-icon"></i>
                </div>
                <div class="results-row">
                    <div class="feature-name wellness"><i class="bi bi-bell-fill"></i> Silent Alert System</div>
                    <i class="bi bi-check-circle-fill check-icon"></i>
                </div>
                <div class="results-row">
                    <div class="feature-name wellness"><i class="bi bi-speedometer2"></i> Counsellor Dashboard</div>
                    <i class="bi bi-check-circle-fill check-icon"></i>
                </div>
                <div class="results-row">
                    <div class="feature-name security"><i class="bi bi-diagram-3-fill"></i> Role-Based Access Control (RBAC)</div>
                    <i class="bi bi-check-circle-fill check-icon"></i>
                </div>
            </div>
        </div>
    </div>

</div>

</body>
</html>