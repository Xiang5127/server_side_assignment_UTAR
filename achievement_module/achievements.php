<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$user_id = $_SESSION['user_id'];

// Fetch user's achievements
$stmt = $conn->prepare("SELECT * FROM achievements WHERE user_id = ? ORDER BY date_received DESC");
$stmt->execute([$user_id]);
$achievements = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/nav.php'; ?>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Achievement Tracker</h2>
        <a href="add_achievement.php" class="btn">Add New Achievement</a>
    </div>

    <?php if (empty($achievements)): ?>
        <p>No achievements found. Click "Add New Achievement" to start tracking your success!</p>
    <?php else: ?>
        <div class="card-grid">
            <?php foreach ($achievements as $row): ?>
                <div class="card">
                    <span class="badge" style="background: #e3f2fd; color: #0d47a1; padding: 4px 8px; border-radius: 4px; font-size: 0.75em; font-weight: bold;">
                        <?= htmlspecialchars($row['achievement_type']) ?>
                    </span>
                    <h3><?= htmlspecialchars($row['title']) ?></h3>
                    <p><strong>Organiser:</strong> <?= htmlspecialchars($row['organiser']) ?></p>
                    <p><strong>Date:</strong> <?= htmlspecialchars($row['date_received']) ?></p>
                    <p><em><?= nl2br(htmlspecialchars($row['remarks'])) ?></em></p>
                    
                    <hr style="margin: 15px 0; border: 0; border-top: 1px solid #eee;">
                    
                    <div style="display: flex; gap: 15px;">
                        <a href="edit_achievement.php?id=<?= $row['achievement_id'] ?>" style="color: #0d47a1; text-decoration: none; font-weight: bold;">Edit</a>
                        <a href="delete_achievement.php?id=<?= $row['achievement_id'] ?>" 
                           style="color: #d32f2f; text-decoration: none; font-weight: bold;" 
                           onclick="return confirm('Are you sure you want to delete this achievement?')">Delete</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>