<?php
session_start();
require_once '../config/database.php';

$base_path_url = "/" . basename(dirname(dirname(__FILE__)));
if ($base_path_url == "/your_project_root_folder_name_if_any" || $base_path_url == "/..") $base_path_url = "";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'ADMIN') {
    header("Location: " . $base_path_url . "/auth/login.php?error=Unauthorized");
    exit();
}
$admin_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['session_id'])) {
    $session_id = (int)$_POST['session_id'];
    $documentation_notes = isset($_POST['documentation_notes']) ? trim($_POST['documentation_notes']) : NULL;

    $stmt_check_tutor = $conn->prepare(
        "SELECT session_id FROM sesi_tutor 
         WHERE session_id = ? AND tutor_id = ?"
    );
    if (!$stmt_check_tutor) {
        header("Location: " . $base_path_url . "/admin/session_documentation.php?session_id=" . $session_id . "&doc_error=Prepare statement gagal (check tutor).");
        exit();
    }
    $stmt_check_tutor->bind_param("ii", $session_id, $admin_id);
    $stmt_check_tutor->execute();
    $result_check_tutor = $stmt_check_tutor->get_result();
    if ($result_check_tutor->num_rows == 0) {
        header("Location: " . $base_path_url . "/admin/my_teaching_sessions.php?error=Tidak berwenang mengubah catatan sesi ini.");
        $stmt_check_tutor->close(); $conn->close(); exit();
    }
    $stmt_check_tutor->close();

    $stmt_update_notes = $conn->prepare("UPDATE sesi_tutor SET documentation_notes = ? WHERE session_id = ?");
    if (!$stmt_update_notes) {
        header("Location: " . $base_path_url . "/admin/session_documentation.php?session_id=" . $session_id . "&doc_error=Prepare statement gagal (update notes).");
        exit();
    }
    $stmt_update_notes->bind_param("si", $documentation_notes, $session_id);

    if ($stmt_update_notes->execute()) {
        header("Location: " . $base_path_url . "/admin/session_documentation.php?session_id=" . $session_id . "&doc_message=Catatan dokumentasi berhasil disimpan.");
    } else {
        header("Location: " . $base_path_url . "/admin/session_documentation.php?session_id=" . $session_id . "&doc_error=Gagal menyimpan catatan dokumentasi: " . $stmt_update_notes->error);
    }
    $stmt_update_notes->close();
    $conn->close();
} else {
    header("Location: " . ($base_path_url ?? '') . "/admin/my_teaching_sessions.php");
    exit();
}
?>
