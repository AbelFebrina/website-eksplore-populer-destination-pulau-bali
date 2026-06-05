<?php
include "koneksi.php";
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email    = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password = $_POST['password'];

    $user = mysqli_fetch_assoc(
        mysqli_query($koneksi, "SELECT * FROM tb_akun WHERE email = '$email'")
    );

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role']     = $user['role'] ?? 'user';
        $_SESSION['avatar']   = $user['avatar'] ?? '';

        if ($_SESSION['role'] == 'admin') {
            header("Location: ../admin/index.php");
        } else {
            header("Location: ../index.php");
        }
    } else {
        header("Location: ../loginWeb.php?error=1");
    }
    exit();
}
?>