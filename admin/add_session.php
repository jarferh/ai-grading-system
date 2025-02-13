<?php
session_start();
include '../includes/db.php';
include '../includes/header.php';
include '../includes/auth.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];

    $stmt = $conn->prepare("INSERT INTO sessions (name) VALUES (:name)");
    $stmt->execute(['name' => $name]);

    header("Location: manage_sessions.php");
    exit();
}
?>

<!-- <div class="content-wrapper"> -->
    <section class="content">
        <div class="container-fluid">
            <h1>Add New Session</h1>
            <form action="add_session.php" method="POST">
                <div class="form-group">
                    <label for="name">Session Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Add Session</button>
            </form>
        </div>
    </section>
</div>

<?php include '../includes/footer.php'; ?>