<?php
session_start();
require_once 'config/db.php';

$full_name = "";
$email = "";
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST["full_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = trim($_POST["password"] ?? "");
    $confirm_password = trim($_POST["confirm_password"] ?? "");

    if (empty($full_name)) {
        $errors[] = "Full name is required.";
    }

    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");

        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $errors[] = "Email already registered.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $insert = $conn->prepare("INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)");
                if ($insert) {
                    $insert->bind_param("sss", $full_name, $email, $hashed_password);

                    if ($insert->execute()) {
                        $_SESSION['success'] = "Registration successful. Please login.";
                        $insert->close();
                        $stmt->close();
                        header("Location: /Assignment/login.php");
                        exit();
                    } else {
                        $errors[] = "Unable to register account.";
                    }

                    $insert->close();
                } else {
                    $errors[] = "Database error: Unable to prepare registration query.";
                }
            }

            $stmt->close();
        } else {
            $errors[] = "Database error: Unable to prepare email check query.";
        }
    }
}
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/nav.php'; ?>

<div class="container">
    <div class="split-auth">
        <div class="auth-showcase">
            <span class="mini-label">Create Account</span>
            <h1>Begin your portfolio</h1>
            <p>
                Register your account to manage events, clubs, merit hours, and achievements
                in one polished and centralized system.
            </p>
        </div>

        <div class="auth-panel">
            <h2>Register</h2>

            <?php if (!empty($errors)): ?>
                <div class="error-box">
                    <?php foreach ($errors as $error): ?>
                        <p><?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($full_name) ?>" required>

                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>

                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>

                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>

                <div class="form-actions">
                    <button type="submit">Create Account</button>
                    <a href="/Assignment/login.php" class="btn-secondary">Back to Login</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>