<?php
session_start();
include '../../config.php';

$error_message = '';

if (isset($_SESSION['user_id'])) {
    // Pengguna sudah login, redirect berdasarkan role
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin/dashboard");
    } else if ($_SESSION['role'] == 'guru') {
        header("Location: guru/dashboard");
    } else {
        header("Location: siswa/dashboard");
    }
    exit;
}

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $query = "SELECT * FROM user WHERE email = '$email'";
    $result = mysqli_query($mysqli, $query);

    if ($user = mysqli_fetch_assoc($result)) {
        // Verifikasi password
        if (password_verify($password, $user['password'])) {
            // Password benar, set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect berdasarkan role
            if ($user['role'] == 'admin') {
                header("Location: admin/dashboard");
            } else if ($user['role'] == 'guru') {
                header("Location: guru/dashboard");
            } else {
                header("Location: siswa/dashboard");
            }
            exit;
        } else {
            echo "Password salah!";
        }
    } else {
        $error_message = "User tidak ditemukan";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | EduPrime</title>
    <link href="https://cdn.boxicons.com/3.0.8/fonts/basic/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/login.css">
</head>
<body>
    <div class="container">
        <!-- <div class="img-box">
            <img src="assets/img/01.jpg" alt="">
        </div> -->
        <div class="form-box login">
            <form action="" method="POST">
                <h1>Login</h1>
                <p><?php echo $error_message ?></p>
                <div class="input-box">
                    <input type="email" name="email" placeholder="Email" required>
                    <i class='bx  bx-envelope'></i>
                </div>
                <div class="input-box">
                    <input type="password" name="password" placeholder="Password" required>
                    <i class='bx  bx-lock'></i>
                </div>
                <div class="forgot-link">
                    <a href="#">Forgot Password?</a>
                </div>
                <button type="submit" class="btn" name="login">Login</button>
                <div class="login-register">
                    <p>Don't have an account? <a href="register.php" class="register-link">Register</a></p>
                </div>
                <div>
                    <p>Pergi ke <a href="../../index.php">Home</a></p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>