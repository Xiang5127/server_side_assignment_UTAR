<nav class="navbar">
    <h2>Co-curricular Portal</h2>

    <ul>
        <?php if (isset($_SESSION['user_id'])): ?>
            <li><a href="/Assignment/dashboard.php">Dashboard</a></li>
            <li><a href="/Assignment/event_module/event_index.php">Events</a></li>
            <li><a href="/Assignment/club_module/clubs.php">Clubs</a></li>
            <li><a href="/Assignment/merit_module/merits.php">Merits</a></li>
            <li><a href="/Assignment/achievement_module/achievements.php">Achievements</a></li>

            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <li><a href="/Assignment/admin_module/admin_dashboard.php">Admin</a></li>
            <?php endif; ?>

            <li><a href="/Assignment/logout.php">Logout</a></li>
        <?php else: ?>
            <li><a href="/Assignment/index.php">Home</a></li>
            <li><a href="/Assignment/login.php">Login</a></li>
            <li><a href="/Assignment/register.php">Register</a></li>
        <?php endif; ?>
    </ul>
</nav>