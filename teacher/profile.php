<?php
@session_start();
include '../includes/db.php';
include '../includes/auth.php';

requireStudent();

$pageTitle = "My Profile";
include '../includes/header.php';

$student_id = $_SESSION['user_id'];

// Fetch user details with class and section
$stmt = $conn->prepare("
    SELECT 
        u.*,
        c.name as class_name,
        s.name as section_name
    FROM users u
    LEFT JOIN classes c ON u.class_id = c.id
    LEFT JOIN sections s ON u.section_id = s.id
    WHERE u.id = :student_id
");
$stmt->execute(['student_id' => $student_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch statistics
$stmt = $conn->prepare("
    SELECT 
        COUNT(DISTINCT a.id) as total_assignments,
        COUNT(DISTINCT s.id) as submitted_assignments,
        AVG(g.score) as average_grade
    FROM assignments a
    LEFT JOIN submissions s ON a.id = s.assignment_id AND s.student_id = :student_id
    LEFT JOIN grades g ON s.id = g.submission_id
    WHERE a.class_id = :class_id
");
$stmt->execute([
    'student_id' => $student_id,
    'class_id' => $user['class_id']
]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $email = $_POST['email'] ?? '';
    
    $errors = [];
    
    // Validate current password
    if (!empty($current_password)) {
        if (!password_verify($current_password, $user['password'])) {
            $errors[] = "Current password is incorrect.";
        } elseif ($new_password !== $confirm_password) {
            $errors[] = "New passwords do not match.";
        } elseif (strlen($new_password) < 6) {
            $errors[] = "New password must be at least 6 characters long.";
        }
    }
    
    // Update profile if no errors
    if (empty($errors)) {
        $query = "UPDATE users SET email = :email";
        $params = ['email' => $email, 'id' => $student_id];
        
        if (!empty($new_password)) {
            $query .= ", password = :password";
            $params['password'] = password_hash($new_password, PASSWORD_DEFAULT);
        }
        
        $query .= " WHERE id = :id";
        
        $stmt = $conn->prepare($query);
        if ($stmt->execute($params)) {
            $_SESSION['success_message'] = "Profile updated successfully!";
            header("Location: profile.php");
            exit;
        } else {
            $errors[] = "Failed to update profile.";
        }
    }
}
?>

<section class="content">
    <div class="container-fluid">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($_SESSION['success_message']) ?>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <div class="row">
            <!-- Profile Details -->
            <div class="col-md-4">
                <div class="card card-primary card-outline">
                    <div class="card-body box-profile">
                        <div class="text-center">
                            <div class="user-avatar <?= $userRoleColor ?> mx-auto" style="width: 100px; height: 100px; font-size: 3em;">
                                <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                            </div>
                        </div>

                        <h3 class="profile-username text-center"><?= htmlspecialchars($user['full_name']) ?></h3>
                        <p class="text-muted text-center"><?= ucfirst($user['role']) ?></p>

                        <ul class="list-group list-group-unbordered mb-3">
                            <li class="list-group-item">
                                <b>Class</b> <a class="float-right"><?= htmlspecialchars($user['class_name']) ?></a>
                            </li>
                            <li class="list-group-item">
                                <b>Section</b> <a class="float-right"><?= htmlspecialchars($user['section_name']) ?></a>
                            </li>
                            <li class="list-group-item">
                                <b>Email</b> <a class="float-right"><?= htmlspecialchars($user['email']) ?></a>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Academic Progress -->
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Academic Progress</h3>
                    </div>
                    <div class="card-body">
                        <div class="progress-group">
                            Assignments Completed
                            <span class="float-right">
                                <?= $stats['submitted_assignments'] ?>/<?= $stats['total_assignments'] ?>
                            </span>
                            <div class="progress">
                                <div class="progress-bar bg-primary" style="width: <?= ($stats['submitted_assignments'] / max(1, $stats['total_assignments'])) * 100 ?>%"></div>
                            </div>
                        </div>
                        <div class="progress-group mt-3">
                            Average Grade
                            <span class="float-right"><?= round($stats['average_grade'] ?? 0, 1) ?>%</span>
                            <div class="progress">
                                <div class="progress-bar bg-success" style="width: <?= $stats['average_grade'] ?? 0 ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Profile -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Edit Profile</h3>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="current_password">Current Password</label>
                                <input type="password" class="form-control" id="current_password" 
                                       name="current_password">
                                <small class="form-text text-muted">
                                    Required only if changing password
                                </small>
                            </div>

                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" class="form-control" id="new_password" 
                                       name="new_password">
                            </div>

                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" 
                                       name="confirm_password">
                            </div>

                            <button type="submit" class="btn btn-primary">Update Profile</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>