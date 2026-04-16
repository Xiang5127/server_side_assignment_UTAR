<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$user_id = $_SESSION['user_id'];
$error_msg = "";
$club = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $club_id = (int) ($_POST['club_id'] ?? 0);
    $club_name = trim($_POST['club_name'] ?? '');
    $role = trim($_POST['role'] ?? '');
    $join_date = trim($_POST['join_date'] ?? '');
    $remarks = trim($_POST['remarks'] ?? '');

    if (empty($club_name) || empty($role) || empty($join_date)) {
        $error_msg = "Please complete all required fields.";
        $club = [
            'club_id' => $club_id,
            'club_name' => $club_name,
            'role' => $role,
            'join_date' => $join_date,
            'remarks' => $remarks
        ];
    } else {
        $stmt = $conn->prepare("UPDATE clubs SET club_name = ?, role = ?, join_date = ?, remarks = ? WHERE club_id = ? AND user_id = ?");

        if ($stmt) {
            $stmt->bind_param("ssssii", $club_name, $role, $join_date, $remarks, $club_id, $user_id);

            if ($stmt->execute()) {
                $stmt->close();
                header("Location: clubs.php?status=updated");
                exit();
            } else {
                $error_msg = "Error: Could not update the club record. Please try again.";
            }

            $stmt->close();
        } else {
            $error_msg = "Database error: Unable to prepare update query.";
        }
    }
} else {
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        header("Location: clubs.php");
        exit();
    }

    $club_id = (int) $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM clubs WHERE club_id = ? AND user_id = ?");

    if ($stmt) {
        $stmt->bind_param("ii", $club_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $club = $result->fetch_assoc();
        $stmt->close();
    }

    if (!$club) {
        header("Location: clubs.php");
        exit();
    }
}

include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="container">
    <div class="hero-box">
        <h1>Edit Club Record</h1>
        <p>Update the details of your club membership or leadership role.</p>
    </div>

    <?php if (!empty($error_msg)): ?>
        <div class="error-box">
            <?= htmlspecialchars($error_msg) ?>
        </div>
    <?php endif; ?>

    <form action="edit_club.php?id=<?= $club['club_id'] ?>" method="POST">
        <input type="hidden" name="club_id" value="<?= $club['club_id'] ?>">

        <label for="club_name">Club Name</label>
        <input type="text" name="club_name" id="club_name" value="<?= htmlspecialchars($club['club_name']) ?>" required>

        <label for="role">Role</label>
        <input type="text" name="role" id="role" value="<?= htmlspecialchars($club['role']) ?>" required>

        <label for="join_date">Join Date</label>
        <input type="date" name="join_date" id="join_date" value="<?= htmlspecialchars($club['join_date']) ?>" required>

        <label for="remarks">Remarks</label>
        <textarea name="remarks" id="remarks" rows="5"><?= htmlspecialchars($club['remarks']) ?></textarea>

        <div class="form-actions">
            <button type="submit" class="btn">Update Club Record</button>
            <a href="clubs.php" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>