<?php

include('../../config.php');

if (isset($_POST['register'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash password
    $role = 'siswa';

    if (empty($name) || empty($email) || empty($_POST['password'])) {
        $error = "All fields are required.";
    }

    if (!isset($error)) {
        $checkQuery = "SELECT * FROM user WHERE email='$email'";
        $result = mysqli_query($mysqli, $checkQuery);
        if (mysqli_num_rows($result) > 0) {
            $error = "Email already registered.";
        }
    } 

    if (!isset($error)) {
        $query = "INSERT INTO user (name, email, password, role) VALUES ('$name', '$email', '$password', '$role')";
        if (mysqli_query($mysqli, $query)) {
            header("Location: login.php");
        } else {
            $error = "Error: " . mysqli_error($mysqli);
    }
}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | EduPrime</title>
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
                <?php if (isset($error)): ?>
                    <p style="color: red;"><?php echo $error; ?></p>
                <?php endif; ?>
                <h1>Register</h1>
                <div class="input-box">
                    <input type="text" name="name" placeholder="Nama Lengkap" required>
                    <i class='bx  bx-user'></i>
                </div>
                <div class="input-box">
                    <input type="email" name="email" placeholder="Email" required>
                    <i class='bx  bx-envelope'></i>
                </div>
                <div class="input-box">
                    <input type="password" name="password" placeholder="Password" required>
                    <i class='bx  bx-lock'></i>
                </div>
                <button type="submit" class="btn" name="register">register</button>
                <div class="login-register">
                    <p>Have an account? <a href="login.php" class="register-link">Login</a></p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>