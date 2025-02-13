<?php
session_start();
include '../includes/db.php';

// Redirect if not admin
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$id = $_GET['id'];

// Delete user
$stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
$stmt->execute(['id' => $id]);

header("Location: manage_users.php");
exit();
?>