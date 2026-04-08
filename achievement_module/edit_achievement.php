<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$user_id = $_SESSION['user_id'];
$id = $_GET['id'] ?? null;
$errors = [];

// Fetch the existing achievement to make sure it belongs to the logged-in user
$stmt = $conn->prepare("SELECT * FROM achievements WHERE achievement_id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
$achievement = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$achievement) {
    header("Location: achievements.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $type = $_POST['achievement_type'];
    $date = $_POST['date_received'];
    $organiser = trim($_POST['organiser']);
    $remarks = trim($_POST['remarks']);

    if (empty($title) || empty($date)) {
        $errors[] = "Title and Date are required.";
    }

    if (empty($errors)) {
        $update = $conn->prepare("UPDATE achievements SET title=?, achievement_type=?, date_received=?, organiser=?, remarks=? WHERE achievement_id=? AND user_id=?");
        if ($update->execute([$title, $type, $date, $organiser, $remarks, $id, $user_id])) {
            header("Location: achievements.php");
            exit();
        } else {
            $errors[] = "Could not update the record.";
        }
    }
}
?>

<?php include '../includes/header.php'; ?>
<?php include 'includes/nav.php'; ?>

<div class="container">
    <h2>Edit Achievement</h2>
    
    <?php if (!empty($errors)): ?>
        <div class="error-box">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <label>Title</label>
        <input type="text" name="title" value="<?= htmlspecialchars($achievement['title']) ?>" required>

        <label>Type</label>
        <select name="achievement_type" style="width: 100%; padding: 10px; margin-bottom: 20px;">
            <option value="Certificate" <?= $achievement['achievement_type'] == 'Certificate' ? 'selected' : '' ?>>Certificate</option>
            <option value="Award" <?= $achievement['achievement_type'] == 'Award' ? 'selected' : '' ?>>Award</option>
            <option value="Medal" <?= $achievement['achievement_type'] == 'Medal' ? 'selected' : '' ?>>Medal</option>
            <option value="Trophy" <?= $achievement['achievement_type'] == 'Trophy' ? 'selected' : '' ?>>Trophy</option>
            <option value="Other" <?= $achievement['achievement_type'] == 'Other' ? 'selected' : '' ?>>Other</option>
        </select>

        <label>Organiser</label>
        <input type="text" name="organiser" value="<?= htmlspecialchars($achievement['organiser']) ?>">

        <label>Date Received</label>
        <input type="date" name="date_received" value="<?= $achievement['date_received'] ?>" required>

        <label>Remarks</label>
        <textarea name="remarks" rows="4" style="width: 100%; padding: 10px; margin-bottom: 20px;"><?= htmlspecialchars($achievement['remarks']) ?></textarea>

        <button type="submit">Update Achievement</button>
        <a href="achievements.php" style="margin-left: 15px; text-decoration: none; color: #666;">Cancel</a>
    </form>
</div>

<?php include '../includes/footer.php'; ?>