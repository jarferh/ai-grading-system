<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';

requireStudent();

$pageTitle = "Submit Assignment";
include '../includes/header.php';

$assignment_id = $_GET['id'] ?? 0;
$student_id = $_SESSION['user_id'];
$current_datetime = '2025-04-08 22:32:20';

// Fetch assignment details with teacher name
$stmt = $conn->prepare("
    SELECT 
        a.id, 
        a.title, 
        a.description, 
        a.due_date, 
        a.total_marks,
        s.name AS subject_name,
        u.full_name AS teacher_name
    FROM assignments a
    JOIN subjects s ON a.subject_id = s.id
    LEFT JOIN users u ON a.teacher_id = u.id
    WHERE a.id = :assignment_id
");
$stmt->execute(['assignment_id' => $assignment_id]);
$assignment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$assignment) {
    die('<div class="alert alert-danger">Assignment not found.</div>');
}

// Check if assignment is past due date
$is_past_due = strtotime($current_datetime) > strtotime($assignment['due_date']);
?>

<section class="content">
    <div class="container-fluid">
        <!-- Page Header -->
        <!-- <div class="row mb-4">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                            <div>
                                <h5 class="card-title h4 text-primary m-3">
                                    <?= htmlspecialchars($assignment['title']) ?>
                                </h5>
                                <div class="d-flex flex-column flex-md-row text-muted small">
                                    <span class="d-flex align-items-center m-3">
                                        <i class="fas fa-book me-2"></i>
                                        <?= htmlspecialchars($assignment['subject_name']) ?>
                                    </span>
                                    <span class="d-flex align-items-center">
                                        <i class="fas fa-user me-2"></i>
                                        <?= htmlspecialchars($assignment['teacher_name']) ?>
                                    </span>
                                </div>
                            </div>

                            <div class="text-md-end mt-3 mt-md-0">
                                <div class="small text-muted mb-2">Due Date</div>
                                <div class="<?= $is_past_due ? 'text-danger' : 'text-dark' ?> fw-medium">
                                    <i class="far fa-clock me-2"></i>
                                    <?= date('M d, Y', strtotime($assignment['due_date'])) ?>
                                    <br class="d-md-none">
                                    <span class="small ms-md-2">
                                        <?= date('H:i', strtotime($assignment['due_date'])) ?> UTC
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> -->

        <div class="row">
            <!-- Assignment Details -->
            <div class="col-md-4">
                <div class="card card-outline card-primary h-100">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-info-circle mr-2"></i>
                            Assignment Details
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="assignment-info">
                            <h5>Question:</h5>
                            <div class="p-3 bg-light rounded mb-4">
                                <?= nl2br(htmlspecialchars($assignment['description'])) ?>
                            </div>

                            <div class="timeline">
                                <div class="time-label">
                                    <span class="bg-info">Important Information</span>
                                </div>
                                <div>
                                    <i class="fas fa-clock bg-blue"></i>
                                    <div class="timeline-item">
                                        <h3 class="timeline-header">Time Remaining</h3>
                                        <div class="timeline-body">
                                            <?php
                                            $time_remaining = strtotime($assignment['due_date']) - strtotime($current_datetime);
                                            if ($time_remaining > 0) {
                                                $days = floor($time_remaining / (60 * 60 * 24));
                                                $hours = floor(($time_remaining % (60 * 60 * 24)) / (60 * 60));
                                                echo "<span class='text-success'>{$days} days {$hours} hours remaining</span>";
                                            } else {
                                                echo "<span class='text-danger'>Past due date</span>";
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <i class="fas fa-star bg-warning"></i>
                                    <div class="timeline-item">
                                        <h3 class="timeline-header">Total Marks</h3>
                                        <div class="timeline-body">
                                            <span class="badge badge-info">
                                                <i class="fas fa-trophy mr-1"></i>
                                                <?= number_format($assignment['total_marks'], 2) ?> marks
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <i class="fas fa-info bg-yellow"></i>
                                    <div class="timeline-item">
                                        <h3 class="timeline-header">Grading Criteria</h3>
                                        <div class="timeline-body">
                                            <ul class="list-unstyled">
                                                <li><i class="fas fa-check text-success mr-2"></i>Attendance: 10%</li>
                                                <li><i class="fas fa-check text-success mr-2"></i>Grammar & Clarity: 10%</li>
                                                <li><i class="fas fa-check text-success mr-2"></i>Answer Accuracy: 70%</li>
                                                <li><i class="fas fa-check text-success mr-2"></i>Originality: 10%</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submission Form -->
            <div class="col-md-8">
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-edit mr-2"></i>
                            Your Submission
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php if ($is_past_due): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                This assignment is past its due date. Contact your teacher if you need an extension.
                            </div>
                        <?php endif; ?>

                        <form id="submitAssignmentForm">
                            <div class="form-group">
                                <label for="content">Your Answer:</label>
                                <textarea
                                    name="content"
                                    id="content"
                                    class="form-control"
                                    rows="12"
                                    required
                                    <?= $is_past_due ? 'disabled' : '' ?>
                                    placeholder="Type your answer here..."></textarea>
                            </div>
                            <div class="text-right">
                                <button type="submit" class="btn btn-primary btn-lg" <?= $is_past_due ? 'disabled' : '' ?>>
                                    <i class="fas fa-paper-plane mr-2"></i>
                                    Submit Assignment
                                </button>
                            </div>
                        </form>

                        <!-- Marking Animation -->
                        <div id="markingAnimation" class="text-center my-4" style="display: none;">
                            <div class="d-flex align-items-center justify-content-center">
                                <div class="spinner-grow text-primary mr-3" role="status">
                                    <span class="sr-only">Loading...</span>
                                </div>
                                <div class="spinner-grow text-success mr-3" role="status">
                                    <span class="sr-only">Loading...</span>
                                </div>
                                <div class="spinner-grow text-info" role="status">
                                    <span class="sr-only">Loading...</span>
                                </div>
                            </div>
                            <p class="lead mt-3">
                                <i class="fas fa-robot mr-2"></i>
                                AI is evaluating your submission...
                            </p>
                        </div>

                        <!-- Results Section -->
                        <div id="assignmentResult" class="mt-4" style="display: none;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .grade-circle {
        width: 150px;
        height: 150px;
        margin: 0 auto;
    }

    .progress-circle {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: conic-gradient(#28a745 0%, #e9ecef 0%);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }

    .progress-circle::before {
        content: '';
        position: absolute;
        width: 80%;
        height: 80%;
        border-radius: 50%;
        background: white;
    }

    .progress-circle-value {
        position: relative;
        font-size: 2rem;
        font-weight: bold;
        color: #28a745;
    }

    .timeline {
        position: relative;
        padding: 20px 0;
    }

    .timeline::before {
        content: '';
        position: absolute;
        top: 0;
        left: 31px;
        height: 100%;
        width: 4px;
        background: #ddd;
    }

    .time-label {
        margin-bottom: 1rem;
    }

    .timeline-item {
        margin-left: 60px;
        margin-bottom: 15px;
        padding: 10px;
        border-radius: 4px;
        background: #f8f9fa;
    }

    .timeline>div>i {
        width: 30px;
        height: 30px;
        font-size: 15px;
        line-height: 30px;
        position: absolute;
        color: #fff;
        text-align: center;
        border-radius: 50%;
        left: 18px;
        top: 0;
    }

    .spinner-grow {
        width: 2rem;
        height: 2rem;
    }

    .feedback-box {
        max-height: 200px;
        overflow-y: auto;
    }
</style>

<script>
    document.getElementById('submitAssignmentForm').addEventListener('submit', function(e) {
        e.preventDefault();

        // Show the marking animation
        document.getElementById('markingAnimation').style.display = 'block';
        document.getElementById('assignmentResult').style.display = 'none';

        // Get form data
        const formData = new FormData();
        formData.append('assignment_id', <?= $assignment_id ?>);
        formData.append('content', document.getElementById('content').value);

        // Send AJAX request
        fetch('submit_assignment_ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Hide the marking animation
                document.getElementById('markingAnimation').style.display = 'none';

                if (data.success) {
                    // Update the results section
                    document.getElementById('assignmentResult').innerHTML = `
                <div class="card bg-gradient-success">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-4 text-center">
                                <div class="grade-circle position-relative">
                                    <div class="progress-circle">
                                        <span class="progress-circle-value">${data.percentage}%</span>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <span class="badge badge-light">
                                        <i class="fas fa-star mr-1"></i>
                                        ${data.achieved_marks.toFixed(2)} / ${data.total_marks.toFixed(2)} marks
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <h4 class="text-white mb-3">
                                    <i class="fas fa-award mr-2"></i>
                                    Your Grade
                                </h4>
                                <div class="feedback-box bg-white p-3 rounded">
                                    <h5 class="text-dark">
                                        <i class="fas fa-comment-alt mr-2"></i>
                                        Feedback:
                                    </h5>
                                    <p id="feedbackResult" class="text-dark mb-0">${data.feedback}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

                    // Show results
                    document.getElementById('assignmentResult').style.display = 'block';

                    // Update the circular progress
                    const progressCircle = document.querySelector('.progress-circle');
                    progressCircle.style.background = `conic-gradient(#28a745 ${data.percentage}%, #e9ecef ${data.percentage}%)`;
                } else {
                    alert(data.error || 'An error occurred while submitting your assignment.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while submitting your assignment.');
            });
    });
</script>

<?php include '../includes/footer.php'; ?>