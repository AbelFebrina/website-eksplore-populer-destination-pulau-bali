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
    $avatar   = $_SESSION['avatar'] ?? '';

    if (!empty($_FILES['avatar']['name']) && $_FILES['avatar']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));

        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $nama = 'avatar_' . $uid . '.' . $ext;
            $dir  = '../upload_gambar/';

            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }

            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $dir . $nama)) {
                $avatar = $nama;
            }
        }
    }

    mysqli_query($koneksi,
        "UPDATE tb_akun
         SET username = '$username', bio = '$bio', avatar = '$avatar'
         WHERE id = $uid"
    );

    $_SESSION['username'] = $username;
    $_SESSION['avatar']   = $avatar;

    header("Location: ../profile.php?sukses=1");
    exit();
}
?>