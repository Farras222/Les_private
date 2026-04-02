<?php 
include '../index.php'; 


$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($course_id == 0) {
    header('Location: courses.php');
    exit;
}

// Ambil data course
$course_query = "SELECT c.*, u.name as teacher_name 
                 FROM courses c 
                 LEFT JOIN user u ON c.guru_id = u.id 
                 WHERE c.id = $course_id";
$course_result = mysqli_query($mysqli, $course_query);

if (!$course_result || mysqli_num_rows($course_result) == 0) {
    header('Location: courses.php');
    exit;
}

$course = mysqli_fetch_assoc($course_result);

// Ambil materi course
$materials_query = "SELECT * FROM materials WHERE course_id = $course_id ORDER BY created_at ASC";
$materials_result = mysqli_query($mysqli, $materials_query);

// Ambil quiz course
$quizzes_query = "SELECT * FROM quizzes WHERE course_id = $course_id ORDER BY created_at DESC";
$quizzes_result = mysqli_query($mysqli, $quizzes_query);

// Ambil jumlah siswa yang mengikuti course
$students_query = "SELECT COUNT(*) as total_students FROM student_progress WHERE material_id = $course_id";
$students_result = mysqli_query($mysqli, $students_query);
$students = mysqli_fetch_assoc($students_result);

// Ambil progress siswa (jika sudah login sebagai siswa)
// === PROGRESS SISWA ===
$student_progress = 0;
$completed_material_ids = []; // ← INI PENTING: array kosong default
$is_enrolled = false;

if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] == 'siswa') {
    $student_id = (int)$_SESSION['user_id'];

    // Ambil semua material ID untuk course ini
    mysqli_data_seek($materials_result, 0);
    $material_ids_in_course = [];
    while ($mat = mysqli_fetch_assoc($materials_result)) {
        $material_ids_in_course[] = (int)$mat['id'];
    }
    $total_materials = count($material_ids_in_course);

    // Cek apakah siswa sudah "terdaftar" (minimal ada 1 progress atau tidak)
    // Karena tidak ada tabel enrollment, kita anggap "terdaftar" jika ada record di student_progress
    if (!empty($material_ids_in_course)) {
        $material_list = implode(',', $material_ids_in_course);
        $enroll_check_query = "SELECT 1 FROM student_progress WHERE siswa_id = $student_id AND material_id IN ($material_list) LIMIT 1";
        $is_enrolled = mysqli_query($mysqli, $enroll_check_query) && mysqli_num_rows(mysqli_query($mysqli, $enroll_check_query)) > 0;

        // Ambil daftar material yang sudah diselesaikan
        $completed_query = "SELECT DISTINCT material_id FROM student_progress WHERE siswa_id = $student_id AND material_id IN ($material_list)";
        $completed_result = mysqli_query($mysqli, $completed_query);
        while ($row = mysqli_fetch_assoc($completed_result)) {
            $completed_material_ids[] = (int)$row['material_id'];
        }

        // Hitung persentase
        $student_progress = ($total_materials > 0) ? round((count($completed_material_ids) / $total_materials) * 100) : 0;
    } else {
        $student_progress = 0;
        $is_enrolled = false;
    }

    // Reset pointer materials_result untuk ditampilkan nanti
    mysqli_data_seek($materials_result, 0);
}

$siswa_id = $_SESSION['user_id'] ?? 0;

$quizzes_query = "
SELECT 
    q.*,
    qa.score
FROM quizzes q
LEFT JOIN quiz_attempts qa 
    ON qa.quiz_id = q.id 
    AND qa.siswa_id = $siswa_id
WHERE q.course_id = $course_id
ORDER BY q.created_at DESC
";

