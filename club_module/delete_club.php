<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$user_id = $_SESSION['user_id'];

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $club_id = (int) $_GET['id'];

    $stmt = $conn->prepare("DELETE FROM clubs WHERE club_id = ? AND user_id = ?");

    if ($stmt) {
        $stmt->bind_param("ii", $club_id, $user_id);

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: clubs.php?status=deleted");
            exit();
        } else {
            $stmt->close();
            header("Location: clubs.php?status=error");
            exit();
        }
    } else {
        header("Location: clubs.php?status=error");
        exit();
    }
} else {
    header("Location: clubs.php");
    exit();
}
?>