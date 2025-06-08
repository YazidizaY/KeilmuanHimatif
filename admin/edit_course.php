<?php
$page_title = "Edit Mata Kuliah";
$current_page = 'admin_courses';
include_once '../partials/header.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'ADMIN') {
    header("Location: " . ($base_path ?? '') . "/auth/login.php?error=Unauthorized");
    exit();
}

if (!isset($_GET['course_id']) || !filter_var($_GET['course_id'], FILTER_VALIDATE_INT)) {
    header("Location: " . ($base_path ?? '') . "/admin/courses.php?error=ID Mata Kuliah tidak valid.");
    exit();
}
$edit_course_id = $_GET['course_id'];

$stmt = $conn->prepare("SELECT course_name, category, semester FROM mata_kuliah WHERE course_id = ?");
if (!$stmt) {
    header("Location: " . ($base_path ?? '') . "/admin/courses.php?error=Gagal mempersiapkan statement.");
    exit();
}
$stmt->bind_param("i", $edit_course_id);
$stmt->execute();
$result = $stmt->get_result();
if (!($course = $result->fetch_assoc())) {
    header("Location: " . ($base_path ?? '') . "/admin/courses.php?error=Mata kuliah tidak ditemukan.");
    $stmt->close();
    $conn->close();
    exit();
}
$stmt->close();
?>

<div class="admin-form-page-container">
    <div class="admin-form-wrapper">
        <h2>Edit Mata Kuliah: <?php echo htmlspecialchars($course['course_name']); ?></h2>

        <?php
        if (isset($_GET['error'])) {
            echo '<p class="alert alert-danger">' . htmlspecialchars($_GET['error']) . '</p>';
        }
        ?>

        <form action="<?php echo ($base_path ?? '') ?>/admin/process_update_course.php" method="POST" class="validate-form">
            <input type="hidden" name="course_id" value="<?php echo $edit_course_id; ?>">
            
            <div class="form-group">
                <label for="course_name">Nama Mata Kuliah:</label>
                <input type="text" id="course_name" name="course_name" value="<?php echo htmlspecialchars($course['course_name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="category">Kategori:</label>
                <select id="category" name="category" required>
                    <option value="UMUM" <?php echo ($course['category'] == 'UMUM') ? 'selected' : ''; ?>>UMUM</option>
                    <option value="MIPA" <?php echo ($course['category'] == 'MIPA') ? 'selected' : ''; ?>>MIPA</option>
                    <option value="TEKNIK" <?php echo ($course['category'] == 'TEKNIK') ? 'selected' : ''; ?>>TEKNIK</option>
                </select>
            </div>
            <div class="form-group">
                <label for="semester">Semester (Angka):</label>
                <input type="number" id="semester" name="semester" value="<?php echo htmlspecialchars($course['semester']); ?>" required min="1" max="14">
            </div>
            
            <div class="form-actions">
                <a href="<?php echo ($base_path ?? '') ?>/admin/courses.php" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn">Update Mata Kuliah</button>
            </div>
        </form>
    </div>
</div>

<?php
$conn->close();
include_once '../partials/footer.php';
?>
