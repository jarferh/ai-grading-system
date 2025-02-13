<?php
session_start();
include '../includes/db.php';
include '../includes/header.php';
include '../includes/auth.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];

    $stmt = $conn->prepare("INSERT INTO classes (name) VALUES (:name)");
    $stmt->execute(['name' => $name]);

    header("Location: manage_classes.php");
    exit();
}
?>

<!-- <div class="content-wrapper"> -->
    <section class="content">
        <div class="container-fluid">
            <h1>Add New Class</h1>
            <form action="add_class.php" method="POST">
                <div class="form-group">
                    <label for="name">Class Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Add Class</button>
            </form>
        </div>
    </section>
</div>

<?php include '../includes/footer.php'; ?>