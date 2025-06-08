<?php
$page_title = "Sesi Mengajar Saya";
$current_page = 'admin_my_teaching';
include_once '../partials/header.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'ADMIN') {
    header("Location: " . ($base_path ?? '') . "/auth/login.php?error=Unauthorized");
    exit();
}
$admin_tutor_id = $_SESSION['user_id'];
$current_datetime_db_format = date('Y-m-d H:i:s');

$filter_time_status = $_GET['time_status'] ?? 'akan_datang';
$filter_semester = isset($_GET['semester']) && $_GET['semester'] !== '' ? (int)$_GET['semester'] : null;
$filter_course_id = isset($_GET['course_id']) && $_GET['course_id'] !== '' ? (int)$_GET['course_id'] : null;

$mata_kuliah_options = [];
$result_mk_options = $conn->query("SELECT course_id, course_name FROM mata_kuliah ORDER BY course_name ASC");
if ($result_mk_options) {
    while ($row_mk = $result_mk_options->fetch_assoc()) {
        $mata_kuliah_options[] = $row_mk;
    }
    $result_mk_options->close();
}

$semester_options = [];
$result_sem_options = $conn->query("SELECT DISTINCT semester FROM mata_kuliah WHERE semester IS NOT NULL ORDER BY semester ASC");
if ($result_sem_options) {
    while ($row_sem = $result_sem_options->fetch_assoc()) {
        $semester_options[] = $row_sem['semester'];
    }
    $result_sem_options->close();
}


$sql_select = "SELECT s.session_id, mk.course_name, s.material, s.session_date, s.session_time ";
$sql_from = "FROM sesi_tutor s JOIN mata_kuliah mk ON s.course_id = mk.course_id ";
$sql_where = "WHERE s.tutor_id = ? AND s.status = 'ACCEPTED' ";
$sql_order = "ORDER BY s.session_date ASC, s.session_time ASC";

$params = [$admin_tutor_id]; 
$param_types = "i"; 

if ($filter_time_status === 'sudah_lewat') {
    $sql_where .= "AND CONCAT(s.session_date, ' ', s.session_time) < ? ";
    $params[] = $current_datetime_db_format;
    $param_types .= "s";
    $sql_order = "ORDER BY s.session_date DESC, s.session_time DESC";
} elseif ($filter_time_status === 'akan_datang') {
    $sql_where .= "AND CONCAT(s.session_date, ' ', s.session_time) >= ? ";
    $params[] = $current_datetime_db_format;
    $param_types .= "s";
}

if ($filter_semester !== null) {
    $sql_where .= "AND mk.semester = ? ";
    $params[] = $filter_semester;
    $param_types .= "i";
}

if ($filter_course_id !== null) {
    $sql_where .= "AND s.course_id = ? ";
    $params[] = $filter_course_id;
    $param_types .= "i";
}

$final_sql = $sql_select . $sql_from . $sql_where . $sql_order;
$stmt_sessions = $conn->prepare($final_sql);

if (!$stmt_sessions) {
    echo "<p class='alert alert-danger'>Error preparing session statement: " . $conn->error . "</p>";
} else {
    if (!empty($params)) {
        $stmt_sessions->bind_param($param_types, ...$params);
    }
    $stmt_sessions->execute();
    $result_sessions = $stmt_sessions->get_result();
}

?>

