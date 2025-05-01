<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';

requireTeacher();

$teacher_id = $_SESSION['user_id'];
$current_datetime = '2025-02-20 20:39:31';
$current_user = 'jarferh';

// Initialize messages array
$messages = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction();

        // Combine date and time for due_date
        $due_date = $_POST['due_date'];
        if (!empty($_POST['due_time'])) {
            $due_date .= ' ' . $_POST['due_time'];
        }

        $stmt = $conn->prepare("
            INSERT INTO assignments (
                title, description, subject_id, class_id, 
                session_id, due_date, teacher_id, total_marks
            ) VALUES (
                :title, :description, :subject_id, :class_id, 
                :session_id, :due_date, :teacher_id, :total_marks
            )
        ");

        $stmt->execute([
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'subject_id' => $_POST['subject_id'],
            'class_id' => $_POST['class_id'],
            'session_id' => $_POST['session_id'],
            'due_date' => $due_date,
            'teacher_id' => $teacher_id,
            'total_marks' => $_POST['total_marks']
        ]);

        $conn->commit();
        
        $_SESSION['success_message'] = "Assignment created successfully!";
        
        // Store redirect URL in session
        $_SESSION['redirect_url'] = 'dashboard.php';
        
        // JavaScript redirect
        $messages['redirect'] = true;
    } catch (Exception $e) {
        $conn->rollBack();
        $messages['error'] = "Error creating assignment: " . $e->getMessage();
    }
}

// Get teacher's information
$stmt = $conn->prepare("
    SELECT u.*, c.name as assigned_class, s.name as assigned_section 
    FROM users u 
    LEFT JOIN classes c ON u.class_id = c.id 
    LEFT JOIN sections s ON u.section_id = s.id 
    WHERE u.id = :teacher_id
");
$stmt->execute(['teacher_id' => $teacher_id]);
$teacher = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch data for dropdowns
$subjects = $conn->query("SELECT * FROM subjects ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$classes = $conn->query("SELECT * FROM classes ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$sessions = $conn->query("SELECT * FROM sessions ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Set page title
$pageTitle = "Create Assignment";
include '../includes/header.php';
?>

<?php if (isset($messages['redirect'])): ?>
<script>
    // Show success message and redirect
    alert("Assignment created successfully!");
    window.location.href = 'dashboard.php';
</script>
<?php endif; ?>

<section class="content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card bg-gradient-info">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title mb-0">Create New Assignment</h5>
                                <small class="text-white">
                                    <i class="fas fa-user mr-1"></i> <?= htmlspecialchars($teacher['full_name']) ?> | 
                                    <i class="fas fa-users mr-1"></i> 
                                    <?= htmlspecialchars($teacher['assigned_class'] ?? 'No Class') ?>
                                    <?= $teacher['assigned_section'] ? ' - ' . htmlspecialchars($teacher['assigned_section']) : '' ?>
                                </small>
                            </div>
                            <div>
                                <span class="badge badge-light">
                                    <i class="far fa-clock mr-1"></i> Current Time (UTC):
                                    <?= date('M d, Y H:i', strtotime($current_datetime)) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (isset($messages['error'])): ?>
            <div class="alert alert-danger">
                <?= $messages['error'] ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Assignment Form -->
            <div class="col-md-8">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Assignment Details</h3>
                    </div>
                    <form action="" method="POST" id="assignmentForm">
                        <div class="card-body">
                            <div class="form-group">
                                <label for="title">
                                    <i class="fas fa-heading mr-1"></i> Assignment Title
                                </label>
                                <input type="text" name="title" id="title" 
                                       class="form-control" required 
                                       placeholder="Enter a descriptive title">
                            </div>

                            <div class="form-group">
                                <label for="description">
                                    <i class="fas fa-question-circle mr-1"></i> Question/Instructions
                                </label>
                                <textarea name="description" id="description" 
                                          class="form-control" rows="6" required
                                          placeholder="Enter detailed instructions for the assignment. Include total marks allocation."></textarea>
                                <small class="form-text text-muted">
                                    Provide clear instructions and mark distribution for each question/section.
                                </small>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="subject_id">
                                            <i class="fas fa-book mr-1"></i> Subject
                                        </label>
                                        <select name="subject_id" id="subject_id" 
                                                class="form-control select2" required>
                                            <option value="">Select Subject</option>
                                            <?php foreach ($subjects as $subject): ?>
                                                <option value="<?= $subject['id'] ?>">
                                                    <?= htmlspecialchars($subject['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="class_id">
                                            <i class="fas fa-users mr-1"></i> Class
                                        </label>
                                        <select name="class_id" id="class_id" 
                                                class="form-control select2" required>
                                            <option value="">Select Class</option>
                                            <?php foreach ($classes as $class): ?>
                                                <option value="<?= $class['id'] ?>"
                                                    <?= ($teacher['class_id'] == $class['id']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($class['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="session_id">
                                            <i class="fas fa-calendar-alt mr-1"></i> Session
                                        </label>
                                        <select name="session_id" id="session_id" 
                                                class="form-control select2" required>
                                            <option value="">Select Session</option>
                                            <?php foreach ($sessions as $session): ?>
                                                <option value="<?= $session['id'] ?>">
                                                    <?= htmlspecialchars($session['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>
                                            <i class="fas fa-calendar mr-1"></i> Due Date
                                        </label>
                                        <input type="date" name="due_date" 
                                               class="form-control" required
                                               min="<?= date('Y-m-d') ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>
                                            <i class="fas fa-star mr-1"></i> Total Marks
                                        </label>
                                        <input type="number" name="total_marks" 
                                               class="form-control" required 
                                               min="1" max="100" value="100"
                                               placeholder="Enter total marks">
                                        <small class="form-text text-muted">
                                            Maximum marks allowed: 100
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save mr-2"></i> Create Assignment
                            </button>
                            <a href="dashboard.php" class="btn btn-secondary btn-lg float-right">
                                <i class="fas fa-times mr-2"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tips and Guidelines -->
            <div class="col-md-4">
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-lightbulb mr-2"></i>
                            Tips for Creating Assignments
                        </h3>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li class="mb-3">
                                <i class="fas fa-check-circle text-success mr-2"></i>
                                Provide clear, specific instructions
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-clock text-warning mr-2"></i>
                                Set reasonable deadlines
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-list-ul text-info mr-2"></i>
                                Break down complex tasks
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-star text-primary mr-2"></i>
                                Include grading criteria
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4'
    });

    // Form validation
    $('#assignmentForm').on('submit', function(e) {
        const title = $('#title').val().trim();
        const description = $('#description').val().trim();
        const totalMarks = parseInt($('#total_marks').val());
        
        if (title.length < 5) {
            e.preventDefault();
            alert('Assignment title must be at least 5 characters long.');
            return false;
        }
        
        if (description.length < 20) {
            e.preventDefault();
            alert('Assignment description must be at least 20 characters long.');
            return false;
        }

        if (isNaN(totalMarks) || totalMarks < 1 || totalMarks > 100) {
            e.preventDefault();
            alert('Total marks must be between 1 and 100.');
            return false;
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>