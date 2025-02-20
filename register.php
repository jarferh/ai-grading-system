<?php
session_start();
include 'includes/db.php';

$current_datetime = '2025-02-20 22:46:45';
$current_user = 'jarferh';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate inputs
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $role = $_POST['role'];

        // Validation checks
        if (empty($username) || empty($password) || empty($full_name) || empty($email)) {
            throw new Exception("All fields are required.");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format.");
        }

        if (strlen($password) < 6) {
            throw new Exception("Password must be at least 6 characters long.");
        }

        // Check if username exists
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("Username already exists.");
        }

        // Check if email exists
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("Email already registered.");
        }

        // Insert new user
        $stmt = $conn->prepare("
            INSERT INTO users (username, password, role, full_name, email, created_at) 
            VALUES (:username, :password, :role, :full_name, :email, :created_at)
        ");
        
        $stmt->execute([
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role,
            'full_name' => $full_name,
            'email' => $email,
            'created_at' => $current_datetime
        ]);

        $_SESSION['success'] = "Registration successful! Please login.";
        header("Location: login.php");
        exit();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - AI Grading System</title>
    <link rel="stylesheet" href="assets/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --primary-color: #007bff;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --error-color: #dc3545;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px 0;
        }

        .register-box {
            width: 450px;
            margin: 0 auto;
        }

        .register-logo {
            text-align: center;
            margin-bottom: 25px;
        }

        .register-logo img {
            width: 80px;
            height: auto;
            margin-bottom: 15px;
        }

        .register-logo b {
            color: var(--primary-color);
            font-size: 24px;
            display: block;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.1);
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .register-card-body {
            padding: 40px;
            border-radius: 15px;
        }

        .input-group {
            margin-bottom: 25px;
            position: relative;
        }

        .input-group-prepend {
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            z-index: 10;
        }

        .input-group-text {
            background-color: transparent;
            border: none;
            color: var(--secondary-color);
            padding-left: 15px;
        }

        .form-control {
            padding: 12px 12px 12px 40px;
            height: auto;
            border-radius: 8px;
        }

        .form-control:focus {
            box-shadow: none;
            border-color: var(--primary-color);
        }

        select.form-control {
            padding-left: 40px;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%236c757d' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: calc(100% - 15px) center;
        }

        .btn-primary {
            padding: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            background-color: var(--primary-color);
            border: none;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
        }

        .error-message {
            color: var(--error-color);
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 5px;
            background-color: rgba(220, 53, 69, 0.1);
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
        }

        .login-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 576px) {
            .register-box {
                width: 90%;
                margin: 20px;
            }
            
            .register-card-body {
                padding: 20px;
            }
        }
    </style>
</head>
<body class="hold-transition register-page">
    <div class="register-box">
        <div class="register-logo">
            <!-- <img src="assets/img/logo.png" alt="Logo"> -->
            <b>AI Grading System</b>
            <small class="d-block text-muted">Create New Account</small>
        </div>
        
        <div class="card">
            <div class="card-body register-card-body">
                <?php if (isset($error)): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-user"></i>
                            </span>
                        </div>
                        <input type="text" name="username" class="form-control" 
                               placeholder="Username" required 
                               value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
                    </div>

                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                        </div>
                        <input type="password" name="password" class="form-control" 
                               placeholder="Password" required>
                    </div>

                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-user-circle"></i>
                            </span>
                        </div>
                        <input type="text" name="full_name" class="form-control" 
                               placeholder="Full Name" required
                               value="<?= isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : '' ?>">
                    </div>

                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-envelope"></i>
                            </span>
                        </div>
                        <input type="email" name="email" class="form-control" 
                               placeholder="Email" required
                               value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                    </div>

                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-user-tag"></i>
                            </span>
                        </div>
                        <select name="role" class="form-control" required>
                            <option value="" disabled selected>Select Role</option>
                            <option value="student" <?= isset($_POST['role']) && $_POST['role'] === 'student' ? 'selected' : '' ?>>Student</option>
                            <option value="teacher" <?= isset($_POST['role']) && $_POST['role'] === 'teacher' ? 'selected' : '' ?>>Teacher</option>
                            <option value="admin" <?= isset($_POST['role']) && $_POST['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-user-plus mr-2"></i>Create Account
                    </button>
                </form>

                <div class="login-link">
                    <a href="login.php">
                        <i class="fas fa-sign-in-alt mr-1"></i>I already have an account
                    </a>
                </div>
            </div>
        </div>

        <div class="text-center mt-3">
            <small class="text-muted">
                <?= htmlspecialchars($current_datetime) ?> UTC
            </small>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Add animation to error message
            $('.error-message').hide().fadeIn(500);
            
            // Add focus effect to input fields
            $('.form-control').focus(function() {
                $(this).parent('.input-group').addClass('focused');
            }).blur(function() {
                $(this).parent('.input-group').removeClass('focused');
            });

            // Password strength indicator
            $('input[name="password"]').on('input', function() {
                var password = $(this).val();
                var strength = 0;
                
                if (password.length >= 6) strength++;
                if (password.match(/[A-Z]/)) strength++;
                if (password.match(/[0-9]/)) strength++;
                if (password.match(/[^A-Za-z0-9]/)) strength++;

                var strengthClass = '';
                switch(strength) {
                    case 0:
                    case 1:
                        strengthClass = 'bg-danger';
                        break;
                    case 2:
                        strengthClass = 'bg-warning';
                        break;
                    case 3:
                        strengthClass = 'bg-info';
                        break;
                    case 4:
                        strengthClass = 'bg-success';
                        break;
                }

                $(this).css('border-bottom-width', '3px');
                $(this).css('border-bottom-style', 'solid');
                $(this).css('border-bottom-color', getComputedStyle(document.documentElement)
                    .getPropertyValue('--' + strengthClass.replace('bg-', '') + '-color'));
            });
        });
    </script>
</body>
</html>