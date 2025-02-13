<?php
session_start();
include '../includes/db.php';
include '../includes/header.php';
include '../includes/auth.php';

requireAdmin();

$id = $_GET['id'];

// Fetch session details
$stmt = $conn->prepare("SELECT * FROM sessions WHERE id = :id");
$stmt->execute(['id' => $id]);
$session = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];

    $stmt = $conn->prepare("UPDATE sessions SET name = :name WHERE id = :id");
    $stmt->execute([
        'name' => $name,
        'id' => $id
    ]);

    header("Location: manage_sessions.php");
    exit();
}
?>

<div class="content-wrapper">
    <section class="content">
        <div class="container-fluid">
            <h1>Edit Session</h1>
            <form action="edit_session.php?id=<?= $id ?>" method="POST">
                <div class="form-group">
                    <label for="name">Session Name</label>
                    <input type="text" name="name" class="form-control" value="<?= $session['name'] ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Update Session</button>
            </form>
        </div>
    </section>
</div>

<?php include '../includes/footer.php'; ?>