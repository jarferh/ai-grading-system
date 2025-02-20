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
    $stats['high_performers'] = count(array_filter($reports, function($r) { return $r['score'] >= 90; }));
    $stats['low_performers'] = count(array_filter($reports, function($r) { return $r['score'] < 70; }));
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
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card bg-gradient-info">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title mb-0">Student Performance Reports</h5>
                                <small class="text-white">
                                    <i class="fas fa-user mr-1"></i> <?= htmlspecialchars($teacher['full_name']) ?> | 
                                    <i class="fas fa-users mr-1"></i> 
                                    <?= htmlspecialchars($teacher['assigned_class'] ?? 'No Class') ?>
                                    <?= $teacher['assigned_section'] ? ' - ' . htmlspecialchars($teacher['assigned_section']) : '' ?>
                                </small>
                            </div>
                            <div>
                                <span class="badge badge-light">
                                    <i class="far fa-clock mr-1"></i> 
                                    <?= date('M d, Y H:i', strtotime($current_datetime)) ?> UTC
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= $stats['total_submissions'] ?></h3>
                        <p>Total Submissions</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?= number_format($stats['average_score'], 1) ?>%</h3>
                        <p>Average Score</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?= $stats['high_performers'] ?></h3>
                        <p>High Performers (≥90%)</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-star"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3><?= $stats['low_performers'] ?></h3>
                        <p>Low Performers (<70%)</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>

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
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Date Range</label>
                            <select name="date_range" class="form-control select2">
                                <option value="">All Time</option>
                                <option value="today" <?= $date_filter == 'today' ? 'selected' : '' ?>>Today</option>
                                <option value="week" <?= $date_filter == 'week' ? 'selected' : '' ?>>Last 7 Days</option>
                                <option value="month" <?= $date_filter == 'month' ? 'selected' : '' ?>>Last 30 Days</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Score Range</label>
                            <select name="score_range" class="form-control select2">
                                <option value="">All Scores</option>
                                <option value="above90" <?= $score_filter == 'above90' ? 'selected' : '' ?>>Above 90%</option>
                                <option value="70to89" <?= $score_filter == '70to89' ? 'selected' : '' ?>>70% - 89%</option>
                                <option value="below70" <?= $score_filter == 'below70' ? 'selected' : '' ?>>Below 70%</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Class</label>
                            <select name="class_id" class="form-control select2">
                                <option value="">All Classes</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?= $class['id'] ?>" 
                                            <?= $class_filter == $class['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($class['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Subject</label>
                            <select name="subject_id" class="form-control select2">
                                <option value="">All Subjects</option>
                                <?php foreach ($subjects as $subject): ?>
                                    <option value="<?= $subject['id'] ?>"
                                            <?= $subject_filter == $subject['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($subject['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-12 text-right">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search mr-2"></i>Apply Filters
                        </button>
                        <a href="view_report.php" class="btn btn-default">
                            <i class="fas fa-undo mr-2"></i>Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row">
            <!-- Student Performance Chart -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Student Performance</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="studentChart" height="300"></canvas>
                    </div>
                </div>
            </div>

            <!-- Score Distribution Chart -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Score Distribution</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="distributionChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Subject and Class Performance -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Performance by Subject</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="subjectChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Performance by Class</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="classChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reports Table -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-table mr-2"></i>
                    Detailed Reports
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-success" onclick="exportToExcel()">
                        <i class="fas fa-file-excel mr-2"></i>Export to Excel
                    </button>
                </div>
            </div>
            <div class="card-body table-responsive">
                <table id="reportsTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Assignment</th>
                            <th>Student</th>
                            <th>Class</th>
                            <th>Subject</th>
                            <th>Score</th>
                            <th>Submission Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reports as $report): ?>
                            <tr>
                                <td><?= htmlspecialchars($report['title']) ?></td>
                                <td>
                                    <?= htmlspecialchars($report['full_name']) ?>
                                    <small class="d-block text-muted"><?= htmlspecialchars($report['email']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($report['class_name']) ?></td>
                                <td><?= htmlspecialchars($report['subject_name']) ?></td>
                                <td>
                                    <?php
                                    $scoreClass = 'success';
                                    if ($report['score'] < 70) $scoreClass = 'danger';
                                    elseif ($report['score'] < 80) $scoreClass = 'warning';
                                    ?>
                                    <span class="badge badge-<?= $scoreClass ?> px-2">
                                        <?= number_format($report['score'], 1) ?>%
                                    </span>
                                </td>
                                <td>
                                    <?= date('M d, Y H:i', strtotime($report['submission_date'])) ?>
                                </td>
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
            </div>
        </div>
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
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable
    $('#reportsTable').DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true
    });

    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4'
    });

    // Chart Configuration
    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                max: 100,
                title: {
                    display: true,
                    text: 'Score (%)'
                }
            }
        },
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    };

    // Student Performance Chart
    new Chart(document.getElementById('studentChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_keys($chart_data['by_student'])) ?>,
            datasets: [{
                label: 'Average Score',
                data: <?= json_encode(array_map(function($data) {
                    return $data['average'];
                }, $chart_data['by_student'])) ?>,
                backgroundColor: 'rgba(60,141,188,0.8)'
            }]
        },
        options: {
            ...chartOptions,
            indexAxis: 'y'
        }
    });

    // Score Distribution Chart
    new Chart(document.getElementById('distributionChart'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_keys($chart_data['score_distribution'])) ?>,
            datasets: [{
                data: <?= json_encode(array_values($chart_data['score_distribution'])) ?>,
                backgroundColor: [
                    '#28a745',
                    '#17a2b8',
                    '#ffc107',
                    '#fd7e14',
                    '#dc3545'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Subject Performance Chart
    new Chart(document.getElementById('subjectChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_keys($chart_data['by_subject'])) ?>,
            datasets: [{
                label: 'Average Score',
                data: <?= json_encode(array_map(function($data) {
                    return $data['average'];
                }, $chart_data['by_subject'])) ?>,
                backgroundColor: 'rgba(40,167,69,0.8)'
            }]
        },
        options: chartOptions
    });

    // Class Performance Chart
    new Chart(document.getElementById('classChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_keys($chart_data['by_class'])) ?>,
            datasets: [{
                label: 'Average Score',
                data: <?= json_encode(array_map(function($data) {
                    return $data['average'];
                }, $chart_data['by_class'])) ?>,
                backgroundColor: 'rgba(23,162,184,0.8)'
            }]
        },
        options: chartOptions
    });
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
    color: rgba(0,0,0,0.15);
}
.select2-container .select2-selection--single {
    height: calc(2.25rem + 2px);
}
</style>

<?php include '../includes/footer.php'; ?>