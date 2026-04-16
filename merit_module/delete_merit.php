<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$user_id = $_SESSION['user_id'];

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $merit_id = (int) $_GET['id'];

    $stmt = $conn->prepare("DELETE FROM merits WHERE merit_id = ? AND user_id = ?");

    if ($stmt) {
        $stmt->bind_param("ii", $merit_id, $user_id);

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: merits.php?status=deleted");
            exit();
        } else {
            $stmt->close();
            header("Location: merits.php?status=error");
            exit();
        }
    } else {
        header("Location: merits.php?status=error");
        exit();
    }
} else {
    header("Location: merits.php");
    exit();
}
?>