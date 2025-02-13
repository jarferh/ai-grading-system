<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';

requireStudent();

// Set page title
$pageTitle = "Submit Assignment";
include '../includes/header.php';

$assignment_id = $_GET['id'];
$student_id = $_SESSION['user_id'];

// Fetch assignment details
$stmt = $conn->prepare("
    SELECT assignments.id, assignments.title, assignments.description, assignments.due_date, subjects.name AS subject_name 
    FROM assignments 
    JOIN subjects ON assignments.subject_id = subjects.id 
    WHERE assignments.id = :assignment_id
");
$stmt->execute(['assignment_id' => $assignment_id]);
$assignment = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!-- <div class="content-wrapper"> -->
<section class="content">
    <div class="container-fluid">
        <h1>Submit Assignment</h1>

        <!-- Assignment Details in Grid Form -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Assignment Details</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Title:</strong>
                            </div>
                            <div class="col-md-8">
                                <?= $assignment['title'] ?>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <strong>Subject:</strong>
                            </div>
                            <div class="col-md-8">
                                <?= $assignment['subject_name'] ?>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <strong>Due Date:</strong>
                            </div>
                            <div class="col-md-8">
                                <?= $assignment['due_date'] ?>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <strong>Description:</strong>
                            </div>
                            <div class="col-md-8">
                                <?= $assignment['description'] ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submission Form -->
        <div class="row mt-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Your Submission</h3>
                    </div>
                    <div class="card-body">
                        <form id="submitAssignmentForm">
                            <div class="form-group">
                                <label for="content">Your Answer</label>
                                <textarea name="content" id="content" class="form-control" rows="10" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit Assignment</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Marking Animation (Hidden by Default) -->
        <div id="markingAnimation" class="text-center mt-4" style="display: none;">
            <div class="spinner-border text-primary" style="width: 5rem; height: 5rem;" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <p class="mt-3">Please wait while we evaluate your submission...</p>
        </div>

        <!-- Results (Hidden by Default) -->
        <!-- Results (Hidden by Default) -->
        <div id="assignmentResult" class="mt-4" style="display: none;">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Your Results</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Percentage (Top) -->
                            <div class="col-md-12 text-center mb-4">
                                <div class="progress-circle" data-percentage="0">
                                    <span class="progress-circle-value">1%</span>
                                </div>
                            </div>

                            <!-- Grade (Middle) -->
                            <div class="col-md-12 text-center mb-4">
                                <h2 class="grade-text">Grade: <span id="scoreResult">0</span></h2>
                            </div>

                            <!-- Feedback (Bottom) -->
                            <div class="col-md-12 text-center">
                                <strong>Feedback:</strong> <span id="feedbackResult">No feedback available.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
</div>

<!-- CSS for Circular Progress Bar -->
<style>
    .progress-circle {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: conic-gradient(#007bff 0%, #e9ecef 0%);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        position: relative;
    }

    .progress-circle-value {
        font-size: 24px;
        font-weight: bold;
        color: #007bff;
    }

    .progress-circle::before {
        content: '';
        position: absolute;
        width: 90%;
        height: 90%;
        border-radius: 50%;
        background: white;
    }
</style>

<!-- JavaScript to Handle Submission and Display Results -->
<script>
    document.getElementById('submitAssignmentForm').addEventListener('submit', function(e) {
        e.preventDefault(); // Prevent form submission

        // Show the marking animation
        document.getElementById('markingAnimation').style.display = 'block';
        document.getElementById('assignmentResult').style.display = 'none';

        // Get form data
        const formData = new FormData(this);
        formData.append('assignment_id', <?= $assignment_id ?>);
        formData.append('student_id', <?= $student_id ?>);

        // Send AJAX request
        fetch('submit_assignment_ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Hide the marking animation
                document.getElementById('markingAnimation').style.display = 'none';

                if (data.success) {
                    // Display the results
                    document.getElementById('scoreResult').textContent = data.score || 'N/A';
                    document.getElementById('feedbackResult').textContent = data.feedback || 'No feedback available.';

                    // Update the circular progress bar
                    const progressCircle = document.querySelector('.progress-circle');
                    const progressValue = document.querySelector('.progress-circle-value');
                    const percentage = data.percentage || 0;

                    progressCircle.style.background = `conic-gradient(#007bff ${percentage}%, #e9ecef ${percentage}%)`;
                    progressValue.textContent = `${percentage}%`;

                    // Show the results
                    document.getElementById('assignmentResult').style.display = 'block';
                } else {
                    // Display the error in the console
                    console.error("Error:", data.error);
                    alert("An error occurred while submitting your assignment: " + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while submitting your assignment.');
            });
    });
</script>

<?php include '../includes/footer.php'; ?>