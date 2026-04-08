<nav class="navbar">
    <h2>Co-curricular Portal</h2>
    <ul>
        <?php if (isset($_SESSION['user_id'])): ?>
            <li><a href="/ASSIGNMENT/dashboard.php">Dashboard</a></li>
            <li><a href="/ASSIGNMENT/event_module/event_index.php">Events</a></li>
            <li><a href="/ASSIGNMENT/clubs_module/clubs.php">Clubs</a></li>
            <li><a href="/ASSIGNMENT/merits_module/merits.php">Merits</a></li>
            <li><a href="/ASSIGNMENT/achievement_module/achievements.php">Achievements</a></li>
            <li><a href="/ASSIGNMENT/logout.php">Logout</a></li>
        <?php else: ?>
            <li><a href="/ASSIGNMENT/index.php">Home</a></li>
            <li><a href="/ASSIGNMENT/login.php">Login</a></li>
            <li><a href="/ASSIGNMENT/register.php">Register</a></li>
        <?php endif; ?>
    </ul>
</nav>