<?php
ob_start(); // Start output buffering
session_start();
include '../includes/db.php';
include '../includes/auth.php';

requireTeacher();

// Set page title
$pageTitle = "Create Assignment";
include '../includes/header.php';

$teacher_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $subject_id = $_POST['subject_id'];
    $class_id = $_POST['class_id'];
    $session_id = $_POST['session_id'];
    $due_date = $_POST['due_date'];

    // Insert assignment into the database
    $stmt = $conn->prepare("INSERT INTO assignments (title, description, subject_id, class_id, session_id, due_date, teacher_id) VALUES (:title, :description, :subject_id, :class_id, :session_id, :due_date, :teacher_id)");
    $stmt->execute([
        'title' => $title,
        'description' => $description,
        'subject_id' => $subject_id,
        'class_id' => $class_id,
        'session_id' => $session_id,
        'due_date' => $due_date,
        'teacher_id' => $teacher_id
    ]);

    // Redirect to avoid form resubmission
    header("Location: dashboard.php");
    exit();
}

// Fetch subjects, classes, and sessions for the dropdowns
$subjects = $conn->query("SELECT * FROM subjects")->fetchAll(PDO::FETCH_ASSOC);
$classes = $conn->query("SELECT * FROM classes")->fetchAll(PDO::FETCH_ASSOC);
$sessions = $conn->query("SELECT * FROM sessions")->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- <div class="content-wrapper"> -->
    <section class="content">
        <div class="container-fluid">
            <h1>Create Assignment</h1>
            <form action="create_assignment.php" method="POST">
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea name="description" class="form-control" required></textarea>
                </div>
                <div class="form-group">
                    <label for="subject_id">Subject</label>
                    <select name="subject_id" class="form-control" required>
                        <?php foreach ($subjects as $subject): ?>
                            <option value="<?= $subject['id'] ?>"><?= $subject['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="class_id">Class</label>
                    <select name="class_id" class="form-control" required>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?= $class['id'] ?>"><?= $class['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="session_id">Session</label>
                    <select name="session_id" class="form-control" required>
                        <?php foreach ($sessions as $session): ?>
                            <option value="<?= $session['id'] ?>"><?= $session['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="due_date">Due Date</label>
                    <input type="date" name="due_date" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Create Assignment</button>
            </form>
        </div>
    </section>
</div>

<?php include '../includes/footer.php'; ?>