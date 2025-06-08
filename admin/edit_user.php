<?php
$page_title = "Edit Pengguna";
$current_page = 'admin_users'; 
include_once '../partials/header.php'; 
require_once '../config/database.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'ADMIN') {
    header("Location: " . ($base_path ?? '') . "/auth/login.php?error=Unauthorized");
    exit();
}

if (!isset($_GET['user_id']) || !filter_var($_GET['user_id'], FILTER_VALIDATE_INT)) {
    header("Location: " . ($base_path ?? '') . "/admin/users.php?error=ID Pengguna tidak valid.");
    exit();
}
$edit_user_id = $_GET['user_id'];

$stmt = $conn->prepare("SELECT user_id, username, email, fullname, npm, role, birth_date, profile_picture_path FROM users WHERE user_id = ?");
if (!$stmt) {
    header("Location: " . ($base_path ?? '') . "/admin/users.php?error=Gagal mempersiapkan statement: " . $conn->error);
    exit();
}
$stmt->bind_param("i", $edit_user_id);
$stmt->execute();
$result = $stmt->get_result();
if (!($user = $result->fetch_assoc())) {
    header("Location: " . ($base_path ?? '') . "/admin/users.php?error=Pengguna tidak ditemukan.");
    $stmt->close();
    $conn->close();
    exit();
}
$stmt->close();

$display_generated_avatar_form = false;
$generated_avatar_initial_form = '';
$generated_avatar_bg_color_form = '';

if (empty($user['profile_picture_path'])) {
    $display_generated_avatar_form = true;
    if (!empty($user['username'])) {
        $generated_avatar_initial_form = strtoupper(substr($user['username'], 0, 1));

        if (function_exists('getRandomAvatarColor')) {
             $generated_avatar_bg_color_form = getRandomAvatarColor($user['username']);
        } else {
            $generated_avatar_bg_color_form = '#5f6368';
        }
    } else {
        $generated_avatar_initial_form = '?';
        $generated_avatar_bg_color_form = '#5f6368';
    }
} else {
    $current_profile_pic_url_form = ($base_path ?? '') . '/' . htmlspecialchars($user['profile_picture_path']);
}

?>

<div class="admin-form-page-container">
    <div class="admin-form-wrapper">
        <h2>Edit Pengguna: <?php echo htmlspecialchars($user['username']); ?></h2>

        <?php
        if (isset($_GET['error'])) {
            echo '<p class="alert alert-danger">' . htmlspecialchars($_GET['error']) . '</p>';
        }
        if (isset($_GET['message'])) {
            echo '<p class="alert alert-success">' . htmlspecialchars($_GET['message']) . '</p>';
        }
        ?>

        <form action="<?php echo ($base_path ?? '') ?>/admin/process_user_update.php" method="POST" class="validate-form" enctype="multipart/form-data">
            <input type="hidden" name="user_id" value="<?php echo $edit_user_id; ?>">
            <input type="hidden" name="current_profile_picture" value="<?php echo htmlspecialchars($user['profile_picture_path'] ?? ''); ?>">

            <div class="form-group" style="text-align: center; margin-bottom: 25px;">
                <label>Gambar Profil Saat Ini:</label>
                <div style="margin-top: 10px;">
                    <?php if ($display_generated_avatar_form): ?>
                        <div class="generated-avatar" style="background-color: <?php echo $generated_avatar_bg_color_form; ?>; width: 120px; height: 120px; font-size: 3em; margin-left:auto; margin-right:auto;">
                            <?php echo htmlspecialchars($generated_avatar_initial_form); ?>
                        </div>
                    <?php else: ?>
                        <img src="<?php echo $current_profile_pic_url_form; ?>" alt="Current Profile Picture" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 2px solid #4a4e52;">
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="profile_picture">Unggah Gambar Profil Baru (Opsional):</label>
                <input type="file" id="profile_picture" name="profile_picture" accept="image/jpeg, image/png, image/gif">
                <small style="color:#9aa0a6; display:block; margin-top:5px;">Kosongkan jika tidak ingin mengubah. Akan menggantikan avatar default jika ada.</small>
            </div>
            
            <?php if (!empty($user['profile_picture_path'])):?>
            <div class="form-group">
                <label for="remove_profile_picture">
                    <input type="checkbox" id="remove_profile_picture" name="remove_profile_picture" value="1">
                    Hapus Gambar Profil Saat Ini (kembali ke avatar default)
                </label>
            </div>
            <?php endif; ?>
            
            <hr style="border-color: #3c4043; margin: 25px 0;">

            
            <div class="form-group">
                <label for="username_display">Username (Read-only):</label>
                <input type="text" id="username_display" name="username_display" value="<?php echo htmlspecialchars($user['username']); ?>" readonly style="background-color: #252628; cursor: not-allowed;">
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="form-group">
                <label for="fullname">Nama Lengkap:</label>
                <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($user['fullname']); ?>" required>
            </div>
            <div class="form-group">
                <label for="npm">NPM:</label>
                <input type="text" id="npm" name="npm" value="<?php echo htmlspecialchars($user['npm']); ?>" required pattern="\d{10,12}">
            </div>
             <div class="form-group">
                <label for="birth_date">Tanggal Lahir:</label>
                <input type="date" id="birth_date" name="birth_date" value="<?php echo htmlspecialchars($user['birth_date']); ?>" required>
            </div>
            <div class="form-group">
                <label for="role">Role:</label>
                <select id="role" name="role" required>
                    <option value="STUDENT" <?php echo ($user['role'] == 'STUDENT') ? 'selected' : ''; ?>>STUDENT</option>
                    <option value="ADMIN" <?php echo ($user['role'] == 'ADMIN') ? 'selected' : ''; ?>>ADMIN</option>
                </select>
            </div>
            <p style="color: #9aa0a6; font-size: 0.9em; margin-bottom: 20px;">Perubahan password tidak termasuk dalam form ini.</p>
            
            <div class="form-actions">
                <a href="<?php echo ($base_path ?? '') ?>/admin/users.php" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn">Update Pengguna</button>
            </div>
        </form>
    </div>
</div>

<?php
$conn->close();
include_once '../partials/footer.php';
?>
