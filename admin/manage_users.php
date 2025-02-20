<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';

requireAdmin();

$current_datetime = '2025-02-20 21:23:34';
$current_user = 'jarferh';

// Handle bulk actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'])) {
    $selected_users = $_POST['selected_users'] ?? [];
    $action = $_POST['bulk_action'];

    if (!empty($selected_users)) {
        try {
            $conn->beginTransaction();

            if ($action === 'delete') {
                $stmt = $conn->prepare("DELETE FROM users WHERE id IN (" . str_repeat('?,', count($selected_users) - 1) . "?)");
                $stmt->execute($selected_users);
                $_SESSION['success'] = count($selected_users) . " users have been deleted.";
            }

            $conn->commit();
        } catch (Exception $e) {
            $conn->rollBack();
            $_SESSION['error'] = "Error: " . $e->getMessage();
        }
    }
}

// Fetch users with additional information
$query = "
    SELECT 
        u.*,
        c.name as class_name,
        s.name as section_name,
        COUNT(DISTINCT a.id) as total_assignments,
        COUNT(DISTINCT sub.id) as total_submissions
    FROM users u
    LEFT JOIN classes c ON u.class_id = c.id
    LEFT JOIN sections s ON u.section_id = s.id
    LEFT JOIN assignments a ON u.id = a.teacher_id
    LEFT JOIN submissions sub ON u.id = sub.student_id
    GROUP BY u.id
    ORDER BY u.id DESC
";

$users = $conn->query($query)->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$stats = [
    'total' => count($users),
    'teachers' => count(array_filter($users, fn($u) => $u['role'] === 'teacher')),
    'students' => count(array_filter($users, fn($u) => $u['role'] === 'student')),
    'admins' => count(array_filter($users, fn($u) => $u['role'] === 'admin')),
];

$pageTitle = "Manage Users";
include '../includes/header.php';
?>

<section class="content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card bg-gradient-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title text-white mb-0">Manage Users</h5>
                                <small class="text-white">
                                    <i class="fas fa-users mr-1"></i> Total Users: <?= $stats['total'] ?>
                                </small>
                            </div>
                            <div>
                                <a href="add_user.php" class="btn btn-light">
                                    <i class="fas fa-user-plus mr-1"></i>Add New User
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="info-box bg-info">
                    <span class="info-box-icon"><i class="fas fa-chalkboard-teacher"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Teachers</span>
                        <span class="info-box-number"><?= $stats['teachers'] ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box bg-success">
                    <span class="info-box-icon"><i class="fas fa-user-graduate"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Students</span>
                        <span class="info-box-number"><?= $stats['students'] ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box bg-warning">
                    <span class="info-box-icon"><i class="fas fa-user-shield"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Admins</span>
                        <span class="info-box-number"><?= $stats['admins'] ?></span>
                    </div>
                </div>
            </div>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <?= $_SESSION['success'] ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <?= $_SESSION['error'] ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Users Table -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">User Management</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form id="bulkActionForm" method="POST">
                    <div class="mb-3">
                        <div class="btn-group">
                            <select name="bulk_action" class="form-control form-control-sm" style="width: 200px;">
                                <option value="">Bulk Actions</option>
                                <option value="delete">Delete Selected</option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary" onclick="return confirmBulkAction()">
                                Apply
                            </button>
                        </div>
                        <button type="button" class="btn btn-sm btn-success float-right" onclick="exportToExcel()">
                            <i class="fas fa-file-excel mr-1"></i>Export to Excel
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table id="usersTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="selectAll">
                                    </th>
                                    <th>User Info</th>
                                    <th>Role & Status</th>
                                    <th>Class/Section</th>
                                    <th>Activity</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="selected_users[]"
                                                value="<?= $user['id'] ?>" class="user-checkbox">
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($user['full_name']) ?></strong>
                                            <small class="d-block text-muted">
                                                @<?= htmlspecialchars($user['username']) ?>
                                            </small>
                                            <small class="text-muted">
                                                <i class="fas fa-envelope mr-1"></i><?= htmlspecialchars($user['email']) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?=
                                                                        $user['role'] == 'admin' ? 'danger' : ($user['role'] == 'teacher' ? 'info' : 'success')
                                                                        ?>">
                                                <?= ucfirst($user['role']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($user['class_name']): ?>
                                                <?= htmlspecialchars($user['class_name']) ?>
                                                <?= $user['section_name'] ? ' - ' . htmlspecialchars($user['section_name']) : '' ?>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($user['role'] == 'teacher'): ?>
                                                <small class="d-block">
                                                    <i class="fas fa-tasks mr-1"></i>
                                                    Assignments: <?= $user['total_assignments'] ?>
                                                </small>
                                            <?php elseif ($user['role'] == 'student'): ?>
                                                <small class="d-block">
                                                    <i class="fas fa-file-alt mr-1"></i>
                                                    Submissions: <?= $user['total_submissions'] ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="edit_user.php?id=<?= $user['id'] ?>"
                                                    class="btn btn-sm btn-info">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="view_user.php?id=<?= $user['id'] ?>"
                                                    class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger"
                                                    onclick="deleteUser(<?= $user['id'] ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#usersTable').DataTable({
            "order": [
                [1, "asc"]
            ],
            "pageLength": 25,
            "responsive": true
        });

        // Handle select all checkbox
        $('#selectAll').change(function() {
            $('.user-checkbox').prop('checked', $(this).prop('checked'));
        });
    });

    function confirmBulkAction() {
    const action = $('select[name="bulk_action"]').val();
    const selected = $('.user-checkbox:checked').length;

    if (!action) {
        alert('Please select an action');
        return false;
    }

    if (selected === 0) {
        alert('Please select at least one user');
        return false;
    }

    return confirm(`Are you sure you want to delete ${selected} users? This action cannot be undone.`);
}

    function deleteUser(userId) {
        if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
            window.location.href = `delete_user.php?id=${userId}`;
        }
    }

    function exportToExcel() {
        const table = $('#usersTable').DataTable();
        const filename = `users_list_${new Date().toISOString().slice(0,10)}.xlsx`;

        const wb = XLSX.utils.table_to_book(
            document.getElementById('usersTable'), {
                sheet: "Users"
            }
        );
        XLSX.writeFile(wb, filename);
    }
</script>

<style>
    .info-box {
        min-height: 100px;
        background: #fff;
        width: 100%;
        box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
        border-radius: 4px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        padding: 20px;
    }

    .info-box-icon {
        width: 70px;
        height: 70px;
        font-size: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        margin-right: 20px;
        color: #fff;
    }

    .badge {
        padding: 0.4em 0.8em;
        font-size: 85%;
    }

    .btn-group-sm>.btn,
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
</style>

<?php include '../includes/footer.php'; ?>