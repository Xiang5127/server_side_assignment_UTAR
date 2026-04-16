<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$user_id = $_SESSION['user_id'];
$error_msg = "";
$merit = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $merit_id = (int) ($_POST['merit_id'] ?? 0);
    $activity_name = trim($_POST['activity_name'] ?? '');
    $hours = trim($_POST['hours'] ?? '');
    $start_date = trim($_POST['start_date'] ?? '');
    $end_date = trim($_POST['end_date'] ?? '');
    $remarks = trim($_POST['remarks'] ?? '');

    if (empty($activity_name) || $hours === '' || empty($start_date)) {
        $error_msg = "Please complete all required fields.";
    } elseif (!is_numeric($hours) || (float)$hours < 0) {
        $error_msg = "Hours must be a valid non-negative number.";
    } elseif (!empty($end_date) && $end_date < $start_date) {
        $error_msg = "End date cannot be earlier than start date.";
    } else {
        $hours_value = (float)$hours;

        $stmt = $conn->prepare("UPDATE merits SET activity_name = ?, hours = ?, start_date = ?, end_date = ?, remarks = ? WHERE merit_id = ? AND user_id = ?");

        if ($stmt) {
            $stmt->bind_param("sdsssii", $activity_name, $hours_value, $start_date, $end_date, $remarks, $merit_id, $user_id);

            if ($stmt->execute()) {
                $stmt->close();
                header("Location: merits.php?status=updated");
                exit();
            } else {
                $error_msg = "Error: Could not update the merit record. Please try again.";
            }

            $stmt->close();
        } else {
            $error_msg = "Database error: Unable to prepare update query.";
        }
    }

    $merit = [
        'merit_id' => $merit_id,
        'activity_name' => $activity_name,
        'hours' => $hours,
        'start_date' => $start_date,
        'end_date' => $end_date,
        'remarks' => $remarks
    ];
} else {
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        header("Location: merits.php");
        exit();
    }

    $merit_id = (int) $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM merits WHERE merit_id = ? AND user_id = ?");

    if ($stmt) {
        $stmt->bind_param("ii", $merit_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $merit = $result->fetch_assoc();
        $stmt->close();
    }

    if (!$merit) {
        header("Location: merits.php");
        exit();
    }
}

include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="container">
    <div class="hero-box">
        <h1>Edit Merit Record</h1>
        <p>Update the details of your contribution hours or service activity.</p>
    </div>

    <?php if (!empty($error_msg)): ?>
        <div class="error-box">
            <?= htmlspecialchars($error_msg) ?>
        </div>
    <?php endif; ?>

    <form action="edit_merit.php?id=<?= $merit['merit_id'] ?>" method="POST">
        <input type="hidden" name="merit_id" value="<?= $merit['merit_id'] ?>">

        <label for="activity_name">Activity Name</label>
        <input type="text" name="activity_name" id="activity_name" value="<?= htmlspecialchars($merit['activity_name']) ?>" required>

        <label for="hours">Contribution Hours</label>
        <input type="number" name="hours" id="hours" value="<?= htmlspecialchars($merit['hours']) ?>" step="0.01" min="0" required>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div>
                <label for="start_date">Start Date</label>
                <input type="date" name="start_date" id="start_date" value="<?= htmlspecialchars($merit['start_date']) ?>" required>
            </div>
            <div>
                <label for="end_date">End Date</label>
                <input type="date" name="end_date" id="end_date" value="<?= htmlspecialchars($merit['end_date']) ?>">
            </div>
        </div>

        <label for="remarks">Remarks</label>
        <textarea name="remarks" id="remarks" rows="5"><?= htmlspecialchars($merit['remarks']) ?></textarea>

        <div class="form-actions">
            <button type="submit" class="btn">Update Merit Record</button>
            <a href="merits.php" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>