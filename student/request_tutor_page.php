<?php
$page_title = "Request Sesi Tutor";
$current_page = 'student_request_tutor';
include_once '../partials/header.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'STUDENT') {
    header("Location: " . ($base_path ?? '') . "/auth/login.php?error=Unauthorized");
    exit();
}

$courses_result = $conn->query("SELECT course_id, course_name FROM mata_kuliah ORDER BY course_name");
$mata_kuliah_list = [];
if ($courses_result && $courses_result->num_rows > 0) {
    while ($row = $courses_result->fetch_assoc()) {
        $mata_kuliah_list[] = $row;
    }
}
?>

<div class="request-tutor-page-container">
    <?php
    if (isset($_GET['message'])) { echo '<p class="alert alert-success" style="margin-bottom:20px; width:100%; max-width:500px;">' . htmlspecialchars($_GET['message']) . '</p>'; }
    if (isset($_GET['error'])) { echo '<p class="alert alert-danger" style="margin-bottom:20px; width:100%; max-width:500px;">' . htmlspecialchars($_GET['error']) . '</p>'; }
    ?>
    <h2>Ajukan Permintaan Sesi Tutor</h2>
    <p class="page-subheading">Siap untuk meningkatkan pemahamanmu? Isi formulir di bawah ini untuk meminta sesi bimbingan dengan tutor kami.</p>
    <button id="openRequestModalBtnOnPage" class="btn-trigger-modal-main">
        <span class="nav-icon">➕</span> Isi Form Permintaan
    </button>
</div>

<div id="requestTutorModal" class="modal">
  <div class="modal-content">
    <div class="modal-header-custom">
        <h2>Form Permintaan Sesi Tutor</h2>
        <span class="close-btn">&times;</span>
    </div>
    <form action="<?php echo ($base_path ?? '') ?>/student/process_request_tutor.php" method="POST" class="validate-form">
        <div class="form-group">
            <label for="course_id">Mata Kuliah:</label>
            <select id="course_id" name="course_id" required>
                <option value="">Pilih Mata Kuliah</option>
                <?php foreach ($mata_kuliah_list as $mk): ?>
                <option value="<?php echo $mk['course_id']; ?>"><?php echo htmlspecialchars($mk['course_name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="material">Materi yang ingin dibahas:</label>
            <textarea id="material" name="material" rows="3" required placeholder="Contoh: Turunan parsial, Integral lipat dua, Implementasi Linked List..."></textarea>
        </div>
        <div class="form-group">
            <label for="session_date">Tanggal yang diinginkan:</label>
            <input type="date" id="session_date" name="session_date" required min="<?php echo date('Y-m-d'); ?>">
        </div>
        <div class="form-group">
            <label for="session_time">Waktu yang diinginkan:</label>
            <input type="time" id="session_time" name="session_time" required>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn">Kirim Permintaan</button>
        </div>
    </form>
  </div>
</div>

<?php
if (isset($courses_result) && $courses_result) $courses_result->close();
$conn->close();
include_once '../partials/footer.php';
?>
