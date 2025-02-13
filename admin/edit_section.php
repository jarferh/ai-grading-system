<?php
session_start();
include '../includes/db.php';
include '../includes/header.php';
include '../includes/auth.php';

requireAdmin();

$id = $_GET['id'];

// Fetch section details
$stmt = $conn->prepare("SELECT * FROM sections WHERE id = :id");
$stmt->execute(['id' => $id]);
$section = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];

    $stmt = $conn->prepare("UPDATE sections SET name = :name WHERE id = :id");
    $stmt->execute([
        'name' => $name,
        'id' => $id
    ]);

    header("Location: manage_sections.php");
    exit();
}
?>

<div class="content-wrapper">
    <section class="content">
        <div class="container-fluid">
            <h1>Edit Section</h1>
            <form action="edit_section.php?id=<?= $id ?>" method="POST">
                <div class="form-group">
                    <label for="name">Section Name</label>
                    <input type="text" name="name" class="form-control" value="<?= $section['name'] ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Update Section</button>
            </form>
        </div>
    </section>
</div>

<?php include '../includes/footer.php'; ?>