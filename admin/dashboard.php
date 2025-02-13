<?php
include '../includes/db.php';
include '../includes/auth.php';

requireAdmin();

// Set page title
$pageTitle = "Admin Dashboard";
include '../includes/header.php';

// Fetch totals
$total_users = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_classes = $conn->query("SELECT COUNT(*) FROM classes")->fetchColumn();
$total_subjects = $conn->query("SELECT COUNT(*) FROM subjects")->fetchColumn();
$total_sessions = $conn->query("SELECT COUNT(*) FROM sessions")->fetchColumn();
?>

<!-- <div class="content-wrapper"> -->
    <section class="content">
        <div class="container-fluid">
            <!-- Summary Cards -->
            <div class="row">
                <div class="col-md-3">
                    <div class="card bg-info">
                        <div class="card-body">
                            <h5 class="card-title">Total Users</h5>
                            <p class="card-text display-4"><?= $total_users ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success">
                        <div class="card-body">
                            <h5 class="card-title">Total Classes</h5>
                            <p class="card-text display-4"><?= $total_classes ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning">
                        <div class="card-body">
                            <h5 class="card-title">Total Subjects</h5>
                            <p class="card-text display-4"><?= $total_subjects ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger">
                        <div class="card-body">
                            <h5 class="card-title">Total Sessions</h5>
                            <p class="card-text display-4"><?= $total_sessions ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Quick Links</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <a href="manage_users.php" class="btn btn-primary btn-block">Manage Users</a>
                                </div>
                                <div class="col-md-3">
                                    <a href="manage_classes.php" class="btn btn-primary btn-block">Manage Classes</a>
                                </div>
                                <div class="col-md-3">
                                    <a href="manage_subjects.php" class="btn btn-primary btn-block">Manage Subjects</a>
                                </div>
                                <div class="col-md-3">
                                    <a href="manage_sessions.php" class="btn btn-primary btn-block">Manage Sessions</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include '../includes/footer.php'; ?>