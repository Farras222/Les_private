<?php
require '../index.php'; 

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

if (!in_array($_SESSION['role'], ['admin', 'superadmin'])) {
    header('Location: ../unauthorized.php');
    exit;
}

$gurus = [];
$result = $mysqli->query("SELECT id, name FROM user WHERE role = 'guru'");
while ($row = $result->fetch_assoc()) {
    $gurus[] = $row;
}

$errors  = [];
$success = false;

$title       = '';
$description = '';
$guru_id     = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $guru_id     = $_POST['guru'] ?? '';

    // --- VALIDASI ---
    if ($title === '') {
        $errors[] = 'Title tidak boleh kosong';
    } elseif (strlen($title) < 3) {
        $errors[] = 'Title minimal 3 karakter';
    }

    if ($description === '') {
        $errors[] = 'Description tidak boleh kosong';
    } elseif (strlen($description) < 10) {
        $errors[] = 'Description minimal 10 karakter';
    }

    if ($guru_id === '') {
        $errors[] = 'Guru harus dipilih';
    } else {
        $stmt = $mysqli->prepare(
            "SELECT id FROM user WHERE id = ? AND role = 'guru'"
        );
        $stmt->bind_param('i', $guru_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 0) {
            $errors[] = 'Guru tidak valid';
        }
        $stmt->close();
    }

    // --- SIMPAN ---
    if (empty($errors)) {
        $stmt = $mysqli->prepare(
            "INSERT INTO courses (title, description, guru_id)
             VALUES (?, ?, ?)"
        );
        $stmt->bind_param('ssi', $title, $description, $guru_id);

        if ($stmt->execute()) {
            $_SESSION['flash_message'] = 'Course berhasil ditambahkan!';
            $_SESSION['flash_type']    = 'success';
            header('Location: course.php');
            exit;
        }

        $errors[] = 'Gagal menyimpan data';
        $stmt->close();
    }
}
$flash_message = $_SESSION['flash_message'] ?? '';
$flash_type    = $_SESSION['flash_type'] ?? '';
unset($_SESSION['flash_message'], $_SESSION['flash_type']);
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
              <li class="breadcrumb-item text-sm text-white active" aria-current="page">Courses</li>
            </ol>
            <h6 class="font-weight-bolder text-white mb-0">Courses</h6>
          </nav>
        </div>
    </nav>
    <!-- End Navbar -->
    
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4 p-4   ">
                    <!-- FLASH MESSAGE -->
            <?php if($flash_message): ?>
            <div class="alert alert-<?php echo $flash_type; ?> alert-dismissible fade show" role="alert">
              <span class="alert-icon"><i class="fas fa-check"></i></span>
              <span class="alert-text"><?php echo $flash_message; ?></span>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
            
            <!-- ERROR MESSAGE -->
            <?php if(!empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <span class="alert-icon"><i class="fas fa-exclamation-triangle"></i></span>
              <span class="alert-text">
                <strong>Oops! Ada kesalahan:</strong>
                <ul class="mb-0 mt-2">
                  <?php foreach($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                  <?php endforeach; ?>
                </ul>
              </span>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

                    <form method="POST">
                        <div class="card-title pb-2">
                            <h3>Tambah Course</h3>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                            <div class="form-group">
                                <label for="title">Title</label>
                                <input type="text" placeholder="Masukan Judul Course" class="form-control" id="title" name="title" required />
                            </div>
                            </div>
                            <div class="col-md-6">
                            <div class="form-group">
                                <label for="description">Description</label>
                                <input type="text" placeholder="Masukan Deskripsi Course" class="form-control" id="description" name="description" required />
                            </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                            <div class="form-group">
                                <label for="guru">Guru</label>
                                <select class="form-control <?php echo (isset($_POST['submit']) && empty($guru_id)) ? 'is-invalid' : ''; ?>" 
                            id="guru" 
                            name="guru" 
                            required>
                      <option value="">-- Pilih Guru --</option>
                      <?php if(empty($gurus)): ?>
                        <option value="">Tidak ada guru tersedia</option>
                      <?php else: ?>
                        <?php foreach ($gurus as $guru): ?>
                          <option value="<?php echo $guru['id']; ?>" 
                            <?php echo (isset($_POST['submit']) && $guru_id == $guru['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($guru['name']); ?>
                          </option>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </select>
                    <?php if(isset($_POST['submit']) && empty($guru_id)): ?>
                      <div class="invalid-feedback">Guru harus dipilih</div>
                    <?php endif; ?>
                            </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success mt-3">Simpan</button>
                        <a href="course.php" class="btn btn-danger mt-3">Batal</a>
                    </form>
                </div>
            </div>
        </div>

        <!-- footer -->
        <?php include '../../../components/footer.php'; ?>
    </main>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
  </body>
</html>