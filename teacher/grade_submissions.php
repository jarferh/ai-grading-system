<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';

requireTeacher();

$teacher_id = $_SESSION['user_id'];

// Handle grade submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction();
        
        $submission_id = $_POST['submission_id'];
        $score = $_POST['score'];
        $remarks = $_POST['remarks'];

        // Check if grade already exists
        $stmt = $conn->prepare("SELECT id FROM grades WHERE submission_id = :submission_id");
        $stmt->execute(['submission_id' => $submission_id]);
        $existing_grade = $stmt->fetch();

        if ($existing_grade) {
            // Update existing grade
            $stmt = $conn->prepare("
                UPDATE grades 
                SET score = :score, remarks = :remarks 
                WHERE submission_id = :submission_id
            ");
        } else {
            // Insert new grade
            $stmt = $conn->prepare("
                INSERT INTO grades (submission_id, score, remarks) 
                VALUES (:submission_id, :score, :remarks)
            ");
        }

        $stmt->execute([
            'submission_id' => $submission_id,
            'score' => $score,
            'remarks' => $remarks
        ]);

        $conn->commit();
        $_SESSION['success_message'] = "Grade has been saved successfully!";
    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['error_message'] = "Error saving grade: " . $e->getMessage();
    }
}

// Fetch submissions with grades
$stmt = $conn->prepare("
    SELECT 
        s.id,
        s.content,
        s.submission_date,
        u.full_name,
        u.email,
        a.title as assignment_title,
        c.name as class_name,
        subj.name as subject_name,
        g.score,
        g.remarks
    FROM submissions s
    JOIN users u ON s.student_id = u.id
    JOIN assignments a ON s.assignment_id = a.id
    JOIN classes c ON a.class_id = c.id
    JOIN subjects subj ON a.subject_id = subj.id
    LEFT JOIN grades g ON s.id = g.submission_id
    WHERE a.teacher_id = :teacher_id
    ORDER BY s.submission_date DESC
");
$stmt->execute(['teacher_id' => $teacher_id]);
$submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "Grade Submissions";
include '../includes/header.php';
?>

<section class="content">
    <div class="container-fluid">
        <!-- Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible">
                <?= $_SESSION['success_message'] ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible">
                <?= $_SESSION['error_message'] ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <!-- Submissions Table -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Student Submissions</h3>
            </div>
            <div class="card-body table-responsive">
                <table id="submissionsTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Assignment</th>
                            <th>Class</th>
                            <th>Subject</th>
                            <th>Submitted</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($submissions as $submission): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars($submission['full_name']) ?>
                                    <small class="d-block text-muted"><?= htmlspecialchars($submission['email']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($submission['assignment_title']) ?></td>
                                <td><?= htmlspecialchars($submission['class_name']) ?></td>
                                <td><?= htmlspecialchars($submission['subject_name']) ?></td>
                                <td><?= date('Y-m-d H:i', strtotime($submission['submission_date'])) ?></td>
                                <td>
                                    <?php if (isset($submission['score'])): ?>
                                        <span class="badge badge-<?= $submission['score'] >= 70 ? 'success' : 'danger' ?>">
                                            <?= $submission['score'] ?>%
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Not Graded</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-primary btn-sm" 
                                            onclick="openGradeModal(<?= htmlspecialchars(json_encode($submission)) ?>)">
                                        <?= isset($submission['score']) ? 'Update Grade' : 'Grade' ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<!-- Grade Modal -->
<div class="modal fade" id="gradeModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Grade Submission</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="submission_id" id="submission_id">
                    
                    <div class="form-group">
                        <label>Student's Answer</label>
                        <div id="submissionContent" class="p-3 bg-light rounded"></div>
                    </div>

                    <div class="form-group">
                        <label for="score">Score (%)</label>
                        <input type="number" class="form-control" name="score" id="score"
                               min="0" max="100" required>
                    </div>

                    <div class="form-group">
                        <label for="remarks">Feedback</label>
                        <textarea class="form-control" name="remarks" id="remarks" 
                                  rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Grade</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#submissionsTable').DataTable({
        "order": [[4, "desc"]]
    });
});

function openGradeModal(submission) {
    $('#submission_id').val(submission.id);
    $('#submissionContent').text(submission.content);
    
    if (submission.score) {
        $('#score').val(submission.score);
        $('#remarks').val(submission.remarks);
    } else {
        $('#score').val('');
        $('#remarks').val('');
    }
    
    $('#gradeModal').modal('show');
}
</script>

<?php include '../includes/footer.php'; ?>