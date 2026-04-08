<?php
// 1. Authentication & Setup
require_once '../includes/auth_check.php';

// db_conn.php that establishes $conn
require_once '../config/db_conn.php';

$user_id = $_SESSION['user_id'];

// 2. Fetch Summary Stats (Total Events)
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM events WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_events = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// 3. Fetch Event Records
// Capture GET variables or set defaults
$filter_loc = $_GET['filter_loc'] ?? 'all';
$sort_by    = $_GET['sort_by'] ?? 'date_desc';
// Determine the SQL ORDER BY clause securely
$order_sql = "ORDER BY event_date DESC"; // Default
if ($sort_by === 'date_asc') {
    $order_sql = "ORDER BY event_date ASC";
} elseif ($sort_by === 'name_asc') {
    $order_sql = "ORDER BY event_name ASC";
}
// Build the Prepared Statement dynamically
if ($filter_loc !== 'all') {
    // If filtering by location type, we need TWO parameters (user_id and location_type)
    $query = "SELECT event_id, event_name, organiser, event_date, location FROM events WHERE user_id = ? AND location_type = ? " . $order_sql;
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $user_id, $filter_loc);
} else {
    // If 'all', we only check the user_id
    $query = "SELECT event_id, event_name, organiser, event_date, location FROM events WHERE user_id = ? " . $order_sql;
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$events_result = $stmt->get_result();

// Count for the list rendering
$list_count = $events_result->num_rows;
$stmt->close();

// 4. Load Header and Nav
include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="container">

    <!-- Display status messages based on 'status' parameter -->
    <!-- from other pages: event_add.php, event_edit.php, event_delete.php -->
    <?php if (isset($_GET['status'])) { ?>
        <?php if ($_GET['status'] == 'added') { ?>
            <div class="success-box">
                ✅ New event successfully recorded!
                <span class="close-btn" onclick="this.parentElement.style.display='none';">&times;</span>
            </div>
        <?php } elseif ($_GET['status'] == 'updated') { ?>
            <div class="success-box">
                ✅ Event details successfully updated.
                <span class="close-btn" onclick="this.parentElement.style.display='none';">&times;</span>
            </div>
        <?php } elseif ($_GET['status'] == 'deleted') { ?>
            <div class="success-box">
                ✅ Event record successfully deleted.
                <span class="close-btn" onclick="this.parentElement.style.display='none';">&times;</span>
            </div>
        <?php } elseif ($_GET['status'] == 'error') { ?>
            <div class="error-box">
                ❌ An error occurred while processing your request. Please try again.
                <span class="close-btn" onclick="this.parentElement.style.display='none';">&times;</span>
            </div>
        <?php } ?>
    <?php } ?>

    <div class="hero-box" style="display: flex; justify-content: space-between; align-items: center; padding: 24px;">
        <div>
            <h1>My Event Tracker</h1>
            <p style="margin-bottom: 0;">Manage and track your co-curricular programmes.</p>
        </div>
        <a href="event_add.php" class="btn">+ Record New Event</a>
    </div>

    <div class="card-grid">
        <div class="card">
            <h3>Total Participations</h3>
            <p style="font-size: 2.2rem; font-weight: bold; color: var(--primary-dark); margin: 0;">
                <!-- php short open tag, same as echo -->
                <?= $total_events ?>
            </p>
        </div>
    </div>

    <?php
    if ($total_events > 0) { ?>
        <table>
            <thead>
                <tr>
                    <th colspan="5" style="text-align: right; padding-bottom: 12px; padding-right: 22px;">
                        <form method="GET" action="event_index.php"
                            style="display: inline-flex; gap: 16px; align-items: center; margin: 0;">

                            <div style="display: inline-flex; align-items: center; gap: 6px;">
                                <label for="filter_loc" title="Filter Locations"
                                    style="margin: 0; display: flex; align-items: center; color: var(--text-soft); cursor: pointer;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                                    </svg>
                                </label>
                                <select name="filter_loc" id="filter_loc" onchange="this.form.submit()"
                                    style="padding: 4px 8px; border-radius: 4px; border: 1px solid var(--border); font-size: 0.85rem; outline: none;">
                                    <option value="all" <?= (isset($_GET['filter_loc']) && $_GET['filter_loc'] == 'all') ? 'selected' : '' ?>>All Locations</option>
                                    <option value="online" <?= (isset($_GET['filter_loc']) && $_GET['filter_loc'] == 'online') ? 'selected' : '' ?>>Online</option>
                                    <option value="campus" <?= (isset($_GET['filter_loc']) && $_GET['filter_loc'] == 'campus') ? 'selected' : '' ?>>In Campus</option>
                                    <option value="other" <?= (isset($_GET['filter_loc']) && $_GET['filter_loc'] == 'other') ? 'selected' : '' ?>>Other</option>
                                </select>
                            </div>

                            <div style="display: inline-flex; align-items: center; gap: 6px;">
                                <label for="sort_by" title="Sort Events"
                                    style="margin: 0; display: flex; align-items: center; color: var(--text-soft); cursor: pointer;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <line x1="4" y1="6" x2="16" y2="6"></line>
                                        <line x1="8" y1="12" x2="20" y2="12"></line>
                                        <line x1="4" y1="18" x2="16" y2="18"></line>
                                    </svg>
                                </label>
                                <select name="sort_by" id="sort_by" onchange="this.form.submit()"
                                    style="padding: 4px 8px; border-radius: 4px; border: 1px solid var(--border); font-size: 0.85rem; outline: none;">
                                    <option value="date_desc" <?= (!isset($_GET['sort_by']) || $_GET['sort_by'] == 'date_desc') ? 'selected' : '' ?>>Newest First</option>
                                    <option value="date_asc" <?= (isset($_GET['sort_by']) && $_GET['sort_by'] == 'date_asc') ? 'selected' : '' ?>>Oldest First</option>
                                    <option value="name_asc" <?= (isset($_GET['sort_by']) && $_GET['sort_by'] == 'name_asc') ? 'selected' : '' ?>>Name (A-Z)</option>
                                </select>
                            </div>

                        </form>
                    </th>
                </tr>
                <tr>
                    <th>Date</th>
                    <th>Event Name</th>
                    <th>Organiser</th>
                    <th>Location</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $events_result->fetch_assoc()) { ?>
                    <tr>
                        <td><?= htmlspecialchars($row['event_date']) ?></td>
                        <td style="font-weight: 600; color: var(--primary-dark);">
                            <?= htmlspecialchars($row['event_name']) ?>
                        </td>
                        <td><?= htmlspecialchars($row['organiser']) ?></td>
                        <td><?= htmlspecialchars($row['location']) ?></td>
                        <td class="action-links">
                            <a href="event_edit.php?id=<?= $row['event_id'] ?>">Edit</a>
                            <a href="event_delete.php?id=<?= $row['event_id'] ?>"
                                onclick="return confirm('Are you sure you want to permanently delete this record?');"
                                style="color: var(--error-text);">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } else { ?>
        <div class="card" style="text-align: center; margin-top: 24px; padding: 40px;">
            <h3 style="color: var(--text-soft);">No events found</h3>
            <p>You haven't recorded any programmes or events yet.</p>
            <a href="event_add.php" class="btn">Record your first event</a>
        </div>
    <?php }
    ?>
</div>

<style>
    .success-box,
    .error-box {
        position: relative;
        padding-right: 40px;
        /* Make sure text doesn't overlap the close button */
    }

    /* Add the new close button styles */
    .close-btn {
        position: absolute;
        top: 50%;
        right: 15px;
        transform: translateY(-50%);
        /* Perfectly centers it vertically */
        font-size: 1.4rem;
        font-weight: bold;
        cursor: pointer;
        opacity: 0.5;
        transition: opacity 0.2s ease;
        line-height: 1;
    }

    .close-btn:hover {
        opacity: 1;
        /* Darkens the cross when hovering */
    }
</style>

<?php
// 5. Load Footer
include '../includes/footer.php';
?>