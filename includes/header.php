<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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
</head>
<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="index.php" class="nav-link">Home</a>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <!-- Current User -->
                <li class="nav-item">
                    <span class="nav-link">Welcome, <?= $_SESSION['full_name'] ?? 'User' ?></span>
                </li>
                <!-- Profile -->
                <li class="nav-item">
                    <a href="profile.php" class="nav-link"><i class="fas fa-user"></i> Profile</a>
                </li>
                <!-- Logout -->
                <li class="nav-item">
                    <a href="../logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </li>
            </ul>
        </nav>

        <!-- Sidebar -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="index.php" class="brand-link">
                <span class="brand-text font-weight-light">AI Grading System</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                        <!-- Dashboard -->
                        <li class="nav-item">
                            <a href="dashboard.php" class="nav-link">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>

                        <!-- Admin Links -->
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <li class="nav-item">
                                <a href="manage_users.php" class="nav-link">
                                    <i class="nav-icon fas fa-users"></i>
                                    <p>Manage Users</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="manage_classes.php" class="nav-link">
                                    <i class="nav-icon fas fa-school"></i>
                                    <p>Manage Classes</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="manage_sections.php" class="nav-link">
                                    <i class="nav-icon fas fa-layer-group"></i>
                                    <p>Manage Sections</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="manage_subjects.php" class="nav-link">
                                    <i class="nav-icon fas fa-book"></i>
                                    <p>Manage Subjects</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="manage_sessions.php" class="nav-link">
                                    <i class="nav-icon fas fa-calendar-alt"></i>
                                    <p>Manage Sessions</p>
                                </a>
                            </li>
                        <?php endif; ?>

                        <!-- Teacher Links -->
                        <?php if ($_SESSION['role'] === 'teacher'): ?>
                            <li class="nav-item">
                                <a href="create_assignment.php" class="nav-link">
                                    <i class="nav-icon fas fa-tasks"></i>
                                    <p>Create Assignment</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="grade_submissions.php" class="nav-link">
                                    <i class="nav-icon fas fa-check-circle"></i>
                                    <p>Grade Submissions</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="view_reports.php" class="nav-link">
                                    <i class="nav-icon fas fa-chart-bar"></i>
                                    <p>View Reports</p>
                                </a>
                            </li>
                        <?php endif; ?>

                        <!-- Student Links -->
                        <?php if ($_SESSION['role'] === 'student'): ?>
                            <li class="nav-item">
                                <a href="view_assignments.php" class="nav-link">
                                    <i class="nav-icon fas fa-tasks"></i>
                                    <p>View Assignments</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="view_grades.php" class="nav-link">
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
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1><?= $pageTitle ?? 'Dashboard' ?></h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                                <li class="breadcrumb-item active"><?= $pageTitle ?? 'Dashboard' ?></li>
                            </ol>
                        </div>
                    </div>
                </div>
            </section>