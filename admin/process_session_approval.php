<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'ADMIN') {
    header("Location: ../auth/login.php?error=Unauthorized");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['session_id']) && isset($_POST['action'])) {
    $session_id = (int)$_POST['session_id'];
    $action = $_POST['action'];
    $admin_id = $_SESSION['user_id']; 

    if ($action == 'approve') {
        $stmt = $conn->prepare("UPDATE sesi_tutor SET status = 'ACCEPTED', tutor_id = ? WHERE session_id = ? AND status = 'PENDING'");
        $stmt->bind_param("ii", $admin_id, $session_id);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                header("Location: index.php?message=Sesi tutor berhasil disetujui.");
            } else {
                header("Location: index.php?error=Sesi tidak ditemukan atau sudah diproses.");
            }
        } else {
            header("Location: index.php?error=Gagal menyetujui sesi: " . $stmt->error);
        }
        $stmt->close();
    } elseif ($action == 'reject') {
        $stmt = $conn->prepare("UPDATE sesi_tutor SET status = 'REJECTED', tutor_id = NULL WHERE session_id = ? AND status = 'PENDING'");

        $stmt->bind_param("i", $session_id);
        if ($stmt->execute()) {
             if ($stmt->affected_rows > 0) {
                header("Location: index.php?message=Sesi tutor berhasil ditolak.");
            } else {
                header("Location: index.php?error=Sesi tidak ditemukan atau sudah diproses.");
            }
        } else {
            header("Location: index.php?error=Gagal menolak sesi: " . $stmt->error);
        }
        $stmt->close();
    } else {
        header("Location: index.php?error=Aksi tidak valid.");
    }
    $conn->close();
} else {
    header("Location: index.php");
    exit();
}
?>