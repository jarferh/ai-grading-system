<?php
@session_start();
include '../includes/db.php';
include '../includes/auth.php';

requireStudent();

// Set page title
$pageTitle = "Student Dashboard";
include '../includes/header.php';

$student_id = $_SESSION['user_id'];

// Fetch student's class and section
$stmt = $conn->prepare("SELECT class_id, section_id FROM users WHERE id = :student_id");
$stmt->execute(['student_id' => $student_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);
$class_id = $student['class_id'];
$section_id = $student['section_id'];

// Fetch all assignments for the student's class
$stmt = $conn->prepare("
    SELECT assignments.id, assignments.title, assignments.description, assignments.due_date, subjects.name AS subject_name 
    FROM assignments 
    JOIN subjects ON assignments.subject_id = subjects.id 
    WHERE assignments.class_id = :class_id
");
$stmt->execute(['class_id' => $class_id]);
$assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch submitted assignments
$stmt = $conn->prepare("
    SELECT assignment_id FROM submissions WHERE student_id = :student_id
");
$stmt->execute(['student_id' => $student_id]);
$submitted_assignments = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Calculate totals
$total_assignments = count($assignments);
$total_submitted = count($submitted_assignments);
$total_pending = $total_assignments - $total_submitted;
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
                            <h5 class="card-title">Submitted Assignments</h5>
                            <p class="card-text display-4"><?= $total_submitted ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-danger">
                        <div class="card-body">
                            <h5 class="card-title">Pending Assignments</h5>
                            <p class="card-text display-4"><?= $total_pending ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Filters</h3>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="subject">Subject</label>
                                            <select name="subject" id="subject" class="form-control">
                                                <option value="">All Subjects</option>
                                                <?php
                                                $stmt = $conn->query("SELECT * FROM subjects");
                                                $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                                foreach ($subjects as $subject): ?>
                                                    <option value="<?= $subject['id'] ?>" <?= isset($_GET['subject']) && $_GET['subject'] == $subject['id'] ? 'selected' : '' ?>>
                                                        <?= $subject['name'] ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="status">Status</label>
                                            <select name="status" id="status" class="form-control">
                                                <option value="">All</option>
                                                <option value="submitted" <?= isset($_GET['status']) && $_GET['status'] == 'submitted' ? 'selected' : '' ?>>Submitted</option>
                                                <option value="pending" <?= isset($_GET['status']) && $_GET['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <button type="submit" class="btn btn-primary btn-block">Apply Filters</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assignments List -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Assignments</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Subject</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($assignments as $assignment): ?>
                                        <?php
                                        $is_submitted = in_array($assignment['id'], $submitted_assignments);
                                        $status = $is_submitted ? 'Submitted' : 'Pending';
                                        ?>
                                        <tr>
                                            <td><?= $assignment['title'] ?></td>
                                            <td><?= $assignment['subject_name'] ?></td>
                                            <td><?= $assignment['due_date'] ?></td>
                                            <td>
                                                <span class="badge <?= $is_submitted ? 'bg-success' : 'bg-danger' ?>">
                                                    <?= $status ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (!$is_submitted): ?>
                                                    <a href="submit_assignment.php?id=<?= $assignment['id'] ?>" class="btn btn-primary btn-sm">Submit</a>
                                                <?php else: ?>
                                                    <span class="text-muted">Submitted</span>
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
</div>

<?php include '../includes/footer.php'; ?>