<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$user_id = $_SESSION['user_id'];
$id = $_GET['id'] ?? null;

if ($id) {
    // Crucial: check both ID and user_id for security
    $stmt = $conn->prepare("DELETE FROM achievements WHERE achievement_id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);
}

header("Location: achievements.php");
exit();