<?php
session_start();

// Cek apakah user adalah siswa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'siswa') {
    header('Location: login.php');
    exit;
}

$siswa_id = $_SESSION['user_id'];
$quiz_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($quiz_id == 0) {
    header('Location: ../course.php');
    exit;
}

include '../../../../../config.php';

// Ambil data quiz
$quiz_query = "SELECT q.*, c.title as course_title 
               FROM quizzes q
               JOIN courses c ON q.course_id = c.id
               WHERE q.id = $quiz_id";
$quiz_result = mysqli_query($mysqli, $quiz_query);

if (!$quiz_result || mysqli_num_rows($quiz_result) == 0) {
    header('Location: ../course.php');
    exit;
}

$quiz = mysqli_fetch_assoc($quiz_result);

// Ambil semua soal
$questions_query = "SELECT * FROM questions WHERE quiz_id = $quiz_id ORDER BY id ASC";
$questions_result = mysqli_query($mysqli, $questions_query);
$total_questions = mysqli_num_rows($questions_result);

// Cek apakah sudah pernah mengerjakan
$check_attempt = "SELECT * FROM quiz_attempts WHERE quiz_id = $quiz_id AND siswa_id = $siswa_id ORDER BY attempted_at DESC LIMIT 1";
$attempt_result = mysqli_query($mysqli, $check_attempt);
$last_attempt = mysqli_fetch_assoc($attempt_result);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($quiz['title']); ?> - Quiz</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #FF8C5A 0%, #FF6B35 100%);
            min-height: 100vh;
            padding-top: 20px;
        }
        .quiz-container {
            max-width: 900px;
            margin: 0 auto;
        }
        .quiz-header {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .quiz-header h1 {
            color: #FF6B35;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .timer-display {
            background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
            color: white;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(255, 65, 108, 0.4);
        }
        .question-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-left: 5px solid #FF6B35;
        }
        .question-number {
            background: linear-gradient(135deg, #FF6B35 0%, #FF8C5A 100%);
            color: white;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 10px;
        }
        .option-btn {
            display: block;
            width: 100%;
            text-align: left;
            padding: 15px 20px;
            margin-bottom: 10px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            background: #f8f9fa;
            transition: all 0.3s;
            font-size: 1.05rem;
        }
        .option-btn:hover {
            background: #e7f3ff;
            border-color: #FF6B35;
            transform: translateX(5px);
        }
        .option-btn.selected {
            background: linear-gradient(135deg, #FF6B35 0%, #FF8C5A 100%);
            color: white;
            border-color: #FF6B35;
            transform: translateX(10px);
        }
        .option-btn.correct {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border-color: #28a745;
        }
        .option-btn.wrong {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            border-color: #dc3545;
        }
        .option-label {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            background: #FF6B35;
            color: white;
            border-radius: 50%;
            margin-right: 15px;
            font-weight: bold;
        }
        .btn-submit {
            background: linear-gradient(135deg, #FF6B35 0%, #FF8C5A 100%);
            border: none;
            padding: 15px 40px;
            border-radius: 25px;
            font-weight: 700;
            font-size: 1.1rem;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.5);
        }
        .btn-submit:disabled {
            opacity: 0.6;
            transform: none;
            box-shadow: none;
        }
        .progress-container {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        .progress-bar-custom {
            height: 10px;
            background: #e9ecef;
            border-radius: 5px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(135deg, #FF6B35 0%, #FF8C5A 100%);
            width: 0%;
            transition: width 0.5s ease;
        }
    </style>
</head>
<body>
    <div class="container quiz-container">
        <!-- Quiz Header -->
        <div class="quiz-header">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h1><?php echo htmlspecialchars($quiz['title']); ?></h1>
                    <p class="text-muted mb-2"><i class="bi bi-book"></i> <?php echo htmlspecialchars($quiz['course_title']); ?></p>
                    <span class="badge bg-primary"><?php echo $quiz['type'] == 'kuis' ? 'Kuis' : 'Ujian Akhir'; ?></span>
                    <span class="badge bg-success ms-2"><i class="bi bi-percent"></i> Passing: <?php echo $quiz['passing_score']; ?>%</span>
                </div>
                <div class="text-end">
                    <div class="h3 mb-0">
                        <i class="bi bi-list-ul"></i> <?php echo $total_questions; ?> Soal
                    </div>
                </div>
            </div>
            
            <!-- Timer -->
            <div class="timer-display" id="timer">
                <i class="bi bi-clock me-2"></i> <span id="time">30:00</span>
            </div>

            <!-- Progress Bar -->
            <div class="progress-container">
                <div class="d-flex justify-content-between mb-2">
                    <small class="fw-bold">Progress</small>
                    <small class="fw-bold"><span id="currentQuestion">0</span> / <?php echo $total_questions; ?></small>
                </div>
                <div class="progress-bar-custom">
                    <div class="progress-fill" id="progressBar"></div>
                </div>
            </div>
        </div>

        <!-- Quiz Form -->
        <form id="quizForm" method="POST" action="quiz_submit.php">
            <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
            <input type="hidden" name="total_questions" value="<?php echo $total_questions; ?>">
            <input type="hidden" name="time_spent" id="timeSpent" value="1800">

            <!-- Questions -->
            <?php 
            $question_num = 1;
            while($question = mysqli_fetch_assoc($questions_result)): 
            ?>
                <div class="question-card" id="question-<?php echo $question_num; ?>">
                    <div class="d-flex align-items-start mb-3">
                        <div class="question-number"><?php echo $question_num; ?></div>
                        <h5 class="mb-0 flex-grow-1"><?php echo htmlspecialchars($question['question']); ?></h5>
                    </div>

                    <div class="options-container">
                        <?php 
                        $options = [
                            'A' => $question['option_a'],
                            'B' => $question['option_b'],
                            'C' => $question['option_c'],
                            'D' => $question['option_d']
                        ];
                        
                        foreach($options as $key => $option): 
                        ?>
                            <label class="option-btn" onclick="selectOption(this, <?php echo $question_num; ?>, '<?php echo $key; ?>')">
                                <input type="radio" name="answers[<?php echo $question['id']; ?>]" value="<?php echo $key; ?>" class="d-none">
                                <span class="option-label"><?php echo $key; ?></span>
                                <?php echo htmlspecialchars($option); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php 
            $question_num++;
            endwhile; 
            ?>

            <!-- Submit Button -->
            <div class="text-center mt-4">
                <button type="submit" class="btn btn-submit" id="submitBtn" disabled>
                    <i class="bi bi-check-circle me-2"></i>Selesai & Submit
                </button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Timer
        let timeLeft = 1800; // 30 minutes in seconds
        const timerDisplay = document.getElementById('time');
        const timeSpentInput = document.getElementById('timeSpent');
        
        const timer = setInterval(() => {
            timeLeft--;
            timeSpentInput.value = timeLeft;
            
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            
            timerDisplay.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            // Warning when 5 minutes left
            if (timeLeft <= 300 && timeLeft > 0) {
                document.getElementById('timer').style.background = 'linear-gradient(135deg, #ff9a9e 0%, #fad0c4 100%)';
            }
            
            // Auto submit when time is up
            if (timeLeft <= 0) {
                clearInterval(timer);
                alert('Waktu habis! Quiz akan otomatis disubmit.');
                document.getElementById('quizForm').submit();
            }
        }, 1000);

        // Answer tracking
        const totalQuestions = <?php echo $total_questions; ?>;
        let answeredCount = 0;
        const answers = {};

        function selectOption(element, questionNum, option) {
            // Remove selected class from all options in this question
            const questionCard = element.closest('.question-card');
            const options = questionCard.querySelectorAll('.option-btn');
            
            options.forEach(opt => opt.classList.remove('selected'));
            
            // Add selected class to clicked option
            element.classList.add('selected');
            
            // Store answer
            const questionId = questionCard.querySelector('input[type="radio"]').name.match(/\[(\d+)\]/)[1];
            answers[questionId] = option;
            
            // Update progress
            if (!element.dataset.answered) {
                element.dataset.answered = 'true';
                answeredCount++;
                updateProgress();
            }
        }

        function updateProgress() {
            // Update progress bar
            const progressPercentage = (answeredCount / totalQuestions) * 100;
            document.getElementById('progressBar').style.width = progressPercentage + '%';
            document.getElementById('currentQuestion').textContent = answeredCount;
            
            // Enable submit button if all questions answered
            const submitBtn = document.getElementById('submitBtn');
            if (answeredCount === totalQuestions) {
                submitBtn.disabled = false;
                submitBtn.classList.add('pulse');
            }
        }

        // Form validation
        document.getElementById('quizForm').addEventListener('submit', function(e) {
            if (answeredCount < totalQuestions) {
                e.preventDefault();
                alert('Mohon jawab semua pertanyaan sebelum submit!');
                return false;
            }
            
            if (!confirm('Apakah kamu yakin ingin submit quiz?')) {
                e.preventDefault();
                return false;
            }
        });
    </script>
</body>
</html>