<!DOCTYPE html>
<html>
<head>
    <title>Login - School Wellness System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container">

    <div class="row justify-content-center align-items-center vh-100">
        <div class="col-md-5">

            <div class="card shadow p-4">

                <div class="text-center mb-3">
                    <img src="logo.png" width="70">
                    <h4 class="mt-2">Login</h4>
                    <p class="text-muted">School Web-Based Management System</p>
                </div>

                <form action="login_process.php" method="POST">

                    <!-- Email -->
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>

                    <!-- Role Selection -->
                    <div class="mb-3">
                        <label class="form-label">Login As</label>
                        <select name="role" class="form-select" required>
                            <option value="">Select Role</option>
                            <option value="student">Student</option>
                            <option value="teacher">Teacher</option>
                            <option value="counsellor">Counsellor</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>

                    <!-- Login Button -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>

                </form>

                <div class="text-center mt-3">
                   <a href="forgot_password.php">Forgot Password?</a>
                </div>

                <div class="text-center mt-2">
                    <small>Don't have an account? <a href="register.php">Register</a></small>
                </div>

            </div>

        </div>
    </div>

</div>

</body>
</html>