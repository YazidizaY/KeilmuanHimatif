<?php
$page_title = "Permintaan Tutor Tertunda";
$current_page = 'admin_pending_requests';
include_once '../partials/header.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'ADMIN') {
    header("Location: " . ($base_path ?? '') . "/auth/login.php?error=Unauthorized");
    exit();
}
?>

<?php
if (isset($_GET['message'])) { echo '<p class="alert alert-success">' . htmlspecialchars($_GET['message']) . '</p>'; }
if (isset($_GET['error'])) { echo '<p class="alert alert-danger">' . htmlspecialchars($_GET['error']) . '</p>'; }
?>

<div class="admin-requests-list">
    <?php
    $stmt_pending = $conn->prepare(
        "SELECT s.session_id, u.fullname AS student_name, u.npm, mk.course_name, s.material, s.session_date, s.session_time
         FROM sesi_tutor s
         JOIN users u ON s.requested_by_student_id = u.user_id
         JOIN mata_kuliah mk ON s.course_id = mk.course_id
         WHERE s.status = 'PENDING' ORDER BY s.session_id DESC"
    );
    $stmt_pending->execute();
    $result_pending = $stmt_pending->get_result();

    if ($result_pending && $result_pending->num_rows > 0) {
        while ($request = $result_pending->fetch_assoc()) {
    ?>
            <div class="admin-request-card">
                <div class="card-header">
                    <h4 class="student-info"><?php echo htmlspecialchars($request['student_name']); ?></h4>
                    <span class="student-npm">NPM: <?php echo htmlspecialchars($request['npm']); ?></span>
                </div>
                <div class="card-body">
                    <p><strong>Mata Kuliah:</strong> <?php echo htmlspecialchars($request['course_name']); ?></p>
                    <p><strong>Materi Diajukan:</strong> <?php echo nl2br(htmlspecialchars($request['material'])); ?></p>
                    <p><strong>Jadwal Diajukan:</strong> <?php echo htmlspecialchars(date('d F Y', strtotime($request['session_date']))); ?> pukul <?php echo htmlspecialchars(date('H:i', strtotime($request['session_time']))); ?> WIB</p>
                </div>
                <div class="card-actions">
                    <form action="process_session_approval.php" method="POST" style="margin:0;">
                        <input type="hidden" name="session_id" value="<?php echo $request['session_id']; ?>">
                        <input type="hidden" name="action" value="approve">
                        <button type="submit" class="btn btn-approve">Setujui</button>
                    </form>
                    <form action="process_session_approval.php" method="POST" style="margin:0;">
                        <input type="hidden" name="session_id" value="<?php echo $request['session_id']; ?>">
                        <input type="hidden" name="action" value="reject">
                        <button type="submit" class="btn btn-reject">Tolak</button>
                    </form>
                    <a href="edit_session.php?session_id=<?php echo $request['session_id']; ?>" class="btn btn-edit-details">Ubah Detail</a>
                </div>
            </div>
    <?php
        }
    } else {
    ?>
        </div>
        <div class="no-admin-requests-message">
            <p>Tidak ada permintaan sesi tutor yang tertunda saat ini.</p>
        </div>
    <?php
    }
    if (isset($stmt_pending)) $stmt_pending->close();

    // Pastikan .admin-requests-list ditutup jika ada permintaan
    if ($result_pending && $result_pending->num_rows > 0) {
        // echo '</div>'; // Sudah ditutup di atas sebelum blok else
    }
    ?>
<?php if (!($result_pending && $result_pending->num_rows > 0)) echo '</div>';?>


<?php
$conn->close();
include_once '../partials/footer.php';
?>
