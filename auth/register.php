<?php
$page_title = "Register";
$current_page = 'register'; 

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['user_id'])) {
    $base_path_redirect = "/" . basename(dirname(dirname(__FILE__)));
    if ($base_path_redirect == "/your_project_root" || $base_path_redirect == "/..") $base_path_redirect = "";
    header("Location: " . ($base_path_redirect ?? '') . "/index.php");
    exit();
}

if (empty($GLOBALS['header_included'])) {
    if (!isset($base_path)) { 
        $base_path = "/" . basename(dirname(dirname(__FILE__)));
        if ($base_path == "/your_project_root" || $base_path == "/..") $base_path = "";
    }
    include_once dirname(__DIR__) . '/partials/header.php';
    $GLOBALS['header_included'] = true; 
}
?>

<div class="auth-page-container">
    <div class="auth-form-wrapper">
        <h2>Registrasi Akun Mahasiswa</h2>

        <?php
        if (isset($_GET['error'])) {
            echo '<p class="alert alert-danger">' . htmlspecialchars($_GET['error']) . '</p>';
        }
        if (isset($_GET['success'])) {
            echo '<p class="alert alert-success">' . htmlspecialchars($_GET['success']) . '</p>';
        }
        ?>

        <form action="process_register.php" method="POST" class="validate-form">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required minlength="6">
            </div>
            <div class="form-group">
                <label for="npm">NPM:</label>
                <input type="text" id="npm" name="npm" required pattern="\d{10,12}">
            </div>
            <div class="form-group">
                <label for="fullname">Nama Lengkap:</label>
                <input type="text" id="fullname" name="fullname" required>
            </div>
            <div class="form-group">
                <label for="birth_date">Tanggal Lahir:</label>
                <input type="date" id="birth_date" name="birth_date" required>
            </div>
            <button type="submit" class="btn">Register</button>
        </form>
        <p class="auth-link">Sudah punya akun? <a href="login.php">Login di sini</a></p>
    </div>
</div>
<?php
    if (!empty($GLOBALS['header_included']) && empty($GLOBALS['footer_included'])) {
        include_once dirname(__DIR__) . '/partials/footer.php';
        $GLOBALS['footer_included'] = true;
    }
?>
