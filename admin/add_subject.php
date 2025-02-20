<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';

requireAdmin();

$current_datetime = '2025-02-20 22:21:01';
$current_user = 'jarferh';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = trim($_POST['name']);
        
        // Validate subject name
        if (empty($name)) {
            throw new Exception("Subject name is required.");
        }

        // Check if subject already exists
        $check = $conn->prepare("SELECT COUNT(*) FROM subjects WHERE name = ?");
        $check->execute([$name]);
        if ($check->fetchColumn() > 0) {
            throw new Exception("A subject with this name already exists.");
        }

        // Insert new subject
        $stmt = $conn->prepare("INSERT INTO subjects (name) VALUES (:name)");
        $stmt->execute(['name' => $name]);

        $_SESSION['success'] = "Subject has been added successfully.";
        header("Location: manage_subjects.php");
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

$pageTitle = "Add New Subject";
include '../includes/header.php';
?>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6 mx-auto">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-book mr-2"></i>Add New Subject
                        </h3>
                        <div class="card-tools">
                            <small class="text-white">
                                <i class="fas fa-user mr-1"></i><?= htmlspecialchars($current_user) ?>
                            </small>
                        </div>
                    </div>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger m-3">
                            <?= $_SESSION['error'] ?>
                            <?php unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <div class="card-body">
                            <div class="form-group">
                                <label for="name">Subject Name</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       required placeholder="Enter subject name">
                                <small class="form-text text-muted">
                                    Enter the name of the subject (e.g., Mathematics, Physics, etc.)
                                </small>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i>Add Subject
                            </button>
                            <a href="manage_subjects.php" class="btn btn-default float-right">
                                <i class="fas fa-times mr-1"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>