<?php
session_start();

// Cek apakah user adalah siswa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'siswa') {
    header('Location: login.php');
    exit;
}

if (!isset($_SESSION['quiz_result'])) {
    header('Location: quiz_list.php');
    exit;
}

$result = $_SESSION['quiz_result'];
$quiz_id = $result['quiz_id'];
$siswa_id = $_SESSION['user_id'];

include '../../../../../config.php';

// Ambil data quiz
$quiz_query = "SELECT q.*, c.title as course_title 
               FROM quizzes q
               JOIN courses c ON q.course_id = c.id
               WHERE q.id = $quiz_id";
$quiz_result_db = mysqli_query($mysqli, $quiz_query);
$quiz = mysqli_fetch_assoc($quiz_result_db);

// Ambil semua soal dan jawaban
$questions_query = "SELECT * FROM questions WHERE quiz_id = $quiz_id ORDER BY id ASC";
$questions_result = mysqli_query($mysqli, $questions_query);

// Hapus session setelah ditampilkan
unset($_SESSION['quiz_result']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Quiz - <?php echo htmlspecialchars($quiz['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e7f4 100%);
            min-height: 100vh;
            padding-top: 20px;
        }
        .result-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .score-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            position: relative;
            overflow: hidden;
        }
        .score-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(102,126,234,0.1) 0%, rgba(255,255,255,0) 70%);
            z-index: 0;
        }
        .score-display {
            position: relative;
            z-index: 1;
        }
        .score-number {
            font-size: 6rem;
            font-weight: 800;
            background: linear-gradient(135deg, #FF6B35 0%, #FF8C5A 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
        }
        .score-badge {
            display: inline-block;
            padding: 10px 30px;
            border-radius: 25px;
            font-weight: 700;
            font-size: 1.2rem;
            margin-top: 15px;
        }
        .badge-passed {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }
        .badge-failed {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-top: 20px;
        }
        .stat-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
        }
        .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            color: #667eea;
        }
        .stat-label {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 5px;
        }
        .question-review {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-left: 5px solid #FF6B35;
        }
        .question-review.wrong {
            border-left-color: #dc3545;
            background: #fff5f5;
        }
        .question-review.correct {
            border-left-color: #28a745;
            background: #f8fff8;
        }
        .question-header {
            display: flex;
            align-items: start;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
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
            margin-right: 15px;
            flex-shrink: 0;
        }
        .question-number.wrong {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        }
        .question-number.correct {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }
        .option-review {
            padding: 12px 15px;
            margin-bottom: 8px;
            border-radius: 8px;
            border-left: 4px solid #e9ecef;
        }
        .option-review.correct {
            background: #d4edda;
            border-left-color: #28a745;
            font-weight: 600;
        }
        .option-review.wrong {
            background: #f8d7da;
            border-left-color: #dc3545;
            text-decoration: line-through;
        }
        .option-review.selected {
            background: #d1ecf1;
            border-left-color: #FF6B35;
        }
        .btn-action {
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            margin: 5px;
        }
        .btn-home {
            background: linear-gradient(135deg, #FF6B35 0%, #FF8C5A 100%);
            border: none;
            color: white;
        }
        .btn-review {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            color: white;
        }
        .confetti {
            position: fixed;
            width: 10px;
            height: 10px;
            background: #ff0;
            border-radius: 50%;
        }
    </style>
</head>
<body>
    <div class="container result-container">
        <!-- Score Card -->
        <div class="score-card">
            <div class="score-display">
                <div class="score-number"><?php echo $result['score']; ?></div>
                <h4 class="mt-3"><?php echo htmlspecialchars($quiz['title']); ?></h4>
                <span class="score-badge <?php echo $result['passed'] ? 'badge-passed' : 'badge-failed'; ?>">
                    <i class="bi <?php echo $result['passed'] ? 'bi-check-circle' : 'bi-x-circle'; ?>"></i>
                    <?php echo $result['passed'] ? 'LULUS' : 'BELUM LULUS'; ?>
                </span>
                
                <div class="stats-grid mt-4">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $result['correct_count']; ?></div>
                        <div class="stat-label">Benar</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $result['wrong_count']; ?></div>
                        <div class="stat-label">Salah</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $result['total_questions']; ?></div>
                        <div class="stat-label">Total Soal</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="text-center mb-4">
            <a href="../../dashboard/" class="btn btn-action btn-home">
                <i class="bi bi-house-door me-2"></i>Kembali ke Dashboard
            </a>
            <a href="../course.php?id=<?php echo $quiz['course_id']; ?>" class="btn btn-action btn-review">
                <i class="bi bi-book me-2"></i>Kembali ke Course
            </a>
        </div>

        <!-- Question Review -->
        <h4 class="mb-4"><i class="bi bi-list-ul"></i> Pembahasan Soal</h4>
        
        <?php 
        $question_num = 1;
        $wrong_answers = $result['wrong_answers'];
        
        while($question = mysqli_fetch_assoc($questions_result)): 
            $is_wrong = in_array($question['id'], $wrong_answers);
            $is_correct = !$is_wrong;
        ?>
            <div class="question-review <?php echo $is_wrong ? 'wrong' : 'correct'; ?>">
                <div class="question-header">
                    <div class="question-number <?php echo $is_wrong ? 'wrong' : 'correct'; ?>">
                        <?php echo $question_num; ?>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-0"><?php echo htmlspecialchars($question['question']); ?></h6>
                    </div>
                </div>

                <div class="options-review">
                    <?php 
                    $options = [
                        'A' => $question['option_a'],
                        'B' => $question['option_b'],
                        'C' => $question['option_c'],
                        'D' => $question['option_d']
                    ];
                    
                    foreach($options as $key => $option): 
                        $is_correct_answer = $key == $question['correct_answer'];
                        $is_selected_wrong = $is_wrong && $key == ($_POST['answers'][$question['id']] ?? '');
                    ?>
                        <div class="option-review <?php 
                            echo $is_correct_answer ? 'correct' : '';
                            echo $is_selected_wrong ? ' wrong selected' : '';
                        ?>">
                            <strong><?php echo $key; ?>.</strong> <?php echo htmlspecialchars($option); ?>
                            <?php if ($is_correct_answer): ?>
                                <span class="float-end"><i class="bi bi-check-circle"></i></span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php 
        $question_num++;
        endwhile; 
        ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Confetti effect if passed
        <?php if ($result['passed']): ?>
        function createConfetti() {
            const colors = ['#ff416c', '#ff4b2b', '#667eea', '#764ba2', '#28a745', '#20c997'];
            const confettiCount = 100;
            
            for (let i = 0; i < confettiCount; i++) {
                const confetti = document.createElement('div');
                confetti.className = 'confetti';
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.left = Math.random() * 100 + 'vw';
                confetti.style.top = Math.random() * 100 + 'vh';
                confetti.style.transform = 'rotate(' + Math.random() * 360 + 'deg)';
                document.body.appendChild(confetti);
                
                // Animate
                const animationDuration = Math.random() * 3 + 2;
                confetti.style.animation = `fall ${animationDuration}s linear forwards`;
            }
            
            // Add keyframes
            const style = document.createElement('style');
            style.innerHTML = `
                @keyframes fall {
                    to {
                        transform: translateY(100vh) rotate(360deg);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
        }
        
        // Trigger confetti after page load
        window.addEventListener('load', createConfetti);
        <?php endif; ?>
    </script>
</body>
</html>