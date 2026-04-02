<?php
require '../index.php';

$course_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($course_id <= 0) {
    $_SESSION['error'] = 'ID course tidak valid';
    header('Location: course.php');
    exit;
}

// Ambil data course
$stmt = $mysqli->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->bind_param('i', $course_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = 'Course tidak ditemukan';
    header('Location: course.php');
    exit;
}
$course_data = $result->fetch_assoc();
$stmt->close();

// Ambil guru
$gurus = [];
$result = $mysqli->query("SELECT id, name FROM user WHERE role = 'guru'");
while ($row = $result->fetch_assoc()) {
    $gurus[] = $row;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $guru_id     = $_POST['guru'] ?? '';

    if ($title === '')        $errors[] = 'Title tidak boleh kosong';
    if ($description === '') $errors[] = 'Deskripsi tidak boleh kosong';

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

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header('Location: edit.php?id=' . $course_id);
        exit;
    }

    $stmt = $mysqli->prepare(
        "UPDATE courses 
         SET title = ?, description = ?, guru_id = ? 
         WHERE id = ?"
    );
    $stmt->bind_param('ssii', $title, $description, $guru_id, $course_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = 'Course berhasil diupdate!';
    } else {
        $_SESSION['error'] = 'Gagal update course';
    }

    $stmt->close();
    header('Location: course.php');
    exit;
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
                    <?php if ($course_data): ?>
                                        <form method="POST">
                        <div class="card-title pb-2">
                            <h3>Edit Course</h3>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                            <div class="form-group">
                                <label for="title">Title</label>
                                <input type="text" placeholder="Masukan Judul Course" class="form-control" id="title" name="title" value="<?php echo $course_data['title']; ?>" required />
                            </div>
                            </div>
                            <div class="col-md-6">
                            <div class="form-group">
                                <label for="description">Description</label>
                                <input type="text" placeholder="Masukan Deskripsi Course" class="form-control" id="description" name="description" value="<?php echo $course_data['description']; ?>" required />
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
                            </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success mt-3">Simpan</button>
                        <a href="course.php" class="btn btn-danger mt-3">Batal</a>
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