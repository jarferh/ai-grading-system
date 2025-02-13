<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';

requireTeacher();

// Set page title
$pageTitle = "Grade Submissions";
include '../includes/header.php';

$teacher_id = $_SESSION['user_id'];

// Fetch all submissions
$stmt = $conn->query("
    SELECT submissions.id, submissions.content, submissions.submission_date, users.full_name, assignments.title 
    FROM submissions 
    JOIN users ON submissions.student_id = users.id 
    JOIN assignments ON submissions.assignment_id = assignments.id
");
$submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Function to call the Gemini API
function callGeminiAPI($prompt) {
    $apiKey = "AIzaSyDsEJyQgh_XMGQsOv3CndiDWvcw3W8OHms"; // Replace with your Gemini API key
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=$apiKey";

    // Prepare the request payload
    $data = [
        "contents" => [
            [
                "parts" => [
                    ["text" => $prompt]
                ]
            ]
        ]
    ];

    // Initialize cURL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    // Execute the request
    $response = curl_exec($ch);

    // Check for errors
    if (curl_errno($ch)) {
        throw new Exception("cURL Error: " . curl_error($ch));
    }

    // Close cURL
    curl_close($ch);

    // Decode the response
    return json_decode($response, true);
}

// Handle form submission for grading
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submission_id = $_POST['submission_id'];
    $score = $_POST['score'];
    $remarks = $_POST['remarks'];

    // Insert grade into the database
    $stmt = $conn->prepare("INSERT INTO grades (submission_id, score, remarks) VALUES (:submission_id, :score, :remarks)");
    $stmt->execute([
        'submission_id' => $submission_id,
        'score' => $score,
        'remarks' => $remarks
    ]);

    // Redirect to avoid form resubmission
    header("Location: grade_submissions.php");
    exit();
}
?>

<!-- <div class="content-wrapper"> -->
    <section class="content">
        <div class="container-fluid">
            <h1>Grade Submissions</h1>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Assignment Title</th>
                        <th>Submission Date</th>
                        <th>Content</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($submissions as $submission): ?>
                        <tr>
                            <td><?= $submission['full_name'] ?></td>
                            <td><?= $submission['title'] ?></td>
                            <td><?= $submission['submission_date'] ?></td>
                            <td><?= $submission['content'] ?></td>
                            <td>
                                <a href="grade_assignment.php?id=<?= $submission['id'] ?>" class="btn btn-primary btn-sm">Grade</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<?php include '../includes/footer.php'; ?>