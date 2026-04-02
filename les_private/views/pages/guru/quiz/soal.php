<?php
include '../index.php'; 

$quiz_data = $_SESSION['quiz_data'];
$num_questions = $quiz_data['num_questions'];

// Proses penyimpanan quiz
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $questions = $_POST['questions'];
    
    // Mulai transaction
    mysqli_begin_transaction($mysqli);
    
    try {
        // Simpan data quiz
        $course_id = mysqli_real_escape_string($mysqli, $quiz_data['course_id']);
        $material_id = mysqli_real_escape_string($mysqli, $quiz_data['material_id']);
        $title = mysqli_real_escape_string($mysqli, $quiz_data['title']);
        $type = mysqli_real_escape_string($mysqli, $quiz_data['type']);
        $passing_score = mysqli_real_escape_string($mysqli, $quiz_data['passing_score']);
        
        $insert_quiz = "INSERT INTO quizzes (course_id, material_id, title, type, passing_score, created_at) 
                        VALUES ('$course_id', " . ($material_id ? "'$material_id'" : "NULL") . ", '$title', '$type', '$passing_score', NOW())";
        
        if (!mysqli_query($mysqli, $insert_quiz)) {
            throw new Exception("Gagal menyimpan data quiz");
        }
        
        $quiz_id = mysqli_insert_id($mysqli);
        
        // Simpan semua soal
        foreach ($questions as $question) {
            $question_text = mysqli_real_escape_string($mysqli, $question['question']);
            $option_a = mysqli_real_escape_string($mysqli, $question['option_a']);
            $option_b = mysqli_real_escape_string($mysqli, $question['option_b']);
            $option_c = mysqli_real_escape_string($mysqli, $question['option_c']);
            $option_d = mysqli_real_escape_string($mysqli, $question['option_d']);
            $correct_answer = mysqli_real_escape_string($mysqli, $question['correct_answer']);
            
            $insert_question = "INSERT INTO questions (quiz_id, question, option_a, option_b, option_c, option_d, correct_answer) 
                               VALUES ('$quiz_id', '$question_text', '$option_a', '$option_b', '$option_c', '$option_d', '$correct_answer')";
            
            if (!mysqli_query($mysqli, $insert_question)) {
                throw new Exception("Gagal menyimpan soal");
            }
        }
        
        // Commit transaction
        mysqli_commit($mysqli);
        
        // Hapus session
        unset($_SESSION['quiz_data']);
        
        // Redirect dengan pesan sukses
        $_SESSION['success_message'] = "Quiz berhasil dibuat!";
        header('Location: quiz.php');
        exit;
        
    } catch (Exception $e) {
        // Rollback jika ada error
        mysqli_rollback($mysqli);
        $error_message = $e->getMessage();
    }
}
?>
    <style>
        .card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
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
        .btn-secondary {
            background: #6c757d;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
        }
        .question-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #667eea;
        }
        .option-group {
            margin-bottom: 15px;
        }
        .option-label {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            background: #667eea;
            color: white;
            border-radius: 50%;
            margin-right: 10px;
            font-weight: bold;
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
            <div class="step completed">1</div>
            <div class="step active">2</div>
        </div>
        
        <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4 p-4   ">
            <div class="card-header">
                <i class="bi bi-pencil-square me-2"></i> Isi Soal Quiz
                <small class="float-end">Soal ke: <span id="current-question">1</span> / <?= $num_questions ?></small>
            </div>
            <div class="card-body">
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i><?= $error_message ?>
                    </div>
                <?php endif; ?>
                
                <div class="mb-4 p-3 bg-light rounded">
                    <h6><i class="bi bi-info-circle me-2"></i> Informasi Quiz</h6>
                    <p class="mb-1"><strong>Judul:</strong> <?= htmlspecialchars($quiz_data['title']) ?></p>
                    <p class="mb-1"><strong>Tipe:</strong> <?= $quiz_data['type'] == 'kuis' ? 'Kuis' : 'Ujian Akhir' ?></p>
                    <p class="mb-1"><strong>Jumlah Soal:</strong> <?= $num_questions ?></p>
                    <p class="mb-0"><strong>Nilai Kelulusan:</strong> <?= $quiz_data['passing_score'] ?>%</p>
                </div>

                <form method="POST" action="">
                    <div class="questions-container">
                        <?php for ($i = 0; $i < $num_questions; $i++): ?>
                            <div class="question-card" id="question-<?= $i + 1 ?>">
                                <h5 class="mb-3">
                                    <i class="bi bi-question-circle me-2"></i> Soal <?= $i + 1 ?>
                                </h5>
                                
                                <div class="mb-3">
                                    <label class="form-label">Pertanyaan</label>
                                    <textarea name="questions[<?= $i ?>][question]" class="form-control" rows="3" required placeholder="Tulis pertanyaan di sini..."></textarea>
                                </div>

                                <div class="mb-3 option-group">
                                    <label class="form-label">
                                        <span class="option-label">A</span> Opsi A
                                    </label>
                                    <textarea name="questions[<?= $i ?>][option_a]" class="form-control" rows="2" required placeholder="Opsi jawaban A..."></textarea>
                                </div>

                                <div class="mb-3 option-group">
                                    <label class="form-label">
                                        <span class="option-label">B</span> Opsi B
                                    </label>
                                    <textarea name="questions[<?= $i ?>][option_b]" class="form-control" rows="2" required placeholder="Opsi jawaban B..."></textarea>
                                </div>

                                <div class="mb-3 option-group">
                                    <label class="form-label">
                                        <span class="option-label">C</span> Opsi C
                                    </label>
                                    <textarea name="questions[<?= $i ?>][option_c]" class="form-control" rows="2" required placeholder="Opsi jawaban C..."></textarea>
                                </div>

                                <div class="mb-3 option-group">
                                    <label class="form-label">
                                        <span class="option-label">D</span> Opsi D
                                    </label>
                                    <textarea name="questions[<?= $i ?>][option_d]" class="form-control" rows="2" required placeholder="Opsi jawaban D..."></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label"><i class="bi bi-check-circle me-2"></i> Jawaban Benar</label>
                                    <select name="questions[<?= $i ?>][correct_answer]" class="form-select" required>
                                        <option value="">Pilih jawaban benar</option>
                                        <option value="A">A</option>
                                        <option value="B">B</option>
                                        <option value="C">C</option>
                                        <option value="D">D</option>
                                    </select>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="create_quiz.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i> Simpan Quiz
                        </button>
                    </div>
                </form>
            </div>
        </div>
        </div>
        </div>
        <?php include '../../../components/footer.php'; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update current question number saat scroll
        window.addEventListener('scroll', function() {
            const questions = document.querySelectorAll('.question-card');
            const scrollPosition = window.scrollY + 200;
            
            questions.forEach((question, index) => {
                const questionTop = question.offsetTop;
                const questionBottom = questionTop + question.offsetHeight;
                
                if (scrollPosition >= questionTop && scrollPosition <= questionBottom) {
                    document.getElementById('current-question').textContent = index + 1;
                }
            });
        });
    </script>
</body>
</html>