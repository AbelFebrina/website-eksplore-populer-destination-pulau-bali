<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../loginWeb.php");
    exit();
}

$uid = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $bio      = mysqli_real_escape_string($koneksi, $_POST['bio'] ?? '');
    $avatar   = '';

    $cek = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM tb_akun WHERE id='$uid'"));
    $avatar = $cek['avatar'];

    if (!empty($_FILES['avatar']['name'])) {

        $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));

        if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {

            $nama_file = "avatar_" . $uid . "." . $ext;

            move_uploaded_file(
                $_FILES['avatar']['tmp_name'],
                "../upload_gambar/" . $nama_file
            );

            $avatar = $nama_file;
        }
    }

    mysqli_query($koneksi,"
        UPDATE tb_akun
        SET
        username='$username',
        bio='$bio',
        avatar='$avatar'
        WHERE id='$uid'
    ");

    header("Location: ../profile.php?sukses=1");
    exit();
}
?>