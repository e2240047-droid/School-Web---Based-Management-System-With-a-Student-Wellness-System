<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Welcome to Y School</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/style.css?v=10">
  <link rel="icon" href="logo.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="body-home">

  <div class="bg-shape bg-shape-1"></div>
  <div class="bg-shape bg-shape-2"></div>
  <div class="bg-shape bg-shape-3"></div>

  <div class="container py-4">

    <nav class="navbar navbar-expand-lg custom-navbar shadow-lg rounded-4 px-3 py-2">
      <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center fw-bold fs-4" href="#">
          <img src="logo.png" width="42" class="me-2 rounded-circle bg-white p-1" alt="Logo">
          Yasodara College
        </a>

        <button class="navbar-toggler border-0 shadow-none" type="button"
          data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
            <li class="nav-item"><a class="nav-link active" href="#">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
            <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
            <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
          </ul>

          <div class="d-flex gap-2">
            <a href="login.php" class="btn btn-light fw-semibold px-4">Login</a>
            <a href="register.php" class="btn btn-warning fw-semibold px-4">Register</a>
          </div>
        </div>
      </div>
    </nav>

    <section class="hero-section mt-4 rounded-4 shadow-lg overflow-hidden">
      <div class="row g-0 align-items-center">
        <div class="col-lg-7 p-5">
          <h1 class="display-4 fw-bold text-white mb-3">
            School Web-Based Management System
            <span class="text-highlight d-block">With A Student Wellness System</span>
          </h1>

          <p class="lead text-white-50 mb-4">
            Manage students, staff, exams, events, results, announcements,
            mood tracking, anonymous support, and counsellor guidance in one secure platform.
          </p>

          <div class="d-flex flex-wrap gap-3">
            <a href="login.php" class="btn btn-light btn-lg rounded-pill px-4 fw-semibold">Get Started</a>
            <a href="#features" class="btn btn-outline-light btn-lg rounded-pill px-4 fw-semibold">Explore Features</a>
          </div>

          <div class="hero-stats row g-3 mt-4">
            <div class="col-sm-4">
              <div class="stat-card">
                <h4>4 Roles</h4>
                <p>Student, Teacher, Counsellor, Admin</p>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="stat-card">
                <h4>24/7</h4>
                <p>Wellness and academic access</p>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="stat-card">
                <h4>Safe</h4>
                <p>Private support and alerts</p>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-5 p-4">
          <div class="hero-panel">
            <div class="hero-logo-wrap mb-3">
              <img src="logo.png" alt="School Logo" class="hero-logo">
            </div>

            <div class="mini-feature-list">
              <div class="mini-item"><i class="bi bi-check-circle-fill"></i> Secure login & role-based access</div>
              <div class="mini-item"><i class="bi bi-check-circle-fill"></i> Exam results & announcements</div>
              <div class="mini-item"><i class="bi bi-check-circle-fill"></i> Mood tracking & wellness tools</div>
              <div class="mini-item"><i class="bi bi-check-circle-fill"></i> Anonymous peer support chat</div>
              <div class="mini-item"><i class="bi bi-check-circle-fill"></i> Silent alert for emergencies</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section id="features" class="mt-5">
      <div class="text-center mb-5">
        <h2 class="section-title">System Features</h2>
        <p class="section-subtitle">Click any feature to log in and continue</p>
      </div>

      <div class="row g-4">

        <div class="col-md-6 col-lg-4">
          <a href="login.php" class="feature-link">
            <div class="feature-card feature-card-blue h-100">
              <div class="feature-top">
                <div class="feature-icon"><i class="bi bi-person-badge-fill"></i></div>
                <span class="feature-tag">Academic</span>
              </div>
              <h4>Student & Staff Management</h4>
              <p>Organize student records, staff details, and school administration efficiently.</p>
            </div>
          </a>
        </div>

        <div class="col-md-6 col-lg-4">
          <a href="login.php" class="feature-link">
            <div class="feature-card feature-card-purple h-100">
              <div class="feature-top">
                <div class="feature-icon"><i class="bi bi-bar-chart-fill"></i></div>
                <span class="feature-tag">Results</span>
              </div>
              <h4>Exam Results</h4>
              <p>Publish and view results online with a simple and student-friendly experience.</p>
            </div>
          </a>
        </div>

        <div class="col-md-6 col-lg-4">
          <a href="login.php" class="feature-link">
            <div class="feature-card feature-card-orange h-100">
              <div class="feature-top">
                <div class="feature-icon"><i class="bi bi-megaphone-fill"></i></div>
                <span class="feature-tag">Updates</span>
              </div>
              <h4>Announcements & Events</h4>
              <p>Share digital notices, school events, and real-time updates across roles.</p>
            </div>
          </a>
        </div>

        <div class="col-md-6 col-lg-4">
          <a href="login.php" class="feature-link">
            <div class="feature-card feature-card-pink h-100">
              <div class="feature-top">
                <div class="feature-icon"><i class="bi bi-emoji-smile-fill"></i></div>
                <span class="feature-tag">Wellness</span>
              </div>
              <h4>Mood Tracking</h4>
              <p>Support students with private emotional check-ins and wellness awareness.</p>
            </div>
          </a>
        </div>

        <div class="col-md-6 col-lg-4">
          <a href="login.php" class="feature-link">
            <div class="feature-card feature-card-green h-100">
              <div class="feature-top">
                <div class="feature-icon"><i class="bi bi-chat-dots-fill"></i></div>
                <span class="feature-tag">Support</span>
              </div>
              <h4>Anonymous Peer Chat</h4>
              <p>Encourage safe peer support with counsellor supervision and misuse reporting.</p>
            </div>
          </a>
        </div>

        <div class="col-md-6 col-lg-4">
          <a href="login.php" class="feature-link">
            <div class="feature-card feature-card-red h-100">
              <div class="feature-top">
                <div class="feature-icon"><i class="bi bi-bell-fill"></i></div>
                <span class="feature-tag">Emergency</span>
              </div>
              <h4>Silent Alert System</h4>
              <p>Allow discreet emotional emergency alerts and faster counsellor intervention.</p>
            </div>
          </a>
        </div>

      </div>
    </section>

    <section id="about" class="mt-5">
      <div class="about-card p-5 rounded-4 shadow-lg">
        <div class="row align-items-center g-4">
          <div class="col-lg-8">
            <span class="section-chip mb-3">About the System</span>
            <h2 class="fw-bold mb-3">Academic Management + Student Wellness in One Platform</h2>
            <p class="about-text mb-0">
              This project improves school administration while supporting students’ emotional well-being.
              It combines secure login, role-based access, records management, results, announcements,
              mood tracking, anonymous support, counsellor monitoring, and early intervention tools.
            </p>
          </div>

          <div class="col-lg-4 text-center">
            <div class="about-icon-box">
              <i class="bi bi-shield-heart-fill about-icon"></i>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section id="contact" class="mt-5 mb-5">
      <div class="contact-card p-5 rounded-4 shadow-lg">
        <div class="text-center mb-4">
          <span class="section-chip mb-3">Contact Us</span>
          <h2 class="fw-bold">Get In Touch</h2>
          <p class="text-muted">Have questions about the system? Send us a message.</p>
        </div>

        <form>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label fw-semibold">Your Name</label>
              <input type="text" class="form-control custom-input" placeholder="Enter your name">
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label fw-semibold">Your Email</label>
              <input type="email" class="form-control custom-input" placeholder="Enter your email">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Message</label>
            <textarea class="form-control custom-input" rows="5" placeholder="Write your message here"></textarea>
          </div>

          <div class="text-center">
            <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5">
              <i class="bi bi-send-fill me-2"></i>Send Message
            </button>
          </div>
        </form>
      </div>
    </section>

    <footer class="custom-footer text-center py-4 rounded-4 mb-3">
      <p class="mb-1 fw-semibold">© 2026 School Web-Based Management System</p>
      <small>Built for safer, smarter, and more supportive schools.</small>
    </footer>

  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>