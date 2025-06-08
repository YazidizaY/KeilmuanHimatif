<?php
$page_title = "Sesi Tutor Disetujui";
$current_admin_page = 'approved';
include_once '../partials/header.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'ADMIN') {
    header("Location: ../auth/login.php?error=Unauthorized");
    exit();
}
?>

<h2>Daftar Sesi Tutor yang Telah Disetujui</h2>

<?php
if (isset($_GET['message'])) {
    echo '<p class="alert alert-success">' . htmlspecialchars($_GET['message']) . '</p>';
}
if (isset($_GET['error'])) {
    echo '<p class="alert alert-danger">' . htmlspecialchars($_GET['error']) . '</p>';
}

$stmt = $conn->prepare(
    "SELECT s.session_id, u_student.fullname AS student_name, 
            mk.course_name, s.material, u_tutor.fullname AS tutor_name, 
            s.session_date, s.session_time, s.status
     FROM sesi_tutor s
     JOIN users u_student ON s.requested_by_student_id = u_student.user_id
     JOIN mata_kuliah mk ON s.course_id = mk.course_id
     LEFT JOIN users u_tutor ON s.tutor_id = u_tutor.user_id
     WHERE s.status = 'ACCEPTED' 
     ORDER BY s.session_date DESC, s.session_time DESC"
);
$stmt->execute();
$result_sessions = $stmt->get_result();
?>

<table class="admin-table">
    <thead>
        <tr>
            <th>ID Sesi</th>
            <th>Mahasiswa</th>
            <th>Mata Kuliah</th>
            <th>Tutor</th>
            <th>Tanggal</th>
            <th>Waktu</th>
            <th>Materi</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result_sessions && $result_sessions->num_rows > 0): ?>
            <?php while($session = $result_sessions->fetch_assoc()): ?>
            <tr>
                <td><?php echo $session['session_id']; ?></td>
                <td><?php echo htmlspecialchars($session['student_name']); ?></td>
                <td><?php echo htmlspecialchars($session['course_name']); ?></td>
                <td><?php echo htmlspecialchars($session['tutor_name'] ?? 'N/A'); ?></td>
                <td><?php echo htmlspecialchars(date('d M Y', strtotime($session['session_date']))); ?></td>
                <td><?php echo htmlspecialchars(date('H:i', strtotime($session['session_time']))); ?></td>
                <td><?php echo nl2br(htmlspecialchars(substr($session['material'], 0, 50))) . (strlen($session['material']) > 50 ? '...' : ''); ?></td>
                <td>
                    <div class="action-buttons">
                        <a href="edit_session.php?session_id=<?php echo $session['session_id']; ?>" class="btn-small btn-edit">Ubah</a>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="8">Tidak ada sesi yang disetujui saat ini.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php
$stmt->close();
$conn->close();
include_once '../partials/footer.php';
?>