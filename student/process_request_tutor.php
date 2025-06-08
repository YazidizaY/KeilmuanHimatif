<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'STUDENT') {
    header("Location: ../auth/login.php?error=Unauthorized");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $requested_by_student_id = $_SESSION['user_id'];
    $course_id = $_POST['course_id'];
    $material = trim($_POST['material']);
    $session_date = $_POST['session_date'];
    $session_time = $_POST['session_time'];

    if (empty($course_id) || empty($material) || empty($session_date) || empty($session_time)) {
        header("Location: index.php?error=Semua field pada form permintaan harus diisi.");
        exit();
    }

    if (strtotime($session_date) < strtotime(date('Y-m-d'))) {
         header("Location: index.php?error=Tanggal sesi tidak boleh di masa lalu.");
         exit();
    }

    $stmt = $conn->prepare("INSERT INTO sesi_tutor (requested_by_student_id, course_id, material, session_date, session_time, status) VALUES (?, ?, ?, ?, ?, 'PENDING')");
    $stmt->bind_param("iisss", $requested_by_student_id, $course_id, $material, $session_date, $session_time);

    if ($stmt->execute()) {
        header("Location: index.php?message=Permintaan sesi tutor berhasil dikirim. Mohon tunggu persetujuan admin.");
    } else {
        header("Location: index.php?error=Gagal mengirim permintaan: " . $stmt->error);
    }
    $stmt->close();
    $conn->close();
} else {
    header("Location: index.php");
    exit();
}
?>