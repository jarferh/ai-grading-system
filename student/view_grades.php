<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';

requireStudent();

$pageTitle = "View Grades";
include '../includes/header.php';

$student_id = $_SESSION['user_id'];
$CURRENT_UTC_DATETIME = '2025-02-20 17:22:17';

// Fetch student details
$stmt = $conn->prepare("
    SELECT u.*, c.name as class_name, s.name as section_name
    FROM users u
    LEFT JOIN classes c ON u.class_id = c.id
    LEFT JOIN sections s ON u.section_id = s.id
    WHERE u.id = :student_id
");
$stmt->execute(['student_id' => $student_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch grades and related data
$query = "
    SELECT 
        g.score, 
        g.remarks, 
        a.title AS assignment_title,
        a.due_date,
        s.name AS subject_name,
        s.id AS subject_id, 
        sub.submission_date,
        u.full_name AS teacher_name
    FROM submissions sub
    JOIN assignments a ON sub.assignment_id = a.id
    JOIN subjects s ON a.subject_id = s.id
    LEFT JOIN grades g ON sub.id = g.submission_id
    LEFT JOIN users u ON a.teacher_id = u.id
    WHERE sub.student_id = :student_id
    ORDER BY sub.submission_date DESC
";
$stmt = $conn->prepare($query);
$stmt->execute(['student_id' => $student_id]);
$grades = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Initialize statistics
$stats = [
    'total_assignments' => 0,
    'average_score' => 0,
    'highest_score' => 0,
    'lowest_score' => 100
];

// Calculate statistics
if (!empty($grades)) {
    $total_score = 0;
    $valid_grades = 0;
    
    foreach ($grades as $grade) {
        if (isset($grade['score']) && is_numeric($grade['score'])) {
            $score = floatval($grade['score']);
            $total_score += $score;
            $valid_grades++;
            $stats['highest_score'] = max($stats['highest_score'], $score);
            $stats['lowest_score'] = min($stats['lowest_score'], $score);
        }
    }
    
    $stats['total_assignments'] = count($grades);
    $stats['average_score'] = $valid_grades > 0 ? $total_score / $valid_grades : 0;
}

// Get subject-wise grades
$subject_grades = [];
foreach ($grades as $grade) {
    $subject_name = $grade['subject_name'];
    if (!isset($subject_grades[$subject_name])) {
        $subject_grades[$subject_name] = [
            'scores' => [],
            'count' => 0,
            'average' => 0
        ];
    }
    
    if (isset($grade['score']) && is_numeric($grade['score'])) {
        $subject_grades[$subject_name]['scores'][] = floatval($grade['score']);
        $subject_grades[$subject_name]['count']++;
    }
}

// Calculate subject averages
foreach ($subject_grades as $subject => &$data) {
    if (!empty($data['scores'])) {
        $data['average'] = array_sum($data['scores']) / count($data['scores']);
    }
}
unset($data); // Break the reference
?>

<section class="content">
    <div class="container-fluid">
        <!-- Student Info -->
        <!-- <div class="row mb-4">
            <div class="col-md-12">
                <div class="info-box bg-info">
                    <span class="info-box-icon"><i class="fas fa-user-graduate"></i></span>
                    <div class="info-box-content">
                        <h5 class="mb-0"><?= htmlspecialchars($student['full_name']) ?></h5>
                        <span>Class: <?= htmlspecialchars($student['class_name']) ?> | 
                              Section: <?= htmlspecialchars($student['section_name']) ?></span>
                    </div>
                </div>
            </div>
        </div> -->

        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-md-3">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?= number_format($stats['average_score'], 1) ?>%</h3>
                        <p>Average Score</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3><?= $stats['total_assignments'] ?></h3>
                        <p>Total Assignments</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-tasks"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?= number_format($stats['highest_score'], 1) ?>%</h3>
                        <p>Highest Score</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3><?= number_format($stats['lowest_score'], 1) ?>%</h3>
                        <p>Lowest Score</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Subject-wise Performance -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-bar mr-2"></i>
                            Subject-wise Performance
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($subject_grades as $subject => $data): ?>
                            <div class="col-md-6">
                                <div class="progress-group">
                                    <span class="progress-text"><?= htmlspecialchars($subject) ?></span>
                                    <span class="float-right">
                                        <b><?= number_format($data['average'], 1) ?>%</b>
                                    </span>
                                    <div class="progress">
                                        <div class="progress-bar bg-primary" 
                                             style="width: <?= $data['average'] ?>%">
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        <?= $data['count'] ?> assignment(s)
                                    </small>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grades Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list mr-2"></i>
                            Detailed Grades
                        </h3>
                        <div class="card-tools">
                            <div class="input-group input-group-sm" style="width: 250px;">
                                <input type="text" id="gradeSearch" class="form-control float-right" 
                                       placeholder="Search assignments...">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-default">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>Assignment</th>
                                    <th>Subject</th>
                                    <th>Submission Date</th>
                                    <th>Score</th>
                                    <th>Teacher</th>
                                    <th>Feedback</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($grades as $grade): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($grade['assignment_title']) ?></strong>
                                    </td>
                                    <td><?= htmlspecialchars($grade['subject_name']) ?></td>
                                    <td>
                                        <?= date('M d, Y H:i', strtotime($grade['submission_date'])) ?>
                                    </td>
                                    <td>
                                        <?php if (isset($grade['score'])): ?>
                                            <?php
                                            $score_class = 'success';
                                            if ($grade['score'] < 60) $score_class = 'danger';
                                            elseif ($grade['score'] < 80) $score_class = 'warning';
                                            ?>
                                            <span class="badge badge-<?= $score_class ?>">
                                                <?= number_format($grade['score'], 1) ?>%
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($grade['teacher_name'] ?? 'N/A') ?></td>
                                    <td>
                                        <?php if (!empty($grade['remarks'])): ?>
                                            <button type="button" class="btn btn-sm btn-info" 
                                                    data-toggle="popover" 
                                                    data-content="<?= htmlspecialchars($grade['remarks']) ?>">
                                                <i class="fas fa-comment"></i> View Feedback
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted">No feedback</span>
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
</section>

<!-- Initialize popovers -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize popovers
    $('[data-toggle="popover"]').popover({
        trigger: 'click',
        placement: 'top',
        html: true
    });

    // Close popover when clicking outside
    $(document).on('click', function(e) {
        if ($(e.target).data('toggle') !== 'popover' && 
            $(e.target).parents('[data-toggle="popover"]').length === 0) {
            $('[data-toggle="popover"]').popover('hide');
        }
    });

    // Search functionality
    $('#gradeSearch').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('table tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
});
</script>

<style>
.progress-group {
    margin-bottom: 1.5rem;
}
.progress-text {
    font-weight: 600;
}
.progress {
    height: 10px;
    margin: 5px 0;
}
.small-box {
    border-radius: 4px;
    position: relative;
    display: block;
    margin-bottom: 20px;
    box-shadow: 0 1px 1px rgba(0,0,0,0.1);
}
.small-box > .inner {
    padding: 10px;
}
.small-box h3 {
    font-size: 2.2rem;
    font-weight: 700;
    margin: 0 0 10px 0;
    white-space: nowrap;
    padding: 0;
}
.small-box .icon {
    position: absolute;
    top: 5px;
    right: 10px;
    font-size: 70px;
    color: rgba(0,0,0,0.15);
}
.badge {
    font-size: 85%;
    font-weight: 600;
    padding: 0.35em 0.65em;
}
.table td {
    vertical-align: middle;
}
</style>

<?php include '../includes/footer.php'; ?>