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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['session_id']) && isset($_POST['attendance'])) {
    $session_id = (int)$_POST['session_id'];
    $attendance_data = $_POST['attendance']; 

    $stmt_check_tutor = $conn->prepare("SELECT session_id FROM sesi_tutor WHERE session_id = ? AND tutor_id = ?");
    if (!$stmt_check_tutor) {
        header("Location: " . $base_path . "/admin/my_teaching_sessions.php?error=Prepare statement gagal (check tutor).");
        exit();
    }
    $stmt_check_tutor->bind_param("ii", $session_id, $admin_tutor_id);
    $stmt_check_tutor->execute();
    $result_check_tutor = $stmt_check_tutor->get_result();
    if ($result_check_tutor->num_rows == 0) {
        header("Location: " . $base_path . "/admin/my_teaching_sessions.php?error=Anda tidak berwenang mengubah absensi sesi ini.");
        $stmt_check_tutor->close();
        $conn->close();
        exit();
    }
    $stmt_check_tutor->close();

    $conn->begin_transaction();
    $all_updates_successful = true;

    $stmt_update_attendance = $conn->prepare("UPDATE enrollment SET attendance_status = ? WHERE enrollment_id = ? AND session_id = ?");
    if (!$stmt_update_attendance) {
        $all_updates_successful = false;
    }

    foreach ($attendance_data as $enrollment_id => $status) {
        $enrollment_id_int = (int)$enrollment_id;
        if (!in_array($status, ['PENDING', 'HADIR', 'TIDAK_HADIR'])) {
            $status = 'PENDING';
        }
        
        if ($stmt_update_attendance) {
            $stmt_update_attendance->bind_param("sii", $status, $enrollment_id_int, $session_id);
            if (!$stmt_update_attendance->execute()) {
                $all_updates_successful = false;
                break; 
            }
        } else {
            $all_updates_successful = false;
            break;
        }
    }

    if ($all_updates_successful) {
        $conn->commit();
        header("Location: " . $base_path . "/admin/my_teaching_sessions.php?message=Absensi berhasil diperbarui untuk sesi #" . $session_id);
    } else {
        $conn->rollback();
        header("Location: " . $base_path . "/admin/my_teaching_sessions.php?error=Gagal memperbarui sebagian atau semua absensi untuk sesi #" . $session_id);
    }

    if ($stmt_update_attendance) $stmt_update_attendance->close();
    $conn->close();
} else {
    header("Location: " . $base_path . "/admin/my_teaching_sessions.php");
    exit();
}
?>
