<?php
session_start();
include '../includes/db.php';
include '../includes/header.php';
include '../includes/auth.php';

requireAdmin();

$id = $_GET['id'];

// Fetch subject details
$stmt = $conn->prepare("SELECT * FROM subjects WHERE id = :id");
$stmt->execute(['id' => $id]);
$subject = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];

    $stmt = $conn->prepare("UPDATE subjects SET name = :name WHERE id = :id");
    $stmt->execute([
        'name' => $name,
        'id' => $id
    ]);

    header("Location: manage_subjects.php");
    exit();
}
?>

<div class="content-wrapper">
    <section class="content">
        <div class="container-fluid">
            <h1>Edit Subject</h1>
            <form action="edit_subject.php?id=<?= $id ?>" method="POST">
                <div class="form-group">
                    <label for="name">Subject Name</label>
                    <input type="text" name="name" class="form-control" value="<?= $subject['name'] ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Update Subject</button>
            </form>
        </div>
    </section>
</div>

<?php include '../includes/footer.php'; ?>