<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = trim($_POST['name']);
        
        // Validate class name
        if (empty($name)) {
            throw new Exception("Class name is required.");
        }

        // Check if class already exists
        $check = $conn->prepare("SELECT COUNT(*) FROM classes WHERE name = ?");
        $check->execute([$name]);
        if ($check->fetchColumn() > 0) {
            throw new Exception("A class with this name already exists.");
        }

        // Insert new class
        $stmt = $conn->prepare("INSERT INTO classes (name) VALUES (:name)");
        $stmt->execute(['name' => $name]);

        $_SESSION['success'] = "Class has been added successfully.";
        header("Location: manage_classes.php");
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

$pageTitle = "Add New Class";
include '../includes/header.php';
?>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6 mx-auto">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Add New Class</h3>
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
                                       required placeholder="Enter class name">
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Add Class</button>
                            <a href="manage_classes.php" class="btn btn-default float-right">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>