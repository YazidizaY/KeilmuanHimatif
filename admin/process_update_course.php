<?php
session_start();
require_once '../config/database.php';

$base_path = "/" . basename(dirname(dirname(__FILE__)));
if ($base_path == "/your_project_root" || $base_path == "/..") $base_path = "";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'ADMIN') {
    header("Location: " . $base_path . "/auth/login.php?error=Unauthorized");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_id = filter_input(INPUT_POST, 'course_id', FILTER_VALIDATE_INT);
    $course_name = trim($_POST['course_name']);
    $category = $_POST['category'];
    $semester = filter_input(INPUT_POST, 'semester', FILTER_VALIDATE_INT);

    if (!$course_id || empty($course_name) || !in_array($category, ['UMUM', 'MIPA', 'TEKNIK']) || $semester === false || $semester < 1) {
        header("Location: " . $base_path . "/admin/edit_course.php?course_id=" . $course_id . "&error=Data tidak lengkap atau tidak valid.");
        exit();
    }

    $stmt_check = $conn->prepare("SELECT course_id FROM mata_kuliah WHERE course_name = ? AND course_id != ?");
    if (!$stmt_check) {
        header("Location: " . $base_path . "/admin/edit_course.php?course_id=" . $course_id . "&error=Gagal mempersiapkan statement (check duplikasi).");
        exit();
    }
    $stmt_check->bind_param("si", $course_name, $course_id);
    $stmt_check->execute();
    $stmt_check->store_result();
    if ($stmt_check->num_rows > 0) {
        header("Location: " . $base_path . "/admin/edit_course.php?course_id=" . $course_id . "&error=Nama mata kuliah sudah ada.");
        $stmt_check->close();
        $conn->close();
        exit();
    }
    $stmt_check->close();

    $stmt_update = $conn->prepare("UPDATE mata_kuliah SET course_name = ?, category = ?, semester = ? WHERE course_id = ?");
    if (!$stmt_update) {
        header("Location: " . $base_path . "/admin/edit_course.php?course_id=" . $course_id . "&error=Gagal mempersiapkan statement (update).");
        exit();
    }
    $stmt_update->bind_param("ssii", $course_name, $category, $semester, $course_id);

    if ($stmt_update->execute()) {
        if ($stmt_update->affected_rows > 0) {
            header("Location: " . $base_path . "/admin/courses.php?message=Data mata kuliah berhasil diperbarui.");
        } else {
            header("Location: " . $base_path . "/admin/courses.php?message=Tidak ada perubahan pada data mata kuliah.");
        }
    } else {
        header("Location: " . $base_path . "/admin/edit_course.php?course_id=" . $course_id . "&error=Gagal memperbarui data mata kuliah: " . $stmt_update->error);
    }
    $stmt_update->close();
    $conn->close();
} else {
    header("Location: " . $base_path . "/admin/courses.php");
    exit();
}
?>
