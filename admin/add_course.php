<?php
$page_title = "Tambah Mata Kuliah Baru";
$current_page = 'admin_courses';
include_once '../partials/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'ADMIN') {
    header("Location: " . ($base_path ?? '') . "/auth/login.php?error=Unauthorized");
    exit();
}
?>

<div class="admin-form-page-container">
    <div class="admin-form-wrapper">
        <h2>Tambah Mata Kuliah Baru</h2>

        <?php
        if (isset($_GET['error'])) {
            echo '<p class="alert alert-danger">' . htmlspecialchars($_GET['error']) . '</p>';
        }
        ?>

        <form action="<?php echo ($base_path ?? '') ?>/admin/process_add_course.php" method="POST" class="validate-form">
            <div class="form-group">
                <label for="course_name">Nama Mata Kuliah:</label>
                <input type="text" id="course_name" name="course_name" required>
            </div>
            <div class="form-group">
                <label for="category">Kategori:</label>
                <select id="category" name="category" required>
                    <option value="">Pilih Kategori</option>
                    <option value="UMUM">UMUM</option>
                    <option value="MIPA">MIPA</option>
                    <option value="TEKNIK">TEKNIK</option>
                </select>
            </div>
            <div class="form-group">
                <label for="semester">Semester (Angka):</label>
                <input type="number" id="semester" name="semester" required min="1" max="14">
            </div>
            <div class="form-actions">
                <a href="<?php echo ($base_path ?? '') ?>/admin/courses.php" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn">Simpan Mata Kuliah</button>
            </div>
        </form>
    </div>
</div>

<?php include_once '../partials/footer.php'; ?>
