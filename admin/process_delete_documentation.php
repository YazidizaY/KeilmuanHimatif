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
$admin_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['documentation_id']) && isset($_POST['session_id']) && isset($_POST['file_path_to_delete'])) {
    $documentation_id = (int)$_POST['documentation_id'];
    $session_id = (int)$_POST['session_id'];
    $file_path_to_delete_relative = $_POST['file_path_to_delete'];

    $stmt_check_owner = $conn->prepare(
        "SELECT st.session_id FROM sesi_dokumentasi sd
         JOIN sesi_tutor st ON sd.session_id = st.session_id
         WHERE sd.documentation_id = ? AND st.tutor_id = ?"
    );
    if (!$stmt_check_owner) {
        header("Location: " . $base_path_url . "/admin/session_documentation.php?session_id=" . $session_id . "&doc_error=Prepare statement gagal (check owner).");
        exit();
    }
    $stmt_check_owner->bind_param("ii", $documentation_id, $admin_id);
    $stmt_check_owner->execute();
    $result_check_owner = $stmt_check_owner->get_result();
    if ($result_check_owner->num_rows == 0) {
        header("Location: " . $base_path_url . "/admin/session_documentation.php?session_id=" . $session_id . "&doc_error=Anda tidak berwenang menghapus file ini.");
        $stmt_check_owner->close(); $conn->close(); exit();
    }
    $stmt_check_owner->close();

    $file_path_absolute = $base_path_server . '/' . $file_path_to_delete_relative;
    $file_deleted_from_server = false;
    if (file_exists($file_path_absolute)) {
        if (unlink($file_path_absolute)) {
            $file_deleted_from_server = true;
        }
    } else {
        $file_deleted_from_server = true;
    }

    if ($file_deleted_from_server) {
        $stmt_delete_db = $conn->prepare("DELETE FROM sesi_dokumentasi WHERE documentation_id = ?");
        if (!$stmt_delete_db) {
            header("Location: " . $base_path_url . "/admin/session_documentation.php?session_id=" . $session_id . "&doc_error=Prepare statement gagal (delete db).");
            exit();
        }
        $stmt_delete_db->bind_param("i", $documentation_id);
        if ($stmt_delete_db->execute()) {
            header("Location: " . $base_path_url . "/admin/session_documentation.php?session_id=" . $session_id . "&doc_message=File dokumentasi berhasil dihapus.");
        } else {
            header("Location: " . $base_path_url . "/admin/session_documentation.php?session_id=" . $session_id . "&doc_error=Gagal menghapus data file dari database: " . $stmt_delete_db->error);
        }
        $stmt_delete_db->close();
    } else {
        header("Location: " . $base_path_url . "/admin/session_documentation.php?session_id=" . $session_id . "&doc_error=Gagal menghapus file dari server.");
    }
    $conn->close();
} else {
    header("Location: " . ($base_path_url ?? '') . "/admin/my_teaching_sessions.php");
    exit();
}
?>
