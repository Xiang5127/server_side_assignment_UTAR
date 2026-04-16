<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$user_id = $_SESSION['user_id'];

// Summary
$stmt = $conn->prepare("SELECT COUNT(*) AS total, COALESCE(SUM(hours), 0) AS total_hours FROM merits WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$summary = $stmt->get_result()->fetch_assoc();
$total_records = $summary['total'];
$total_hours = $summary['total_hours'];
$stmt->close();

// Sorting
$sort_by = $_GET['sort_by'] ?? 'start_desc';
$order_sql = "ORDER BY start_date DESC, merit_id DESC";

if ($sort_by === 'start_asc') {
    $order_sql = "ORDER BY start_date ASC, merit_id ASC";
} elseif ($sort_by === 'hours_desc') {
    $order_sql = "ORDER BY hours DESC, start_date DESC";
} elseif ($sort_by === 'hours_asc') {
    $order_sql = "ORDER BY hours ASC, start_date ASC";
} elseif ($sort_by === 'name_asc') {
    $order_sql = "ORDER BY activity_name ASC";
}

// Fetch records
$query = "SELECT merit_id, activity_name, hours, start_date, end_date, remarks FROM merits WHERE user_id = ? " . $order_sql;
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$merits = [];
while ($row = $result->fetch_assoc()) {
    $merits[] = $row;
}
$stmt->close();

include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="container">
    <?php if (isset($_GET['status'])): ?>
        <?php if ($_GET['status'] === 'added'): ?>
            <div class="success-box">
                Merit record successfully added.
                <span class="close-btn" onclick="this.parentElement.style.display='none';">&times;</span>
            </div>
        <?php elseif ($_GET['status'] === 'updated'): ?>
            <div class="success-box">
                Merit record successfully updated.
                <span class="close-btn" onclick="this.parentElement.style.display='none';">&times;</span>
            </div>
        <?php elseif ($_GET['status'] === 'deleted'): ?>
            <div class="success-box">
                Merit record successfully deleted.
                <span class="close-btn" onclick="this.parentElement.style.display='none';">&times;</span>
            </div>
        <?php elseif ($_GET['status'] === 'error'): ?>
            <div class="error-box">
                An error occurred while processing your request. Please try again.
                <span class="close-btn" onclick="this.parentElement.style.display='none';">&times;</span>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="hero-box merit-hero">
        <div>
            <h1>Merit Tracker</h1>
            <p>Track your co-curricular contribution hours, service activities, and participation effort in an organized and elegant record view.</p>
        </div>
        <a href="add_merit.php" class="btn">+ Add Merit Record</a>
    </div>

    <div class="merit-toolbar">
        <div class="merit-summary-grid">
            <div class="merit-summary-card">
                <span class="merit-summary-label">Total Merit Records</span>
                <span class="merit-summary-number"><?= $total_records ?></span>
            </div>

            <div class="merit-summary-card">
                <span class="merit-summary-label">Total Contribution Hours</span>
                <span class="merit-summary-number"><?= htmlspecialchars(number_format((float)$total_hours, 2)) ?></span>
            </div>
        </div>

        <form method="GET" action="merits.php" class="merit-sort-form">
            <label for="sort_by">Sort By</label>
            <select name="sort_by" id="sort_by" onchange="this.form.submit()">
                <option value="start_desc" <?= $sort_by === 'start_desc' ? 'selected' : '' ?>>Newest First</option>
                <option value="start_asc" <?= $sort_by === 'start_asc' ? 'selected' : '' ?>>Oldest First</option>
                <option value="hours_desc" <?= $sort_by === 'hours_desc' ? 'selected' : '' ?>>Highest Hours</option>
                <option value="hours_asc" <?= $sort_by === 'hours_asc' ? 'selected' : '' ?>>Lowest Hours</option>
                <option value="name_asc" <?= $sort_by === 'name_asc' ? 'selected' : '' ?>>Activity Name (A–Z)</option>
            </select>
        </form>
    </div>

    <?php if (empty($merits)): ?>
        <div class="empty-merit-state">
            <h3>No merit records yet</h3>
            <p>Start recording your contribution hours and service activities by adding your first merit record.</p>
            <a href="add_merit.php" class="btn">Add First Record</a>
        </div>
    <?php else: ?>
        <div class="merit-record-grid">
            <?php foreach ($merits as $row): ?>
                <div class="merit-record-card">
                    <div class="merit-record-top">
                        <span class="merit-hours-badge"><?= htmlspecialchars(number_format((float)$row['hours'], 2)) ?> hrs</span>
                        <div class="merit-date-chip">
                            <?= htmlspecialchars($row['start_date']) ?>
                            <?php if (!empty($row['end_date'])): ?>
                                &nbsp;–&nbsp;<?= htmlspecialchars($row['end_date']) ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <h3 class="merit-record-title"><?= htmlspecialchars($row['activity_name']) ?></h3>

                    <div class="merit-record-meta">
                        <div class="merit-meta-item">
                            <span class="merit-meta-label">Contribution Hours</span>
                            <span class="merit-meta-value"><?= htmlspecialchars(number_format((float)$row['hours'], 2)) ?> hours</span>
                        </div>
                        <div class="merit-meta-item">
                            <span class="merit-meta-label">Activity Period</span>
                            <span class="merit-meta-value">
                                <?= htmlspecialchars($row['start_date']) ?>
                                <?php if (!empty($row['end_date'])): ?>
                                    to <?= htmlspecialchars($row['end_date']) ?>
                                <?php else: ?>
                                    onward
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>

                    <div class="merit-remarks-box">
                        <span class="merit-meta-label">Remarks</span>
                        <p>
                            <?= !empty($row['remarks']) ? nl2br(htmlspecialchars($row['remarks'])) : 'No additional remarks provided.' ?>
                        </p>
                    </div>

                    <div class="merit-record-actions">
                        <a class="merit-action-btn merit-action-edit" href="edit_merit.php?id=<?= $row['merit_id'] ?>">Edit</a>
                        <a class="merit-action-btn merit-action-delete"
                           href="delete_merit.php?id=<?= $row['merit_id'] ?>"
                           onclick="return confirm('Delete this merit record?')">Delete</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
    .merit-hero {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 28px;
    }

    .merit-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 20px;
        flex-wrap: wrap;
        margin-bottom: 28px;
    }

    .merit-summary-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(220px, 1fr));
        gap: 16px;
        flex: 1;
    }

    .merit-summary-card {
        padding: 20px 24px;
        border-radius: 20px;
        background: linear-gradient(135deg, rgba(255,255,255,0.82), rgba(244,235,223,0.90));
        border: 1px solid var(--border);
        box-shadow: var(--shadow-soft);
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .merit-summary-label {
        color: var(--text-soft);
        font-size: 0.95rem;
        font-weight: 600;
    }

    .merit-summary-number {
        font-size: 2.15rem;
        font-weight: 700;
        color: var(--primary-dark);
        line-height: 1;
    }

    .merit-sort-form {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .merit-sort-form label {
        margin: 0;
        color: var(--text-soft);
        font-weight: 600;
        font-size: 0.9rem;
    }

    .merit-sort-form select {
        width: auto;
        min-width: 190px;
        padding: 10px 12px;
        border-radius: 12px;
        border: 1px solid var(--border);
        background: rgba(255,255,255,0.92);
    }

    .merit-record-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 24px;
    }

    .merit-record-card {
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

    .merit-record-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-luxury);
    }

    .merit-record-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        flex-wrap: wrap;
    }

    .merit-hours-badge {
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

    .merit-date-chip {
        font-size: 0.82rem;
        color: var(--text-faint);
        background: rgba(255,255,255,0.55);
        border: 1px solid var(--border);
        border-radius: 999px;
        padding: 7px 12px;
    }

    .merit-record-title {
        margin: 0;
        color: var(--primary-dark);
        font-size: 1.35rem;
        line-height: 1.3;
    }

    .merit-record-meta {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }

    .merit-meta-item {
        padding: 14px 16px;
        border-radius: 16px;
        background: rgba(255,255,255,0.55);
        border: 1px solid var(--border);
    }

    .merit-meta-label {
        display: block;
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--text-faint);
        text-transform: uppercase;
        letter-spacing: 0.4px;
        margin-bottom: 6px;
    }

    .merit-meta-value {
        color: var(--text-main);
        font-weight: 600;
    }

    .merit-remarks-box {
        padding: 16px 18px;
        border-radius: 18px;
        background: rgba(255,255,255,0.45);
        border: 1px solid var(--border);
    }

    .merit-remarks-box p {
        margin: 0;
        color: var(--text-soft);
        line-height: 1.7;
    }

    .merit-record-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        padding-top: 4px;
    }

    .merit-action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 16px;
        border-radius: 999px;
        font-size: 0.9rem;
        font-weight: 700;
        transition: all 0.25s ease;
    }

    .merit-action-edit {
        background: rgba(255,255,255,0.72);
        color: var(--primary-dark);
        border: 1px solid var(--border);
    }

    .merit-action-edit:hover {
        background: rgba(255,255,255,0.96);
    }

    .merit-action-delete {
        background: rgba(157, 93, 85, 0.10);
        color: #9d5d55;
        border: 1px solid rgba(157, 93, 85, 0.16);
    }

    .merit-action-delete:hover {
        background: rgba(157, 93, 85, 0.16);
    }

    .empty-merit-state {
        padding: 48px 28px;
        text-align: center;
        border-radius: 24px;
        background: linear-gradient(180deg, rgba(255,255,255,0.84), rgba(244,236,224,0.90));
        border: 1px solid var(--border);
        box-shadow: var(--shadow-soft);
    }

    .empty-merit-state h3 {
        margin-bottom: 10px;
    }

    .empty-merit-state p {
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

    @media (max-width: 900px) {
        .merit-summary-grid {
            grid-template-columns: 1fr;
            width: 100%;
        }
    }

    @media (max-width: 700px) {
        .merit-record-meta {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php include '../includes/footer.php'; ?>