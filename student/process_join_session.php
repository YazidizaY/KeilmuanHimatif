<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'STUDENT') {
    header("Location: ../auth/login.php?error=Unauthorized");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['session_id'])) {
    $student_id = $_SESSION['user_id'];
    $session_id = (int)$_POST['session_id'];

    $stmt_check_session = $conn->prepare("SELECT session_id FROM sesi_tutor WHERE session_id = ? AND status = 'ACCEPTED'");
    $stmt_check_session->bind_param("i", $session_id);
    $stmt_check_session->execute();
    $stmt_check_session->store_result();

    if ($stmt_check_session->num_rows == 0) {
        header("Location: index.php?error=Sesi tidak valid atau belum disetujui.");
        $stmt_check_session->close();
        $conn->close();
        exit();
    }
    $stmt_check_session->close();

    $stmt_check_enroll = $conn->prepare("SELECT enrollment_id FROM enrollment WHERE user_id = ? AND session_id = ? AND status = 'ACTIVE'");
    $stmt_check_enroll->bind_param("ii", $student_id, $session_id);
    $stmt_check_enroll->execute();
    $stmt_check_enroll->store_result();

    if ($stmt_check_enroll->num_rows > 0) {
        header("Location: index.php?message=Anda sudah bergabung dengan sesi ini.");
        $stmt_check_enroll->close();
        $conn->close();
        exit();
    }
    $stmt_check_enroll->close();
    
    $stmt_enroll = $conn->prepare("INSERT INTO enrollment (user_id, session_id, status) VALUES (?, ?, 'ACTIVE')");
    $stmt_enroll->bind_param("ii", $student_id, $session_id);

    if ($stmt_enroll->execute()) {
        header("Location: index.php?message=Berhasil bergabung dengan sesi tutor!");
    } else {
        header("Location: index.php?error=Gagal bergabung dengan sesi: " . $stmt_enroll->error);
    }
    $stmt_enroll->close();
    $conn->close();
} else {
    header("Location: index.php");
    exit();
}
?>