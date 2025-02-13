<?php
session_start();
include '../includes/db.php';
include '../includes/header.php';

// Redirect if not student
if ($_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

// Fetch assignments for the student's class
$stmt = $conn->prepare("
    SELECT assignments.id, assignments.title, assignments.description, assignments.due_date 
    FROM assignments 
    JOIN users ON assignments.class_id = users.class_id 
    WHERE users.id = :student_id
");
$stmt->execute(['student_id' => $student_id]);
$assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- <div class="content-wrapper"> -->
    <section class="content">
        <div class="container-fluid">
            <h1>View Assignments</h1>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Due Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($assignments as $assignment): ?>
                        <tr>
                            <td><?= $assignment['title'] ?></td>
                            <td><?= $assignment['description'] ?></td>
                            <td><?= $assignment['due_date'] ?></td>
                            <td>
                                <a href="submit_assignment.php?id=<?= $assignment['id'] ?>" class="btn btn-primary btn-sm">Submit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<?php include '../includes/footer.php'; ?>