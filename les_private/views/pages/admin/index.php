<?php
session_start();
include '../../../../config.php';

function admin_auth() {
    global $mysqli;
    
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error_message'] = "Silakan login terlebih dahulu!";
        header('Location: ../../login.php');
        exit();
    }
    
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        $_SESSION['error_message'] = "Akses ditolak!";
        header('Location: ../../login.php');
        exit();
    }
    
    // Cek session timeout
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > 4800) {
        session_unset();
        session_destroy();
        $_SESSION['error_message'] = "Session berakhir. Login ulang.";
        header('Location: ../../login.php?timeout=1');
        exit();
    }
    
    $_SESSION['last_activity'] = time();
    
    // Cek akun masih aktif
    $query = "SELECT id FROM user WHERE id = '{$_SESSION['user_id']}' AND role = 'admin'";
    $result = mysqli_query($mysqli, $query);
    
    if (!$result || mysqli_num_rows($result) === 0) {
        session_unset();
        session_destroy();
        $_SESSION['error_message'] = "Akun tidak valid!";
        header('Location: ../../login.php');
        exit();
    }
}

// Panggil validasi
admin_auth();

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Ambil data admin
$admin_id = $_SESSION['user_id'];
$admin_query = "SELECT * FROM user WHERE id = '$admin_id'";
$admin = mysqli_fetch_assoc(mysqli_query($mysqli, $admin_query));

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png">
  <link rel="icon" type="image/png" href="../assets/img/favicon.png">
  <title>
    Admin Dashboard | EduPrime
  </title>
  <!--     Fonts and icons     -->
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
  <!-- Nucleo Icons -->
  <link href="https://demos.creative-tim.com/argon-dashboard-pro/assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="https://demos.creative-tim.com/argon-dashboard-pro/assets/css/nucleo-svg.css" rel="stylesheet" />
  <!-- Font Awesome Icons -->
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <link href="https://demos.creative-tim.com/argon-dashboard-pro/assets/css/nucleo-svg.css" rel="stylesheet" />
  <!-- CSS Files -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../../../assets/css/style.css">
  <link id="pagestyle" href="https://demos.creative-tim.com/argon-dashboard/assets/css/argon-dashboard.min.css?v=2.1.0" rel="stylesheet" />
</head>