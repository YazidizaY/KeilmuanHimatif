<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

if ($_SESSION['role'] == 'ADMIN') {
    header("Location: admin/index.php");
    exit();
} elseif ($_SESSION['role'] == 'STUDENT') {
    header("Location: student/index.php");
    exit();
} else {
    header("Location: auth/login.php?error=invalidrole");
    exit();
}
?>