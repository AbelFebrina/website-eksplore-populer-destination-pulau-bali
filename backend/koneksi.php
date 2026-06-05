<?php
$host     = "localhost";
$username = "root";
$password = "";
$database = "db_pjbl";

$koneksi = mysqli_connect($host, $username, $password, $database);

if (!$koneksi) {
    die("Error: " . mysqli_connect_error());
}

mysqli_set_charset($koneksi, "utf8");
?>