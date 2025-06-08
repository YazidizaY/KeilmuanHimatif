<?php
session_start();
require_once '../config/database.php';

$base_path_url = "/" . basename(dirname(dirname(__FILE__)));
if ($base_path_url == "/your_project_root_folder_name_if_any" || $base_path_url == "/..") {
    $base_path_url = "";
}


if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'ADMIN') {
    header("Location: " . $base_path_url . "/auth/login.php?error=Unauthorized");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $session_id = filter_input(INPUT_POST, 'session_id', FILTER_VALIDATE_INT);
    $material = trim($_POST['material']);
    $session_date = $_POST['session_date'];
    $session_time = $_POST['session_time'];
    $tutor_id_input = $_POST['tutor_id']; 
    $status = $_POST['status'];
    $original_status = $_POST['original_status'] ?? 'PENDING'; 
    $ref_page = $_POST['ref'] ?? '';

    $location_type = isset($_POST['location_type']) && in_array($_POST['location_type'], ['ONLINE', 'OFFLINE']) ? $_POST['location_type'] : NULL;
    $location_detail = !empty($_POST['location_detail']) ? trim($_POST['location_detail']) : NULL;


    $tutor_id = (!empty($tutor_id_input) && filter_var($tutor_id_input, FILTER_VALIDATE_INT)) ? (int)$tutor_id_input : NULL;

    if (!$session_id || empty($material) || empty($session_date) || empty($session_time) || !in_array($status, ['PENDING', 'ACCEPTED', 'REJECTED', 'COMPLETED'])) {
        header("Location: edit_session.php?session_id=" . $session_id . "&ref=" . $ref_page . "&error=Data tidak lengkap atau status tidak valid.");
        exit();
    }
    
    if (($status == 'ACCEPTED' || $status == 'COMPLETED') && $tutor_id === NULL) {
        header("Location: edit_session.php?session_id=" . $session_id . "&ref=" . $ref_page . "&error=Tutor harus dipilih jika status ACCEPTED atau COMPLETED.");
        exit();
    }
    if (($status == 'ACCEPTED' || $status == 'COMPLETED') && (empty($location_type) || empty($location_detail))) {
        header("Location: edit_session.php?session_id=" . $session_id . "&ref=" . $ref_page . "&error=Tipe lokasi dan detail lokasi wajib diisi jika status ACCEPTED atau COMPLETED.");
        exit();
    }


    if (($status == 'PENDING' || $status == 'REJECTED')) {
        $tutor_id = NULL;
    }


    $stmt = $conn->prepare(
        "UPDATE sesi_tutor SET material = ?, session_date = ?, session_time = ?, 
         tutor_id = ?, status = ?, location_type = ?, location_detail = ? 
         WHERE session_id = ?"
    );
    if (!$stmt) {
        header("Location: edit_session.php?session_id=" . $session_id . "&ref=" . $ref_page . "&error=Gagal mempersiapkan statement: " . $conn->error);
        exit();
    }
    $stmt->bind_param("sssissis", $material, $session_date, $session_time, $tutor_id, $status, $location_type, $location_detail, $session_id);

    if ($stmt->execute()) {
        $redirect_page = "index.php";
        if ($ref_page == 'myteaching') {
            $redirect_page = "my_teaching_sessions.php";
        } elseif ($ref_page == 'approved' || $status == 'ACCEPTED' || $status == 'COMPLETED') {
            $redirect_page = "approved_sessions.php";
        } elseif ($ref_page == 'pending' || $status == 'PENDING' || $status == 'REJECTED') {
            $redirect_page = "pending_requests.php";
        }

        header("Location: " . $redirect_page . "?message=Sesi tutor #" . $session_id . " berhasil diperbarui.");
    } else {
        header("Location: edit_session.php?session_id=" . $session_id . "&ref=" . $ref_page . "&error=Gagal memperbarui sesi: " . $stmt->error);
    }
    $stmt->close();
    $conn->close();
} else {
    header("Location: " . ($base_path_url ?? '') . "/admin/index.php");
    exit();
}
?>
