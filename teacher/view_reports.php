<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';

requireTeacher();

$teacher_id = $_SESSION['user_id'];
$current_datetime = '2025-02-20 20:43:52';
$current_user = 'jarferh';

// Get teacher's information
$stmt = $conn->prepare("
    SELECT u.*, c.name as assigned_class, s.name as assigned_section 
    FROM users u 
    LEFT JOIN classes c ON u.class_id = c.id 
    LEFT JOIN sections s ON u.section_id = s.id 
    WHERE u.id = :teacher_id
");
$stmt->execute(['teacher_id' => $teacher_id]);
$teacher = $stmt->fetch(PDO::FETCH_ASSOC);

// Get filter values
$date_filter = $_GET['date_range'] ?? '';
$score_filter = $_GET['score_range'] ?? '';
$class_filter = $_GET['class_id'] ?? '';
$subject_filter = $_GET['subject_id'] ?? '';

// Base query with additional fields
$query = "
    SELECT 
        a.title,
        a.description,
        u.full_name,
        u.email,
        c.name as class_name,
        s.name as subject_name,
        g.score,
        g.remarks,
        sub.submission_date,
        sub.content as submission_content
    FROM grades g
    JOIN submissions sub ON g.submission_id = sub.id
    JOIN assignments a ON sub.assignment_id = a.id
    JOIN users u ON sub.student_id = u.id
    JOIN classes c ON a.class_id = c.id
    JOIN subjects s ON a.subject_id = s.id
    WHERE a.teacher_id = :teacher_id
";

// Add filters
$params = ['teacher_id' => $teacher_id];

if ($date_filter) {
    switch ($date_filter) {
        case 'today':
            $query .= " AND DATE(sub.submission_date) = CURDATE()";
            break;
        case 'week':
            $query .= " AND sub.submission_date >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK)";
            break;
        case 'month':
            $query .= " AND sub.submission_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
            break;
    }
}

if ($score_filter) {
    switch ($score_filter) {
        case 'above90':
            $query .= " AND g.score >= 90";
            break;
        case '70to89':
            $query .= " AND g.score BETWEEN 70 AND 89";
            break;
        case 'below70':
            $query .= " AND g.score < 70";
            break;
    }
}

if ($class_filter) {
    $query .= " AND a.class_id = :class_id";
    $params['class_id'] = $class_filter;
}

if ($subject_filter) {
    $query .= " AND a.subject_id = :subject_id";
    $params['subject_id'] = $subject_filter;
}

$query .= " ORDER BY sub.submission_date DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$stats = [
    'total_submissions' => count($reports),
    'average_score' => 0,
    'high_performers' => 0,
    'low_performers' => 0
];

if (!empty($reports)) {
    $total_score = array_sum(array_column($reports, 'score'));
    $stats['average_score'] = $total_score / count($reports);
    $stats['high_performers'] = count(array_filter($reports, function ($r) {
        return $r['score'] >= 90;
    }));
    $stats['low_performers'] = count(array_filter($reports, function ($r) {
        return $r['score'] < 70;
    }));
}

