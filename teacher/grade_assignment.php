<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';

requireTeacher();

$submission_id = $_GET['id'];

// Fetch submission details
$stmt = $conn->prepare("
    SELECT submissions.id, submissions.content, submissions.submission_date, users.full_name, assignments.title 
    FROM submissions 
    JOIN users ON submissions.student_id = users.id 
    JOIN assignments ON submissions.assignment_id = assignments.id 
    WHERE submissions.id = :id
");
$stmt->execute(['id' => $submission_id]);
$submission = $stmt->fetch(PDO::FETCH_ASSOC);

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

// Generate feedback using the Gemini API
$prompt = "Provide feedback on the following assignment submission:\n\n";
$prompt .= "Assignment Title: " . $submission['title'] . "\n";
$prompt .= "Submission Content: " . $submission['content'] . "\n";
$prompt .= "Provide constructive feedback focusing on clarity, relevance, and grammar.";

try {
    $response = callGeminiAPI($prompt);
    $generatedFeedback = $response['candidates'][0]['content']['parts'][0]['text'] ?? "No feedback generated.";
} catch (Exception $e) {
    $generatedFeedback = "Error generating feedback: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
            <h1>Grade Assignment</h1>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><?= $submission['title'] ?></h5>
                    <h6 class="card-subtitle mb-2 text-muted">Submitted by: <?= $submission['full_name'] ?></h6>
                    <p class="card-text"><?= $submission['content'] ?></p>
                </div>
            </div>
            <form action="grade_assignment.php?id=<?= $submission_id ?>" method="POST" class="mt-3">
                <div class="form-group">
                    <label for="score">Score</label>
                    <input type="number" name="score" class="form-control" min="0" max="100" required>
                </div>
                <div class="form-group">
                    <label for="remarks">Remarks</label>
                    <textarea name="remarks" class="form-control" required><?= $generatedFeedback ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Submit Grade</button>
            </form>
        </div>
    </section>
</div>

<?php include '../includes/footer.php'; ?>