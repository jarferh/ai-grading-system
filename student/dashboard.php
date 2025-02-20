<?php
@session_start();
include '../includes/db.php';
include '../includes/auth.php';

requireStudent();

// Set page title
$pageTitle = "Student Dashboard";
include '../includes/header.php';

$student_id = $_SESSION['user_id'];
$current_datetime = '2025-02-20 16:40:37'; // Current UTC time

// Fetch student's information
$stmt = $conn->prepare("
    SELECT u.*, c.name as class_name, s.name as section_name 
    FROM users u 
    LEFT JOIN classes c ON u.class_id = c.id 
    LEFT JOIN sections s ON u.section_id = s.id 
    WHERE u.id = :student_id
");
$stmt->execute(['student_id' => $student_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch assignments for the student's class
$stmt = $conn->prepare("
    SELECT 
        a.*, 
        s.name AS subject_name,
        u.full_name as teacher_name,
        (SELECT COUNT(*) FROM submissions sub WHERE sub.assignment_id = a.id AND sub.student_id = :student_id) as is_submitted
    FROM assignments a
    JOIN subjects s ON a.subject_id = s.id 
    LEFT JOIN users u ON a.teacher_id = u.id
    WHERE a.class_id = :class_id
    ORDER BY a.due_date DESC
");
$stmt->execute([
    'student_id' => $student_id,
    'class_id' => $student['class_id']
]);
$assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$total_assignments = count($assignments);
$total_submitted = array_sum(array_column($assignments, 'is_submitted'));
$total_pending = $total_assignments - $total_submitted;

// Calculate average grade
$stmt = $conn->prepare("
    SELECT AVG(g.score) 
    FROM submissions s
    JOIN grades g ON s.id = g.submission_id
    WHERE s.student_id = :student_id
");
$stmt->execute(['student_id' => $student_id]);
$average_grade = round($stmt->fetchColumn(), 1) ?: 0;
?>

<section class="content">
    <div class="container-fluid">
        <!-- Student Info Box -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="info-box bg-gradient-info">
                    <span class="info-box-icon"><i class="fas fa-user-graduate"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Welcome, <?= htmlspecialchars($student['full_name']) ?></span>
                        <span class="info-box-text">
                            Class: <?= htmlspecialchars($student['class_name']) ?> | 
                            Section: <?= htmlspecialchars($student['section_name']) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-12 col-sm-6 col-md-3">
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

            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Submitted</span>
                        <span class="info-box-number"><?= $total_submitted ?></span>
                        <div class="progress">
                            <div class="progress-bar bg-success" style="width: <?= ($total_submitted / max(1, $total_assignments)) * 100 ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Pending</span>
                        <span class="info-box-number"><?= $total_pending ?></span>
                        <div class="progress">
                            <div class="progress-bar bg-warning" style="width: <?= ($total_pending / max(1, $total_assignments)) * 100 ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-primary"><i class="fas fa-star"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Average Grade</span>
                        <span class="info-box-number"><?= $average_grade ?>%</span>
                        <div class="progress">
                            <div class="progress-bar bg-primary" style="width: <?= $average_grade ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Upcoming Assignments Card -->
            <div class="col-md-6">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-calendar-alt mr-2"></i>
                            Upcoming Assignments
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Subject</th>
                                        <th>Due Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $upcoming = array_filter($assignments, function($a) use ($current_datetime) {
                                        return strtotime($a['due_date']) > strtotime($current_datetime);
                                    });
                                    $upcoming = array_slice($upcoming, 0, 5);
                                    
                                    foreach ($upcoming as $assignment): 
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($assignment['title']) ?></td>
                                        <td><?= htmlspecialchars($assignment['subject_name']) ?></td>
                                        <td>
                                            <span class="badge badge-info">
                                                <?= date('M d, Y', strtotime($assignment['due_date'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!$assignment['is_submitted']): ?>
                                                <a href="submit_assignment.php?id=<?= $assignment['id'] ?>" 
                                                   class="btn btn-primary btn-sm">
                                                    <i class="fas fa-upload"></i> Submit
                                                </a>
                                            <?php else: ?>
                                                <span class="badge badge-success">
                                                    <i class="fas fa-check"></i> Submitted
                                                </span>
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

            <!-- Recent Activity Card -->
            <div class="col-md-6">
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-history mr-2"></i>
                            Recent Activity
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Assignment</th>
                                        <th>Subject</th>
                                        <th>Status</th>
                                        <th>Grade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $stmt = $conn->prepare("
                                        SELECT 
                                            a.title, 
                                            s.name as subject_name, 
                                            sub.submission_date, 
                                            g.score
                                        FROM submissions sub
                                        JOIN assignments a ON sub.assignment_id = a.id
                                        JOIN subjects s ON a.subject_id = s.id
                                        LEFT JOIN grades g ON sub.id = g.submission_id
                                        WHERE sub.student_id = :student_id
                                        ORDER BY sub.submission_date DESC
                                        LIMIT 5
                                    ");
                                    $stmt->execute(['student_id' => $student_id]);
                                    $recent_activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    
                                    foreach ($recent_activities as $activity): 
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($activity['title']) ?></td>
                                        <td><?= htmlspecialchars($activity['subject_name']) ?></td>
                                        <td>
                                            <?php if (isset($activity['score'])): ?>
                                                <span class="badge badge-success">Graded</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning">Pending</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (isset($activity['score'])): ?>
                                                <span class="badge badge-info"><?= $activity['score'] ?>%</span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
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

        <!-- All Assignments Card -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list mr-2"></i>
                            All Assignments
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Subject</th>
                                        <th>Teacher</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($assignments as $assignment): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($assignment['title']) ?></td>
                                            <td><?= htmlspecialchars($assignment['subject_name']) ?></td>
                                            <td><?= htmlspecialchars($assignment['teacher_name']) ?></td>
                                            <td>
                                                <?php
                                                $due_date = strtotime($assignment['due_date']);
                                                $badge_class = time() > $due_date ? 'badge-danger' : 'badge-info';
                                                ?>
                                                <span class="badge <?= $badge_class ?>">
                                                    <?= date('M d, Y', $due_date) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($assignment['is_submitted']): ?>
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check"></i> Submitted
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-warning">
                                                        <i class="fas fa-clock"></i> Pending
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!$assignment['is_submitted']): ?>
                                                    <a href="submit_assignment.php?id=<?= $assignment['id'] ?>" 
                                                       class="btn btn-primary btn-sm">
                                                        <i class="fas fa-upload"></i> Submit
                                                    </a>
                                                <?php else: ?>
                                                    <a href="view_submission.php?id=<?= $assignment['id'] ?>" 
                                                       class="btn btn-info btn-sm">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
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

<?php include '../includes/footer.php'; ?>