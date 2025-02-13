<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';

requireAdmin();

$id = $_GET['id'];

// Delete class
$stmt = $conn->prepare("DELETE FROM classes WHERE id = :id");
$stmt->execute(['id' => $id]);

header("Location: manage_classes.php");
exit();
?>