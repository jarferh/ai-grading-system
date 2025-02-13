<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';

requireStudent();

// Set page title
$pageTitle = "View Grades";
include '../includes/header.php';

$student_id = $_SESSION['user_id'];

// Fetch grades and related data
$query = "
    SELECT grades.score, grades.remarks, assignments.title AS assignment_title, 
           subjects.name AS subject_name, submissions.submission_date 
    FROM grades 
    JOIN submissions ON grades.submission_id = submissions.id 
    JOIN assignments ON submissions.assignment_id = assignments.id 
    JOIN subjects ON assignments.subject_id = subjects.id 
    WHERE submissions.student_id = :student_id
    ORDER BY submissions.submission_date DESC
";
$stmt = $conn->prepare($query);
$stmt->execute(['student_id' => $student_id]);
$grades = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- <div class="content-wrapper"> -->
    <section class="content">
        <div class="container-fluid">
            <h1>View Grades</h1>

            <!-- Filters -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="subjectFilter">Filter by Subject:</label>
                    <select id="subjectFilter" class="form-control">
                        <option value="">All Subjects</option>
                        <?php
                        // Fetch unique subjects for the filter dropdown
                        $subjectsQuery = "SELECT DISTINCT subjects.name FROM subjects JOIN assignments ON subjects.id = assignments.subject_id JOIN submissions ON assignments.id = submissions.assignment_id WHERE submissions.student_id = :student_id";
                        $subjectsStmt = $conn->prepare($subjectsQuery);
                        $subjectsStmt->execute(['student_id' => $student_id]);
                        $subjects = $subjectsStmt->fetchAll(PDO::FETCH_COLUMN);

                        foreach ($subjects as $subject) {
                            echo "<option value='$subject'>$subject</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="dateSort">Sort by Date:</label>
                    <select id="dateSort" class="form-control">
                        <option value="desc">Newest First</option>
                        <option value="asc">Oldest First</option>
                    </select>
                </div>
            </div>

            <!-- Grades Table -->
            <div class="card">
                <div class="card-body">
                    <table id="gradesTable" class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Assignment</th>
                                <th>Date Submitted</th>
                                <th>Score</th>
                                <th>Feedback</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($grades as $grade): ?>
                                <tr>
                                    <td><?= $grade['subject_name'] ?></td>
                                    <td><?= $grade['assignment_title'] ?></td>
                                    <td><?= date('Y-m-d', strtotime($grade['submission_date'])) ?></td>
                                    <td><?= $grade['score'] ?></td>
                                    <td><?= $grade['remarks'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- JavaScript for Filtering and Sorting -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const gradesTable = document.getElementById('gradesTable').getElementsByTagName('tbody')[0];
    const subjectFilter = document.getElementById('subjectFilter');
    const dateSort = document.getElementById('dateSort');

    // Function to filter and sort the table
    function updateTable() {
        const selectedSubject = subjectFilter.value;
        const sortOrder = dateSort.value;

        // Fetch all rows
        const rows = Array.from(gradesTable.getElementsByTagName('tr'));

        // Filter by subject
        rows.forEach(row => {
            const subject = row.getElementsByTagName('td')[0].textContent;
            if (selectedSubject === "" || subject === selectedSubject) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });

        // Sort by date
        const visibleRows = rows.filter(row => row.style.display !== 'none');
        visibleRows.sort((a, b) => {
            const dateA = new Date(a.getElementsByTagName('td')[2].textContent);
            const dateB = new Date(b.getElementsByTagName('td')[2].textContent);
            return sortOrder === 'asc' ? dateA - dateB : dateB - dateA;
        });

        // Reorder the table
        gradesTable.innerHTML = '';
        visibleRows.forEach(row => gradesTable.appendChild(row));
    }

    // Add event listeners for filters
    subjectFilter.addEventListener('change', updateTable);
    dateSort.addEventListener('change', updateTable);
});
</script>

<?php include '../includes/footer.php'; ?>