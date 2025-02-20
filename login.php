<?php
session_start();
include 'includes/db.php';

$current_datetime = '2025-02-20 22:44:00';
$current_user = 'jarferh';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        if (empty($username) || empty($password)) {
            throw new Exception("Please enter both username and password.");
        }

        // Modified query to get all user details
        $stmt = $conn->prepare("SELECT u.*, c.name as class_name, s.name as section_name 
                               FROM users u 
                               LEFT JOIN classes c ON u.class_id = c.id 
                               LEFT JOIN sections s ON u.section_id = s.id 
                               WHERE u.username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Store all relevant user details in session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['class_id'] = $user['class_id'];
            $_SESSION['section_id'] = $user['section_id'];
            $_SESSION['class_name'] = $user['class_name'];
            $_SESSION['section_name'] = $user['section_name'];
            
            // Log login time and details
            $log_stmt = $conn->prepare("INSERT INTO user_activity_logs (user_id, activity_type, activity_details, timestamp) 
                                      VALUES (:user_id, 'login', :details, :current_datetime)");
            $details = "User logged in from " . $_SERVER['REMOTE_ADDR'];
            $log_stmt->execute([
                'user_id' => $user['id'],
                'details' => $details,
                'current_datetime' => $current_datetime
            ]);

            // Redirect based on role
            switch($user['role']) {
                case 'admin':
                    header("Location: admin/dashboard.php");
                    break;
                case 'teacher':
                    header("Location: teacher/dashboard.php");
                    break;
                case 'student':
                    header("Location: student/dashboard.php");
                    break;
                default:
                    header("Location: index.php");
            }
            exit();
        } else {
            throw new Exception("Invalid username or password.");
        }
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
    <title>Login - AI Grading System</title>
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
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-box {
            width: 400px;
            margin: 0 auto;
        }

        .login-logo {
            text-align: center;
            margin-bottom: 25px;
        }

        .login-logo img {
            width: 80px;
            height: auto;
            margin-bottom: 15px;
        }

        .login-logo b {
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

        .login-card-body {
            padding: 40px;
            border-radius: 15px;
        }

        .login-box-msg {
            color: var(--secondary-color);
            text-align: center;
            margin-bottom: 30px;
            font-size: 16px;
        }

        .input-group {
            margin-bottom: 25px;
        }

        .input-group-text {
            background-color: transparent;
            border-left: none;
            color: var(--secondary-color);
        }

        .form-control {
            border-right: none;
            padding: 12px;
            height: auto;
        }

        .form-control:focus {
            box-shadow: none;
            border-color: var(--primary-color);
        }

        .btn-primary {
            padding: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            background-color: var(--primary-color);
            border: none;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
        }

        .error-message {
            color: var(--error-color);
            text-align: center;
            margin-top: 20px;
            padding: 10px;
            border-radius: 5px;
            background-color: rgba(220, 53, 69, 0.1);
        }

        .footer-links {
            text-align: center;
            margin-top: 20px;
        }

        .footer-links a {
            color: var(--secondary-color);
            text-decoration: none;
            margin: 0 10px;
            font-size: 14px;
        }

        .footer-links a:hover {
            color: var(--primary-color);
        }

        @media (max-width: 576px) {
            .login-box {
                width: 90%;
                margin: 20px;
            }
            
            .login-card-body {
                padding: 20px;
            }
        }
    </style>
</head>
<body class="hold-transition login-page">
    <div class="login-box">
        <div class="login-logo">
            <!-- <img src="assets/img/logo.png" alt="Logo"> -->
            <b>AI Grading System</b>
            <!-- <small class="d-block text-muted">Intelligent Assessment Platform</small> -->
        </div>
        
        <div class="card">
            <div class="card-body login-card-body">
                <p class="login-box-msg">Sign in to your account</p>
                
                <?php if (isset($error)): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" class="mt-4">
                    <div class="input-group">
                        <input type="text" name="username" class="form-control" 
                               placeholder="Username" required 
                               value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-user"></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="input-group">
                        <input type="password" name="password" class="form-control" 
                               placeholder="Password" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-sign-in-alt mr-2"></i>Sign In
                            </button>
                        </div>
                    </div>
                </form>

                <div class="footer-links">
                    <a href="#" onclick="alert('Please contact your administrator for password reset.')">
                        <i class="fas fa-key mr-1"></i>Forgot Password?
                    </a>
                    <a href="#" onclick="alert('Please contact support for assistance.')">
                        <i class="fas fa-question-circle mr-1"></i>Need Help?
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
        });
    </script>
</body>
</html>