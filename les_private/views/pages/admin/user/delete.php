<?php 
include '../index.php';

// Ambil ID
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($user_id > 0) {
    // ✅ PREPARED STATEMENT (WAJIB anti SQL Injection)
    $stmt = $mysqli->prepare("DELETE FROM user WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "User berhasil dihapus!";
    } else {
        $_SESSION['error'] = "Gagal menghapus: " . $mysqli->error;
    }
}

header('Location: user.php');
exit();
?>