$quizzes_result = mysqli_query($mysqli, $quizzes_query);

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
              <li class="breadcrumb-item text-sm"><a class="opacity-5 text-white" href="javascript:;">Pages / Siswa</a></li>
              <li class="breadcrumb-item text-sm text-white active" aria-current="page">Course</li>
            </ol>
            <h6 class="font-weight-bolder text-white mb-0">Course</h6>
          </nav>
          <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
                <form method="GET" action="" class="ms-md-auto pe-md-3 d-flex align-items-center">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control" 
                            placeholder="Cari judul course..." 
                            value="<?php echo htmlspecialchars($search); ?>">
                        <button class="btn btn-warning" type="submit">Cari</button>
                    </div>
                </form>
            </div>
        </div>
    </nav>
    <!-- End Navbar -->

        <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <h6>Course Details</h6>
                    </div>
                    <div class="card-body px-0 pt-0 pb-2 m-4">
                        <div>
                    <h1><?php echo htmlspecialchars($course['title']); ?></h1>
                    <p><?php echo htmlspecialchars($course['description']); ?></p>
                    
                    <div class="mt-3">
                        <span class="info-badge">
                            <i class="bi bi-person"></i> Pengajar: <?php echo htmlspecialchars($course['teacher_name']); ?>
                        </span>
                        <span class="info-badge">
                            <i class="bi bi-file-pdf"></i> Materi PDF: <?php echo mysqli_num_rows($materials_result); ?>
                        </span>
                        <span class="info-badge">
                            <i class="bi bi-file-earmark-text"></i> Quiz: <?php echo mysqli_num_rows($quizzes_result); ?>
                        </span>
                        <span class="info-badge">
                            <i class="bi bi-people"></i> Siswa: <?php echo $students['total_students']; ?>
                        </span>
                    </div>
                </div>
                <div>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'siswa'): ?>
                        <?php
                        // Cek apakah siswa sudah terdaftar
                        $check_enroll = "SELECT * FROM student_progress WHERE material_id = $course_id AND siswa_id = " . $_SESSION['user_id'];
                        $enroll_result = mysqli_query($mysqli, $check_enroll);
                        $is_enrolled = mysqli_num_rows($enroll_result) > 0;
                        ?>
                        
                        <?php if (!$is_enrolled): ?>
                            <button class="btn btn-enroll" onclick="enrollCourse(<?php echo $course_id; ?>)">
                                <i class="bi bi-bookmark-plus"></i> Daftar Course
                            </button>
                        <?php else: ?>
                            <a href="start_course.php?id=<?php echo $course_id; ?>" class="btn btn-start">
                                <i class="bi bi-play-circle"></i> Mulai Belajar
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <!-- Progress Bar (untuk siswa yang sudah terdaftar) -->
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'siswa' && $is_enrolled): ?>
        <div class="progress-container">
            <h5>Progress Belajarmu</h5>
            <div class="progress" style="height: 25px; border-radius: 12px; overflow: hidden;">
                <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $student_progress; ?>%" 
                     aria-valuenow="<?php echo $student_progress; ?>" aria-valuemin="0" aria-valuemax="100">
                    <?php echo $student_progress; ?>%
                </div>
            </div>
            <small class="text-muted mt-2 d-block">
                <?php 
                mysqli_data_seek($materials_result, 0);
                $completed_count = count($completed_material_ids);
                ?>
                Sudah menyelesaikan <?php echo $completed_count; ?> dari <?php echo mysqli_num_rows($materials_result); ?> materi
            </small>
        </div>
        <?php endif; ?>

        <!-- Materi Section -->
        <h3 class="section-title">
            <i class="bi bi-book"></i> Materi Pembelajaran (PDF)
        </h3>
        
        <?php 
        mysqli_data_seek($materials_result, 0);
        if (mysqli_num_rows($materials_result) > 0): 
        ?>
            <div class="row">
                <?php 
                $material_index = 1;
                while($material = mysqli_fetch_assoc($materials_result)): 
                $is_completed = in_array($material['id'], $completed_material_ids);
                // Bangun path absolut ke file
// Ambil dari DB
$db_content = $material['content']; // "/les_private/uploads/materials/file.pdf"
$filename = basename($db_content); // "file.pdf"

// Bangun path absolut
$full_path = __DIR__ . '/../../../../uploads/materials/' . $filename;

// Normalisasi: ganti backslash, handle spasi
$full_path = str_replace('\\', '/', $full_path);
$full_path = realpath($full_path);

