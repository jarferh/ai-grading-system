<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';

requireStudent();

$pageTitle = "View Assignments";
include '../includes/header.php';

$current_datetime = '2025-02-20 17:02:21';
$student_id = $_SESSION['user_id'];

// Get filter values
$subject_filter = isset($_GET['subject']) ? intval($_GET['subject']) : 0;
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'due_date';
$sort_order = isset($_GET['order']) ? $_GET['order'] : 'ASC';

// Base query
$query = "
    SELECT 
        a.id,
        a.title,
        a.description,
        a.due_date,
        s.name AS subject_name,
        u.full_name AS teacher_name,
        (SELECT COUNT(*) FROM submissions sub WHERE sub.assignment_id = a.id AND sub.student_id = :student_id) as is_submitted,
        (SELECT g.score FROM submissions sub 
         LEFT JOIN grades g ON sub.id = g.submission_id 
         WHERE sub.assignment_id = a.id AND sub.student_id = :student_id) as grade
    FROM assignments a
    JOIN subjects s ON a.subject_id = s.id
    LEFT JOIN users u ON a.teacher_id = u.id
    JOIN users student ON student.class_id = a.class_id
    WHERE student.id = :student_id
";

// Add filters
if ($subject_filter) {
    $query .= " AND a.subject_id = :subject_filter";
}
if ($status_filter === 'submitted') {
    $query .= " AND EXISTS (SELECT 1 FROM submissions sub WHERE sub.assignment_id = a.id AND sub.student_id = :student_id_sub)";
} elseif ($status_filter === 'pending') {
    $query .= " AND NOT EXISTS (SELECT 1 FROM submissions sub WHERE sub.assignment_id = a.id AND sub.student_id = :student_id_sub)";
}

// Add sorting
$query .= " ORDER BY " . ($sort_by === 'due_date' ? 'a.due_date' : 'a.title') . " $sort_order";

$stmt = $conn->prepare($query);
$params = ['student_id' => $student_id];
if ($subject_filter) {
    $params['subject_filter'] = $subject_filter;
}
if ($status_filter !== '') {
    $params['student_id_sub'] = $student_id;
}
$stmt->execute($params);
$assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch subjects for filter
$stmt = $conn->query("SELECT * FROM subjects ORDER BY name");
$subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="content">
    <div class="container-fluid">
        <!-- Page Title and Filters -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-book mr-2"></i>
                            Assignments
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="mb-0">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Subject</label>
                                        <select name="subject" class="form-control">
                                            <option value="">All Subjects</option>
                                            <?php foreach ($subjects as $subject): ?>
                                                <option value="<?= $subject['id'] ?>" <?= $subject_filter == $subject['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($subject['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select name="status" class="form-control">
                                            <option value="">All Status</option>
                                            <option value="submitted" <?= $status_filter === 'submitted' ? 'selected' : '' ?>>Submitted</option>
                                            <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Sort By</label>
                                        <select name="sort" class="form-control">
                                            <option value="due_date" <?= $sort_by === 'due_date' ? 'selected' : '' ?>>Due Date</option>
                                            <option value="title" <?= $sort_by === 'title' ? 'selected' : '' ?>>Title</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <button type="submit" class="btn btn-primary btn-block">
                                            <i class="fas fa-filter mr-2"></i> Apply Filters
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assignments Grid -->
        <div class="row">
            <?php foreach ($assignments as $assignment): ?>
                <div class="col-md-4">
                    <div class="card <?= $assignment['is_submitted'] ? 'card-success' : 'card-primary' ?> card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><?= htmlspecialchars($assignment['title']) ?></h3>
                            <div class="card-tools">
                                <?php if ($assignment['is_submitted']): ?>
                                    <span class="badge badge-success">
                                        <i class="fas fa-check"></i> Submitted
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-warning">
                                        <i class="fas fa-clock"></i> Pending
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong><i class="fas fa-book mr-2"></i> Subject:</strong>
                                <span class="text-muted"><?= htmlspecialchars($assignment['subject_name']) ?></span>
                            </div>
                            <div class="mb-3">
                                <strong><i class="fas fa-user mr-2"></i> Teacher:</strong>
                                <span class="text-muted"><?= htmlspecialchars($assignment['teacher_name']) ?></span>
                            </div>
                            <div class="mb-3">
                                <strong><i class="fas fa-calendar mr-2"></i> Due Date:</strong>
                                <?php
                                $due_date = strtotime($assignment['due_date']);
                                $current = strtotime($current_datetime);
                                $badge_class = $current > $due_date ? 'badge-danger' : 'badge-info';
                                ?>
                                <span class="badge <?= $badge_class ?>">
                                    <?= date('M d, Y', $due_date) ?>
                                </span>
                            </div>
                            <div class="mb-3">
                                <strong><i class="fas fa-align-left mr-2"></i> Description:</strong>
                                <p class="text-muted mt-2">
                                    <?= nl2br(htmlspecialchars(substr($assignment['description'], 0, 100))) ?>
                                    <?= strlen($assignment['description']) > 100 ? '...' : '' ?>
                                </p>
                            </div>
                            <?php if (isset($assignment['grade'])): ?>
                                <div class="mb-3">
                                    <strong><i class="fas fa-star mr-2"></i> Grade:</strong>
                                    <span class="badge badge-info"><?= $assignment['grade'] ?>%</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer">
                            <?php if (!$assignment['is_submitted']): ?>
                                <a href="submit_assignment.php?id=<?= $assignment['id'] ?>" 
                                   class="btn btn-primary btn-block">
                                    <i class="fas fa-upload mr-2"></i> Submit Assignment
                                </a>
                            <?php else: ?>
                                <a href="view_submission.php?id=<?= $assignment['id'] ?>" 
                                   class="btn btn-info btn-block">
                                    <i class="fas fa-eye mr-2"></i> View Submission
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>

<style>
.card {
    margin-bottom: 1.5rem;
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
}
.card-header {
    border-bottom: 1px solid rgba(0,0,0,.125);
    padding: .75rem 1.25rem;
}
.card-title {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 400;
    margin-bottom: 0;
}
.badge {
    font-size: 85%;
    font-weight: 400;
}
.text-muted {
    color: #6c757d!important;
}
</style>