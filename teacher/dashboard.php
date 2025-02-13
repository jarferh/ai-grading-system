<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';

requireTeacher();

// Set page title
$pageTitle = "Teacher Dashboard";
include '../includes/header.php';

$teacher_id = $_SESSION['user_id'];

// Fetch totals
$total_assignments = $conn->query("SELECT COUNT(*) FROM assignments WHERE teacher_id = $teacher_id")->fetchColumn();
$total_graded = $conn->query("SELECT COUNT(DISTINCT submissions.id) FROM grades JOIN submissions ON grades.submission_id = submissions.id JOIN assignments ON submissions.assignment_id = assignments.id WHERE assignments.teacher_id = $teacher_id")->fetchColumn();
$total_pending = $total_assignments - $total_graded;
?>

<!-- <div class="content-wrapper"> -->
    <section class="content">
        <div class="container-fluid">
            <!-- Summary Cards -->
            <div class="row">
                <div class="col-md-4">
                    <div class="card bg-info">
                        <div class="card-body">
                            <h5 class="card-title">Total Assignments</h5>
                            <p class="card-text display-4"><?= $total_assignments ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success">
                        <div class="card-body">
                            <h5 class="card-title">Graded Submissions</h5>
                            <p class="card-text display-4"><?= $total_graded ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-danger">
                        <div class="card-body">
                            <h5 class="card-title">Pending Submissions</h5>
                            <p class="card-text display-4"><?= $total_pending ?></p>
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
                                <div class="col-md-4">
                                    <a href="create_assignment.php" class="btn btn-primary btn-block">Create Assignment</a>
                                </div>
                                <div class="col-md-4">
                                    <a href="grade_submissions.php" class="btn btn-primary btn-block">Grade Submissions</a>
                                </div>
                                <div class="col-md-4">
                                    <a href="view_reports.php" class="btn btn-primary btn-block">View Reports</a>
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