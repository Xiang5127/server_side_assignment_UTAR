<?php
session_start();
require_once 'config/db.php';

$email = "";
$errors = [];

if (isset($_COOKIE['remember_email'])) {
    $email = $_COOKIE['remember_email'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = trim($_POST["password"] ?? "");
    $remember = isset($_POST["remember"]);

    if (empty($email) || empty($password)) {
        $errors[] = "Email and password are required.";
    } else {
        $stmt = $conn->prepare("SELECT user_id, full_name, email, password, role FROM users WHERE email = ?");

        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                if ($remember) {
                    setcookie("remember_email", $email, time() + (86400 * 30), "/");
                } else {
                    setcookie("remember_email", "", time() - 3600, "/");
                }

                header("Location: /Assignment/dashboard.php");
                exit();
            } else {
                $errors[] = "Invalid email or password.";
            }
        } else {
            $errors[] = "Database error: Unable to prepare login query.";
        }
    }
}
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/nav.php'; ?>

<div class="container">
    <div class="split-auth">
        <div class="auth-showcase">
            <span class="mini-label">Welcome Back</span>
            <h1>Sign in to your portal</h1>
            <p>
                Access your personal co-curricular records, manage your achievements,
                and continue building your student portfolio with ease.
            </p>
        </div>

        <div class="auth-panel">
            <h2>Login</h2>

            <?php
            if (isset($_SESSION['success'])) {
                echo "<div class='success-box'><p>" . htmlspecialchars($_SESSION['success']) . "</p></div>";
                unset($_SESSION['success']);
            }
            ?>

            <?php if (!empty($errors)): ?>
                <div class="error-box">
                    <?php foreach ($errors as $error): ?>
                        <p><?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>

                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>

                <label style="display: flex; align-items: center; gap: 10px; font-weight: 500; margin-top: 14px;">
                    <input type="checkbox" name="remember" style="width: auto;">
                    Remember Email
                </label>

                <div class="form-actions">
                    <button type="submit">Sign In</button>
                    <a href="/Assignment/register.php" class="btn-secondary">Create Account</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>