// Fetch classes and subjects for filters
$classes = $conn->query("SELECT * FROM classes ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$subjects = $conn->query("SELECT * FROM subjects ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Prepare data for charts
$chart_data = [
    'by_student' => [],
    'by_class' => [],
    'by_subject' => [],
    'score_distribution' => [
        '90-100' => 0,
        '80-89' => 0,
        '70-79' => 0,
        '60-69' => 0,
        'Below 60' => 0
    ]
];

foreach ($reports as $report) {
    // Student averages
    if (!isset($chart_data['by_student'][$report['full_name']])) {
        $chart_data['by_student'][$report['full_name']] = ['scores' => [], 'average' => 0];
    }
    $chart_data['by_student'][$report['full_name']]['scores'][] = $report['score'];

    // Class averages
    if (!isset($chart_data['by_class'][$report['class_name']])) {
        $chart_data['by_class'][$report['class_name']] = ['scores' => [], 'average' => 0];
    }
    $chart_data['by_class'][$report['class_name']]['scores'][] = $report['score'];

    // Subject averages
    if (!isset($chart_data['by_subject'][$report['subject_name']])) {
        $chart_data['by_subject'][$report['subject_name']] = ['scores' => [], 'average' => 0];
    }
    $chart_data['by_subject'][$report['subject_name']]['scores'][] = $report['score'];

    // Score distribution
    if ($report['score'] >= 90) $chart_data['score_distribution']['90-100']++;
    elseif ($report['score'] >= 80) $chart_data['score_distribution']['80-89']++;
    elseif ($report['score'] >= 70) $chart_data['score_distribution']['70-79']++;
    elseif ($report['score'] >= 60) $chart_data['score_distribution']['60-69']++;
    else $chart_data['score_distribution']['Below 60']++;
}

// Calculate averages
foreach (['by_student', 'by_class', 'by_subject'] as $key) {
    foreach ($chart_data[$key] as &$data) {
        $data['average'] = array_sum($data['scores']) / count($data['scores']);
    }
    unset($data);
}

$pageTitle = "View Reports";
include '../includes/header.php';
?>

<section class="content">
    <div class="container-fluid">
        <!-- Filters Card -->
        <div class="card card-outline card-primary mb-4">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-filter mr-2"></i>
                    Filter Reports
                </h3>
            </div>
            <div class="card-body">
                <form method="GET" class="row">
                    <!-- Subject Filter (Primary Filter) -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <select name="subject_id" class="form-control select2" id="subjectFilter">
                                <option value="">Select Subject</option>
                                <?php foreach ($subjects as $subject): ?>
                                    <option value="<?= $subject['id'] ?>"
                                        <?= $subject_filter == $subject['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($subject['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Class Filter -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <select name="class_id" class="form-control select2">
                                <option value="">Select Class</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?= $class['id'] ?>"
                                        <?= $class_filter == $class['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($class['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search mr-2"></i>View Reports
                        </button>
                        <a href="view_reports.php" class="btn btn-default ml-2">
                            <i class="fas fa-undo mr-2"></i>Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Reports Table (Initially Hidden) -->
        <div class="card" id="reportsSection" style="display: <?= ($subject_filter || $class_filter) ? 'block' : 'none' ?>;">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-table mr-2"></i>
                    Score Sheet
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-success" onclick="exportToExcel()">
                        <i class="fas fa-file-excel mr-2"></i>Export
                    </button>
                </div>
            </div>
            <div class="card-body table-responsive">
                <?php if (!empty($reports)): ?>
                    <table id="reportsTable" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Assignment</th>
                                <th>Score</th>
                                <th>Submission Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reports as $report): ?>
                                <tr>
                                    <td><?= htmlspecialchars($report['full_name']) ?></td>
                                    <td><?= htmlspecialchars($report['title']) ?></td>
                                    <td>
                                        <span class="badge badge-<?= getScoreClass($report['score']) ?>">
                                            <?= number_format($report['score'], 1) ?>%
                                        </span>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($report['submission_date'])) ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info"
                                            onclick="viewDetails(<?= htmlspecialchars(json_encode($report)) ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        Select a subject and class to view the score sheet.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php
        function getScoreClass($score)
        {
            if ($score >= 90) return 'success';
            if ($score >= 80) return 'info';
            if ($score >= 70) return 'warning';
            return 'danger';
        }
        ?>

    </div>
</section>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Submission Details</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Assignment Information</h6>
                        <p id="modalTitle" class="font-weight-bold"></p>
                        <p id="modalDescription" class="text-muted"></p>
                    </div>
                    <div class="col-md-6">
                        <h6>Student Information</h6>
                        <p id="modalStudent"></p>
                        <p id="modalClass"></p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-12">
                        <h6>Student's Answer</h6>
                        <div id="modalContent" class="p-3 bg-light rounded"></div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-12">
                        <h6>Feedback</h6>
                        <div id="modalRemarks" class="p-3 border-left border-info"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>

<script>
     $(document).ready(function() {
            // Initialize Select2
            $('.select2').select2({
                theme: 'bootstrap4',
                placeholder: 'Select an option'
            });

            // Initialize DataTable only if there are reports
            if ($('#reportsTable tbody tr').length > 0) {
                $('#reportsTable').DataTable({
                    "paging": true,
                    "lengthChange": false,
                    "searching": true,
                    "ordering": true,
                    "info": true,
                    "autoWidth": false,
                    "responsive": true,
                    "pageLength": 10
                });
            }
        });

    // View Details Function
    function viewDetails(report) {
        $('#modalTitle').text(report.title);
        $('#modalDescription').text(report.description);
        $('#modalStudent').html(`<strong>Name:</strong> ${report.full_name}<br><strong>Email:</strong> ${report.email}`);
        $('#modalClass').html(`<strong>Class:</strong> ${report.class_name}<br><strong>Subject:</strong> ${report.subject_name}`);
        $('#modalContent').text(report.submission_content);
        $('#modalRemarks').text(report.remarks);
        $('#detailsModal').modal('show');
    }

    // Export to Excel Function
    function exportToExcel() {
        const table = $('#reportsTable').DataTable();
        table.button('.buttons-excel').trigger();
    }
</script>

<style>
    .small-box {
        position: relative;
        padding: 20px;
        margin-bottom: 20px;
        border-radius: 4px;
    }

    .small-box .inner {
        padding: 10px;
    }

    .small-box h3 {
        font-size: 2.2rem;
        font-weight: 700;
        margin: 0 0 10px 0;
        white-space: nowrap;
        padding: 0;
    }

    .small-box .icon {
        position: absolute;
        top: 15px;
        right: 15px;
        font-size: 70px;
        color: rgba(0, 0, 0, 0.15);
    }

    .select2-container .select2-selection--single {
        height: calc(2.25rem + 2px);
    }
</style>

<?php include '../includes/footer.php'; ?>