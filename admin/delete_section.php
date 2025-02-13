<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';

requireAdmin();

$id = $_GET['id'];

// Delete section
$stmt = $conn->prepare("DELETE FROM sections WHERE id = :id");
$stmt->execute(['id' => $id]);

header("Location: manage_sections.php");
exit();
?>