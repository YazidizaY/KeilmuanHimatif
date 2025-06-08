<?php
$page_title = "Ubah Detail Sesi Tutor";
include_once '../partials/header.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'ADMIN') {
    header("Location: " . ($base_path ?? '') . "/auth/login.php?error=Unauthorized");
    exit();
}

if (!isset($_GET['session_id']) || !filter_var($_GET['session_id'], FILTER_VALIDATE_INT)) {
    header("Location: " . ($base_path ?? '') . "/admin/index.php?error=ID Sesi tidak valid.");
    exit();
}
$edit_session_id = $_GET['session_id'];

$stmt_session = $conn->prepare(
    "SELECT s.*, u_student.fullname AS student_name, mk.course_name AS course_display_name
     FROM sesi_tutor s
     JOIN users u_student ON s.requested_by_student_id = u_student.user_id
     JOIN mata_kuliah mk ON s.course_id = mk.course_id
     WHERE s.session_id = ?"
);
if (!$stmt_session) {
    header("Location: " . ($base_path ?? '') . "/admin/index.php?error=Gagal mempersiapkan statement sesi.");
    exit();
}
$stmt_session->bind_param("i", $edit_session_id);
$stmt_session->execute();
$result_session = $stmt_session->get_result();
if (!($session = $result_session->fetch_assoc())) {
    header("Location: " . ($base_path ?? '') . "/admin/index.php?error=Sesi tidak ditemukan.");
    $stmt_session->close(); $conn->close(); exit();
}
$stmt_session->close();

$current_page = ($session['status'] == 'ACCEPTED') ? 'admin_approved' : 'admin_pending_requests';
if ($session['status'] == 'REJECTED' && isset($_GET['ref']) && $_GET['ref'] == 'approved') {
    $current_page = 'admin_approved';
}

$admin_users = [];
$result_admins = $conn->query("SELECT user_id, fullname FROM users WHERE role = 'ADMIN' ORDER BY fullname");
if ($result_admins) {
    while ($admin_row = $result_admins->fetch_assoc()) {
        $admin_users[] = $admin_row;
    }
    $result_admins->close();
}
?>

<div class="admin-form-page-container">
    <div class="admin-form-wrapper">
        <h2>Ubah Detail Sesi Tutor #<?php echo $edit_session_id; ?></h2>
        
        <div class="form-info">
            <p><strong>Diajukan oleh:</strong> <?php echo htmlspecialchars($session['student_name']); ?></p>
            <p><strong>Mata Kuliah:</strong> <?php echo htmlspecialchars($session['course_display_name']); ?></p>
        </div>

        <?php
        if (isset($_GET['error'])) {
            echo '<p class="alert alert-danger">' . htmlspecialchars($_GET['error']) . '</p>';
        }

        if (isset($_GET['message'])) {
            echo '<p class="alert alert-success">' . htmlspecialchars($_GET['message']) . '</p>';
        }
        ?>

        <form action="<?php echo ($base_path ?? '') ?>/admin/process_update_session.php" method="POST" class="validate-form">
            <input type="hidden" name="session_id" value="<?php echo $edit_session_id; ?>">
            <input type="hidden" name="original_status" value="<?php echo htmlspecialchars($session['status']); ?>">

            <?php if (isset($_GET['ref'])): ?>
                <input type="hidden" name="ref" value="<?php echo htmlspecialchars($_GET['ref']); ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="material">Materi yang akan dibahas:</label>
                <textarea id="material" name="material" rows="4" required><?php echo htmlspecialchars($session['material']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="session_date">Tanggal Sesi:</label>
                <input type="date" id="session_date" name="session_date" value="<?php echo htmlspecialchars($session['session_date']); ?>" required>
            </div>
            <div class="form-group">
                <label for="session_time">Waktu Sesi:</label>
                <input type="time" id="session_time" name="session_time" value="<?php echo htmlspecialchars($session['session_time']); ?>" required>
            </div>
            <div class="form-group">
                <label for="tutor_id">Tutor yang Ditugaskan:</label>
                <select id="tutor_id" name="tutor_id">
                    <option value="">-- Belum Ditugaskan --</option>
                    <?php foreach ($admin_users as $admin_opt): ?>
                    <option value="<?php echo $admin_opt['user_id']; ?>" <?php echo ($session['tutor_id'] == $admin_opt['user_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($admin_opt['fullname']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <small style="color:#9aa0a6; display:block; margin-top:5px;">Kosongkan jika status diubah ke 'PENDING' atau 'REJECTED'. Wajib diisi jika status 'ACCEPTED'.</small>
            </div>
            <div class="form-group">
                <label for="status">Status Sesi:</label>
                <select id="status" name="status" required>
                    <option value="PENDING" <?php echo ($session['status'] == 'PENDING') ? 'selected' : ''; ?>>PENDING</option>
                    <option value="ACCEPTED" <?php echo ($session['status'] == 'ACCEPTED') ? 'selected' : ''; ?>>ACCEPTED</option>
                    <option value="REJECTED" <?php echo ($session['status'] == 'REJECTED') ? 'selected' : ''; ?>>REJECTED</option>
                    <option value="COMPLETED" <?php echo ($session['status'] == 'COMPLETED') ? 'selected' : ''; ?>>COMPLETED</option>
                </select>
            </div>
            
            <div class="form-actions">
                <?php 
                $cancel_link = ($base_path ?? '') . "/admin/index.php";
                if ($session['status'] == 'ACCEPTED') {
                    $cancel_link = ($base_path ?? '') . "/admin/approved_sessions.php";
                } elseif ($session['status'] == 'PENDING') {
                    $cancel_link = ($base_path ?? '') . "/admin/pending_requests.php";
                }
                
                if (isset($_GET['ref']) && $_GET['ref'] == 'approved') {
                    $cancel_link = ($base_path ?? '') . "/admin/approved_sessions.php";
                } elseif (isset($_GET['ref']) && $_GET['ref'] == 'pending') {
                     $cancel_link = ($base_path ?? '') . "/admin/pending_requests.php";
                }
                ?>
                <a href="<?php echo $cancel_link; ?>" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<?php
$conn->close();
include_once '../partials/footer.php';
?>
