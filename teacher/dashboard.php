<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';

requireTeacher();

// Set page title
$pageTitle = "Teacher Dashboard";
include '../includes/header.php';

$teacher_id = $_SESSION['user_id'];

// Fetch assignments total
$stmt = $conn->prepare("SELECT COUNT(*) FROM assignments WHERE teacher_id = :teacher_id");
$stmt->execute(['teacher_id' => $teacher_id]);
$total_assignments = $stmt->fetchColumn();

// Fetch total submissions
$stmt = $conn->prepare("
    SELECT COUNT(DISTINCT submissions.id) 
    FROM submissions 
    JOIN assignments ON submissions.assignment_id = assignments.id 
    WHERE assignments.teacher_id = :teacher_id
");
$stmt->execute(['teacher_id' => $teacher_id]);
$total_submissions = $stmt->fetchColumn();

// Calculate average grade
$stmt = $conn->prepare("
    SELECT AVG(grades.score) 
    FROM grades 
    JOIN submissions ON grades.submission_id = submissions.id 
    JOIN assignments ON submissions.assignment_id = assignments.id 
    WHERE assignments.teacher_id = :teacher_id
");
$stmt->execute(['teacher_id' => $teacher_id]);
$average_grade = round($stmt->fetchColumn(), 1);

// Get recent submissions
$stmt = $conn->prepare("
    SELECT 
        assignments.title,
        users.full_name,
        submissions.submission_date,
        grades.score
    FROM submissions 
    JOIN assignments ON submissions.assignment_id = assignments.id 
    JOIN users ON submissions.student_id = users.id
    LEFT JOIN grades ON submissions.id = grades.submission_id
    WHERE assignments.teacher_id = :teacher_id
    ORDER BY submissions.submission_date DESC
    LIMIT 5
");
$stmt->execute(['teacher_id' => $teacher_id]);
$recent_submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- <div class="content-wrapper"> -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <!-- <div class="col-sm-6">
                    <h1 class="m-0">Teacher Dashboard</h1>
                </div> -->
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Info Boxes -->
            <div class="row">
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="fas fa-book"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Assignments</span>
                            <span class="info-box-number"><?= $total_assignments ?></span>
                            <div class="progress">
                                <div class="progress-bar bg-info" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-success"><i class="fas fa-file-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Submissions</span>
                            <span class="info-box-number"><?= $total_submissions ?></span>
                            <div class="progress">
                                <div class="progress-bar bg-success" style="width: <?= ($total_submissions / max(1, $total_assignments)) * 100 ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="fas fa-star"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Average Grade</span>
                            <span class="info-box-number"><?= $average_grade ?>%</span>
                            <div class="progress">
                                <div class="progress-bar bg-warning" style="width: <?= $average_grade ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Quick Actions Card -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Quick Actions</h3>
                        </div>
                        <div class="card-body">
                            <a href="create_assignment.php" class="btn btn-primary btn-block mb-3">
                                <i class="fas fa-plus mr-2"></i> Create Assignment
                            </a>
                            <a href="grade_submissions.php" class="btn btn-success btn-block mb-3">
                                <i class="fas fa-check mr-2"></i> Grade Submissions
                            </a>
                            <a href="view_reports.php" class="btn btn-info btn-block">
                                <i class="fas fa-chart-bar mr-2"></i> View Reports
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Recent Submissions -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Recent Submissions</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table m-0">
                                    <thead>
                                        <tr>
                                            <th>Assignment</th>
                                            <th>Student</th>
                                            <th>Submitted</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_submissions as $submission): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($submission['title']) ?></td>
                                                <td><?= htmlspecialchars($submission['full_name']) ?></td>
                                                <td><?= date('M d, Y H:i', strtotime($submission['submission_date'])) ?></td>
                                                <td>
                                                    <?php if (isset($submission['score'])): ?>
                                                        <span class="badge badge-success">Graded - <?= $submission['score'] ?>%</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-warning">Pending</span>
                                                    <?php endif; ?>
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
        </div>
    </section>
</div>

<?php include '../includes/footer.php'; ?>