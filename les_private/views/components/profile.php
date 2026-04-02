<?php
session_start();
include '../../config.php'; // isinya $mysqli = new mysqli(...);

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$success = '';
$error = '';

// Ambil data user
$query = mysqli_query($mysqli, "SELECT * FROM user WHERE id = $user_id");
$user = mysqli_fetch_assoc($query);

// Update profile
if (isset($_POST['update'])) {
    $name     = mysqli_real_escape_string($mysqli, $_POST['name']);
    $email    = mysqli_real_escape_string($mysqli, $_POST['email']);
    $no_hp = mysqli_real_escape_string($mysqli, $_POST['no_hp']);

    if ($name && $email && $no_hp) {
        $update = mysqli_query($mysqli, "
            UPDATE user SET
                name = '$name',
                email = '$email',
                no_hp = '$no_hp'
            WHERE id = $user_id
        ");

        if ($update) {
            $success = "Profil berhasil diperbarui.";
            // Refresh data
            $query = mysqli_query($mysqli, "SELECT * FROM user WHERE id = $user_id");
            $user = mysqli_fetch_assoc($query);
        } else {
            $error = "Gagal update profil.";
        }
    } else {
        $error = "Semua field wajib diisi.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png">
  <link rel="icon" type="image/png" href="../assets/img/favicon.png">
  <title>
    Profile | EduPrime
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
  <link id="pagestyle" href="https://demos.creative-tim.com/argon-dashboard/assets/css/argon-dashboard.min.css?v=2.1.0" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>



<body class="g-sidenav-show bg-warning">

<main class="main-content position-relative border-radius-lg ">
      <!-- Navbar -->
      <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl " id="navbarBlur" data-scroll="false">
        <div class="container-fluid py-1 px-3">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
              <li class="breadcrumb-item text-sm"><a class="opacity-5 text-white" href="../pages/login.php"><--Dashboard</a></li>
              <li class="breadcrumb-item text-sm text-white active" aria-current="page">Profile</li>
            </ol>
            <h6 class="font-weight-bolder text-white mb-0">Profile</h6>
          </nav>
        </div>
    </nav>

<div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4 p-4">
                    <h4 class="mb-3 text-center">Profil Saya</h4>

                    <?php if ($success): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Nama</label>
                            <input type="text" name="name" class="form-control"
                                   value="<?= htmlspecialchars($user['name']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">No. Telp</label>
                            <input type="text" name="no_hp" class="form-control"
                                   value="<?= htmlspecialchars($user['no_hp']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control"
                                   value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>

                        <button type="submit" name="update" class="btn btn-warning w-100">
                            Simpan Perubahan
                        </button>
                    </form>

            </div>

        </div>
    </div>
</div>

</main>

</body>
</html>
