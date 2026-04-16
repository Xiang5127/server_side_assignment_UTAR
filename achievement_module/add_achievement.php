<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$user_id = $_SESSION['user_id'];
$error_msg = "";

$title = '';
$achievement_type = 'Certificate';
$date_received = '';
$organiser = '';
$remarks = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title'] ?? '');
    $achievement_type = trim($_POST['achievement_type'] ?? 'Certificate');
    $date_received = trim($_POST['date_received'] ?? '');
    $organiser = trim($_POST['organiser'] ?? '');
    $remarks = trim($_POST['remarks'] ?? '');

    if (empty($title) || empty($date_received)) {
        $error_msg = "Achievement title and date received are required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO achievements (user_id, title, achievement_type, date_received, organiser, remarks)
                                VALUES (?, ?, ?, ?, ?, ?)");

        if ($stmt) {
            $stmt->bind_param("isssss", $user_id, $title, $achievement_type, $date_received, $organiser, $remarks);

            if ($stmt->execute()) {
                $stmt->close();
                // Redirect with status to show success box on the list page
                header("Location: achievements.php?status=added");
                exit();
            } else {
                $error_msg = "Unable to save the achievement record. Please try again.";
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
        <h1>Add Achievement</h1>
        <p>Enter the details of your award, certificate, or recognition below.</p>
    </div>

    <?php if (!empty($error_msg)): ?>
        <div class="error-box">
            <?= htmlspecialchars($error_msg) ?>
            <span class="close-btn" onclick="this.parentElement.style.display='none';">&times;</span>
        </div>
    <?php endif; ?>

    <form method="POST" action="add_achievement.php">
        <label for="title">Achievement Title</label>
        <input type="text" id="title" name="title" value="<?= htmlspecialchars($title) ?>" 
               placeholder="e.g. First Place in Innovation Challenge" required>

        <label for="achievement_type">Type</label>
        <select id="achievement_type" name="achievement_type" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--border); margin-bottom: 20px; background-color: white;">
            <option value="Certificate" <?= $achievement_type === 'Certificate' ? 'selected' : '' ?>>Certificate</option>
            <option value="Award" <?= $achievement_type === 'Award' ? 'selected' : '' ?>>Award</option>
            <option value="Medal" <?= $achievement_type === 'Medal' ? 'selected' : '' ?>>Medal</option>
            <option value="Trophy" <?= $achievement_type === 'Trophy' ? 'selected' : '' ?>>Trophy</option>
            <option value="Other" <?= $achievement_type === 'Other' ? 'selected' : '' ?>>Other</option>
        </select>

        <label for="organiser">Organiser</label>
        <input type="text" id="organiser" name="organiser" value="<?= htmlspecialchars($organiser) ?>" 
               placeholder="e.g. UTAR, Google, Ministry of Education">

        <label for="date_received">Date Received</label>
        <input type="date" id="date_received" name="date_received" value="<?= htmlspecialchars($date_received) ?>" required>

        <label for="remarks">Remarks</label>
        <textarea id="remarks" name="remarks" rows="5" 
                  placeholder="Any extra notes about your achievement..."><?= htmlspecialchars($remarks) ?></textarea>

        <div class="form-actions">
            <button type="submit" class="btn">Save Achievement</button>
            <a href="achievements.php" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<style>
    /* Styling for the close button in error box */
    .error-box {
        position: relative;
        padding-right: 40px;
        margin-bottom: 20px;
    }

    .close-btn {
        position: absolute;
        top: 50%;
        right: 15px;
        transform: translateY(-50%);
        font-size: 1.4rem;
        font-weight: bold;
        cursor: pointer;
        opacity: 0.5;
        transition: opacity 0.2s ease;
    }

    .close-btn:hover {
        opacity: 1;
    }
</style>

<?php include '../includes/footer.php'; ?>