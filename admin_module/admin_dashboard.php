<?php
require_once '../includes/admin_check.php';
require_once '../config/db.php';

$search = trim($_GET['search'] ?? '');

// Build main query
$sql = "
    SELECT 
        u.user_id,
        u.full_name,
        u.email,
        u.role,
        COALESCE(e.total_events, 0) AS total_events,
        COALESCE(c.total_clubs, 0) AS total_clubs,
        COALESCE(m.total_merits, 0) AS total_merits,
        COALESCE(a.total_achievements, 0) AS total_achievements
    FROM users u
    LEFT JOIN (
        SELECT user_id, COUNT(*) AS total_events
        FROM events
        GROUP BY user_id
    ) e ON u.user_id = e.user_id
    LEFT JOIN (
        SELECT user_id, COUNT(*) AS total_clubs
        FROM clubs
        GROUP BY user_id
    ) c ON u.user_id = c.user_id
    LEFT JOIN (
        SELECT user_id, COUNT(*) AS total_merits
        FROM merits
        GROUP BY user_id
    ) m ON u.user_id = m.user_id
    LEFT JOIN (
        SELECT user_id, COUNT(*) AS total_achievements
        FROM achievements
        GROUP BY user_id
    ) a ON u.user_id = a.user_id
";

if ($search !== '') {
    $sql .= " WHERE u.full_name LIKE ? OR u.email LIKE ? ";
}

$sql .= " ORDER BY u.full_name ASC";

$stmt = $conn->prepare($sql);

if ($search !== '') {
    $search_param = "%" . $search . "%";
    $stmt->bind_param("ss", $search_param, $search_param);
}

$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}
$stmt->close();

// Summary cards
$total_users = count($users);
$total_events = 0;
$total_clubs = 0;
$total_merits = 0;
$total_achievements = 0;

foreach ($users as $u) {
    $total_events += (int)$u['total_events'];
    $total_clubs += (int)$u['total_clubs'];
    $total_merits += (int)$u['total_merits'];
    $total_achievements += (int)$u['total_achievements'];
}

include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="container">
    <div class="hero-box admin-hero">
        <div>
            <h1>Admin Dashboard</h1>
            <p>View all registered users and their overall co-curricular activity summaries.</p>
        </div>
    </div>

    <div class="admin-toolbar">
        <form method="GET" action="admin_dashboard.php" class="admin-search-form">
            <label for="search">Search User</label>
            <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by name or email">
            <button type="submit" class="btn">Search</button>
        </form>
    </div>

    <div class="admin-summary-grid">
        <div class="admin-summary-card">
            <span class="admin-summary-label">Total Users</span>
            <span class="admin-summary-number"><?= $total_users ?></span>
        </div>
        <div class="admin-summary-card">
            <span class="admin-summary-label">Total Events</span>
            <span class="admin-summary-number"><?= $total_events ?></span>
        </div>
        <div class="admin-summary-card">
            <span class="admin-summary-label">Total Clubs</span>
            <span class="admin-summary-number"><?= $total_clubs ?></span>
        </div>
        <div class="admin-summary-card">
            <span class="admin-summary-label">Total Merit Records</span>
            <span class="admin-summary-number"><?= $total_merits ?></span>
        </div>
        <div class="admin-summary-card">
            <span class="admin-summary-label">Total Achievements</span>
            <span class="admin-summary-number"><?= $total_achievements ?></span>
        </div>
    </div>

    <?php if (empty($users)): ?>
        <div class="empty-admin-state">
            <h3>No users found</h3>
            <p>No user records matched your search.</p>
        </div>
    <?php else: ?>
        <div class="admin-user-grid">
            <?php foreach ($users as $u): ?>
                <div class="admin-user-card">
                    <div class="admin-user-top">
                        <div>
                            <h3 class="admin-user-name"><?= htmlspecialchars($u['full_name']) ?></h3>
                            <p class="admin-user-email"><?= htmlspecialchars($u['email']) ?></p>
                        </div>
                        <span class="admin-role-badge"><?= htmlspecialchars($u['role']) ?></span>
                    </div>

                    <div class="admin-user-stats">
                        <div class="admin-stat-box">
                            <span class="admin-stat-label">Events</span>
                            <span class="admin-stat-value"><?= (int)$u['total_events'] ?></span>
                        </div>
                        <div class="admin-stat-box">
                            <span class="admin-stat-label">Clubs</span>
                            <span class="admin-stat-value"><?= (int)$u['total_clubs'] ?></span>
                        </div>
                        <div class="admin-stat-box">
                            <span class="admin-stat-label">Merits</span>
                            <span class="admin-stat-value"><?= (int)$u['total_merits'] ?></span>
                        </div>
                        <div class="admin-stat-box">
                            <span class="admin-stat-label">Achievements</span>
                            <span class="admin-stat-value"><?= (int)$u['total_achievements'] ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
    .admin-hero {
        margin-bottom: 28px;
    }

    .admin-toolbar {
        margin-bottom: 24px;
    }

    .admin-search-form {
        display: flex;
        gap: 12px;
        align-items: end;
        flex-wrap: wrap;
    }

    .admin-search-form label {
        margin: 0;
        color: var(--text-soft);
        font-weight: 600;
    }

    .admin-search-form input {
        min-width: 260px;
    }

    .admin-summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 18px;
        margin-bottom: 28px;
    }

    .admin-summary-card {
        padding: 20px 22px;
        border-radius: 20px;
        background: linear-gradient(135deg, rgba(255,255,255,0.82), rgba(244,235,223,0.90));
        border: 1px solid var(--border);
        box-shadow: var(--shadow-soft);
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .admin-summary-label {
        color: var(--text-soft);
        font-size: 0.92rem;
        font-weight: 600;
    }

    .admin-summary-number {
        font-size: 2rem;
        font-weight: 700;
        color: var(--primary-dark);
    }

    .admin-user-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 22px;
    }

    .admin-user-card {
        padding: 24px;
        border-radius: 24px;
        background: linear-gradient(180deg, rgba(255,255,255,0.92), rgba(243,233,220,0.95));
        border: 1px solid var(--border);
        box-shadow: var(--shadow-soft);
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .admin-user-top {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: flex-start;
        flex-wrap: wrap;
    }

    .admin-user-name {
        margin: 0;
        color: var(--primary-dark);
    }

    .admin-user-email {
        margin: 4px 0 0;
        color: var(--text-soft);
    }

    .admin-role-badge {
        display: inline-block;
        padding: 7px 12px;
        border-radius: 999px;
        background: linear-gradient(135deg, #efe2cd, #f8f2e8);
        color: var(--primary-dark);
        border: 1px solid var(--border);
        font-size: 0.76rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .admin-user-stats {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }

    .admin-stat-box {
        padding: 14px 16px;
        border-radius: 16px;
        background: rgba(255,255,255,0.55);
        border: 1px solid var(--border);
    }

    .admin-stat-label {
        display: block;
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--text-faint);
        text-transform: uppercase;
        margin-bottom: 6px;
    }

    .admin-stat-value {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--text-main);
    }

    .empty-admin-state {
        padding: 48px 28px;
        text-align: center;
        border-radius: 24px;
        background: linear-gradient(180deg, rgba(255,255,255,0.84), rgba(244,236,224,0.90));
        border: 1px solid var(--border);
        box-shadow: var(--shadow-soft);
    }

    @media (max-width: 700px) {
        .admin-user-stats {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php include '../includes/footer.php'; ?>