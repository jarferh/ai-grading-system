<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';

requireAdmin();

$current_datetime = '2025-02-20 22:32:52';
$current_user = 'jarferh';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    $_SESSION['error'] = "Invalid class ID";
    header("Location: manage_classes.php");
    exit();
}

try {
    // Fetch class details
    $stmt = $conn->prepare("SELECT * FROM classes WHERE id = ?");
    $stmt->execute([$id]);
    $class = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$class) {
        throw new Exception("Class not found");
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name']);

        // Validate input
        if (empty($name)) {
            throw new Exception("Class name is required");
        }

        // Check if name exists for another class
        $stmt = $conn->prepare("SELECT COUNT(*) FROM classes WHERE name = ? AND id != ?");
        $stmt->execute([$name, $id]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("A class with this name already exists");
        }

        // Update class
        $stmt = $conn->prepare("UPDATE classes SET name = ?, updated_at = ? WHERE id = ?");
        $stmt->execute([$name, $current_datetime, $id]);

        $_SESSION['success'] = "Class updated successfully";
        header("Location: manage_classes.php");
        exit();
    }
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
}

$pageTitle = "Edit Class";
include '../includes/header.php';
?>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6 mx-auto">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-edit mr-2"></i>Edit Class
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
                                <label for="name">Class Name</label>
                                <input type="text" class="form-control" id="name" name="name"
                                       value="<?= htmlspecialchars($class['name']) ?>"
                                       required>
                                <small class="form-text text-muted">
                                    Enter the name of the class (e.g., Class 10, Grade 12)
                                </small>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i>Update Class
                            </button>
                            <a href="manage_classes.php" class="btn btn-default float-right">
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