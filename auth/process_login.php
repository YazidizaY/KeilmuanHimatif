<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/database.php';

$base_path_redirect = "/" . basename(dirname(dirname(__FILE__)));
if ($base_path_redirect == "/your_project_root" || $base_path_redirect == "/..") $base_path_redirect = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username_email = trim($_POST['username_email']);
    $password = $_POST['password'];

    if (empty($username_email) || empty($password)) {
        header("Location: login.php?error=Username/Email dan password harus diisi");
        exit();
    }

    $stmt = $conn->prepare("SELECT user_id, username, password, role, fullname, profile_picture_path FROM users WHERE username = ? OR email = ?");
    if (!$stmt) {
        header("Location: login.php?error=Gagal mempersiapkan statement: " . $conn->error);
        exit();
    }
    $stmt->bind_param("ss", $username_email, $username_email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['profile_picture_path'] = $user['profile_picture_path']; 

            header("Location: " . $base_path_redirect . "/index.php"); 
            exit();
        } else {
            header("Location: login.php?error=Password salah");
            exit();
        }
    } else {
        header("Location: login.php?error=Username atau Email tidak ditemukan");
        exit();
    }
    $stmt->close();
    $conn->close();
} else {
    header("Location: login.php");
    exit();
}
?>
