<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'ADMIN') {
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_name = trim($_POST['course_name']);
    $category = $_POST['category'];
    $semester = filter_input(INPUT_POST, 'semester', FILTER_VALIDATE_INT);

    if (empty($course_name) || !in_array($category, ['UMUM', 'MIPA', 'TEKNIK']) || $semester === false || $semester < 1) {
        header("Location: add_course.php?error=Data tidak lengkap atau tidak valid.");
        exit();
    }

    $stmt_check = $conn->prepare("SELECT course_id FROM mata_kuliah WHERE course_name = ?");
    $stmt_check->bind_param("s", $course_name);
    $stmt_check->execute();
    $stmt_check->store_result();
    if ($stmt_check->num_rows > 0) {
        header("Location: add_course.php?error=Nama mata kuliah sudah ada.");
        $stmt_check->close();
        $conn->close();
        exit();
    }
    $stmt_check->close();

    $stmt = $conn->prepare("INSERT INTO mata_kuliah (course_name, category, semester) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $course_name, $category, $semester);

    if ($stmt->execute()) {
        header("Location: courses.php?message=Mata kuliah berhasil ditambahkan.");
    } else {
        header("Location: add_course.php?error=Gagal menambahkan mata kuliah: " . $stmt->error);
    }
    $stmt->close();
    $conn->close();
} else {
    header("Location: courses.php");
    exit();
}
?>