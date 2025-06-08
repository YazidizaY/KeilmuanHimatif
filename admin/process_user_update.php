<?php
session_start();
require_once '../config/database.php';

$base_path_server = dirname(dirname(__FILE__));
$base_path_url = "/" . basename($base_path_server);
if ($base_path_url == "/your_project_root_folder_name_if_any" || $base_path_url == "/..") {
    $base_path_url = "";
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'ADMIN') {
    header("Location: " . $base_path_url . "/auth/login.php?error=Unauthorized");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id_to_edit = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    $email = trim($_POST['email']);
    $fullname = trim($_POST['fullname']);
    $npm = trim($_POST['npm']);
    $birth_date = $_POST['birth_date'];
    $role = $_POST['role'];
    $current_profile_picture_path = $_POST['current_profile_picture'] ?? null;
    $remove_profile_picture = isset($_POST['remove_profile_picture']) && $_POST['remove_profile_picture'] == '1';

    if (!$user_id_to_edit || empty($email) || empty($fullname) || empty($npm) || empty($birth_date) || !in_array($role, ['STUDENT', 'ADMIN'])) {
        header("Location: edit_user.php?user_id=" . $user_id_to_edit . "&error=Data tidak lengkap atau tidak valid.");
        exit();
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: edit_user.php?user_id=" . $user_id_to_edit . "&error=Format email tidak valid.");
        exit();
    }

    $new_profile_picture_path_db = $current_profile_picture_path; 
    
    if ($remove_profile_picture) {
        if (!empty($current_profile_picture_path) && file_exists($base_path_server . '/' . $current_profile_picture_path)) {
            unlink($base_path_server . '/' . $current_profile_picture_path); 
        }
        $new_profile_picture_path_db = NULL; 
    }

    if (!$remove_profile_picture && isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES['profile_picture']['tmp_name'];
        $file_name = $_FILES['profile_picture']['name'];
        $file_size = $_FILES['profile_picture']['size'];
        $file_name_parts = explode('.', $file_name);
        $file_ext = strtolower(end($file_name_parts));

        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $max_file_size = 2 * 1024 * 1024;

        if (in_array($file_ext, $allowed_extensions)) {
            if ($file_size <= $max_file_size) {
                if (!empty($current_profile_picture_path) && file_exists($base_path_server . '/' . $current_profile_picture_path)) {
                    unlink($base_path_server . '/' . $current_profile_picture_path);
                }

                $new_file_name_for_upload = uniqid('profile_', true) . '.' . $file_ext;
                $upload_dir = $base_path_server . '/uploads/profile_pictures/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0775, true);
                }
                $dest_path_on_server = $upload_dir . $new_file_name_for_upload;

                if (move_uploaded_file($file_tmp_path, $dest_path_on_server)) {
                    $new_profile_picture_path_db = 'uploads/profile_pictures/' . $new_file_name_for_upload;
                } else {
                    header("Location: edit_user.php?user_id=" . $user_id_to_edit . "&error=Gagal memindahkan file gambar profil baru.");
                    exit();
                }
            } else {
                header("Location: edit_user.php?user_id=" . $user_id_to_edit . "&error=Ukuran file gambar profil baru terlalu besar (maks 2MB).");
                exit();
            }
        } else {
            header("Location: edit_user.php?user_id=" . $user_id_to_edit . "&error=Format file gambar profil baru tidak didukung.");
            exit();
        }
    } elseif (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] != UPLOAD_ERR_NO_FILE && $_FILES['profile_picture']['error'] != UPLOAD_ERR_OK) {
        header("Location: edit_user.php?user_id=" . $user_id_to_edit . "&error=Terjadi kesalahan saat mengunggah gambar profil baru: error code " . $_FILES['profile_picture']['error']);
        exit();
    }

    $stmt_update = $conn->prepare("UPDATE users SET email = ?, fullname = ?, npm = ?, birth_date = ?, role = ?, profile_picture_path = ? WHERE user_id = ?");
    if (!$stmt_update) {
        header("Location: edit_user.php?user_id=" . $user_id_to_edit . "&error=Gagal mempersiapkan statement update: " . $conn->error);
        if ($new_profile_picture_path_db !== $current_profile_picture_path && !empty($new_profile_picture_path_db) && file_exists($base_path_server . '/' . $new_profile_picture_path_db)) {
             if ($new_profile_picture_path_db !== ('uploads/profile_pictures/' . basename($current_profile_picture_path ?? ''))) { // Pastikan bukan file lama
                unlink($base_path_server . '/' . $new_profile_picture_path_db);
             }
        }
        exit();
    }
    $stmt_update->bind_param("ssssssi", $email, $fullname, $npm, $birth_date, $role, $new_profile_picture_path_db, $user_id_to_edit);

    if ($stmt_update->execute()) {
        if ($_SESSION['user_id'] == $user_id_to_edit) {
            $_SESSION['profile_picture_path'] = $new_profile_picture_path_db;
            $_SESSION['fullname'] = $fullname; 
        }
        header("Location: edit_user.php?user_id=" . $user_id_to_edit . "&message=Data pengguna berhasil diperbarui.");
    } else {
        header("Location: edit_user.php?user_id=" . $user_id_to_edit . "&error=Gagal memperbarui data pengguna: " . $stmt_update->error);
        if ($new_profile_picture_path_db !== $current_profile_picture_path && !empty($new_profile_picture_path_db) && file_exists($base_path_server . '/' . $new_profile_picture_path_db)) {
            if ($new_profile_picture_path_db !== ('uploads/profile_pictures/' . basename($current_profile_picture_path ?? ''))) {
                unlink($base_path_server . '/' . $new_profile_picture_path_db);
            }
        }
    }
    $stmt_update->close();
    $conn->close();
} else {
    header("Location: " . $base_path_url . "/admin/users.php");
    exit();
}
?>
