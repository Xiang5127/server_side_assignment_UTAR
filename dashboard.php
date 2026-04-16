<?php
require_once 'includes/auth_check.php';
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/nav.php'; ?>

<div class="container">
    <div class="page-header">
        <div>
            <h1>Welcome Back</h1>
            <p class="page-subtitle">
                Hello, <strong><?= htmlspecialchars($_SESSION['full_name']) ?></strong>. Select a module to continue managing your co-curricular portfolio.
            </p>
        </div>
    </div>

    <div class="card-grid">
        <div class="card">
            <h3>Event Tracker</h3>
            <p>Record workshops, talks, competitions, and formal university programmes with clarity and structure.</p>
            <a class="btn" href="/Assignment/event_module/event_index.php">View Module</a>
        </div>

        <div class="card">
            <h3>Club Tracker</h3>
            <p>Maintain your memberships, society involvement, and leadership roles in one organized space.</p>
            <a class="btn" href="/Assignment/club_module/clubs.php">View Module</a>
        </div>

        <div class="card">
            <h3>Merit Tracker</h3>
            <p>Monitor contribution hours from volunteering, service, and co-curricular activities with ease.</p>
            <a class="btn" href="/Assignment/merit_module/merits.php">View Module</a>
        </div>

        <div class="card">
            <h3>Achievement Tracker</h3>
            <p>Preserve your awards, certificates, medals, trophies, and recognitions for future reference.</p>
            <a class="btn" href="/Assignment/achievement_module/achievements.php">View Module</a>
        </div>

        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <div class="card">
                <h3>Admin Module</h3>
                <p>View all registered users and basic usage summaries across all modules.</p>
                <a class="btn" href="/Assignment/admin_module/admin_dashboard.php">View Module</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>