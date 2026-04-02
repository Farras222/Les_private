<?php
session_start();

// Cek apakah user adalah siswa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'siswa') {
    header('Location: login.php');
    exit;
}

$siswa_id = $_SESSION['user_id'];

include '../../../../../config.php';

// Ambil data dari POST
$quiz_id = intval($_POST['quiz_id']);
$total_questions = intval($_POST['total_questions']);
$time_spent = intval($_POST['time_spent']);
$answers = $_POST['answers'];

// Ambil jawaban benar dari database
$correct_answers_query = "SELECT id, correct_answer FROM questions WHERE quiz_id = $quiz_id";
$correct_answers_result = mysqli_query($mysqli, $correct_answers_query);

$correct_answers = [];
while($row = mysqli_fetch_assoc($correct_answers_result)) {
    $correct_answers[$row['id']] = $row['correct_answer'];
}

// Hitung skor
$score = 0;
$wrong_answers = [];

foreach($answers as $question_id => $answer) {
    if (isset($correct_answers[$question_id]) && $answer == $correct_answers[$question_id]) {
        $score += (100 / $total_questions);
    } else {
        $wrong_answers[] = $question_id;
    }
}

$score = round($score, 2);

// Ambil passing score
$passing_score_query = "SELECT passing_score FROM quizzes WHERE id = $quiz_id";
$passing_score_result = mysqli_query($mysqli, $passing_score_query);
$passing_score = mysqli_fetch_assoc($passing_score_result)['passing_score'];

$passed = $score >= $passing_score ? 1 : 0;

// Simpan ke database
$insert_attempt = "INSERT INTO quiz_attempts (quiz_id, siswa_id, score, passed, attempted_at) 
                   VALUES ($quiz_id, $siswa_id, $score, $passed, NOW())";

if (mysqli_query($mysqli, $insert_attempt)) {
    $attempt_id = mysqli_insert_id($mysqli);
    
    // Simpan session untuk halaman result
    $_SESSION['quiz_result'] = [
        'quiz_id' => $quiz_id,
        'score' => $score,
        'passed' => $passed,
        'total_questions' => $total_questions,
        'correct_count' => $total_questions - count($wrong_answers),
        'wrong_count' => count($wrong_answers),
        'time_spent' => $time_spent,
        'wrong_answers' => $wrong_answers
    ];
    
    header('Location: quiz_result.php');
    exit;
} else {
    die("Gagal menyimpan hasil quiz: " . mysqli_error($mysqli));
}
?>