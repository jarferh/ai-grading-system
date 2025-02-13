<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';

requireAdmin();

$id = $_GET['id'];

// Delete session
$stmt = $conn->prepare("DELETE FROM sessions WHERE id = :id");
$stmt->execute(['id' => $id]);

header("Location: manage_sessions.php");
exit();
?>