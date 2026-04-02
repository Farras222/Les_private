<?php 
include '../index.php'; 

// Ambil ID user
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Cek apakah user ada
if ($user_id <= 0) {
    $_SESSION['error'] = "ID user tidak valid!";
    header('Location: user.php');
    exit();
}

$result = $mysqli->query("SELECT * FROM user WHERE id = $user_id");
if (!$result || $result->num_rows == 0) {
    $_SESSION['error'] = "User tidak ditemukan!";
    header('Location: user.php');
    exit();
}
$user_data = $result->fetch_assoc();

// Proses update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil & sanitasi input
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);
    $phone = trim($_POST['phone']);
    $alamat = trim($_POST['alamat']);

    // Validasi minimal
    $errors = [];
    if (empty($name)) $errors[] = "Nama tidak boleh kosong";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email tidak valid";
    if (empty($role)) $errors[] = "Role harus dipilih";

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header('Location: edit_user.php?id=' . $user_id);
        exit();
    }

    // Handle password (hash hanya jika diisi)
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    } else {
        $password = $user_data['password']; // pakai password lama
    }

    // ✅ PREPARED STATEMENT (anti SQL Injection)
    $stmt = $mysqli->prepare("UPDATE user SET name = ?, email = ?, password = ?, role = ?, no_hp = ?, alamat = ? WHERE id = ?");
    $stmt->bind_param("ssssssi", $name, $email, $password, $role, $phone, $alamat, $user_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "User berhasil diupdate!";
    } else {
        $_SESSION['error'] = "Gagal update: " . $mysqli->error;
    }

    header('Location: user.php');
    exit();
}
?>


    <style>
    /* Agar form search tetap bagus di berbagai posisi */
#navbar .input-group {
    min-width: 300px;
    max-width: 500px;
}

/* Responsif */
@media (max-width: 768px) {
    #navbar .input-group {
        min-width: 100%;
        max-width: 100%;
    }
    
    #navbar .btn-primary {
        padding-left: 1rem;
        padding-right: 1rem;
    }
}

/* Agar tombol dan input sejajar */
#navbar .input-group-text,
#navbar .form-control,
#navbar .btn {
    height: 38px;
}
.avatar-initial {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #5e72e4; /* bebas, sesuaikan tema */
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 14px;
  text-transform: uppercase;
  padding: 8px;
  margin-right: 10px;
}

   </style>

<body class="g-sidenav-show bg-warning">
  <!-- sidebar -->
  <?php include '../../../components/sidebar.php'; ?>  
    <main class="main-content position-relative border-radius-lg ">
      <!-- Navbar -->
      <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl " id="navbarBlur" data-scroll="false">
        <div class="container-fluid py-1 px-3">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
              <li class="breadcrumb-item text-sm"><a class="opacity-5 text-white" href="javascript:;">Pages / Admin</a></li>
              <li class="breadcrumb-item text-sm text-white active" aria-current="page">Users</li>
            </ol>
            <h6 class="font-weight-bolder text-white mb-0">Users</h6>
          </nav>
        </div>
    </nav>
    <!-- End Navbar -->
    
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <!-- Alert Error -->
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Alert Success -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Alert Validasi -->
                <?php if (isset($_SESSION['errors'])): ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <strong>Perbaiki:</strong>
                        <ul class="mb-0 mt-2">
                            <?php foreach ($_SESSION['errors'] as $err): ?>
                                <li><?php echo $err; ?></li>
                            <?php endforeach; unset($_SESSION['errors']); ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <div class="card mb-4 p-4   ">
                    <?php if ($user_data): ?>
                    <form method="POST">
                        <div class="card-title pb-2">
                            <h3>Edit User</h3>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Name</label>
                                <input type="text" value="<?php echo htmlspecialchars($user_data['name']); ?>"  class="form-control" id="name" name="name" required />
                            </div>
                            </div>
                            <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                            </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" placeholder="Password" class="form-control" id="password" name="password" />
                            </div>
                            </div>
                            <div class="col-md-6">
                            <div class="form-group">
                                <label for="role">Role</label>
                                <select class="form-control" id="role" name="role">
                                    <option value="admin" <?php echo $user_data['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                    <option value="user" <?php echo $user_data['role'] == 'user' ? 'selected' : ''; ?>>User</option>
                                    <option value="guru" <?php echo $user_data['role'] == 'guru' ? 'selected' : ''; ?>>Guru</option>
                                    <option value="siswa" <?php echo $user_data['role'] == 'siswa' ? 'selected' : ''; ?>>Siswa</option>
                                </select>
                            </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone">No. Hp</label>
                                <input type="phone"  value="<?php echo htmlspecialchars($user_data['no_hp']); ?>"  placeholder="No. Hp" class="form-control" id="phone" name="phone" />
                            </div>
                            </div>
                            <div class="col-md-6">
                            <div class="form-group">
                                <label for="alamat">Alamat</label>
                                <textarea type="text" placeholder="Alamat" class="form-control" id="alamat" name="alamat" ><?php echo htmlspecialchars($user_data['alamat']); ?></textarea>
                            </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success mt-3" >Simpan</button>
                        <a href="user.php" class="btn btn-danger mt-3">Batal</a>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- footer -->
        <?php include '../../../components/footer.php'; ?>
    </main>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
  </body>
</html>