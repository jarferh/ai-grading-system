<?php
session_start();
include '../includes/db.php';
include '../includes/header.php';
include '../includes/auth.php';

requireAdmin();

// Fetch all subjects
$stmt = $conn->query("SELECT * FROM subjects");
$subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];

    $stmt = $conn->prepare("INSERT INTO subjects (name) VALUES (:name)");
    $stmt->execute(['name' => $name]);

    header("Location: manage_subjects.php");
    exit();
}
?>

<!-- <div class="content-wrapper"> -->
    <section class="content">
        <div class="container-fluid">
            <h1>Manage Subjects</h1>
            <a href="add_subject.php" class="btn btn-success mb-3">Add New Subject</a>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subjects as $subject): ?>
                        <tr>
                            <td><?= $subject['id'] ?></td>
                            <td><?= $subject['name'] ?></td>
                            <td>
                                <a href="edit_subject.php?id=<?= $subject['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="delete_subject.php?id=<?= $subject['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<?php include '../includes/footer.php'; ?>