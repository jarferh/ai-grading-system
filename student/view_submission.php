<?php
@session_start();
include '../includes/db.php';
include '../includes/auth.php';

requireStudent();

$pageTitle = "View Submission";
include '../includes/header.php';

$student_id = $_SESSION['user_id'];
$assignment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch submission details with assignment and grade information
$stmt = $conn->prepare("
    SELECT 
        s.*, 
        a.title as assignment_title,
        a.description as assignment_description,
        a.due_date,
        sub.name as subject_name,
        g.score,
        g.remarks,
        u.full_name as teacher_name
    FROM assignments a
    JOIN subjects sub ON a.subject_id = sub.id
    LEFT JOIN users u ON a.teacher_id = u.id
    LEFT JOIN submissions s ON s.assignment_id = a.id AND s.student_id = :student_id
    LEFT JOIN grades g ON s.id = g.submission_id
    WHERE a.id = :assignment_id
");

$stmt->execute([
    'student_id' => $student_id,
    'assignment_id' => $assignment_id
]);

$submission = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$submission) {
    echo "<div class='alert alert-danger'>Submission not found.</div>";
    exit;
}
?>

<section class="content">
    <div class="container-fluid">
        <!-- Assignment Details Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-file-alt mr-2"></i>
                    Assignment Details
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-4">Assignment Title:</dt>
                            <dd class="col-sm-8"><?= htmlspecialchars($submission['assignment_title']) ?></dd>

                            <dt class="col-sm-4">Subject:</dt>
                            <dd class="col-sm-8"><?= htmlspecialchars($submission['subject_name']) ?></dd>

                            <dt class="col-sm-4">Teacher:</dt>
                            <dd class="col-sm-8"><?= htmlspecialchars($submission['teacher_name']) ?></dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-4">Due Date:</dt>
                            <dd class="col-sm-8">
                                <?= date('F d, Y', strtotime($submission['due_date'])) ?>
                            </dd>

                            <dt class="col-sm-4">Submission Date:</dt>
                            <dd class="col-sm-8">
                                <?= $submission['submission_date'] ? date('F d, Y H:i:s', strtotime($submission['submission_date'])) : 'Not submitted' ?>
                            </dd>

                            <dt class="col-sm-4">Status:</dt>
                            <dd class="col-sm-8">
                                <?php if ($submission['submission_date']): ?>
                                    <?php if (isset($submission['score'])): ?>
                                        <span class="badge badge-success">Graded</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Submitted - Pending Grade</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge badge-danger">Not Submitted</span>
                                <?php endif; ?>
                            </dd>
                        </dl>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <h5>Assignment Description:</h5>
                        <div class="p-3 bg-light rounded">
                            <?= nl2br(htmlspecialchars($submission['assignment_description'])) ?>
                        </div>
                    </div>
                </div>

                <?php if ($submission['submission_date']): ?>
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>Your Submission:</h5>
                            <div class="p-3 bg-light rounded">
                                <?= nl2br(htmlspecialchars($submission['content'])) ?>
                            </div>
                        </div>
                    </div>

                    <?php if (isset($submission['score'])): ?>
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card bg-success">
                                    <div class="card-header">
                                        <h3 class="card-title">Grade and Feedback</h3>
                                    </div>
                                    <div class="card-body">
                                        <h2 class="display-4 text-center"><?= $submission['score'] ?>%</h2>
                                        <?php if ($submission['remarks']): ?>
                                            <hr>
                                            <h5>Teacher's Remarks:</h5>
                                            <p><?= nl2br(htmlspecialchars($submission['remarks'])) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>