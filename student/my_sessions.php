<?php
$page_title = "Sesi Tutor Saya";
$current_page = 'student_my_sessions';
include_once '../partials/header.php'; 
require_once '../config/database.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'STUDENT') {
    header("Location: " . ($base_path ?? '') . "/auth/login.php?error=Unauthorized");
    exit();
}
$student_id = $_SESSION['user_id'];
$current_datetime_db_format = date('Y-m-d H:i:s'); 

$stmt_all_sessions = $conn->prepare(
    "SELECT s.session_id, mk.course_name, s.material, u.fullname AS tutor_name, 
            s.session_date, s.session_time,
            (SELECT COUNT(*) FROM enrollment e WHERE e.user_id = ? AND e.session_id = s.session_id AND e.status = 'ACTIVE') AS is_enrolled
     FROM sesi_tutor s
     JOIN mata_kuliah mk ON s.course_id = mk.course_id
     JOIN users u ON s.tutor_id = u.user_id
     WHERE s.status = 'ACCEPTED' AND CONCAT(s.session_date, ' ', s.session_time) >= ?
     ORDER BY s.session_date ASC, s.session_time ASC"
);

if (!$stmt_all_sessions) {
    // Penanganan error jika prepare statement gagal
    // Misalnya: echo "Error preparing statement: " . $conn->error;
    // exit();
     // Untuk sekarang, kita asumsikan prepare berhasil atau ada error handling lain
} else {
    $stmt_all_sessions->bind_param("is", $student_id, $current_datetime_db_format);
    $stmt_all_sessions->execute();
    $result_all_sessions = $stmt_all_sessions->get_result();
}


$joined_sessions = [];
$available_sessions = [];

if (isset($result_all_sessions) && $result_all_sessions && $result_all_sessions->num_rows > 0) {
    while ($session = $result_all_sessions->fetch_assoc()) {
        if ($session['is_enrolled'] > 0) {
            $joined_sessions[] = $session;
        } else {
            $available_sessions[] = $session;
        }
    }
}
if(isset($stmt_all_sessions) && $stmt_all_sessions) $stmt_all_sessions->close();

?>

<?php
if (isset($_GET['message'])) { echo '<p class="alert alert-success">' . htmlspecialchars($_GET['message']) . '</p>'; }
if (isset($_GET['error'])) { echo '<p class="alert alert-danger">' . htmlspecialchars($_GET['error']) . '</p>'; }
?>

<div class="sessions-container">
    <section class="session-category">
        <h2><span class="nav-icon">⭐</span> Tutor yang Saya Ikuti</h2>
        <?php if (!empty($joined_sessions)): ?>
            <div class="my-sessions-list">
                <?php foreach ($joined_sessions as $session): ?>
                    <div class="session-card">
                        <div class="card-header">
                            <h3 class="course-title"><?php echo htmlspecialchars($session['course_name']); ?></h3>
                        </div>
                        <div class="card-body">
                            <p><strong>Materi:</strong> <?php echo nl2br(htmlspecialchars($session['material'])); ?></p>
                            <p><strong>Tutor:</strong> <?php echo htmlspecialchars($session['tutor_name']); ?></p>
                            <p><strong>Tanggal:</strong> <?php echo htmlspecialchars(date('d F Y', strtotime($session['session_date']))); ?></p>
                            <p><strong>Waktu:</strong> <?php echo htmlspecialchars(date('H:i', strtotime($session['session_time']))); ?> WIB</p>
                        </div>
                        <div class="card-footer">
                            <form action="<?php echo ($base_path ?? '') ?>/student/process_cancel_join_session.php" method="POST" style="margin:0;">
                                <input type="hidden" name="session_id" value="<?php echo $session['session_id']; ?>">
                                <button type="submit" class="btn btn-danger btn-small">Batalkan Kehadiran</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-sessions-message compact">
                <p>Anda belum mengikuti sesi tutor manapun yang akan datang.</p>
            </div>
        <?php endif; ?>
    </section>

    <section class="session-category">
        <h2><span class="nav-icon">📚</span> Sesi Tersedia Lainnya (Belum Diikuti)</h2>
        <?php if (!empty($available_sessions)): ?>
            <div class="my-sessions-list">
                <?php foreach ($available_sessions as $session): ?>
                    <div class="session-card">
                        <div class="card-header">
                            <h3 class="course-title"><?php echo htmlspecialchars($session['course_name']); ?></h3>
                        </div>
                        <div class="card-body">
                            <p><strong>Materi:</strong> <?php echo nl2br(htmlspecialchars($session['material'])); ?></p>
                            <p><strong>Tutor:</strong> <?php echo htmlspecialchars($session['tutor_name']); ?></p>
                            <p><strong>Tanggal:</strong> <?php echo htmlspecialchars(date('d F Y', strtotime($session['session_date']))); ?></p>
                            <p><strong>Waktu:</strong> <?php echo htmlspecialchars(date('H:i', strtotime($session['session_time']))); ?> WIB</p>
                        </div>
                        <div class="card-footer">
                            <form action="<?php echo ($base_path ?? '') ?>/student/process_join_session.php" method="POST" style="margin:0;">
                                <input type="hidden" name="session_id" value="<?php echo $session['session_id']; ?>">
                                <button type="submit" class="btn btn-join-session">Join Sesi</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-sessions-message compact">
                <p>Tidak ada sesi tutor tersedia lainnya yang akan datang.</p>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php if (empty($joined_sessions) && empty($available_sessions)): ?>
    <div class="no-sessions-message"> 
        <p>Tidak ada sesi tutor yang akan datang tersedia untuk Anda saat ini.</p>
        <a href="<?php echo ($base_path ?? '') ?>/student/request_tutor_page.php" class="btn btn-primary-action">Ajukan Permintaan Tutor Sekarang</a>
    </div>
<?php endif; ?>

<?php
$conn->close(); 
include_once '../partials/footer.php';
?>
