<?php
session_start();
include '../includes/db.php';
include '../includes/header.php';

// Redirect if not admin
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$id = $_GET['id'];

// Fetch user details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : $user['password'];
    $role = $_POST['role'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];

    $stmt = $conn->prepare("UPDATE users SET username = :username, password = :password, role = :role, full_name = :full_name, email = :email WHERE id = :id");
    $stmt->execute([
        'username' => $username,
        'password' => $password,
        'role' => $role,
        'full_name' => $full_name,
        'email' => $email,
        'id' => $id
    ]);

    header("Location: manage_users.php");
    exit();
}
?>

<div class="content-wrapper">
    <section class="content">
        <div class="container-fluid">
            <h1>Edit User</h1>
            <form action="edit_user.php?id=<?= $id ?>" method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" class="form-control" value="<?= $user['username'] ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">Password (leave blank to keep current)</label>
                    <input type="password" name="password" class="form-control">
                </div>
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" name="full_name" class="form-control" value="<?= $user['full_name'] ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= $user['email'] ?>" required>
                </div>
                <div class="form-group">
                    <label for="role">Role</label>
                    <select name="role" class="form-control" required>
                        <option value="student" <?= $user['role'] === 'student' ? 'selected' : '' ?>>Student</option>
                        <option value="teacher" <?= $user['role'] === 'teacher' ? 'selected' : '' ?>>Teacher</option>
                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Update User</button>
            </form>
        </div>
    </section>
</div>

<?php include '../includes/footer.php'; ?>