<?php
session_start();
include '../includes/db.php';
include '../includes/header.php';
include '../includes/auth.php';

requireAdmin();

// Fetch all sections
$stmt = $conn->query("SELECT * FROM sections");
$sections = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];

    $stmt = $conn->prepare("INSERT INTO sections (name) VALUES (:name)");
    $stmt->execute(['name' => $name]);

    header("Location: manage_sections.php");
    exit();
}
?>

<!-- <div class="content-wrapper"> -->
    <section class="content">
        <div class="container-fluid">
            <h1>Manage Sections</h1>
            <a href="add_section.php" class="btn btn-success mb-3">Add New Section</a>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sections as $section): ?>
                        <tr>
                            <td><?= $section['id'] ?></td>
                            <td><?= $section['name'] ?></td>
                            <td>
                                <a href="edit_section.php?id=<?= $section['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="delete_section.php?id=<?= $section['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<?php include '../includes/footer.php'; ?>