<?php 
include '../index.php';

// Ambil ID
$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($course_id > 0) {
    // ✅ PREPARED STATEMENT (WAJIB anti SQL Injection)
    $stmt = $mysqli->prepare("DELETE FROM courses WHERE id = ?");
    $stmt->bind_param("i", $course_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Course berhasil dihapus!";
    } else {
        $_SESSION['error'] = "Gagal menghapus: " . $mysqli->error;
    }
}

header('Location: course.php');
exit();
?>