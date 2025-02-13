<?php
session_start();
include '../includes/db.php';
include '../includes/header.php';
include '../includes/auth.php';

requireTeacher();

$teacher_id = $_SESSION['user_id'];

// Fetch assignments and grades for the teacher's classes
$stmt = $conn->prepare("
    SELECT assignments.title, users.full_name, grades.score, grades.remarks 
    FROM grades 
    JOIN submissions ON grades.submission_id = submissions.id 
    JOIN assignments ON submissions.assignment_id = assignments.id 
    JOIN users ON submissions.student_id = users.id 
    WHERE assignments.teacher_id = :teacher_id
");
$stmt->execute(['teacher_id' => $teacher_id]);
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- <div class="content-wrapper"> -->
    <section class="content">
        <div class="container-fluid">
            <h1>View Reports</h1>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Assignment Title</th>
                        <th>Student Name</th>
                        <th>Score</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reports as $report): ?>
                        <tr>
                            <td><?= $report['title'] ?></td>
                            <td><?= $report['full_name'] ?></td>
                            <td><?= $report['score'] ?></td>
                            <td><?= $report['remarks'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<?php include '../includes/footer.php'; ?>