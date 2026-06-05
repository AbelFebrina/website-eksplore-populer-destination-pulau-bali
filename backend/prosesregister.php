<?php
include "koneksi.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $email    = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $cek = mysqli_query($koneksi, "SELECT id FROM tb_akun WHERE email = '$email'");

    if (mysqli_num_rows($cek) > 0) {
        header("Location: ../signup.php?error=2");
        exit();
    }

    mysqli_query($koneksi,
        "INSERT INTO tb_akun (username, email, password, role)
         VALUES ('$username', '$email', '$password', 'user')"
    );

    header("Location: ../loginWeb.php?sukses=1");
    exit();
}
?>