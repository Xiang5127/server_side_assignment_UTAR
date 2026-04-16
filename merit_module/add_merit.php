<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$user_id = $_SESSION['user_id'];
$error_msg = "";

$activity_name = "";
$hours = "";
$start_date = "";
$end_date = "";
$remarks = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
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

        $stmt = $conn->prepare("INSERT INTO merits (user_id, activity_name, hours, start_date, end_date, remarks) VALUES (?, ?, ?, ?, ?, ?)");

        if ($stmt) {
            $stmt->bind_param("isdsss", $user_id, $activity_name, $hours_value, $start_date, $end_date, $remarks);

            if ($stmt->execute()) {
                $stmt->close();
                header("Location: merits.php?status=added");
                exit();
            } else {
                $error_msg = "Error: Could not save the merit record. Please try again.";
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
        <h1>Add Merit Record</h1>
        <p>Enter the details of your contribution hours or service activity.</p>
    </div>

    <?php if (!empty($error_msg)): ?>
        <div class="error-box">
            <?= htmlspecialchars($error_msg) ?>
        </div>
    <?php endif; ?>

    <form action="add_merit.php" method="POST">
        <label for="activity_name">Activity Name</label>
        <input type="text" name="activity_name" id="activity_name" value="<?= htmlspecialchars($activity_name) ?>" placeholder="e.g. Volunteer at Faculty Open Day" required>

        <label for="hours">Contribution Hours</label>
        <input type="number" name="hours" id="hours" value="<?= htmlspecialchars($hours) ?>" step="0.01" min="0" placeholder="e.g. 5.50" required>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div>
                <label for="start_date">Start Date</label>
                <input type="date" name="start_date" id="start_date" value="<?= htmlspecialchars($start_date) ?>" required>
            </div>
            <div>
                <label for="end_date">End Date</label>
                <input type="date" name="end_date" id="end_date" value="<?= htmlspecialchars($end_date) ?>">
            </div>
        </div>

        <label for="remarks">Remarks</label>
        <textarea name="remarks" id="remarks" rows="5" placeholder="Any extra notes about the activity or contribution..."><?= htmlspecialchars($remarks) ?></textarea>

        <div class="form-actions">
            <button type="submit" class="btn">Save Merit Record</button>
            <a href="merits.php" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>