<div class="filters-container">
    <h3><span class="nav-icon">🔍</span> Filter Sesi Mengajar</h3>
    <form action="" method="GET" class="filters-form">
        <div class="form-group">
            <label for="time_status">Status Waktu Sesi:</label>
            <select name="time_status" id="time_status">
                <option value="akan_datang" <?php echo ($filter_time_status == 'akan_datang') ? 'selected' : ''; ?>>Akan Datang</option>
                <option value="sudah_lewat" <?php echo ($filter_time_status == 'sudah_lewat') ? 'selected' : ''; ?>>Sudah Lewat</option>
                <option value="semua" <?php echo ($filter_time_status == 'semua') ? 'selected' : ''; ?>>Semua Sesi</option>
            </select>
        </div>
        <div class="form-group">
            <label for="semester">Semester:</label>
            <select name="semester" id="semester">
                <option value="">Semua Semester</option>
                <?php foreach ($semester_options as $semester): ?>
                    <option value="<?php echo $semester; ?>" <?php echo ($filter_semester == $semester) ? 'selected' : ''; ?>>
                        Semester <?php echo $semester; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="course_id">Mata Kuliah:</label>
            <select name="course_id" id="course_id">
                <option value="">Semua Mata Kuliah</option>
                <?php foreach ($mata_kuliah_options as $mk): ?>
                    <option value="<?php echo $mk['course_id']; ?>" <?php echo ($filter_course_id == $mk['course_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($mk['course_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-actions">
            <button type="submit" class="btn btn-filter">Terapkan Filter</button>
            <a href="<?php echo ($base_path ?? '') ?>/admin/my_teaching_sessions.php" class="btn btn-reset-filter">Reset Filter</a>
        </div>
    </form>
</div>


<?php
if (isset($_GET['message'])) { echo '<p class="alert alert-success">' . htmlspecialchars($_GET['message']) . '</p>'; }
if (isset($_GET['error'])) { echo '<p class="alert alert-danger">' . htmlspecialchars($_GET['error']) . '</p>'; }
?>

<div class="teaching-sessions-container">
    <?php if (isset($result_sessions) && $result_sessions && $result_sessions->num_rows > 0): ?>
        <?php while ($session = $result_sessions->fetch_assoc()): ?>
            <div class="teaching-session-card">
                <div class="card-header">
                    <h3 class="course-title"><?php echo htmlspecialchars($session['course_name']); ?></h3>
                    <span class="session-schedule">
                        <?php echo htmlspecialchars(date('d F Y', strtotime($session['session_date']))); ?> 
                        pukul <?php echo htmlspecialchars(date('H:i', strtotime($session['session_time']))); ?> WIB
                    </span>
                </div>
                <div class="card-body">
                    <p><strong>Materi:</strong> <?php echo nl2br(htmlspecialchars($session['material'])); ?></p>
                    
                    <h4>Daftar Peserta & Absensi:</h4>
                    <?php
                    $stmt_participants = $conn->prepare(
                        "SELECT u.user_id, u.fullname, u.npm, e.enrollment_id, e.attendance_status
                         FROM enrollment e
                         JOIN users u ON e.user_id = u.user_id
                         WHERE e.session_id = ? AND e.status = 'ACTIVE'
                         ORDER BY u.fullname ASC"
                    );
                    if ($stmt_participants) {
                        $stmt_participants->bind_param("i", $session['session_id']);
                        $stmt_participants->execute();
                        $result_participants = $stmt_participants->get_result();
                    
                        if ($result_participants && $result_participants->num_rows > 0): ?>
                            <form action="<?php echo ($base_path ?? '') ?>/admin/process_attendance.php" method="POST" class="attendance-form">
                                <input type="hidden" name="session_id" value="<?php echo $session['session_id']; ?>">
                                <table class="attendance-table">
                                    <thead><tr><th>Nama Mahasiswa</th><th>NPM</th><th>Kehadiran</th></tr></thead>
                                    <tbody>
                                    <?php while ($participant = $result_participants->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($participant['fullname']); ?></td>
                                            <td><?php echo htmlspecialchars($participant['npm']); ?></td>
                                            <td>
                                                <div class="attendance-options">
                                                    <label class="attendance-radio"><input type="radio" name="attendance[<?php echo $participant['enrollment_id']; ?>]" value="HADIR" <?php echo ($participant['attendance_status'] == 'HADIR') ? 'checked' : ''; ?>> Hadir</label>
                                                    <label class="attendance-radio"><input type="radio" name="attendance[<?php echo $participant['enrollment_id']; ?>]" value="TIDAK_HADIR" <?php echo ($participant['attendance_status'] == 'TIDAK_HADIR') ? 'checked' : ''; ?>> Tidak Hadir</label>
                                                    <label class="attendance-radio"><input type="radio" name="attendance[<?php echo $participant['enrollment_id']; ?>]" value="PENDING" <?php echo (in_array($participant['attendance_status'], ['PENDING', NULL], true)) ? 'checked' : ''; ?>> Pending</label>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                    </tbody>
                                </table>
                                <div class="form-actions" style="margin-top: 15px; border-top: none; padding-top:0;">
                                    <button type="submit" class="btn btn-save-attendance">Simpan Absensi</button>
                                </div>
                            </form>
                        <?php else: ?>
                            <p style="color: #9aa0a6;">Belum ada peserta yang terdaftar untuk sesi ini.</p>
                        <?php endif;
                        if(isset($stmt_participants)) $stmt_participants->close();
                    } else {
                         echo "<p class='alert alert-danger'>Error preparing participant statement: " . $conn->error . "</p>";
                    }
                    ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="no-sessions-message">
            <p>Tidak ada sesi mengajar yang sesuai dengan filter Anda.</p>
        </div>
    <?php endif; ?>
    <?php if(isset($stmt_sessions) && $stmt_sessions) $stmt_sessions->close(); ?>
</div>

<?php
$conn->close();
include_once '../partials/footer.php';
?>
