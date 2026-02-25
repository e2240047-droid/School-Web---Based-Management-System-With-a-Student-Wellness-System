<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Welcome to Y School</title>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
	<link rel="stylesheet" href="css/style.css">
	<link rel="icon" href="logo.png">
</head>
<body class="body-home">
   <div class="bg-light">

  <div class="container">

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mt-3 rounded">
      <div class="container-fluid">

        <a class="navbar-brand d-flex align-items-center" href="#">
          <img src="logo.png" width="35" class="me-2">
          School Wellness System
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
          data-bs-target="#navbarSupportedContent">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">

          <ul class="navbar-nav me-auto mb-2 mb-lg-0">

            <li class="nav-item">
              <a class="nav-link active" href="#">Home</a>
            </li>

            <li class="nav-item">
              <a class="nav-link" href="#about">About</a>
            </li>

            <li class="nav-item">
              <a class="nav-link" href="#features">Features</a>
            </li>

            <li class="nav-item">
              <a class="nav-link" href="#contact">Contact</a>
            </li>

          </ul>

          <div>
            <a href="login.php" class="btn btn-light btn-sm me-2">Login</a>
            <a href="register.php" class="btn btn-warning btn-sm">Register</a>
          </div>

        </div>
      </div>
    </nav>


    <!-- WELCOME SECTION -->
    <section class="text-center p-5 mt-4 bg-white rounded shadow-sm">

      <img src="logo.png" width="90" class="mb-3">

      <h2>School Web-Based Management System</h2>
      <p class="text-muted">With Student Wellness System</p>

      <p>
        A secure platform for managing student records, exam results,
        announcements, and supporting student emotional well-being.
      </p>

      <a href="login.php" class="btn btn-primary mt-3">Get Started</a>

    </section>


    <!-- FEATURES SECTION -->
    <section id="features" class="mt-5">

      <h3 class="text-center mb-4">System Features</h3>

      <div class="row text-center">

        <div class="col-md-4 mb-3">
          <div class="card p-3 shadow-sm">
            <h5>Student Management</h5>
            <p>Manage student and staff records securely.</p>
          </div>
        </div>

        <div class="col-md-4 mb-3">
          <div class="card p-3 shadow-sm">
            <h5>Exam Results</h5>
            <p>Publish and view exam results easily.</p>
          </div>
        </div>

        <div class="col-md-4 mb-3">
          <div class="card p-3 shadow-sm">
            <h5>Announcements</h5>
            <p>Digital notice board for school updates.</p>
          </div>
        </div>

      </div>
    </section>


    <!-- STUDENT WELLNESS SECTION -->
    <section class="mt-5">

      <h3 class="text-center mb-4">Student Wellness System</h3>

      <div class="row text-center">

        <div class="col-md-4 mb-3">
          <div class="card p-3 shadow-sm">
            <h5>Mood Tracker</h5>
            <p>Students can privately track daily emotions.</p>
          </div>
        </div>

        <div class="col-md-4 mb-3">
          <div class="card p-3 shadow-sm">
            <h5>Anonymous Chat</h5>
            <p>Safe moderated peer communication.</p>
          </div>
        </div>

        <div class="col-md-4 mb-3">
          <div class="card p-3 shadow-sm">
            <h5>Emergency Alert</h5>
            <p>Silent emotional help request system.</p>
          </div>
        </div>

      </div>

    </section>


    <!-- ABOUT SECTION -->
    <section id="about" class="mt-5">

      <div class="card shadow-sm p-4">
        <h4>About the System</h4>
        <p>
          This system integrates school administration and student wellness
          into one secure web-based platform. It promotes efficient management
          while ensuring emotional safety and privacy for students.
        </p>
      </div>

    </section>


    <!-- CONTACT SECTION -->
    <section id="contact" class="mt-5 mb-5">

      <div class="card p-4 shadow-sm">

        <h4>Contact Us</h4>

        <form>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" class="form-control">
          </div>

          <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" class="form-control">
          </div>

          <div class="mb-3">
            <label class="form-label">Message</label>
            <textarea class="form-control" rows="4"></textarea>
          </div>

          <button type="submit" class="btn btn-primary">Send Message</button>
        </form>

      </div>

    </section>


    <!-- FOOTER -->
    <footer class="text-center p-3 bg-primary text-white rounded">
      © 2026 School Web-Based Management System | All Rights Reserved
    </footer>

  </div>
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>	
</body>
</html>