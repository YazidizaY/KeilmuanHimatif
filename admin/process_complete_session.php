<?php
session_start();
require_once '../config/database.php';

$base_path = "/" . basename(dirname(dirname(__FILE__)));
if ($base_path == "/your_project_root" || $base_path == "/..") $base_path = "";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'ADMIN') {
    header("Location: " . $base_path . "/auth/login.php?error=Unauthorized");
    exit();
}
$admin_tutor_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['session_id'])) {
    $session_id = (int)$_POST['session_id'];

    $stmt_check_session = $conn->prepare(
        "SELECT session_id FROM sesi_tutor 
         WHERE session_id = ? AND tutor_id = ? AND status = 'ACCEPTED'"
    );
    if (!$stmt_check_session) {
        header("Location: " . $base_path . "/admin/my_teaching_sessions.php?error=Prepare statement gagal (check session).");
        exit();
    }
    $stmt_check_session->bind_param("ii", $session_id, $admin_tutor_id);
    $stmt_check_session->execute();
    $result_check_session = $stmt_check_session->get_result();

    if ($result_check_session->num_rows == 0) {
        header("Location: " . $base_path . "/admin/my_teaching_sessions.php?error=Sesi tidak valid, bukan milik Anda, atau sudah selesai.");
        $stmt_check_session->close();
        $conn->close();
        exit();
    }
    $stmt_check_session->close();

    $stmt_complete_session = $conn->prepare("UPDATE sesi_tutor SET status = 'COMPLETED' WHERE session_id = ?");
    if (!$stmt_complete_session) {
        header("Location: " . $base_path . "/admin/my_teaching_sessions.php?error=Prepare statement gagal (complete session).");
        exit();
    }
    $stmt_complete_session->bind_param("i", $session_id);

    if ($stmt_complete_session->execute()) {
        if ($stmt_complete_session->affected_rows > 0) {
            header("Location: " . $base_path . "/admin/my_teaching_sessions.php?message=Sesi #" . $session_id . " berhasil diselesaikan.");
        } else {
            header("Location: " . $base_path . "/admin/my_teaching_sessions.php?error=Tidak ada perubahan status pada sesi #" . $session_id . " (mungkin sudah selesai).");
        }
    } else {
        header("Location: " . $base_path . "/admin/my_teaching_sessions.php?error=Gagal menyelesaikan sesi #" . $session_id . ": " . $stmt_complete_session->error);
    }
    $stmt_complete_session->close();
    $conn->close();
} else {
    header("Location: " . $base_path . "/admin/my_teaching_sessions.php");
    exit();
}
?>
