<?php
session_start();
require_once '../config/database.php';

$base_path = "/" . basename(dirname(dirname(__FILE__)));
if ($base_path == "/your_project_root" || $base_path == "/..") $base_path = "";


if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'STUDENT') {
    header("Location: " . $base_path . "/auth/login.php?error=Unauthorized");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['session_id'])) {
    $student_id = $_SESSION['user_id'];
    $session_id = (int)$_POST['session_id'];
    $current_datetime_db_format = date('Y-m-d H:i:s');

    $stmt_check_session_time = $conn->prepare(
        "SELECT session_id FROM sesi_tutor 
         WHERE session_id = ? AND CONCAT(session_date, ' ', session_time) >= ?"
    );
    if (!$stmt_check_session_time) {
        header("Location: my_sessions.php?error=Prepare statement gagal (check session time).");
        exit();
    }
    $stmt_check_session_time->bind_param("is", $session_id, $current_datetime_db_format);
    $stmt_check_session_time->execute();
    $result_check_session_time = $stmt_check_session_time->get_result();

    if ($result_check_session_time->num_rows == 0) {
        header("Location: my_sessions.php?error=Tidak dapat membatalkan sesi yang sudah lewat atau tidak valid.");
        $stmt_check_session_time->close();
        $conn->close();
        exit();
    }
    $stmt_check_session_time->close();

    $stmt_cancel = $conn->prepare(
        "UPDATE enrollment SET status = 'CANCELLED' 
         WHERE user_id = ? AND session_id = ? AND status = 'ACTIVE'"
    );
    if (!$stmt_cancel) {
        header("Location: my_sessions.php?error=Prepare statement gagal (cancel enrollment).");
        exit();
    }
    $stmt_cancel->bind_param("ii", $student_id, $session_id);

    if ($stmt_cancel->execute()) {
        if ($stmt_cancel->affected_rows > 0) {
            header("Location: my_sessions.php?message=Kehadiran pada sesi berhasil dibatalkan.");
        } else {
            header("Location: my_sessions.php?error=Anda tidak terdaftar di sesi ini atau kehadiran sudah dibatalkan.");
        }
    } else {
        header("Location: my_sessions.php?error=Gagal membatalkan kehadiran: " . $stmt_cancel->error);
    }
    $stmt_cancel->close();
    $conn->close();
} else {
    header("Location: my_sessions.php");
    exit();
}
?>