// Cek
$real_path = realpath($full_path);
$file_exists = ($real_path !== false && is_file($real_path));
$file_size_mb = $file_exists ? round(filesize($real_path) / 1024 / 1024, 2) : 0;


                $debug = "<!-- DEBUG: DB path = " . htmlspecialchars($material['content']) . " -->\n";
                $debug_full = "<!-- DEBUG: Full path = " . htmlspecialchars($full_path) . " -->\n";
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card material-card">
                        <div class="card-body">
                            <div class="d-flex align-items-start mb-3">
                                <div class="material-icon">
                                    <i class="bi bi-file-pdf"></i>
                                    
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">
                                        <?php echo $material_index . '. ' . htmlspecialchars($material['title']); ?>
                                        <?php if ($is_completed): ?>
                                            <span class="completed-badge ms-2">
                                                <i class="bi bi-check-circle"></i> Selesai
                                            </span>
                                        <?php endif; ?>
                                    </h6>
                                    <small class="text-muted d-block mb-2">
                                        <i class="bi bi-calendar"></i> <?php echo date('d M Y', strtotime($material['created_at'])); ?>
                                    </small>
                                    <span class="pdf-badge">
                                        <i class="bi bi-file-pdf"></i> PDF
                                    </span>
                                    <?php echo $debug; ?>
                                    <?php echo $debug_full; ?>
                                </div>
                            </div>
                            
                            <?php if (!empty($material['description'])): ?>
                                <p class="text-muted small mb-2">
                                    <?php echo htmlspecialchars(substr($material['description'], 0, 80)); ?>...
                                </p>
                            <?php endif; ?>
                            
                            <div class="file-info">
                                <div><i class="bi bi-file-earmark"></i> File: <strong><?php echo htmlspecialchars($material['content']); ?></strong></div>
                                <div><i class="bi bi-hdd-stack"></i> Ukuran: <strong><?php echo $file_size_mb; ?> MB</strong></div>
                            </div>
                            
                            <div class="mt-3 d-flex gap-2">
    <?php if ($file_exists): 
        // Gunakan URL dari DB (sudah berupa path web)
        $web_url = str_replace(' ', '%20', $material['content']); // encode spasi
    ?>
        <a href="<?php echo htmlspecialchars($web_url); ?>" target="_blank" class="btn btn-pdf btn-view flex-grow-1" title="Lihat PDF">
            <i class="bi bi-eye"></i> Lihat
        </a>
        <a href="<?php echo htmlspecialchars($web_url); ?>" download class="btn btn-pdf btn-download" title="Download PDF">
            <i class="bi bi-download"></i>
        </a>
        
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'siswa' && $is_enrolled && !$is_completed): ?>
            <button class="btn btn-sm btn-success" onclick="markAsRead(<?php echo (int)$material['id']; ?>, <?php echo (int)$course_id; ?>)">
                <i class="bi bi-check-circle"></i> Selesai
            </button>
        <?php endif; ?>
    <?php else: ?>
        <button class="btn btn-secondary w-100" disabled>
            <i class="bi bi-exclamation-triangle"></i> File tidak ditemukan
        </button>
    <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php 
                $material_index++;
                endwhile; 
                ?>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                <i class="bi bi-info-circle"></i> Belum ada materi PDF untuk course ini.
            </div>
        <?php endif; ?>

        <!-- Quiz Section -->
        <h3 class="section-title mt-5">
            <i class="bi bi-question-circle"></i> Quiz & Ujian
        </h3>
        
        <?php 
        mysqli_data_seek($quizzes_result, 0);
        if (mysqli_num_rows($quizzes_result) > 0): 
        ?>
            <div class="row">
                <?php while($quiz = mysqli_fetch_assoc($quizzes_result)): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card quiz-card">
                        <div class="card-body d-flex align-items-center">
                            <div class="quiz-icon">
                                <i class="bi bi-question-circle"></i>
                            </div>
                            <div>
                                <h6 class="mb-1"><?php echo htmlspecialchars($quiz['title']); ?></h6>
                                <small class="text-muted">
                                    <?php echo $quiz['type'] == 'kuis' ? 'Kuis' : 'Ujian Akhir'; ?> - 
                                    Nilai: <?php echo $quiz['passing_score']; ?>%
                                </small>
                            </div>
                           <?php if (!is_null($quiz['score'])): ?>
    <?php if ($quiz['score'] >= $quiz['passing_score']): ?>
        <span class="btn btn-sm btn-success ms-auto">
            <i class="bi bi-check-circle"></i> Lulus (<?php echo $quiz['score']; ?>)
        </span>
    <?php else: ?>
        <a href="quiz/quiz_start.php?id=<?php echo $quiz['id']; ?>" 
           class="btn btn-sm btn-warning ms-auto">
            <i class="bi bi-arrow-repeat"></i> Ulangi
        </a>
    <?php endif; ?>
<?php else: ?>
    <a href="quiz/quiz_start.php?id=<?php echo $quiz['id']; ?>" 
       class="btn btn-sm btn-danger ms-auto">
        <i class="bi bi-play-circle"></i> Mulai Kuis
    </a>
<?php endif; ?>

                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                <i class="bi bi-info-circle"></i> Belum ada quiz untuk course ini.
            </div>
        <?php endif; ?>
            </div>
            </div>
        </div>
    </div>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function enrollCourse(courseId) {
            if (confirm('Apakah kamu yakin ingin mendaftar course ini?')) {
                window.location.href = 'enroll_course.php?course_id=' + courseId;
            }
        }
        
        function markAsRead(materialId, courseId) {
            if (confirm('Tandai materi ini sebagai sudah dibaca?')) {
                fetch('mark_material_read.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'material_id=' + materialId + '&course_id=' + courseId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Materi ditandai sebagai selesai!');
                        location.reload();
                    } else {
                        alert('Gagal menandai materi: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Terjadi kesalahan: ' + error);
                });
            }
        }
    </script>
</body>