<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';

requireStudent();

// Set security headers
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

// Configuration
$CURRENT_UTC_DATETIME = '2025-02-20 17:18:17';
$CURRENT_USER_LOGIN = 'jarferh';
$MAX_API_RETRIES = 3;
$API_KEY = "AIzaSyDsEJyQgh_XMGQsOv3CndiDWvcw3W8OHms";

// Input validation
$student_id = $_SESSION['user_id'] ?? 0;
$assignment_id = filter_input(INPUT_POST, 'assignment_id', FILTER_VALIDATE_INT);
$content = trim($_POST['content'] ?? '');

// Debug logging
error_log("Received request - Student ID: $student_id, Assignment ID: $assignment_id");

// Validate required inputs
if (!$student_id || !$assignment_id || empty($content)) {
    echo json_encode([
        'success' => false,
        'error' => 'Missing or invalid required parameters.',
        'timestamp' => $CURRENT_UTC_DATETIME,
        'debug' => [
            'student_id' => $student_id,
            'assignment_id' => $assignment_id,
            'content_length' => strlen($content)
        ]
    ]);
    exit();
}

// Function to log events
function logEvent($type, $message, $data = []) {
    global $CURRENT_UTC_DATETIME, $CURRENT_USER_LOGIN;
    error_log(sprintf(
        "[%s] [%s] [%s] %s - %s",
        $CURRENT_UTC_DATETIME,
        $CURRENT_USER_LOGIN,
        $type,
        $message,
        json_encode($data)
    ));
}

// Function to call Gemini API
function callGeminiAPI($prompt, $maxRetries) {
    global $API_KEY;
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=$API_KEY";

    $data = [
        "contents" => [
            [
                "parts" => [
                    ["text" => $prompt]
                ]
            ]
        ]
    ];

    $attempt = 0;
    while ($attempt < $maxRetries) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            return json_decode($response, true);
        }

        $attempt++;
        if ($attempt < $maxRetries) {
            sleep(1);
        }
    }

    throw new Exception("API call failed after $maxRetries attempts. HTTP Code: $httpCode. Error: $curlError");
}

try {
    // Start transaction
    $conn->beginTransaction();

    // Check if assignment exists and is accessible to the student
    $stmt = $conn->prepare("
        SELECT a.*, s.name as subject_name 
        FROM assignments a
        JOIN subjects s ON a.subject_id = s.id
        JOIN users u ON u.class_id = a.class_id
        WHERE a.id = :assignment_id 
        AND u.id = :student_id
    ");
    
    $stmt->execute([
        'assignment_id' => $assignment_id,
        'student_id' => $student_id
    ]);
    
    $assignment = $stmt->fetch(PDO::FETCH_ASSOC);

    // Debug logging
    error_log("Assignment query result: " . json_encode($assignment));

    if (!$assignment) {
        throw new Exception('Assignment not found or not accessible to this student.');
    }

    // Check due date
    if (strtotime($CURRENT_UTC_DATETIME) > strtotime($assignment['due_date'])) {
        throw new Exception('This assignment is past its due date.');
    }

    // Check for existing submission
    $stmt = $conn->prepare("
        SELECT id FROM submissions 
        WHERE student_id = :student_id 
        AND assignment_id = :assignment_id
    ");
    $stmt->execute([
        'student_id' => $student_id,
        'assignment_id' => $assignment_id
    ]);

    if ($stmt->fetch()) {
        throw new Exception('You have already submitted this assignment.');
    }

    // Prepare grading prompt
    $prompt = "You are an automated grading assistant. Grade the following student answer based on the given question and provide specific, constructive feedback.\n\n" .
             "Subject: {$assignment['subject_name']}\n" .
             "Question: {$assignment['description']}\n\n" .
             "Student Answer: {$content}\n\n" .
             "Grade based on these criteria (Total 100%):\n" .
             "- Attendance: 10% (automatically awarded)\n" .
             "- Grammar & Clarity: 10%\n" .
             "- Answer Accuracy: 70%\n" .
             "- Originality: 10%\n\n" .
             "Provide response in this format exactly:\n" .
             "Grade: [0-100]\n" .
             "Feedback: [Specific feedback with suggestions for improvement]";

    // Call API and process response
    $response = callGeminiAPI($prompt, $MAX_API_RETRIES);
    
    if (!isset($response['candidates'][0]['content']['parts'][0]['text'])) {
        throw new Exception('Invalid API response format.');
    }

    $generatedText = $response['candidates'][0]['content']['parts'][0]['text'];

    // Parse grade and feedback
    preg_match("/Grade:\s*(\d+)/i", $generatedText, $gradeMatches);
    preg_match("/Feedback:\s*(.+?)(?=\n|$)/is", $generatedText, $feedbackMatches);

    if (!isset($gradeMatches[1])) {
        throw new Exception('Could not extract grade from API response.');
    }

    $grade = min(100, max(0, intval($gradeMatches[1])));
    $feedback = $feedbackMatches[1] ?? 'No specific feedback provided.';

    // Insert submission
    $stmt = $conn->prepare("
        INSERT INTO submissions (student_id, assignment_id, content, submission_date) 
        VALUES (:student_id, :assignment_id, :content, :submission_date)
    ");
    $stmt->execute([
        'student_id' => $student_id,
        'assignment_id' => $assignment_id,
        'content' => $content,
        'submission_date' => $CURRENT_UTC_DATETIME
    ]);
    
    $submission_id = $conn->lastInsertId();

    // Insert grade
    $stmt = $conn->prepare("
        INSERT INTO grades (submission_id, score, remarks) 
        VALUES (:submission_id, :score, :remarks)
    ");
    $stmt->execute([
        'submission_id' => $submission_id,
        'score' => $grade,
        'remarks' => $feedback
    ]);

    // Commit transaction
    $conn->commit();

    // Log successful submission
    logEvent('SUBMISSION', 'Assignment submitted successfully', [
        'assignment_id' => $assignment_id,
        'student_id' => $student_id,
        'grade' => $grade
    ]);

    // Return success response
    echo json_encode([
        'success' => true,
        'score' => $grade,
        'percentage' => $grade,
        'feedback' => $feedback,
        'submission_id' => $submission_id,
        'timestamp' => $CURRENT_UTC_DATETIME
    ]);

} catch (Exception $e) {
    // Rollback transaction if active
    if ($conn && $conn->inTransaction()) {
        $conn->rollBack();
    }

    // Log error
    logEvent('ERROR', $e->getMessage(), [
        'assignment_id' => $assignment_id,
        'student_id' => $student_id,
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);

    // Return error response
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => $CURRENT_UTC_DATETIME,
        'debug' => [
            'student_id' => $student_id,
            'assignment_id' => $assignment_id
        ]
    ]);
}
?>