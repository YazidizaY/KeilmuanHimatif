<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($base_path)) { 
    $base_path = "/" . basename(dirname(dirname(__FILE__))); 
    if ($base_path == "/your_project_root_folder_name_if_any" || $base_path == "/..") { 
        $base_path = ""; 
    }
}


$page_title_display = isset($page_title) ? htmlspecialchars($page_title) : "Keilmuan Himatif";
$current_page_active = $current_page ?? '';

$user_profile_pic_url = $base_path . "/assets/avatar_default.png"; 
if (isset($_SESSION['user_id']) && isset($_SESSION['profile_picture_path']) && !empty($_SESSION['profile_picture_path'])) {
    $user_profile_pic_url = $base_path . "/" . htmlspecialchars($_SESSION['profile_picture_path']);
} elseif (isset($_SESSION['user_id'])) {
    // Jika user login tapi tidak ada path di session, coba ambil dari DB (opsional, tergantung kapan Anda set session)
    // Untuk sekarang, kita asumsikan path sudah ada di session saat login.
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title_display; ?></title>
    <link rel="stylesheet" href="<?php echo $base_path; ?>/css/style.css">
</head>
<body>
    <div class="gemini-layout">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3 style="background-image: linear-gradient(140deg, #ffaa00, #fff958); color: transparent; -webkit-background-clip: text; background-clip: text;">Keilmuan Himatif</h3>
                <?php if (isset($_SESSION['user_id'])): ?>
                <div class="user-profile-sidebar">
                    <img src="<?php echo $user_profile_pic_url; ?>" 
                         alt="Avatar <?php echo htmlspecialchars($_SESSION['username']); ?>" 
                         class="avatar-sidebar"
                         onerror="this.onerror=null; this.src='<?php echo $base_path; ?>/assets/avatar_default.png';">
                    <span><?php echo htmlspecialchars($_SESSION['username']); ?> (<?php echo htmlspecialchars($_SESSION['role']);?>)</span>
                </div>
                <?php endif; ?>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($_SESSION['role'] == 'STUDENT'): ?>
                            <li><a href="<?php echo $base_path; ?>/student/index.php" class="<?php echo $current_page_active == 'student_dashboard' ? 'active' : ''; ?>"><span class="nav-icon">🏠</span> Dashboard</a></li>
                            <li><a href="<?php echo $base_path; ?>/student/request_tutor_page.php" class="<?php echo $current_page_active == 'student_request_tutor' ? 'active' : ''; ?>"><span class="nav-icon">➕</span> Request Tutor</a></li>
                            <li><a href="<?php echo $base_path; ?>/student/my_sessions.php" class="<?php echo $current_page_active == 'student_my_sessions' ? 'active' : ''; ?>"><span class="nav-icon">📅</span> Sesi Saya</a></li>
                        <?php elseif ($_SESSION['role'] == 'ADMIN'): ?>
                            <li><a href="<?php echo $base_path; ?>/admin/index.php" class="<?php echo $current_page_active == 'admin_dashboard' ? 'active' : ''; ?>"><span class="nav-icon">📊</span> Dashboard Admin</a></li>
                            <li><a href="<?php echo $base_path; ?>/admin/my_teaching_sessions.php" class="<?php echo $current_page_active == 'admin_my_teaching' ? 'active' : ''; ?>"><span class="nav-icon">👨‍🏫</span> Sesi Mengajar Saya</a></li>
                            <li><a href="<?php echo $base_path; ?>/admin/pending_requests.php" class="<?php echo $current_page_active == 'admin_pending_requests' ? 'active' : ''; ?>"><span class="nav-icon">🔔</span> Permintaan Tutor</a></li>
                            <li><a href="<?php echo $base_path; ?>/admin/approved_sessions.php" class="<?php echo $current_page_active == 'admin_approved' ? 'active' : ''; ?>"><span class="nav-icon">✅</span> Semua Sesi Disetujui</a></li>
                            <li class="nav-section-title">Manajemen Data</li>
                            <li><a href="<?php echo $base_path; ?>/admin/users.php" class="<?php echo $current_page_active == 'admin_users' ? 'active' : ''; ?>"><span class="nav-icon">👥</span> Kelola Pengguna</a></li>
                            <li><a href="<?php echo $base_path; ?>/admin/courses.php" class="<?php echo $current_page_active == 'admin_courses' ? 'active' : ''; ?>"><span class="nav-icon">📚</span> Kelola Mata Kuliah</a></li>
                        <?php endif; ?>
                    <?php else: ?>
                        <li><a href="<?php echo $base_path; ?>/auth/login.php" class="<?php echo $current_page_active == 'login' ? 'active' : ''; ?>"><span class="nav-icon">🔑</span> Login</a></li>
                        <li><a href="<?php echo $base_path; ?>/auth/register.php" class="<?php echo $current_page_active == 'register' ? 'active' : ''; ?>"><span class="nav-icon">📝</span> Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php if (isset($_SESSION['user_id'])): ?>
            <div class="sidebar-footer">
                <a href="<?php echo $base_path; ?>/auth/logout.php"><span class="nav-icon">🚪</span> Logout</a>
            </div>
            <?php endif; ?>
        </aside>

        <div class="main-content-wrapper">
            <header class="main-header-top">
                <h2><?php echo $page_title_display; ?></h2>
            </header>
            <main class="content-area">
