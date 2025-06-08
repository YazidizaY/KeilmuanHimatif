<?php
// auth/process_register.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/database.php'; // $conn

// Tentukan base_path jika belum ada (untuk redirect dan path file)
// $base_path_server = dirname(dirname(__FILE__)); // Tidak lagi dibutuhkan untuk upload di sini
// $base_path_url = "/" . basename($base_path_server);
// if ($base_path_url == "/your_project_root" || $base_path_url == "/..") $base_path_url = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $npm = trim($_POST['npm']);
    $fullname = trim($_POST['fullname']);
    $birth_date = $_POST['birth_date'];
    $role = 'STUDENT'; 
    $profile_picture_path_db = NULL; // Default path gambar profil adalah NULL

    // Validasi input dasar (sama seperti sebelumnya)
    if (empty($username) || empty($email) || empty($password) || empty($npm) || empty($fullname) || empty($birth_date)) {
        header("Location: register.php?error=Harap isi semua field wajib");
        exit();
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: register.php?error=Format email tidak valid");
        exit();
    }
    if (strlen($password) < 6) {
        header("Location: register.php?error=Password minimal 6 karakter");
        exit();
    }

    // Cek duplikasi username, email, npm (sama seperti sebelumnya)
    $stmt_check = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ? OR npm = ?");
    if (!$stmt_check) {
        header("Location: register.php?error=Gagal mempersiapkan pengecekan data: " . $conn->error);
        exit();
    }
    $stmt_check->bind_param("sss", $username, $email, $npm);
    $stmt_check->execute();
    $stmt_check->store_result();
    if ($stmt_check->num_rows > 0) {
        header("Location: register.php?error=Username, email, atau NPM sudah terdaftar");
        $stmt_check->close();
        $conn->close();
        exit();
    }
    $stmt_check->close();

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt_insert = $conn->prepare("INSERT INTO users (username, email, password, npm, fullname, birth_date, profile_picture_path, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt_insert) {
        header("Location: register.php?error=Gagal mempersiapkan statement insert: " . $conn->error);
        exit();
    }
    
    $stmt_insert->bind_param("ssssssss", $username, $email, $hashed_password, $npm, $fullname, $birth_date, $profile_picture_path_db, $role);

    if ($stmt_insert->execute()) {
        header("Location: login.php?success=Registrasi berhasil. Silakan login.");
    } else {
        header("Location: register.php?error=Registrasi gagal: " . $stmt_insert->error);
    }
    $stmt_insert->close();
    $conn->close();
} else {
    header("Location: register.php");
    exit();
}
?>
