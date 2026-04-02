<?php
include '../index.php';

$id = $_GET['id'];
$query = "DELETE FROM quizzes WHERE id = $id";
$result = mysqli_query($mysqli, $query);

if ($result) {
    header("Location: quiz.php");
} else {
    echo "Error: " . $query . "<br>" . mysqli_error($mysqli);
}
?>