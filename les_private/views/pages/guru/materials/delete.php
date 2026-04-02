<?php 
include '../index.php';

// Ambil ID
$material_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($material_id > 0) {
    // ✅ PREPARED STATEMENT (WAJIB anti SQL Injection)
    $stmt = $mysqli->prepare("DELETE FROM materials WHERE id = ?");
    $stmt->bind_param("i", $material_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Material berhasil dihapus!";
    } else {
        $_SESSION['error'] = "Gagal menghapus: " . $mysqli->error;
    }
}

header('Location: materi.php');
exit();
?>