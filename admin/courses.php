<?php
$page_title = "Kelola Mata Kuliah";
$current_page = 'admin_courses';
include_once '../partials/header.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'ADMIN') {
    header("Location: " . ($base_path ?? '') . "/auth/login.php?error=Unauthorized");
    exit();
}
?>

<?php
if (isset($_GET['message'])) {
    echo '<p class="alert alert-success" style="max-width: none;">' . htmlspecialchars($_GET['message']) . '</p>';
}
if (isset($_GET['error'])) {
    echo '<p class="alert alert-danger" style="max-width: none;">' . htmlspecialchars($_GET['error']) . '</p>';
}
?>

<div class="admin-table-header-actions">
    <a href="<?php echo ($base_path ?? '') ?>/admin/add_course.php" class="btn btn-add-new">
        <span class="nav-icon">➕</span> Tambah Mata Kuliah Baru
    </a>
</div>

<?php
$result = $conn->query("SELECT course_id, course_name, category, semester FROM mata_kuliah ORDER BY course_name");
?>
<table class="admin-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nama Mata Kuliah</th>
            <th>Kategori</th>
            <th>Semester</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($course = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $course['course_id']; ?></td>
                <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                <td><?php echo htmlspecialchars($course['category']); ?></td>
                <td><?php echo $course['semester']; ?></td>
                <td>
                    <div class="action-buttons">
                        <a href="<?php echo ($base_path ?? '') ?>/admin/edit_course.php?course_id=<?php echo $course['course_id']; ?>" class="btn-small btn-edit">Edit</a>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5" style="text-align:center; padding: 20px;">Tidak ada data mata kuliah.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php
if ($result) $result->close();
$conn->close();
include_once '../partials/footer.php';
?>
