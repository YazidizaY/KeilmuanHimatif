<?php
session_start();
require_once '../config/database.php';

$base_path_server = dirname(dirname(__FILE__)); 
$base_path_url = "/" . basename($base_path_server);
if ($base_path_url == "/your_project_root_folder_name_if_any" || $base_path_url == "/..") $base_path_url = "";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'ADMIN') {
    header("Location: " . $base_path_url . "/auth/login.php?error=Unauthorized");
    exit();
}
$uploader_user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['session_id']) && isset($_FILES['documentation_file'])) {
    $session_id = (int)$_POST['session_id'];
    $file_description = isset($_POST['file_description']) ? trim($_POST['file_description']) : NULL;

    $stmt_check_session = $conn->prepare(
        "SELECT session_id FROM sesi_tutor 
         WHERE session_id = ? AND tutor_id = ? AND status = 'COMPLETED'"
    );
    if (!$stmt_check_session) {
        header("Location: " . $base_path_url . "/admin/session_documentation.php?session_id=" . $session_id . "&doc_error=Prepare statement gagal (check session).");
        exit();
    }
    $stmt_check_session->bind_param("ii", $session_id, $uploader_user_id);
    $stmt_check_session->execute();
    $result_check_session = $stmt_check_session->get_result();
    if ($result_check_session->num_rows == 0) {
        header("Location: " . $base_path_url . "/admin/my_teaching_sessions.php?error=Tidak berwenang mengunggah dokumentasi untuk sesi ini atau sesi belum selesai.");
        $stmt_check_session->close(); $conn->close(); exit();
    }
    $stmt_check_session->close();

    if ($_FILES['documentation_file']['error'] == UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES['documentation_file']['tmp_name'];
        $file_name = basename($_FILES['documentation_file']['name']); 
        $file_size = $_FILES['documentation_file']['size'];
        $file_type = $_FILES['documentation_file']['type'];
        $file_name_parts = explode('.', $file_name);
        $file_ext = strtolower(end($file_name_parts));

        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'ppt', 'pptx', 'mp4', 'mov', 'avi', 'txt'];
        $max_file_size = 10 * 1024 * 1024;

        if (in_array($file_ext, $allowed_extensions)) {
            if ($file_size <= $max_file_size) {
                $session_upload_dir_relative = 'uploads/session_docs/' . $session_id . '/';
                $session_upload_dir_absolute = $base_path_server . '/' . $session_upload_dir_relative;
                
                if (!is_dir($session_upload_dir_absolute)) {
                    mkdir($session_upload_dir_absolute, 0775, true);
                }

                $new_file_name_on_server = uniqid('doc_', true) . '_' . preg_replace("/[^a-zA-Z0-9.\-_]/", "_", $file_name);
                $dest_path_absolute = $session_upload_dir_absolute . $new_file_name_on_server;
                $dest_path_relative_for_db = $session_upload_dir_relative . $new_file_name_on_server;


                if (move_uploaded_file($file_tmp_path, $dest_path_absolute)) {
                    $stmt_insert_doc = $conn->prepare(
                        "INSERT INTO sesi_dokumentasi (session_id, uploader_user_id, file_name, file_path, file_type, file_size, description) 
                         VALUES (?, ?, ?, ?, ?, ?, ?)"
                    );
                    if (!$stmt_insert_doc) {
                         header("Location: " . $base_path_url . "/admin/session_documentation.php?session_id=" . $session_id . "&doc_error=Prepare statement gagal (insert doc): " . $conn->error);
                         unlink($dest_path_absolute);
                         exit();
                    }
                    $stmt_insert_doc->bind_param("iisssis", $session_id, $uploader_user_id, $file_name, $dest_path_relative_for_db, $file_type, $file_size, $file_description);
                    
                    if ($stmt_insert_doc->execute()) {
                        header("Location: " . $base_path_url . "/admin/session_documentation.php?session_id=" . $session_id . "&doc_message=Dokumentasi berhasil diunggah.");
                    } else {
                        unlink($dest_path_absolute);
                        header("Location: " . $base_path_url . "/admin/session_documentation.php?session_id=" . $session_id . "&doc_error=Gagal menyimpan data dokumentasi: " . $stmt_insert_doc->error);
                    }
                    $stmt_insert_doc->close();
                } else {
                    header("Location: " . $base_path_url . "/admin/session_documentation.php?session_id=" . $session_id . "&doc_error=Gagal memindahkan file yang diunggah.");
                }
            } else {
                header("Location: " . $base_path_url . "/admin/session_documentation.php?session_id=" . $session_id . "&doc_error=Ukuran file terlalu besar (maks 10MB).");
            }
        } else {
            header("Location: " . $base_path_url . "/admin/session_documentation.php?session_id=" . $session_id . "&doc_error=Format file tidak didukung.");
        }
    } else {
        header("Location: " . $base_path_url . "/admin/session_documentation.php?session_id=" . $session_id . "&doc_error=Tidak ada file yang dipilih atau terjadi kesalahan unggah: error code " . ($_FILES['documentation_file']['error'] ?? 'N/A'));
    }
    $conn->close();
} else {
    header("Location: " . ($base_path_url ?? '') . "/admin/my_teaching_sessions.php");
    exit();
}
?>
