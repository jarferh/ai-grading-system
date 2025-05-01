<?php
session_start();
include '../../includes/db.php';
include '../../includes/auth.php';

requireTeacher();

header('Content-Type: application/json');

try {
    $assignment_id = $_GET['assignment_id'] ?? null;
    $teacher_id = $_SESSION['user_id'];

    // Get assignment details with class info and total students count
    $stmt = $conn->prepare("
        SELECT 
            a.*, 
            c.name as class_name,
            s.name as subject_name,
            (SELECT COUNT(*) FROM users WHERE class_id = a.class_id AND role = 'student') as total_students
        FROM assignments a
        JOIN classes c ON a.class_id = c.id
        JOIN subjects s ON a.subject_id = s.id
        WHERE a.id = :assignment_id 
        AND a.teacher_id = :teacher_id
    ");
    
    $stmt->execute([
        ':assignment_id' => $assignment_id,
        ':teacher_id' => $teacher_id
    ]);
    
    $assignment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$assignment) {
        throw new Exception('Assignment not found');
    }

    // Get all students in the class with their submission status
    $stmt = $conn->prepare("
        SELECT 
            u.id,
            u.full_name,
            u.email,
            sub.submission_date,
            CASE WHEN sub.id IS NOT NULL THEN 1 ELSE 0 END as submitted,
            g.achieved_marks as score,
            g.remarks
        FROM users u
        LEFT JOIN submissions sub ON u.id = sub.student_id 
            AND sub.assignment_id = :assignment_id
        LEFT JOIN grades g ON sub.id = g.submission_id
        WHERE u.class_id = :class_id 
        AND u.role = 'student'
        ORDER BY u.full_name
    ");

    $stmt->execute([
        ':assignment_id' => $assignment_id,
        ':class_id' => $assignment['class_id']
    ]);

    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'assignment' => $assignment,
        'students' => array_map(function($student) {
            return [
                'id' => $student['id'],
                'full_name' => htmlspecialchars($student['full_name']),
                'email' => htmlspecialchars($student['email']),
                'submitted' => (bool)$student['submitted'],
                'submission_date' => $student['submission_date'] ? 
                    date('M d, Y H:i', strtotime($student['submission_date'])) : null,
                'score' => $student['score'],
                'remarks' => $student['remarks']
            ];
        }, $students)
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>