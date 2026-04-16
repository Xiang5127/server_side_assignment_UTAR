<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$user_id = $_SESSION['user_id'];

// Total events
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM events WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_events = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Filter + sort
$filter_loc = $_GET['filter_loc'] ?? 'all';
$sort_by = $_GET['sort_by'] ?? 'date_desc';

$order_sql = "ORDER BY event_date DESC, event_id DESC";
if ($sort_by === 'date_asc') {
    $order_sql = "ORDER BY event_date ASC, event_id ASC";
} elseif ($sort_by === 'name_asc') {
    $order_sql = "ORDER BY event_name ASC";
}

if ($filter_loc !== 'all') {
    $query = "SELECT event_id, event_name, organiser, event_date, location, location_type, description 
              FROM events 
              WHERE user_id = ? AND location_type = ? " . $order_sql;
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $user_id, $filter_loc);
} else {
    $query = "SELECT event_id, event_name, organiser, event_date, location, location_type, description 
              FROM events 
              WHERE user_id = ? " . $order_sql;
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$result = $stmt->get_result();

$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}
$stmt->close();

include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="container">
    <?php if (isset($_GET['status'])): ?>
        <?php if ($_GET['status'] === 'added'): ?>
            <div class="success-box">
                New event successfully recorded.
                <span class="close-btn" onclick="this.parentElement.style.display='none';">&times;</span>
            </div>
        <?php elseif ($_GET['status'] === 'updated'): ?>
            <div class="success-box">
                Event details successfully updated.
                <span class="close-btn" onclick="this.parentElement.style.display='none';">&times;</span>
            </div>
        <?php elseif ($_GET['status'] === 'deleted'): ?>
            <div class="success-box">
                Event record successfully deleted.
                <span class="close-btn" onclick="this.parentElement.style.display='none';">&times;</span>
            </div>
        <?php elseif ($_GET['status'] === 'error'): ?>
            <div class="error-box">
                An error occurred while processing your request. Please try again.
                <span class="close-btn" onclick="this.parentElement.style.display='none';">&times;</span>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="hero-box event-hero">
        <div>
            <h1>Event Tracker</h1>
            <p>Manage your programmes, competitions, workshops, talks, and co-curricular participations in one refined event record.</p>
        </div>
        <a href="event_add.php" class="btn">+ Record Event</a>
    </div>

    <div class="event-toolbar">
        <div class="event-summary-card">
            <span class="event-summary-label">Total Participations</span>
            <span class="event-summary-number"><?= $total_events ?></span>
        </div>

        <form method="GET" action="event_index.php" class="event-filter-form">
            <div class="filter-group">
                <label for="filter_loc">Location</label>
                <select name="filter_loc" id="filter_loc" onchange="this.form.submit()">
                    <option value="all" <?= $filter_loc === 'all' ? 'selected' : '' ?>>All Locations</option>
                    <option value="online" <?= $filter_loc === 'online' ? 'selected' : '' ?>>Online</option>
                    <option value="campus" <?= $filter_loc === 'campus' ? 'selected' : '' ?>>In Campus</option>
                    <option value="other" <?= $filter_loc === 'other' ? 'selected' : '' ?>>Other</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="sort_by">Sort By</label>
                <select name="sort_by" id="sort_by" onchange="this.form.submit()">
                    <option value="date_desc" <?= $sort_by === 'date_desc' ? 'selected' : '' ?>>Newest First</option>
                    <option value="date_asc" <?= $sort_by === 'date_asc' ? 'selected' : '' ?>>Oldest First</option>
                    <option value="name_asc" <?= $sort_by === 'name_asc' ? 'selected' : '' ?>>Event Name (A–Z)</option>
                </select>
            </div>
        </form>
    </div>

    <?php if (empty($events)): ?>
        <div class="empty-event-state">
            <h3>No events recorded yet</h3>
            <p>Start documenting your programmes and participations by adding your first event record.</p>
            <a href="event_add.php" class="btn">Add First Event</a>
        </div>
    <?php else: ?>
        <div class="event-record-grid">
            <?php foreach ($events as $row): ?>
                <div class="event-record-card">
                    <div class="event-record-top">
                        <span class="event-type-badge"><?= htmlspecialchars($row['location_type']) ?></span>
                        <div class="event-date-chip">
                            <?= htmlspecialchars($row['event_date']) ?>
                        </div>
                    </div>

                    <h3 class="event-record-title"><?= htmlspecialchars($row['event_name']) ?></h3>

                    <div class="event-record-meta">
                        <div class="event-meta-item">
                            <span class="event-meta-label">Organiser</span>
                            <span class="event-meta-value"><?= htmlspecialchars($row['organiser']) ?></span>
                        </div>
                        <div class="event-meta-item">
                            <span class="event-meta-label">Location</span>
                            <span class="event-meta-value"><?= htmlspecialchars($row['location']) ?></span>
                        </div>
                    </div>

                    <div class="event-description-box">
                        <span class="event-meta-label">Remarks / Description</span>
                        <p>
                            <?= !empty($row['description']) ? nl2br(htmlspecialchars($row['description'])) : 'No additional description provided.' ?>
                        </p>
                    </div>

                    <div class="event-record-actions">
                        <a class="event-action-btn event-action-edit" href="event_edit.php?id=<?= $row['event_id'] ?>">Edit</a>
                        <a class="event-action-btn event-action-delete"
                           href="event_delete.php?id=<?= $row['event_id'] ?>"
                           onclick="return confirm('Delete this event record?')">Delete</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
    .event-hero {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 28px;
    }

    .event-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 18px;
        flex-wrap: wrap;
        margin-bottom: 28px;
    }

    .event-summary-card {
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

    .event-summary-label {
        color: var(--text-soft);
        font-size: 0.95rem;
        font-weight: 600;
    }

    .event-summary-number {
        font-size: 2.15rem;
        font-weight: 700;
        color: var(--primary-dark);
        line-height: 1;
    }

    .event-filter-form {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        align-items: end;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .filter-group label {
        margin: 0;
        color: var(--text-soft);
        font-weight: 600;
        font-size: 0.9rem;
    }

    .filter-group select {
        width: auto;
        min-width: 170px;
        padding: 10px 12px;
        border-radius: 12px;
        border: 1px solid var(--border);
        background: rgba(255,255,255,0.92);
    }

    .event-record-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 24px;
    }

    .event-record-card {
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

    .event-record-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-luxury);
    }

    .event-record-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        flex-wrap: wrap;
    }

    .event-type-badge {
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

    .event-date-chip {
        font-size: 0.82rem;
        color: var(--text-faint);
        background: rgba(255,255,255,0.55);
        border: 1px solid var(--border);
        border-radius: 999px;
        padding: 7px 12px;
    }

    .event-record-title {
        margin: 0;
        color: var(--primary-dark);
        font-size: 1.35rem;
        line-height: 1.3;
    }

    .event-record-meta {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }

    .event-meta-item {
        padding: 14px 16px;
        border-radius: 16px;
        background: rgba(255,255,255,0.55);
        border: 1px solid var(--border);
    }

    .event-meta-label {
        display: block;
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--text-faint);
        text-transform: uppercase;
        letter-spacing: 0.4px;
        margin-bottom: 6px;
    }

    .event-meta-value {
        color: var(--text-main);
        font-weight: 600;
    }

    .event-description-box {
        padding: 16px 18px;
        border-radius: 18px;
        background: rgba(255,255,255,0.45);
        border: 1px solid var(--border);
    }

    .event-description-box p {
        margin: 0;
        color: var(--text-soft);
        line-height: 1.7;
    }

    .event-record-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        padding-top: 4px;
    }

    .event-action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 16px;
        border-radius: 999px;
        font-size: 0.9rem;
        font-weight: 700;
        transition: all 0.25s ease;
    }

    .event-action-edit {
        background: rgba(255,255,255,0.72);
        color: var(--primary-dark);
        border: 1px solid var(--border);
    }

    .event-action-edit:hover {
        background: rgba(255,255,255,0.96);
    }

    .event-action-delete {
        background: rgba(157, 93, 85, 0.10);
        color: #9d5d55;
        border: 1px solid rgba(157, 93, 85, 0.16);
    }

    .event-action-delete:hover {
        background: rgba(157, 93, 85, 0.16);
    }

    .empty-event-state {
        padding: 48px 28px;
        text-align: center;
        border-radius: 24px;
        background: linear-gradient(180deg, rgba(255,255,255,0.84), rgba(244,236,224,0.90));
        border: 1px solid var(--border);
        box-shadow: var(--shadow-soft);
    }

    .empty-event-state h3 {
        margin-bottom: 10px;
    }

    .empty-event-state p {
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
        .event-record-meta {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php include '../includes/footer.php'; ?>