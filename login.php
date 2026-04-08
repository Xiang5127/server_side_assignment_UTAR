<?php
session_start();
require_once 'config/db.php';

$email = "";
$errors = [];

if (isset($_COOKIE['remember_email'])) {
    $email = $_COOKIE['remember_email'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $remember = isset($_POST["remember"]);

    if (empty($email) || empty($password)) {
        $errors[] = "Email and password are required.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];

            if ($remember) {
                setcookie("remember_email", $email, time() + (86400 * 30), "/");
            } else {
                setcookie("remember_email", "", time() - 3600, "/");
            }

            header("Location: dashboard.php");
            exit();
        } else {
            $errors[] = "Invalid email or password.";
        }
    }
}
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/nav.php'; ?>

<div class="container">
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
        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <label>
            <input type="checkbox" name="remember"> Remember Email
        </label>

        <button type="submit">Login</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>