<?php
// 1. Authentication & Database Connection
require_once '../includes/auth_check.php';
require_once '../config/db_conn.php';

// Get the currently logged-in user
$user_id = $_SESSION['user_id'];

// 2. Validate the incoming ID
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $event_id = $_GET['id'];

    // 3. Prepare the DELETE statement
    $stmt = $conn->prepare("DELETE FROM events WHERE event_id = ? AND user_id = ?");
    
    // "ii" = 2 integers (event_id, user_id)
    $stmt->bind_param("ii", $event_id, $user_id);

    // 4. Execute and Redirect
    if ($stmt->execute()) {
        // Redirect back to dashboard with a success flag
        header("Location: event_index.php?status=deleted");
    } else {
        // Redirect back with an error flag
        header("Location: event_index.php?status=error");
    }
    
    $stmt->close();
    exit();
    
} else {
    // If someone tries to access event_delete.php directly without an ID, kick them back to dashboard
    header("Location: event_index.php");
    exit();
}
?>