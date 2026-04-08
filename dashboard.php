<?php
require_once 'includes/auth_check.php';
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/nav.php'; ?>

<div class="container">
    <h1>Welcome Back</h1>
    <p>Hello, <strong><?= htmlspecialchars($_SESSION['full_name']) ?></strong>. Please choose a module to continue.</p>

    <div class="card-grid">
        <div class="card">
            <h3>Event Tracker</h3>
            <p>Record and manage your participation in workshops, talks, competitions, and university programmes.</p>
            <a class="btn" href="event_module/event_index.php">Open Module</a>
        </div>

        <div class="card">
            <h3>Club Tracker</h3>
            <p>Keep track of your club memberships, societies, and leadership roles in one place.</p>
            <a class="btn" href="#">Open Module</a>
        </div>

        <div class="card">
            <h3>Merit Tracker</h3>
            <p>Monitor your co-curricular contribution hours from volunteering and service activities.</p>
            <a class="btn" href="#">Open Module</a>
        </div>

        <div class="card">
            <h3>Achievement Tracker</h3>
            <p>Store your awards, recognitions, and certificates for easy reference and organization.</p>
            <a class="btn" href="achievement_module/achievements.php">Open Module</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>