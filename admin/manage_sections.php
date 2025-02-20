<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';

requireAdmin();

$current_datetime = '2025-02-20 21:50:11';
$current_user = 'jarferh';

// Handle bulk actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['bulk_action']) && isset($_POST['selected_sections'])) {
        try {
            $conn->beginTransaction();
            
            if ($_POST['bulk_action'] === 'delete') {
                $selected = $_POST['selected_sections'];
                $placeholders = str_repeat('?,', count($selected) - 1) . '?';
                
                // Check if sections have associated students
                $stmt = $conn->prepare("
                    SELECT COUNT(*) FROM users 
                    WHERE section_id IN ($placeholders)
                ");
                $stmt->execute($selected);
                $hasStudents = $stmt->fetchColumn() > 0;

                if ($hasStudents) {
                    throw new Exception("Cannot delete sections that have associated students.");
                }

                $stmt = $conn->prepare("DELETE FROM sections WHERE id IN ($placeholders)");
                $stmt->execute($selected);
                $_SESSION['success'] = "Selected sections have been deleted successfully.";
            }
            
            $conn->commit();
        } catch (Exception $e) {
            $conn->rollBack();
            $_SESSION['error'] = $e->getMessage();
        }
    } elseif (isset($_POST['name'])) {
        // Add new section
        try {
            $stmt = $conn->prepare("INSERT INTO sections (name) VALUES (:name)");
            $stmt->execute(['name' => $_POST['name']]);
            $_SESSION['success'] = "Section has been added successfully.";
        } catch (Exception $e) {
            $_SESSION['error'] = "Error adding section: " . $e->getMessage();
        }
    }
}

// Fetch sections with related information
$query = "
    SELECT 
        s.*,
        COUNT(DISTINCT u.id) as student_count
    FROM sections s
    LEFT JOIN users u ON s.id = u.section_id AND u.role = 'student'
    GROUP BY s.id
    ORDER BY s.name ASC
";

$sections = $conn->query($query)->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$stats = [
    'total_sections' => count($sections),
    'total_students' => array_sum(array_column($sections, 'student_count'))
];

$pageTitle = "Manage Sections";
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
                                <h5 class="card-title text-white mb-0">Manage Sections</h5>
                                <small class="text-white">
                                    <i class="fas fa-layer-group mr-1"></i>
                                    Total Sections: <?= $stats['total_sections'] ?>
                                </small>
                            </div>
                            <button type="button" class="btn btn-light" data-toggle="modal" data-target="#addSectionModal">
                                <i class="fas fa-plus mr-1"></i>Add New Section
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
                    <span class="info-box-icon"><i class="fas fa-layer-group"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Sections</span>
                        <span class="info-box-number"><?= $stats['total_sections'] ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-box bg-success">
                    <span class="info-box-icon"><i class="fas fa-user-graduate"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Students</span>
                        <span class="info-box-number"><?= $stats['total_students'] ?></span>
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

        <!-- Sections Table -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Section Management</h3>
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
                        <table id="sectionsTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th width="5%"><input type="checkbox" id="selectAll"></th>
                                    <th>Section Name</th>
                                    <th>Students</th>
                                    <th width="15%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sections as $section): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="selected_sections[]" 
                                                   value="<?= $section['id'] ?>" class="section-checkbox">
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($section['name']) ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">
                                                <?= $section['student_count'] ?> Students
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-info" 
                                                        onclick="editSection(<?= $section['id'] ?>, '<?= htmlspecialchars($section['name']) ?>')">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-primary" 
                                                        onclick="viewSection(<?= $section['id'] ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger" 
                                                        onclick="deleteSection(<?= $section['id'] ?>)">
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

<!-- Add Section Modal -->
<div class="modal fade" id="addSectionModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Section</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Section Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Section</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Section Modal -->
<div class="modal fade" id="editSectionModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Section</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="editSectionForm" action="update_section.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" id="edit_section_id" name="id">
                    <div class="form-group">
                        <label for="edit_name">Section Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Section</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#sectionsTable').DataTable({
        "order": [[1, "asc"]],
        "pageLength": 25,
        "responsive": true
    });

    // Handle select all checkbox
    $('#selectAll').change(function() {
        $('.section-checkbox').prop('checked', $(this).prop('checked'));
    });
});

function confirmBulkAction() {
    const action = $('select[name="bulk_action"]').val();
    const selected = $('.section-checkbox:checked').length;

    if (!action) {
        alert('Please select an action');
        return false;
    }

    if (selected === 0) {
        alert('Please select at least one section');
        return false;
    }

    return confirm(`Are you sure you want to delete ${selected} sections?`);
}

function editSection(id, name) {
    $('#edit_section_id').val(id);
    $('#edit_name').val(name);
    $('#editSectionModal').modal('show');
}

function deleteSection(id) {
    if (confirm('Are you sure you want to delete this section? This action cannot be undone.')) {
        window.location.href = `delete_section.php?id=${id}`;
    }
}

function viewSection(id) {
    window.location.href = `view_section.php?id=${id}`;
}

function exportToExcel() {
    const table = $('#sectionsTable').DataTable();
    const filename = `sections_list_${new Date().toISOString().slice(0,10)}.xlsx`;
    
    const wb = XLSX.utils.table_to_book(
        document.getElementById('sectionsTable'), 
        {sheet: "Sections"}
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