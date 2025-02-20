<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';

requireAdmin();

$current_datetime = '2025-02-20 21:19:44';
$current_user = 'jarferh';

// Fetch basic statistics
$stats = [
    'users' => [
        'total' => $conn->query("SELECT COUNT(*) FROM users")->fetchColumn(),
        'teachers' => $conn->query("SELECT COUNT(*) FROM users WHERE role = 'teacher'")->fetchColumn(),
        'students' => $conn->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn(),
        'admins' => $conn->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn()
    ],
    'classes' => $conn->query("SELECT COUNT(*) FROM classes")->fetchColumn(),
    'subjects' => $conn->query("SELECT COUNT(*) FROM subjects")->fetchColumn(),
    'sessions' => $conn->query("SELECT COUNT(*) FROM sessions")->fetchColumn(),
    'assignments' => $conn->query("SELECT COUNT(*) FROM assignments")->fetchColumn(),
    'submissions' => $conn->query("SELECT COUNT(*) FROM submissions")->fetchColumn()
];

// Fetch recent activities
$recent_users = $conn->query("
    SELECT id, username, full_name, role, email 
    FROM users 
    ORDER BY id DESC LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Fetch submission statistics
$submission_stats = $conn->query("
    SELECT 
        COUNT(*) as total_submissions,
        COUNT(g.id) as graded_submissions,
        COALESCE(AVG(g.score), 0) as average_score
    FROM submissions s
    LEFT JOIN grades g ON s.id = g.submission_id
")->fetch(PDO::FETCH_ASSOC);

// Set page title
$pageTitle = "Admin Dashboard";
include '../includes/header.php';
?>

<section class="content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card bg-gradient-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title text-white mb-0">Admin Dashboard</h5>
                                <small class="text-white">
                                    <i class="fas fa-user-shield mr-1"></i> Welcome, Administrator
                                </small>
                            </div>
                            <div>
                                <span class="badge badge-light">
                                    <i class="far fa-clock mr-1"></i> 
                                    <?= date('M d, Y H:i', strtotime($current_datetime)) ?> UTC
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= $stats['users']['total'] ?></h3>
                        <p>Total Users</p>
                        <div class="mt-2">
                            <small class="mr-2">
                                <i class="fas fa-chalkboard-teacher"></i> Teachers: <?= $stats['users']['teachers'] ?>
                            </small>
                            <small>
                                <i class="fas fa-user-graduate"></i> Students: <?= $stats['users']['students'] ?>
                            </small>
                        </div>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <a href="manage_users.php" class="small-box-footer">
                        Manage Users <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?= $stats['classes'] ?></h3>
                        <p>Active Classes</p>
                        <div class="mt-2">
                            <small>
                                <i class="fas fa-book"></i> <?= $stats['subjects'] ?> Subjects
                            </small>
                        </div>
                    </div>
                    <div class="icon">
                        <i class="fas fa-school"></i>
                    </div>
                    <a href="manage_classes.php" class="small-box-footer">
                        Manage Classes <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?= $stats['assignments'] ?></h3>
                        <p>Total Assignments</p>
                        <div class="mt-2">
                            <small>
                                <i class="fas fa-file-alt"></i> <?= $stats['submissions'] ?> Submissions
                            </small>
                        </div>
                    </div>
                    <div class="icon">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <a href="view_assignments.php" class="small-box-footer">
                        View Details <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3><?= $stats['sessions'] ?></h3>
                        <p>Academic Sessions</p>
                        <div class="mt-2">
                            <small>
                                <i class="fas fa-calendar-alt"></i> Active Sessions
                            </small>
                        </div>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <a href="manage_sessions.php" class="small-box-footer">
                        Manage Sessions <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Content Row -->
        <div class="row">
            <!-- Quick Actions -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-bolt mr-2"></i>Quick Actions
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <a href="add_user.php" class="btn btn-primary btn-block">
                                    <i class="fas fa-user-plus mr-2"></i>Add New User
                                </a>
                            </div>
                            <div class="col-md-6 mb-3">
                                <a href="add_class.php" class="btn btn-success btn-block">
                                    <i class="fas fa-plus-circle mr-2"></i>Add New Class
                                </a>
                            </div>
                            <div class="col-md-6 mb-3">
                                <a href="add_subject.php" class="btn btn-info btn-block">
                                    <i class="fas fa-book-medical mr-2"></i>Add New Subject
                                </a>
                            </div>
                            <div class="col-md-6 mb-3">
                                <a href="add_session.php" class="btn btn-warning btn-block">
                                    <i class="fas fa-calendar-plus mr-2"></i>Add New Session
                                </a>
                            </div>
                            <!-- <div class="col-md-6 mb-3">
                                <a href="system_settings.php" class="btn btn-secondary btn-block">
                                    <i class="fas fa-cogs mr-2"></i>System Settings
                                </a>
                            </div>
                            <div class="col-md-6 mb-3">
                                <a href="view_logs.php" class="btn btn-dark btn-block">
                                    <i class="fas fa-history mr-2"></i>View System Logs
                                </a>
                            </div> -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-history mr-2"></i>Recent Activities
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Role</th>
                                        <th>Email</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_users as $user): ?>
                                        <tr>
                                            <td>
                                                <?= htmlspecialchars($user['full_name']) ?>
                                                <small class="d-block text-muted">@<?= htmlspecialchars($user['username']) ?></small>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?= 
                                                    $user['role'] == 'admin' ? 'danger' : 
                                                    ($user['role'] == 'teacher' ? 'info' : 'success') 
                                                ?>">
                                                    <?= ucfirst($user['role']) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($user['email']) ?></td>
                                            <td>
                                                <a href="edit_user.php?id=<?= $user['id'] ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Overview -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-bar mr-2"></i>System Overview
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="info-box bg-gradient-info">
                                    <span class="info-box-icon">
                                        <i class="fas fa-file-alt"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Submissions</span>
                                        <span class="info-box-number"><?= $submission_stats['total_submissions'] ?></span>
                                        <div class="progress">
                                            <div class="progress-bar" style="width: <?= 
                                                $submission_stats['total_submissions'] > 0 
                                                    ? ($submission_stats['graded_submissions'] / $submission_stats['total_submissions'] * 100) 
                                                    : 0 
                                            ?>%"></div>
                                        </div>
                                        <span class="progress-description">
                                            <?= $submission_stats['graded_submissions'] ?> Graded
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-box bg-gradient-success">
                                    <span class="info-box-icon">
                                        <i class="fas fa-star"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Average Score</span>
                                        <span class="info-box-number">
                                            <?= number_format($submission_stats['average_score'], 1) ?>%
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-box bg-gradient-warning">
                                    <span class="info-box-icon">
                                        <i class="fas fa-user-graduate"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Student/Teacher Ratio</span>
                                        <span class="info-box-number">
                                            <?= $stats['users']['teachers'] > 0 
                                                ? number_format($stats['users']['students'] / $stats['users']['teachers'], 1) 
                                                : 'N/A' 
                                            ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.small-box {
    position: relative;
    padding: 20px;
    margin-bottom: 20px;
    border-radius: 4px;
}
.small-box .icon {
    position: absolute;
    top: 15px;
    right: 15px;
    font-size: 70px;
    color: rgba(0,0,0,0.15);
}
.small-box .inner {
    padding: 10px;
}
.small-box h3 {
    font-size: 2.2rem;
    font-weight: 700;
    margin: 0 0 10px 0;
    white-space: nowrap;
    padding: 0;
}
.info-box {
    padding: 20px;
    border-radius: 4px;
}
.info-box-icon {
    font-size: 2rem;
    margin-right: 15px;
}
.progress {
    height: 2px;
    margin: 5px 0;
}
.card {
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
}
</style>

<?php include '../includes/footer.php'; ?>