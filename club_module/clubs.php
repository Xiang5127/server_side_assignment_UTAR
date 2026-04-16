<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$user_id = $_SESSION['user_id'];

// Total count
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM clubs WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_clubs = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Sorting
$sort_by = $_GET['sort_by'] ?? 'join_desc';
$order_sql = "ORDER BY join_date DESC, club_id DESC";

if ($sort_by === 'join_asc') {
    $order_sql = "ORDER BY join_date ASC, club_id ASC";
} elseif ($sort_by === 'name_asc') {
    $order_sql = "ORDER BY club_name ASC";
} elseif ($sort_by === 'role_asc') {
    $order_sql = "ORDER BY role ASC, club_name ASC";
}

$query = "SELECT * FROM clubs WHERE user_id = ? " . $order_sql;
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$clubs = [];
while ($row = $result->fetch_assoc()) {
    $clubs[] = $row;
}
$stmt->close();

include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="container">
    <?php if (isset($_GET['status'])): ?>
        <div class="<?= strpos($_GET['status'], 'error') !== false ? 'error-box' : 'success-box' ?>">
            <?= htmlspecialchars(ucfirst($_GET['status'])) ?> successfully.
            <span class="close-btn" onclick="this.parentElement.style.display='none';">&times;</span>
        </div>
    <?php endif; ?>

    <div class="hero-box club-hero">
        <div>
            <h1>Club Tracker</h1>
            <p>Manage your memberships, affiliations, and leadership roles in a polished record space.</p>
        </div>
        <a href="add_club.php" class="btn">+ Add Club</a>
    </div>

    <div class="club-toolbar">
        <div class="club-summary-card">
            <span class="club-summary-label">Total Club Records</span>
            <span class="club-summary-number"><?= $total_clubs ?></span>
        </div>

        <form method="GET" action="clubs.php" class="club-sort-form">
            <label for="sort_by">Sort By</label>
            <select name="sort_by" id="sort_by" onchange="this.form.submit()">
                <option value="join_desc" <?= $sort_by === 'join_desc' ? 'selected' : '' ?>>Newest First</option>
                <option value="join_asc" <?= $sort_by === 'join_asc' ? 'selected' : '' ?>>Oldest First</option>
                <option value="name_asc" <?= $sort_by === 'name_asc' ? 'selected' : '' ?>>Club Name (A–Z)</option>
                <option value="role_asc" <?= $sort_by === 'role_asc' ? 'selected' : '' ?>>Role (A–Z)</option>
            </select>
        </form>
    </div>

    <?php if (empty($clubs)): ?>
        <div class="empty-club-state">
            <h3>No club records yet</h3>
            <p>Begin documenting your memberships and leadership roles by adding your first club record.</p>
            <a href="add_club.php" class="btn">Add First Club</a>
        </div>
    <?php else: ?>
        <div class="club-record-grid">
            <?php foreach ($clubs as $row): ?>
                <div class="club-record-card">
                    <div class="club-record-top">
                        <span class="club-role-badge"><?= htmlspecialchars($row['role']) ?></span>
                        <div class="club-date-chip">
                            Joined <?= htmlspecialchars($row['join_date']) ?>
                        </div>
                    </div>

                    <h3 class="club-record-title"><?= htmlspecialchars($row['club_name']) ?></h3>

                    <div class="club-record-meta">
                        <div class="club-meta-item">
                            <span class="club-meta-label">Position</span>
                            <span class="club-meta-value"><?= htmlspecialchars($row['role']) ?></span>
                        </div>
                        <div class="club-meta-item">
                            <span class="club-meta-label">Membership Since</span>
                            <span class="club-meta-value"><?= htmlspecialchars($row['join_date']) ?></span>
                        </div>
                    </div>

                    <div class="club-remarks-box">
                        <span class="club-meta-label">Remarks</span>
                        <p>
                            <?= !empty($row['remarks']) ? nl2br(htmlspecialchars($row['remarks'])) : 'No additional remarks provided.' ?>
                        </p>
                    </div>

                    <div class="club-record-actions">
                        <a class="club-action-btn club-action-edit" href="edit_club.php?id=<?= $row['club_id'] ?>">Edit</a>
                        <a class="club-action-btn club-action-delete"
                           href="delete_club.php?id=<?= $row['club_id'] ?>"
                           onclick="return confirm('Delete this club record?')">Delete</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
    .club-hero {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 28px;
    }

    .club-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 18px;
        flex-wrap: wrap;
        margin-bottom: 28px;
    }

    .club-summary-card {
        min-width: 220px;
        padding: 20px 24px;
        border-radius: 20px;
        background: linear-gradient(135deg, rgba(255,255,255,0.82), rgba(244,235,223,0.90));
        border: 1px solid var(--border);
        box-shadow: var(--shadow-soft);
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .club-summary-label {
        color: var(--text-soft);
        font-size: 0.95rem;
        font-weight: 600;
    }

    .club-summary-number {
        font-size: 2.15rem;
        font-weight: 700;
        color: var(--primary-dark);
        line-height: 1;
    }

    .club-sort-form {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .club-sort-form label {
        margin: 0;
        color: var(--text-soft);
        font-weight: 600;
    }

    .club-sort-form select {
        width: auto;
        min-width: 180px;
        padding: 10px 12px;
        border-radius: 12px;
        border: 1px solid var(--border);
        background: rgba(255,255,255,0.92);
    }

    .club-record-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 24px;
    }

    .club-record-card {
        padding: 24px;
        border-radius: 24px;
        background: linear-gradient(180deg, rgba(255,255,255,0.92), rgba(243,233,220,0.95));
        border: 1px solid var(--border);
        box-shadow: var(--shadow-soft);
        display: flex;
        flex-direction: column;
        gap: 18px;
        transition: all 0.25s ease;
    }

    .club-record-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-luxury);
    }

    .club-record-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        flex-wrap: wrap;
    }

    .club-role-badge {
        display: inline-block;
        padding: 7px 12px;
        border-radius: 999px;
        background: linear-gradient(135deg, #efe2cd, #f8f2e8);
        color: var(--primary-dark);
        border: 1px solid var(--border);
        font-size: 0.76rem;
        font-weight: 700;
        letter-spacing: 0.3px;
        text-transform: uppercase;
    }

    .club-date-chip {
        font-size: 0.82rem;
        color: var(--text-faint);
        background: rgba(255,255,255,0.55);
        border: 1px solid var(--border);
        border-radius: 999px;
        padding: 7px 12px;
    }

    .club-record-title {
        margin: 0;
        color: var(--primary-dark);
        font-size: 1.35rem;
        line-height: 1.3;
    }

    .club-record-meta {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }

    .club-meta-item {
        padding: 14px 16px;
        border-radius: 16px;
        background: rgba(255,255,255,0.55);
        border: 1px solid var(--border);
    }

    .club-meta-label {
        display: block;
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--text-faint);
        text-transform: uppercase;
        letter-spacing: 0.4px;
        margin-bottom: 6px;
    }

    .club-meta-value {
        color: var(--text-main);
        font-weight: 600;
    }

    .club-remarks-box {
        padding: 16px 18px;
        border-radius: 18px;
        background: rgba(255,255,255,0.45);
        border: 1px solid var(--border);
    }

    .club-remarks-box p {
        margin: 0;
        color: var(--text-soft);
        line-height: 1.7;
    }

    .club-record-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        padding-top: 4px;
    }

    .club-action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 16px;
        border-radius: 999px;
        font-size: 0.9rem;
        font-weight: 700;
        transition: all 0.25s ease;
    }

    .club-action-edit {
        background: rgba(255,255,255,0.72);
        color: var(--primary-dark);
        border: 1px solid var(--border);
    }

    .club-action-edit:hover {
        background: rgba(255,255,255,0.96);
    }

    .club-action-delete {
        background: rgba(157, 93, 85, 0.10);
        color: #9d5d55;
        border: 1px solid rgba(157, 93, 85, 0.16);
    }

    .club-action-delete:hover {
        background: rgba(157, 93, 85, 0.16);
    }

    .empty-club-state {
        padding: 48px 28px;
        text-align: center;
        border-radius: 24px;
        background: linear-gradient(180deg, rgba(255,255,255,0.84), rgba(244,236,224,0.90));
        border: 1px solid var(--border);
        box-shadow: var(--shadow-soft);
    }

    .empty-club-state h3 {
        margin-bottom: 10px;
    }

    .empty-club-state p {
        margin-bottom: 18px;
    }

    .success-box,
    .error-box {
        position: relative;
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 10px;
    }

    .success-box {
        background: #eef7ed;
        color: #1e4620;
        border: 1px solid #c3e6cb;
    }

    .error-box {
        background: #fdf2f2;
        color: #9b1c1c;
        border: 1px solid #fbd5d5;
    }

    .close-btn {
        position: absolute;
        right: 15px;
        cursor: pointer;
        font-weight: bold;
        opacity: 0.5;
    }

    .close-btn:hover {
        opacity: 1;
    }

    @media (max-width: 700px) {
        .club-record-meta {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php include '../includes/footer.php'; ?>