<?php
session_start();
include '../includes/db.php';
include '../includes/header.php';
include '../includes/auth.php';

requireAdmin();

$id = $_GET['id'];

// Fetch class details
$stmt = $conn->prepare("SELECT * FROM classes WHERE id = :id");
$stmt->execute(['id' => $id]);
$class = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];

    $stmt = $conn->prepare("UPDATE classes SET name = :name WHERE id = :id");
    $stmt->execute([
        'name' => $name,
        'id' => $id
    ]);

    header("Location: manage_classes.php");
    exit();
}
?>

<div class="content-wrapper">
    <section class="content">
        <div class="container-fluid">
            <h1>Edit Class</h1>
            <form action="edit_class.php?id=<?= $id ?>" method="POST">
                <div class="form-group">
                    <label for="name">Class Name</label>
                    <input type="text" name="name" class="form-control" value="<?= $class['name'] ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Update Class</button>
            </form>
        </div>
    </section>
</div>

<?php include '../includes/footer.php'; ?>