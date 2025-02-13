<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if user is not authenticated
function requireAuth() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit();
    }
}

// Redirect to login if user is not an admin
function requireAdmin() {
    requireAuth();
    if ($_SESSION['role'] !== 'admin') {
        header("Location: ../login.php");
        exit();
    }
}

// Redirect to login if user is not a teacher
function requireTeacher() {
    requireAuth();
    if ($_SESSION['role'] !== 'teacher') {
        header("Location: ../login.php");
        exit();
    }
}

// Redirect to login if user is not a student
function requireStudent() {
    requireAuth();
    if ($_SESSION['role'] !== 'student') {
        header("Location: ../login.php");
        exit();
    }
}
?>