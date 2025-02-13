<?php
session_start();
include '../includes/db.php';
include '../includes/header.php';
include '../includes/auth.php';

requireAdmin();

// Fetch all sessions
$stmt = $conn->query("SELECT * FROM sessions");
$sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
            <h1>Manage Sessions</h1>
            <a href="add_session.php" class="btn btn-success mb-3">Add New Session</a>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sessions as $session): ?>
                        <tr>
                            <td><?= $session['id'] ?></td>
                            <td><?= $session['name'] ?></td>
                            <td>
                                <a href="edit_session.php?id=<?= $session['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="delete_session.php?id=<?= $session['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<?php include '../includes/footer.php'; ?>