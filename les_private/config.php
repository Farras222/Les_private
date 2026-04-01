<?php

$host = 'localhost';
$db   = 'les_private';
$user = 'root';
$pass = '';

$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_error) {
    die("Koneksi gagal: " . $mysqli->connect_error);
} 

define('ROOT_PATH', __DIR__);

?>