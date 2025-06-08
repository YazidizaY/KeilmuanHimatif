<?php
$page_title = "Dashboard Admin";
$current_page = 'admin_dashboard';
include_once '../partials/header.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'ADMIN') {
    header("Location: " . ($base_path ?? '') . "/auth/login.php?error=Unauthorized");
    exit();
}
$admin_fullname = $_SESSION['fullname'] ?? $_SESSION['username'];

$stmt_pending_count = $conn->query("SELECT COUNT(*) AS total FROM sesi_tutor WHERE status = 'PENDING'");
$pending_count = ($stmt_pending_count) ? $stmt_pending_count->fetch_assoc()['total'] : 0;

$stmt_users_count = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'STUDENT'");
$student_count = ($stmt_users_count) ? $stmt_users_count->fetch_assoc()['total'] : 0;
?>

<div class="welcome-section">
    <h1 class="greeting-text">Halo, <span class="user-name">Admin</span>!</h1>
    <p class="tagline">Selamat datang di panel administrasi Sistem Tutor.</p>
    <div class="suggestion-chips">
        <a href="<?php echo ($base_path ?? '') ?>/admin/pending_requests.php" class="chip-button"><span class="chip-icon">🔔</span> Permintaan Baru (<?php echo $pending_count; ?>)</a>
        <a href="<?php echo ($base_path ?? '') ?>/admin/approved_sessions.php" class="chip-button"><span class="chip-icon">✅</span> Sesi Disetujui</a>
        <a href="<?php echo ($base_path ?? '') ?>/admin/users.php" class="chip-button"><span class="chip-icon">👥</span> Kelola Pengguna (<?php echo $student_count; ?> Mahasiswa)</a>
        <a href="<?php echo ($base_path ?? '') ?>/admin/courses.php" class="chip-button"><span class="chip-icon">📚</span> Kelola Mata Kuliah</a>
    </div>
</div>

<?php
if (isset($stmt_pending_count) && $stmt_pending_count) $stmt_pending_count->close();
if (isset($stmt_users_count) && $stmt_users_count) $stmt_users_count->close();
$conn->close();
include_once '../partials/footer.php';
?>