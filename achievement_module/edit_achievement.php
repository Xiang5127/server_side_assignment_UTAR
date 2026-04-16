<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$user_id = $_SESSION['user_id'];
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$error_msg = "";

// Initial fetch of the achievement record
$stmt = $conn->prepare("SELECT * FROM achievements WHERE achievement_id = ? AND user_id = ?");

if ($stmt) {
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $achievement = $result->fetch_assoc();
    $stmt->close();
} else {
    $achievement = null;
}

// Redirect if achievement doesn't exist or doesn't belong to the user
if (!$achievement) {
    header("Location: achievements.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title'] ?? '');
    $type = trim($_POST['achievement_type'] ?? '');
    $date = trim($_POST['date_received'] ?? '');
    $organiser = trim($_POST['organiser'] ?? '');
    $remarks = trim($_POST['remarks'] ?? '');

    if (empty($title) || empty($date)) {
        $error_msg = "Achievement title and date received are required.";
    } else {
        $update = $conn->prepare("UPDATE achievements 
                                  SET title = ?, achievement_type = ?, date_received = ?, organiser = ?, remarks = ? 
                                  WHERE achievement_id = ? AND user_id = ?");

        if ($update) {
            $update->bind_param("sssssii", $title, $type, $date, $organiser, $remarks, $id, $user_id);

            if ($update->execute()) {
                $update->close();
                // Redirect with status to show success box on the list page
                header("Location: achievements.php?status=updated");
                exit();
            } else {
                $error_msg = "Unable to update the achievement record. Please try again.";
            }
            $update->close();
        } else {
            $error_msg = "Database error: Unable to prepare update query.";
        }
    }

    // Preserve input values in case of error
    $achievement['title'] = $title;
    $achievement['achievement_type'] = $type;
    $achievement['date_received'] = $date;
    $achievement['organiser'] = $organiser;
    $achievement['remarks'] = $remarks;
}

include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="container">
    <div class="hero-box">
        <h1>Edit Achievement</h1>
        <p>Update the details of your award, certificate, or recognition below.</p>
    </div>

    <?php if (!empty($error_msg)): ?>
        <div class="error-box">
            <?= htmlspecialchars($error_msg) ?>
            <span class="close-btn" onclick="this.parentElement.style.display='none';">&times;</span>
        </div>
    <?php endif; ?>

    <form method="POST">
        <label for="title">Achievement Title</label>
        <input type="text" id="title" name="title" value="<?= htmlspecialchars($achievement['title']) ?>" 
               placeholder="e.g. First Place in Innovation Challenge" required>

        <label for="achievement_type">Type</label>
        <select id="achievement_type" name="achievement_type" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--border); margin-bottom: 20px; background-color: white;">
            <option value="Certificate" <?= $achievement['achievement_type'] === 'Certificate' ? 'selected' : '' ?>>Certificate</option>
            <option value="Award" <?= $achievement['achievement_type'] === 'Award' ? 'selected' : '' ?>>Award</option>
            <option value="Medal" <?= $achievement['achievement_type'] === 'Medal' ? 'selected' : '' ?>>Medal</option>
            <option value="Trophy" <?= $achievement['achievement_type'] === 'Trophy' ? 'selected' : '' ?>>Trophy</option>
            <option value="Other" <?= $achievement['achievement_type'] === 'Other' ? 'selected' : '' ?>>Other</option>
        </select>

        <label for="organiser">Organiser</label>
        <input type="text" id="organiser" name="organiser" value="<?= htmlspecialchars($achievement['organiser']) ?>" 
               placeholder="e.g. UTAR, Google, Ministry of Education">

        <label for="date_received">Date Received</label>
        <input type="date" id="date_received" name="date_received" value="<?= htmlspecialchars($achievement['date_received']) ?>" required>

        <label for="remarks">Remarks</label>
        <textarea id="remarks" name="remarks" rows="5" 
                  placeholder="Any extra notes about your achievement..."><?= htmlspecialchars($achievement['remarks']) ?></textarea>

        <div class="form-actions">
            <button type="submit" class="btn">Update Achievement</button>
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