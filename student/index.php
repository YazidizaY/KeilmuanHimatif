<?php
$page_title = "Dashboard Mahasiswa";
$current_page = 'student_dashboard';
include_once '../partials/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'STUDENT') {
    header("Location: " . ($base_path ?? '') . "/auth/login.php?error=Unauthorized");
    exit();
}
$student_fullname = $_SESSION['fullname'] ?? $_SESSION['username'];
$first_name = explode(' ', $student_fullname)[0];
?>

<div class="welcome-section">
    <h1 class="greeting-text">Halo, <span class="user-name"><?php echo htmlspecialchars($first_name); ?></span>!</h1>
    <p class="tagline">Selamat datang kembali! Manfaatkan fitur di bawah ini untuk membantu studimu.</p>
    <div class="suggestion-chips">
        <a href="<?php echo ($base_path ?? '') ?>/student/request_tutor_page.php" class="chip-button"><span class="chip-icon">➕</span> Ajukan Permintaan Tutor</a>
        <a href="<?php echo ($base_path ?? '') ?>/student/my_sessions.php" class="chip-button"><span class="chip-icon">📅</span> Lihat Sesi Tutor Saya</a>
    </div>
</div>