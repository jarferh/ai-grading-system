<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if not authenticated
if (!isset($_SESSION['username'])) {
    header('Location: ../login.php');
    exit();
}

// Database connection
try {
    $pdo = new PDO('mysql:host=localhost;dbname=ai_grading_system', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Get user data from database
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$_SESSION['username']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Update session with fresh user data
if ($user) {
    $_SESSION['id'] = $user['id'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['email'] = $user['email'];
}

// Get user role for avatar color
$roleColors = [
    'admin' => 'bg-danger',
    'teacher' => 'bg-success',
    'student' => 'bg-info'
];
$userRoleColor = $roleColors[$_SESSION['role']] ?? 'bg-secondary';

// Get first letter of user's name for avatar
$userInitial = strtoupper(substr($_SESSION['full_name'] ?? 'U', 0, 1));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'AI Grading System' ?></title>
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Custom CSS -->
    <!-- Add to your header.php -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap4.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>

    <style>
        .main-header {
            border-bottom: 1px solid #dee2e6;
            box-shadow: 0 0 1rem rgba(0, 0, 0, .075);
        }

        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            margin-right: 10px;
        }

        .brand-link {
            border-bottom: 1px solid #4b545c !important;
        }

        .brand-link .brand-image {
            margin-left: 0.8rem;
            margin-right: 0.5rem;
            margin-top: -3px;
        }

        .sidebar-dark-primary {
            background: #343a40;
        }

        .nav-sidebar .nav-item .nav-link {
            margin-bottom: 0.2rem;
        }

        .nav-sidebar .nav-item .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .user-panel {
            border-bottom: 1px solid #4b545c;
            padding: 1rem;
        }

        .navbar-badge {
            font-size: 0.6rem;
            font-weight: 300;
            padding: 2px 4px;
            right: 5px;
            top: 9px;
        }

        .dropdown-menu-lg {
            min-width: 280px;
            max-width: 300px;
        }

        .dropdown-header {
            padding: 1rem;
            text-align: center;
        }
    </style>
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                        <i class="fas fa-bars"></i>
                    </a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="dashboard.php" class="nav-link">
                        <i class="fas fa-home"></i> Home
                    </a>
                </li>
                <?php if ($_SESSION['role'] === 'teacher'): ?>
                    <li class="nav-item d-none d-sm-inline-block">
                        <a href="create_assignment.php" class="nav-link">
                            <i class="fas fa-plus"></i> New Assignment
                        </a>
                    </li>
                <?php endif; ?>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <!-- User Dropdown Menu -->
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#" role="button">
                        <div class="user-avatar <?= $userRoleColor ?>"><?= $userInitial ?></div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <div class="dropdown-header">
                            <div class="user-avatar <?= $userRoleColor ?> mx-auto mb-2" style="width: 50px; height: 50px;">
                                <?= $userInitial ?>
                            </div>
                            <strong><?= htmlspecialchars($_SESSION['full_name']) ?></strong>
                            <br>
                            <small class="text-muted"><?= ucfirst($_SESSION['role']) ?></small>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a href="profile.php" class="dropdown-item">
                            <i class="fas fa-user mr-2"></i> My Profile
                        </a>
                        <!-- <a href="settings.php" class="dropdown-item">
                            <i class="fas fa-cog mr-2"></i> Settings
                        </a> -->
                        <div class="dropdown-divider"></div>
                        <a href="../logout.php" class="dropdown-item text-danger">
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                        </a>
                    </div>
                </li>
            </ul>
        </nav>

        <!-- Main Sidebar -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="dashboard.php" class="brand-link">
                <i class="fas fa-graduation-cap brand-image"></i>
                <span class="brand-text font-weight-light">AI Grading System</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar user panel -->
                <div class="user-panel mt-3 pb-3 mb-3">
                    <div class="d-flex">
                        <div class="user-avatar <?= $userRoleColor ?>"><?= $userInitial ?></div>
                        <div class="info">
                            <a href="profile.php" class="d-block">
                                <?= htmlspecialchars($_SESSION['full_name']) ?>
                                <small class="d-block text-muted"><?= ucfirst($_SESSION['role']) ?></small>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                        <!-- Dashboard -->
                        <li class="nav-item">
                            <a href="dashboard.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>

                        <!-- Admin Links -->
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <li class="nav-header">ADMINISTRATION</li>
                            <li class="nav-item">
                                <a href="manage_users.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'manage_users.php' ? 'active' : '' ?>">
                                    <i class="nav-icon fas fa-users"></i>
                                    <p>Manage Users</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="manage_classes.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'manage_classes.php' ? 'active' : '' ?>">
                                    <i class="nav-icon fas fa-school"></i>
                                    <p>Manage Classes</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="manage_sections.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'manage_sections.php' ? 'active' : '' ?>">
                                    <i class="nav-icon fas fa-layer-group"></i>
                                    <p>Manage Sections</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="manage_subjects.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'manage_subjects.php' ? 'active' : '' ?>">
                                    <i class="nav-icon fas fa-book"></i>
                                    <p>Manage Subjects</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="manage_sessions.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'manage_sessions.php' ? 'active' : '' ?>">
                                    <i class="nav-icon fas fa-calendar-alt"></i>
                                    <p>Manage Sessions</p>
                                </a>
                            </li>
                        <?php endif; ?>

                        <!-- Teacher Links -->
                        <?php if ($_SESSION['role'] === 'teacher'): ?>
                            <li class="nav-header">TEACHING</li>
                            <li class="nav-item">
                                <a href="create_assignment.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'create_assignment.php' ? 'active' : '' ?>">
                                    <i class="nav-icon fas fa-tasks"></i>
                                    <p>Create Assignment</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="grade_submissions.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'grade_submissions.php' ? 'active' : '' ?>">
                                    <i class="nav-icon fas fa-check-circle"></i>
                                    <p>Grade Submissions</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="view_reports.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'view_reports.php' ? 'active' : '' ?>">
                                    <i class="nav-icon fas fa-chart-bar"></i>
                                    <p>View Reports</p>
                                </a>
                            </li>
                        <?php endif; ?>

                        <!-- Student Links -->
                        <?php if ($_SESSION['role'] === 'student'): ?>
                            <li class="nav-header">LEARNING</li>
                            <li class="nav-item">
                                <a href="view_assignments.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'view_assignments.php' ? 'active' : '' ?>">
                                    <i class="nav-icon fas fa-tasks"></i>
                                    <p>View Assignments</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="view_grades.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'view_grades.php' ? 'active' : '' ?>">
                                    <i class="nav-icon fas fa-check-circle"></i>
                                    <p>View Grades</p>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <!-- Content Header -->
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1><?= $pageTitle ?? 'Dashboard' ?></h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                                <li class="breadcrumb-item active"><?= $pageTitle ?? 'Dashboard' ?></li>
                            </ol>
                        </div>
                    </div>
                </div>
            </section>