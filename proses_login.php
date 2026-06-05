<?php
include "koneksi.php";
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email    = $_POST['email'];
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE email = '$email'";
    $hasil = mysqli_query($koneksi, $query);
    $user  = mysqli_fetch_assoc($hasil);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama']    = $user['nama'];
        header("Location: ../frontend/index.php");
        exit();
    } else {
        header("Location: ../frontend/loginWeb.php?error=1");
        exit();
    }
}
?>