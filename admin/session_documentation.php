<?php
$page_title = "Dokumentasi Sesi";
$current_page = 'admin_my_teaching';
include_once '../partials/header.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'ADMIN') {
    header("Location: " . ($base_path ?? '') . "/auth/login.php?error=Unauthorized");
    exit();
}
$admin_uploader_id = $_SESSION['user_id'];

if (!isset($_GET['session_id']) || !filter_var($_GET['session_id'], FILTER_VALIDATE_INT)) {
    header("Location: " . ($base_path ?? '') . "/admin/my_teaching_sessions.php?error=ID Sesi tidak valid.");
    exit();
}
$session_id_for_docs = $_GET['session_id'];

$stmt_session_info = $conn->prepare(
    "SELECT s.session_id, mk.course_name, s.session_date, s.session_time, s.tutor_id, s.status, s.documentation_notes
     FROM sesi_tutor s
     JOIN mata_kuliah mk ON s.course_id = mk.course_id
     WHERE s.session_id = ? AND s.tutor_id = ?" 
);
if (!$stmt_session_info) {
    die("Error prepare: " . $conn->error);
}
$stmt_session_info->bind_param("ii", $session_id_for_docs, $admin_uploader_id);
$stmt_session_info->execute();
$result_session_info = $stmt_session_info->get_result();
if (!($session_info = $result_session_info->fetch_assoc()) || $session_info['status'] !== 'COMPLETED') {
    header("Location: " . ($base_path ?? '') . "/admin/my_teaching_sessions.php?error=Sesi tidak ditemukan, bukan milik Anda, atau belum selesai.");
    $stmt_session_info->close(); $conn->close(); exit();
}
$stmt_session_info->close();

$existing_docs = [];
$stmt_docs = $conn->prepare("SELECT documentation_id, file_name, file_path, description, upload_timestamp FROM sesi_dokumentasi WHERE session_id = ? ORDER BY upload_timestamp DESC");
if ($stmt_docs) {
    $stmt_docs->bind_param("i", $session_id_for_docs);
    $stmt_docs->execute();
    $result_docs = $stmt_docs->get_result();
    while ($doc_row = $result_docs->fetch_assoc()) {
        $existing_docs[] = $doc_row;
    }
    $stmt_docs->close();
}

?>
<div class="admin-form-page-container">
    <div class="admin-form-wrapper" style="max-width: 800px;">
        <h2>Dokumentasi Sesi: <?php echo htmlspecialchars($session_info['course_name']); ?></h2>
        <p style="color: #bdc1c6; margin-bottom: 20px;">
            Tanggal: <?php echo htmlspecialchars(date('d F Y', strtotime($session_info['session_date']))); ?>, 
            Pukul: <?php echo htmlspecialchars(date('H:i', strtotime($session_info['session_time']))); ?> WIB
        </p>

        <?php
        if (isset($_GET['doc_message'])) { echo '<p class="alert alert-success">' . htmlspecialchars($_GET['doc_message']) . '</p>'; }
        if (isset($_GET['doc_error'])) { echo '<p class="alert alert-danger">' . htmlspecialchars($_GET['doc_error']) . '</p>'; }
        ?>

        <div class="documentation-section">
            <h4>Unggah Dokumentasi Baru</h4>
            <form action="<?php echo ($base_path ?? '') ?>/admin/process_upload_documentation.php" method="POST" enctype="multipart/form-data" class="validate-form" style="margin-bottom: 30px;">
                <input type="hidden" name="session_id" value="<?php echo $session_id_for_docs; ?>">
                <div class="form-group">
                    <label for="documentation_file">Pilih File:</label>
                    <input type="file" id="documentation_file" name="documentation_file" required>
                    <small style="color:#9aa0a6; display:block; margin-top:5px;">Tipe file: JPG, PNG, GIF, PDF, DOCX, PPTX, MP4, MOV. Maks: 10MB.</small>
                </div>
                <div class="form-group">
                    <label for="file_description">Deskripsi File (Opsional):</label>
                    <textarea id="file_description" name="file_description" rows="2" placeholder="Contoh: Foto papan tulis bagian 1, Rekaman sesi, Materi presentasi..."></textarea>
                </div>
                <button type="submit" class="btn btn-upload-doc">Unggah Dokumentasi</button>
            </form>

            <hr style="border-color: #3c4043; margin: 30px 0;">
            
            <h4>Catatan Dokumentasi Sesi (Opsional)</h4>
            <form action="<?php echo ($base_path ?? '') ?>/admin/process_update_doc_notes.php" method="POST" style="margin-bottom: 30px;">
                <input type="hidden" name="session_id" value="<?php echo $session_id_for_docs; ?>">
                <div class="form-group">
                    <textarea name="documentation_notes" rows="3" placeholder="Catatan umum tentang pelaksanaan sesi atau dokumentasi..."><?php echo htmlspecialchars($session_info['documentation_notes'] ?? ''); ?></textarea>
                </div>
                <button type="submit" class="btn">Simpan Catatan</button>
            </form>

            <hr style="border-color: #3c4043; margin: 30px 0;">

            <h4>Dokumentasi Tersimpan</h4>
            <?php if (!empty($existing_docs)): ?>
                <ul class="documentation-list">
                    <?php foreach ($existing_docs as $doc): ?>
                        <li>
                            <a href="<?php echo ($base_path ?? '') . '/' . htmlspecialchars($doc['file_path']); ?>" target="_blank" rel="noopener noreferrer">
                                <span class="nav-icon">📄</span> <?php echo htmlspecialchars($doc['file_name']); ?>
                            </a>
                            <?php if (!empty($doc['description'])): ?>
                                <small class="doc-description">- <?php echo htmlspecialchars($doc['description']); ?></small>
                            <?php endif; ?>
                            <small class="doc-timestamp">(Diupload: <?php echo htmlspecialchars(date('d M Y, H:i', strtotime($doc['upload_timestamp']))); ?>)</small>
                            <form action="<?php echo ($base_path ?? '') ?>/admin/process_delete_documentation.php" method="POST" onsubmit="return confirm('Yakin ingin menghapus file dokumentasi ini?');" style="display:inline; margin-left:10px;">
                                <input type="hidden" name="documentation_id" value="<?php echo $doc['documentation_id']; ?>">
                                <input type="hidden" name="session_id" value="<?php echo $session_id_for_docs; ?>">
                                <input type="hidden" name="file_path_to_delete" value="<?php echo htmlspecialchars($doc['file_path']); ?>">
                                <button type="submit" class="btn-delete-doc">Hapus</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p style="color: #9aa0a6;">Belum ada dokumentasi yang diunggah untuk sesi ini.</p>
            <?php endif; ?>
        </div>
        <div class="form-actions" style="margin-top: 30px;">
            <a href="<?php echo ($base_path ?? '') ?>/admin/my_teaching_sessions.php" class="btn btn-secondary">Kembali ke Sesi Mengajar</a>
        </div>
    </div>
</div>

<?php
$conn->close();
include_once '../partials/footer.php';
?>
