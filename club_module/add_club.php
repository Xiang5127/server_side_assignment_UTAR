<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$user_id = $_SESSION['user_id'];
$error_msg = "";

$club_name = "";
$role = "";
$join_date = "";
$remarks = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $club_name = trim($_POST['club_name'] ?? '');
    $role = trim($_POST['role'] ?? '');
    $join_date = trim($_POST['join_date'] ?? '');
    $remarks = trim($_POST['remarks'] ?? '');

    if (empty($club_name) || empty($role) || empty($join_date)) {
        $error_msg = "Please complete all required fields.";
    } else {
        $stmt = $conn->prepare("INSERT INTO clubs (user_id, club_name, role, join_date, remarks) VALUES (?, ?, ?, ?, ?)");

        if ($stmt) {
            $stmt->bind_param("issss", $user_id, $club_name, $role, $join_date, $remarks);

            if ($stmt->execute()) {
                $stmt->close();
                header("Location: clubs.php?status=added");
                exit();
            } else {
                $error_msg = "Error: Could not save the club record. Please try again.";
            }

            $stmt->close();
        } else {
            $error_msg = "Database error: Unable to prepare the query.";
        }
    }
}

include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="container">
    <div class="hero-box">
        <h1>Add Club Record</h1>
        <p>Enter the details of your club membership or leadership role.</p>
    </div>

    <?php if (!empty($error_msg)): ?>
        <div class="error-box">
            <?= htmlspecialchars($error_msg) ?>
        </div>
    <?php endif; ?>

    <form action="add_club.php" method="POST">
        <label for="club_name">Club Name</label>
        <input type="text" name="club_name" id="club_name" value="<?= htmlspecialchars($club_name) ?>" placeholder="e.g. Computer Science Society" required>

        <label for="role">Role</label>
        <input type="text" name="role" id="role" value="<?= htmlspecialchars($role) ?>" placeholder="e.g. Member, Committee, President" required>

        <label for="join_date">Join Date</label>
        <input type="date" name="join_date" id="join_date" value="<?= htmlspecialchars($join_date) ?>" required>

        <label for="remarks">Remarks</label>
        <textarea name="remarks" id="remarks" rows="5" placeholder="Any extra notes about your membership or responsibilities..."><?= htmlspecialchars($remarks) ?></textarea>

        <div class="form-actions">
            <button type="submit" class="btn">Save Club Record</button>
            <a href="clubs.php" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>