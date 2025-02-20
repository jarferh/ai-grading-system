<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';

requireAdmin();

$current_datetime = '2025-02-20 21:59:55';
$current_user = 'jarferh';

// Handle bulk actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['bulk_action']) && isset($_POST['selected_subjects'])) {
        try {
            $conn->beginTransaction();
            
            if ($_POST['bulk_action'] === 'delete') {
                $selected = $_POST['selected_subjects'];
                $placeholders = str_repeat('?,', count($selected) - 1) . '?';
                
                // Check if subjects have associated assignments
                $stmt = $conn->prepare("
                    SELECT COUNT(*) FROM assignments 
                    WHERE subject_id IN ($placeholders)
                ");
                $stmt->execute($selected);
                $hasAssignments = $stmt->fetchColumn() > 0;

                if ($hasAssignments) {
                    throw new Exception("Cannot delete subjects that have associated assignments.");
                }

                $stmt = $conn->prepare("DELETE FROM subjects WHERE id IN ($placeholders)");
                $stmt->execute($selected);
                $_SESSION['success'] = "Selected subjects have been deleted successfully.";
            }
            
            $conn->commit();
        } catch (Exception $e) {
            $conn->rollBack();
            $_SESSION['error'] = $e->getMessage();
        }
    } elseif (isset($_POST['name'])) {
        // Add new subject
        try {
            $stmt = $conn->prepare("INSERT INTO subjects (name) VALUES (:name)");
            $stmt->execute(['name' => $_POST['name']]);
            $_SESSION['success'] = "Subject has been added successfully.";
        } catch (Exception $e) {
            $_SESSION['error'] = "Error adding subject: " . $e->getMessage();
        }
    }
}

// Fetch subjects with related information
$query = "
    SELECT 
        s.*,
        COUNT(DISTINCT a.id) as assignment_count
    FROM subjects s
    LEFT JOIN assignments a ON s.id = a.subject_id
    GROUP BY s.id
    ORDER BY s.name ASC
";

$subjects = $conn->query($query)->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$stats = [
    'total_subjects' => count($subjects),
    'total_assignments' => array_sum(array_column($subjects, 'assignment_count')),
];

$pageTitle = "Manage Subjects";
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
                                <h5 class="card-title text-white mb-0">Manage Subjects</h5>
                                <small class="text-white">
                                    <i class="fas fa-book mr-1"></i>
                                    Total Subjects: <?= $stats['total_subjects'] ?>
                                </small>
                            </div>
                            <button type="button" class="btn btn-light" data-toggle="modal" data-target="#addSubjectModal">
                                <i class="fas fa-plus mr-1"></i>Add New Subject
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="info-box bg-info">
                    <span class="info-box-icon"><i class="fas fa-book"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Subjects</span>
                        <span class="info-box-number"><?= $stats['total_subjects'] ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-box bg-warning">
                    <span class="info-box-icon"><i class="fas fa-tasks"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Assignments</span>
                        <span class="info-box-number"><?= $stats['total_assignments'] ?></span>
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

        <!-- Subjects Table -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Subject Management</h3>
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
                        <table id="subjectsTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th width="5%"><input type="checkbox" id="selectAll"></th>
                                    <th>Subject Name</th>
                                    <th>Assignments</th>
                                    <th width="15%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($subjects as $subject): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="selected_subjects[]" 
                                                   value="<?= $subject['id'] ?>" class="subject-checkbox">
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($subject['name']) ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-warning">
                                                <?= $subject['assignment_count'] ?> Assignments
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-info" 
                                                        onclick="editSubject(<?= $subject['id'] ?>, '<?= htmlspecialchars($subject['name']) ?>')">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-primary" 
                                                        onclick="viewSubject(<?= $subject['id'] ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger" 
                                                        onclick="deleteSubject(<?= $subject['id'] ?>)">
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

<!-- Add Subject Modal -->
<div class="modal fade" id="addSubjectModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Subject</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Subject Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Subject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Subject Modal -->
<div class="modal fade" id="editSubjectModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Subject</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="editSubjectForm" action="update_subject.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" id="edit_subject_id" name="id">
                    <div class="form-group">
                        <label for="edit_name">Subject Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Subject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#subjectsTable').DataTable({
        "order": [[1, "asc"]],
        "pageLength": 25,
        "responsive": true
    });

    // Handle select all checkbox
    $('#selectAll').change(function() {
        $('.subject-checkbox').prop('checked', $(this).prop('checked'));
    });
});

function confirmBulkAction() {
    const action = $('select[name="bulk_action"]').val();
    const selected = $('.subject-checkbox:checked').length;

    if (!action) {
        alert('Please select an action');
        return false;
    }

    if (selected === 0) {
        alert('Please select at least one subject');
        return false;
    }

    return confirm(`Are you sure you want to delete ${selected} subjects?`);
}

function editSubject(id, name) {
    $('#edit_subject_id').val(id);
    $('#edit_name').val(name);
    $('#editSubjectModal').modal('show');
}

function deleteSubject(id) {
    if (confirm('Are you sure you want to delete this subject? This action cannot be undone.')) {
        window.location.href = `delete_subject.php?id=${id}`;
    }
}

function viewSubject(id) {
    window.location.href = `view_subject.php?id=${id}`;
}

function exportToExcel() {
    const table = $('#subjectsTable').DataTable();
    const filename = `subjects_list_${new Date().toISOString().slice(0,10)}.xlsx`;
    
    const wb = XLSX.utils.table_to_book(
        document.getElementById('subjectsTable'), 
        {sheet: "Subjects"}
    );
    XLSX.writeFile(wb, filename);
}
</script>

<style>
.info-box {
    min-height: 100px;
    background: #fff;
    width: 100%;
    box-shadow: 0 1px 1px rgba(0,0,0,0.1);
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

.btn-group-sm > .btn, .btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.table td {
    vertical-align: middle;
}
</style>

<?php include '../includes/footer.php'; ?>