<?php
$page_title = "Login";
include_once '../partials/header.php';

if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}
?>

<div class="auth-page-container">
    <div class="auth-form-wrapper">
        <h2>Login Akun</h2>

        <?php
        if (isset($_GET['error'])) {
            echo '<p class="alert alert-danger">' . htmlspecialchars($_GET['error']) . '</p>';
        }
        if (isset($_GET['success'])) {
            echo '<p class="alert alert-success">' . htmlspecialchars($_GET['success']) . '</p>';
        }
        ?>

        <form action="process_login.php" method="POST" class="validate-form">
            <div class="form-group">
                <label for="username_email">Username atau Email:</label>
                <input type="text" id="username_email" name="username_email" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
        <p class="auth-link">Belum punya akun? <a href="register.php">Register di sini</a></p>
    </div>
</div>

<?php include_once '../partials/footer.php'; ?>