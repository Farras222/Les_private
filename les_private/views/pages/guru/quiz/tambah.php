<?php
include '../index.php'; 

// Ambil data course untuk dropdown
$courses_query = "SELECT id, title FROM courses ORDER BY title";
$courses_result = mysqli_query($mysqli, $courses_query);

// Ambil data material untuk dropdown
$materials_query = "SELECT id, title FROM materials ORDER BY title";
$materials_result = mysqli_query($mysqli, $materials_query);

// Proses form jika ada POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['step'])) {
    if ($_POST['step'] == '1') {
        // Simpan data sementara di session
        $_SESSION['quiz_data'] = [
            'course_id' => $_POST['course_id'],
            'material_id' => $_POST['material_id'],
            'title' => $_POST['title'],
            'type' => $_POST['type'],
            'passing_score' => $_POST['passing_score'],
            'num_questions' => $_POST['num_questions']
        ];
        header('Location: soal.php');
        exit;
    }
}
?>

    <style>
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            font-weight: 600;
        }
        .form-control, .form-select {
            border-radius: 10px;
            padding: 10px 15px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            transition: transform 0.2s;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }
        .step {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 10px;
            font-weight: bold;
            color: #6c757d;
            position: relative;
        }
        .step.active {
            background: #667eea;
            color: white;
        }
        .step.completed {
            background: #28a745;
            color: white;
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
              <li class="breadcrumb-item text-sm"><a class="opacity-5 text-white" href="javascript:;">Pages / Guru</a></li>
              <li class="breadcrumb-item text-sm text-white active" aria-current="page">Quiz</li>
            </ol>
            <h6 class="font-weight-bolder text-white mb-0">Quiz</h6>
          </nav>
        </div>
    </nav>
    <!-- End Navbar -->
    <div class="container mt-5">
        <div class="step-indicator">
            <div class="step active">1</div>
            <div class="step">2</div>
        </div>
        
        <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4 p-4   ">
            <div class="card-header">
                <i class="bi bi-file-earmark-text me-2"></i> Buat Quiz Baru
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="step" value="1">
                    
                    <div class="mb-4">
                        <label class="form-label"><i class="bi bi-book me-2"></i> Mata Pelajaran</label>
                        <select name="course_id" class="form-select" required>
                            <option value="">Pilih Mata Pelajaran</option>
                            <?php while($course = mysqli_fetch_assoc($courses_result)): ?>
                                <option value="<?= $course['id'] ?>"><?= htmlspecialchars($course['title']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label"><i class="bi bi-file-earmark me-2"></i> Materi</label>
                        <select name="material_id" class="form-select">
                            <option value="">Pilih Materi (Opsional)</option>
                            <?php while($material = mysqli_fetch_assoc($materials_result)): ?>
                                <option value="<?= $material['id'] ?>"><?= htmlspecialchars($material['title']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label"><i class="bi bi-card-text me-2"></i> Judul Quiz</label>
                        <input type="text" name="title" class="form-control" placeholder="Masukkan judul quiz" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label"><i class="bi bi-tag me-2"></i> Tipe Quiz</label>
                        <select name="type" class="form-select" required>
                            <option value="kuis">Kuis</option>
                            <option value="ujian_akhir">Ujian Akhir</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label"><i class="bi bi-percent me-2"></i> Nilai Kelulusan (%)</label>
                        <input type="number" name="passing_score" class="form-control" min="0" max="100" value="70" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label"><i class="bi bi-list-ol me-2"></i> Jumlah Soal</label>
                        <input type="number" name="num_questions" class="form-control" min="1" max="50" value="10" required>
                        <small class="text-muted">Maksimal 50 soal</small>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-arrow-right-circle me-2"></i> Lanjut ke Soal
                        </button>
                    </div>
                </form>
            </div>
        </div>
        </div>
        <?php include '../../../components/footer.php'; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>