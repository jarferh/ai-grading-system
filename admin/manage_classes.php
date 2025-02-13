<?php
session_start();
include '../includes/db.php';
include '../includes/header.php';
include '../includes/auth.php';

requireAdmin();

// Fetch all classes
$stmt = $conn->query("SELECT * FROM classes");
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
            <h1>Manage Classes</h1>
            <a href="add_class.php" class="btn btn-success mb-3">Add New Class</a>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($classes as $class): ?>
                        <tr>
                            <td><?= $class['id'] ?></td>
                            <td><?= $class['name'] ?></td>
                            <td>
                                <a href="edit_class.php?id=<?= $class['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="delete_class.php?id=<?= $class['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<?php include '../includes/footer.php'; ?>