<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';

requireAdmin();

$id = $_GET['id'];

// Delete subject
$stmt = $conn->prepare("DELETE FROM subjects WHERE id = :id");
$stmt->execute(['id' => $id]);

header("Location: manage_subjects.php");
exit();
?>