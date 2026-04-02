<?php
include '../index.php';

// Ambil data dari POST
$material_id = isset($_POST['material_id']) ? intval($_POST['material_id']) : 0;
$course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
$student_id = $_SESSION['user_id'];

if ($material_id == 0 || $course_id == 0) {
    echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
    exit;
}

// Cek apakah siswa sudah terdaftar di course ini
$check_enroll = "SELECT * FROM course_enrollments WHERE course_id = $course_id AND student_id = $student_id";
$enroll_result = mysqli_query($conn, $check_enroll);

if (mysqli_num_rows($enroll_result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Anda belum terdaftar di course ini']);
    exit;
}

// Cek apakah materi sudah ditandai selesai
$check_read = "SELECT * FROM material_views WHERE material_id = $material_id AND student_id = $student_id AND course_id = $course_id";
$read_result = mysqli_query($conn, $check_read);

if (mysqli_num_rows($read_result) > 0) {
    echo json_encode(['success' => false, 'message' => 'Materi sudah ditandai selesai']);
    exit;
}

// Tandai materi sebagai selesai dibaca
$insert = "INSERT INTO material_views (material_id, student_id, course_id, viewed_at) 
           VALUES ($material_id, $student_id, $course_id, NOW())";

if (mysqli_query($conn, $insert)) {
    echo json_encode(['success' => true, 'message' => 'Materi berhasil ditandai selesai']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menandai materi: ' . mysqli_error($conn)]);
}

mysqli_close($conn);
?>