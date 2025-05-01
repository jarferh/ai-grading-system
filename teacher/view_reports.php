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

// Modified base query to get assignments instead of submissions
$query = "
    SELECT 
        a.id,
        a.title,
        a.description,
        a.due_date,
        a.total_marks,
        c.name as class_name,
        s.name as subject_name,
        COUNT(DISTINCT sub.id) as submission_count,
        COUNT(DISTINCT g.id) as graded_count,
        COALESCE(AVG(g.score), 0) as average_score
    FROM assignments a
    JOIN classes c ON a.class_id = c.id
    JOIN subjects s ON a.subject_id = s.id
    LEFT JOIN submissions sub ON a.id = sub.assignment_id
    LEFT JOIN grades g ON sub.id = g.submission_id
    WHERE a.teacher_id = :teacher_id
";

// Add filters
$params = ['teacher_id' => $teacher_id];

if ($date_filter) {
    switch ($date_filter) {
        case 'today':
            $query .= " AND DATE(a.due_date) = CURDATE()";
            break;
        case 'week':
            $query .= " AND a.due_date >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK)";
            break;
        case 'month':
            $query .= " AND a.due_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
            break;
    }
}

if ($score_filter) {
    switch ($score_filter) {
        case 'above90':
            $query .= " AND COALESCE(AVG(g.score), 0) >= 90";
            break;
        case '70to89':
            $query .= " AND COALESCE(AVG(g.score), 0) BETWEEN 70 AND 89";
            break;
        case 'below70':
            $query .= " AND COALESCE(AVG(g.score), 0) < 70";
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

$query .= " GROUP BY a.id ORDER BY a.due_date DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch classes and subjects for filters
$classes = $conn->query("SELECT * FROM classes ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$subjects = $conn->query("SELECT * FROM subjects ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "View Reports";
include '../includes/header.php';
?>

<section class="content">
    <div class="container-fluid">
        <!-- Scoresheet Generation Button (Initially Hidden) -->
        <div id="generateScoresheet" style="display: none;" class="mb-3">
            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#scoresheetModal">
                <i class="fas fa-file-excel mr-2"></i>Generate Combined Scoresheet
            </button>
        </div>

        <!-- Scoresheet Modal -->
        <div class="modal fade" id="scoresheetModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Generate Combined Scoresheet</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Total Marks for Combined Scoresheet</label>
                            <input type="number" class="form-control" id="totalScoreMarks" value="100">
                            <small class="text-muted">Selected assignments will be weighted equally to sum up to this total.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="generateCombinedScoresheet()">Generate</button>
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

        <!-- Assignments Table -->
        <div class="card" id="assignmentsCard">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-tasks mr-2"></i>
                    Assignment List
                </h3>
            </div>
            <div class="card-body table-responsive">
                <?php if (!empty($reports)): ?>
                    <table id="assignmentsTable" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" id="selectAll" onclick="toggleAllAssignments()">
                                </th>
                                <th>Title</th>
                                <th>Class</th>
                                <th>Subject</th>
                                <th>Due Date</th>
                                <th>Submissions</th>
                                <th>Average Score</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reports as $assignment): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="assignment-select" 
                                               value="<?= $assignment['id'] ?>" 
                                               data-title="<?= htmlspecialchars($assignment['title']) ?>"
                                               data-total="<?= $assignment['total_marks'] ?>"
                                               onchange="checkSelectedAssignments()">
                                    </td>
                                    <td><?= htmlspecialchars($assignment['title']) ?></td>
                                    <td><?= htmlspecialchars($assignment['class_name']) ?></td>
                                    <td><?= htmlspecialchars($assignment['subject_name']) ?></td>
                                    <td><?= date('M d, Y', strtotime($assignment['due_date'])) ?></td>
                                    <td>
                                        <span class="badge badge-info">
                                            <?= $assignment['submission_count'] ?> / 
                                            <?= $assignment['total_students'] ?? '?' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= getScoreClass($assignment['average_score']) ?>">
                                            <?= number_format($assignment['average_score'], 1) ?>%
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary"
                                                onclick="viewSubmissions(<?= $assignment['id'] ?>, '<?= htmlspecialchars($assignment['title']) ?>')">
                                            <i class="fas fa-list"></i> View Submissions
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        No assignments found for the selected filters.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Submissions Table (Initially Hidden) -->
        <div class="card" id="submissionsCard" style="display: none;">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-file-alt mr-2"></i>
                    <span id="submissionTitle">Submissions</span>
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-success btn-sm mr-2" onclick="exportToPDF()">
                        <i class="fas fa-file-pdf mr-1"></i> Export PDF
                    </button>
                    <button type="button" class="btn btn-default btn-sm" onclick="showAssignments()">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Assignments
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="submissionsList">
                    <!-- Submissions will be loaded here -->
                </div>
            </div>
        </div>

        <?php
        function getScoreClass($score) {
            if ($score >= 90) return 'success';
            if ($score >= 80) return 'info';
            if ($score >= 70) return 'warning';
            return 'danger';
        }
        ?>

    </div>
</section>

<!-- Add jsPDF and html2canvas libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
    // Add this function at the start of your script section
    function getScoreClass(score) {
        score = parseFloat(score);
        if (score >= 90) return 'success';
        if (score >= 80) return 'info';
        if (score >= 70) return 'warning';
        return 'danger';
    }

    $(document).ready(function() {
        // Initialize Select2
        $('.select2').select2({
            theme: 'bootstrap4',
            placeholder: 'Select an option'
        });

        // Initialize DataTable only if there are reports
        if ($('#assignmentsTable tbody tr').length > 0) {
            $('#assignmentsTable').DataTable({
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

    function toggleAllAssignments() {
        const checkboxes = document.getElementsByClassName('assignment-select');
        const selectAll = document.getElementById('selectAll');
        Array.from(checkboxes).forEach(checkbox => checkbox.checked = selectAll.checked);
        checkSelectedAssignments();
    }

    function checkSelectedAssignments() {
        const selected = document.querySelectorAll('.assignment-select:checked');
        const generateBtn = document.getElementById('generateScoresheet');
        generateBtn.style.display = selected.length > 0 ? 'block' : 'none';
    }

    function generateCombinedScoresheet() {
        const selected = document.querySelectorAll('.assignment-select:checked');
        const totalMarks = parseFloat(document.getElementById('totalScoreMarks').value);
        const assignmentIds = Array.from(selected).map(cb => cb.value);
        const weightPerAssignment = totalMarks / selected.length;

        Promise.all(assignmentIds.map(id => 
            fetch(`ajax/get_submissions.php?assignment_id=${id}`)
                .then(res => res.json())
        )).then(responses => {
            const studentScores = {};
            const assignmentTitles = [];

            responses.forEach((response, index) => {
                if (!response.success) return;
                assignmentTitles.push(response.assignment.title);

                response.students.forEach(student => {
                    if (!studentScores[student.full_name]) {
                        studentScores[student.full_name] = {
                            scores: new Array(selected.length).fill(0),
                            total: 0
                        };
                    }
                    
                    // Calculate weighted score and round to nearest whole number
                    const score = student.score || 0;
                    const maxScore = response.assignment.total_marks;
                    const weightedScore = Math.round((score / maxScore) * weightPerAssignment);
                    
                    studentScores[student.full_name].scores[index] = weightedScore;
                    studentScores[student.full_name].total += weightedScore;
                });
            });

            generateCombinedPDF(studentScores, assignmentTitles, totalMarks);
            $('#scoresheetModal').modal('hide');
        });
    }

    function generateCombinedPDF(studentScores, assignmentTitles, totalMarks) {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        
        // Set up PDF
        doc.setFontSize(16);
        doc.text('Combined Scoresheet', 14, 15);
        
        doc.setFontSize(10);
        doc.text('Generated on: ' + new Date().toLocaleString(), 14, 22);
        
        // Calculate column widths
        const startX = 14;
        const startY = 35;
        const nameWidth = 60;
        const scoreWidth = 25;
        const totalWidth = 30;
        const rowHeight = 8;
        
        // Draw headers
        doc.setFontSize(11);
        let currentX = startX;
        
        // Name column
        doc.rect(currentX, startY - 5, nameWidth, rowHeight);
        doc.text('Student Name', currentX + 2, startY);
        currentX += nameWidth;
        
        // Assignment columns
        assignmentTitles.forEach((title, index) => {
            doc.rect(currentX, startY - 5, scoreWidth, rowHeight);
            doc.text(`A ${index + 1}`, currentX + 2, startY);
            currentX += scoreWidth;
        });
        
        // Total column
        doc.rect(currentX, startY - 5, totalWidth, rowHeight);
        doc.text('Total', currentX + 2, startY);
        
        // Add data rows
        let currentY = startY + rowHeight;
        
        Object.entries(studentScores).forEach(([name, data]) => {
            if (currentY > 280) {
                doc.addPage();
                currentY = 20;
            }
            
            currentX = startX;
            
            // Name
            doc.rect(currentX, currentY - 5, nameWidth, rowHeight);
            doc.text(name, currentX + 2, currentY);
            currentX += nameWidth;
            
            // Individual assignment scores (already rounded in generateCombinedScoresheet)
            data.scores.forEach(score => {
                doc.rect(currentX, currentY - 5, scoreWidth, rowHeight);
                doc.text(score.toString(), currentX + 2, currentY);
                currentX += scoreWidth;
            });
            
            // Total (already a sum of rounded scores)
            doc.rect(currentX, currentY - 5, totalWidth, rowHeight);
            doc.text(data.total.toString(), currentX + 2, currentY);
            
            currentY += rowHeight;
        });
        
        doc.save('combined-scoresheet.pdf');
    }

    function viewSubmissions(assignmentId, title) {
        // Show loading state
        $('#submissionsList').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i><br>Loading submissions...</div>');
        $('#submissionTitle').text('Submissions for: ' + title);
        
        // Hide assignments, show submissions
        $('#assignmentsCard').hide();
        $('#submissionsCard').show();

        // Fetch submissions with error handling
        $.ajax({
            url: 'ajax/get_submissions.php',
            method: 'GET',
            data: { assignment_id: assignmentId },
            dataType: 'json',
            success: function(response) {
                console.log('Response:', response); // Debug log

                if (!response.success) {
                    $('#submissionsList').html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            ${response.error || 'Failed to load submissions'}
                        </div>
                    `);
                    return;
                }

                // Create submissions table
                let tableHtml = `
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Submission Date</th>
                                    <th>Score (out of ${response.assignment.total_marks})</th>
                                </tr>
                            </thead>
                            <tbody>`;

                if (!response.students || response.students.length === 0) {
                    tableHtml += `
                        <tr>
                            <td colspan="5" class="text-center">No students found in this class</td>
                        </tr>`;
                } else {
                    response.students.forEach(student => {
                        const score = student.score || 0;
                        const percentage = (score / response.assignment.total_marks) * 100;
                        let scoreDisplay = student.submitted ? 
                            `<span class="badge badge-${getScoreClass(percentage)}">
                                ${score}/${response.assignment.total_marks}
                            </span>` : 
                            '<span class="badge badge-secondary">0</span>';

                        tableHtml += `
                            <tr>
                                <td>${student.full_name}</td>
                                <td>${student.email}</td>
                                <td>
                                    ${student.submitted ? 
                                        '<span class="badge badge-success">Submitted</span>' : 
                                        '<span class="badge badge-warning">Not Submitted</span>'}
                                </td>
                                <td>${student.submission_date || 'N/A'}</td>
                                <td>${scoreDisplay}</td>
                            </tr>`;
                    });
                }

                tableHtml += '</tbody></table></div>';
                $('#submissionsList').html(tableHtml);
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                $('#submissionsList').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        Error loading submissions. Status: ${status}, Error: ${error}
                    </div>
                `);
            }
        });
    }

    function showAssignments() {
        $('#submissionsCard').hide();
        $('#assignmentsCard').show();
    }

    function exportToPDF() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        
        // Get the assignment title and total marks
        const title = document.getElementById('submissionTitle').innerText;
        const table = document.querySelector('#submissionsList table');
        const rows = table.querySelectorAll('tbody tr');
        
        // Get total marks from title
        const totalMarksMatch = title.match(/out of (\d+)/);
        const assignmentTotalMarks = totalMarksMatch ? totalMarksMatch[1] : '100';
        
        // Set up PDF
        doc.setFontSize(16);
        doc.text(title, 14, 15);
        
        doc.setFontSize(10);
        doc.text('Generated on: ' + new Date().toLocaleString(), 14, 22);
        
        // Table settings
        const startX = 14;
        const startY = 35;
        const colWidths = [90, 30, 30]; // Name, Score, Total
        const rowHeight = 8;
        let currentY = startY;
        
        // Draw table headers
        doc.setFontSize(12);
        doc.setLineWidth(0.2);
        
        // Header cells
        doc.rect(startX, currentY - 5, colWidths[0], rowHeight); // Name cell
        doc.rect(startX + colWidths[0], currentY - 5, colWidths[1], rowHeight); // Score cell
        doc.rect(startX + colWidths[0] + colWidths[1], currentY - 5, colWidths[2], rowHeight); // Total cell
        
        // Header texts
        doc.text('Student Name', startX + 2, currentY);
        doc.text('Score', startX + colWidths[0] + 2, currentY);
        doc.text('Total', startX + colWidths[0] + colWidths[1] + 2, currentY);
        
        currentY += rowHeight;
        
        // Add data rows
        rows.forEach(row => {
            if (currentY > 280) {
                doc.addPage();
                currentY = 20;
            }
            
            const name = row.cells[0]?.textContent?.trim() || 'N/A';
            
            // Get score information
            let score = '0';
            const scoreCell = row.cells[4];
            if (scoreCell) {
                const scoreElement = scoreCell.querySelector('span');
                if (scoreElement && scoreElement.textContent.includes('/')) {
                    score = scoreElement.textContent.split('/')[0].trim() || '0';
                }
            }
            
            // Draw row cells
            doc.rect(startX, currentY - 5, colWidths[0], rowHeight); // Name cell
            doc.rect(startX + colWidths[0], currentY - 5, colWidths[1], rowHeight); // Score cell
            doc.rect(startX + colWidths[0] + colWidths[1], currentY - 5, colWidths[2], rowHeight); // Total cell
            
            // Write cell contents
            doc.text(name, startX + 2, currentY);
            doc.text(score, startX + colWidths[0] + 2, currentY);
            doc.text(assignmentTotalMarks, startX + colWidths[0] + colWidths[1] + 2, currentY);
            
            currentY += rowHeight;
        });
        
        // Save the PDF
        doc.save('submissions-scoresheet.pdf');
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