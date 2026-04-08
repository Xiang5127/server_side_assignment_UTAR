<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $achievement_type = $_POST['achievement_type'];
    $date_received = $_POST['date_received'];
    $organiser = trim($_POST['organiser']);
    $remarks = trim($_POST['remarks']);
    $user_id = $_SESSION['user_id'];

    if (empty($title) || empty($date_received)) {
        $errors[] = "Title and Date Received are required.";
    }

    if (empty($errors)) {
        // SQL matching your exact table structure
        $sql = "INSERT INTO achievements (user_id, title, achievement_type, date_received, organiser, remarks) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if ($stmt->execute([$user_id, $title, $achievement_type, $date_received, $organiser, $remarks])) {
            header("Location: achievements.php");
            exit();
        } else {
            $errors[] = "Database error: Could not save achievement.";
        }
    }
}
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/nav.php'; ?>

<div class="container">
    <h2>Record Achievement</h2>
    
    <?php if (!empty($errors)): ?>
        <div class="error-box">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <label>Achievement Title</label>
        <input type="text" name="title" required placeholder="e.g. First Place in Hackathon">

        <label>Type</label>
        <select name="achievement_type" style="width: 100%; padding: 10px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 4px;">
            <option value="Certificate">Certificate</option>
            <option value="Award">Award</option>
            <option value="Medal">Medal</option>
            <option value="Trophy">Trophy</option>
            <option value="Other">Other</option>
        </select>

        <label>Organiser</label>
        <input type="text" name="organiser" placeholder="e.g. Google, University, Ministry of Education">

        <label>Date Received</label>
        <input type="date" name="date_received" required>

        <label>Remarks</label>
        <textarea name="remarks" rows="4" style="width: 100%; padding: 10px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 4px;" placeholder="Add any extra details here..."></textarea>

        <button type="submit">Save to Records</button>
        <a href="achievements.php" style="margin-left: 15px; text-decoration: none; color: #666;">Back</a>
    </form>
</div>

<?php include '../includes/footer.php'; ?>