<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../loginWeb.php");
    exit();
}

$aksi = $_POST['aksi'] ?? $_GET['aksi'] ?? '';
$uid  = $_SESSION['user_id'];

/* ── POST REVIEW ── */
if ($aksi == 'post' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $did     = (int)$_POST['destination_id'];
    $caption = mysqli_real_escape_string($koneksi, $_POST['caption'] ?? '');
    $rating  = (int)($_POST['rating'] ?? 5);
    $foto    = '';

    if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $nama = 'review_' . time() . '_' . $uid . '.' . $ext;
            $dir  = "upload_gambar/";
            if (!is_dir($dir)) mkdir($dir, 0777, true);
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $dir . $nama)) {
                $foto = $nama;
            }
        }
    }

    mysqli_query($koneksi,
        "INSERT INTO reviews (user_id, destination_id, komentar,  foto_review,rating)
         VALUES ($uid, $did, '$caption', '$foto', $rating)"
    );

    header("Location: detail.php?id=$did");
    exit();
}

/* ── LIKE / UNLIKE ── */
if ($aksi == 'like') {
    $rid = (int)($_POST['review_id'] ?? $_GET['review_id'] ?? 0);
    $did = (int)($_POST['destination_id'] ?? 0);

    $cek = mysqli_query($koneksi,
        "SELECT id FROM review_likes WHERE review_id=$rid AND user_id=$uid"
    );

    if (mysqli_num_rows($cek) > 0) {
        mysqli_query($koneksi, "DELETE FROM review_likes WHERE review_id=$rid AND user_id=$uid");
        // mysqli_query($koneksi, "UPDATE reviews SET likes=likes-1 WHERE id=$rid AND likes>0");
    } else {
        mysqli_query($koneksi, "INSERT INTO review_likes (review_id, user_id) VALUES ($rid, $uid)");
        // mysqli_query($koneksi, "UPDATE reviews SET likes=likes+1 WHERE id=$rid");
    }

    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../index.php'));
    exit();
}

/* ── HAPUS ── */
if ($aksi == 'hapus') {
    $rid = (int)($_GET['id']   ?? 0);
    $did = (int)($_GET['dest'] ?? 0);
    mysqli_query($koneksi, "DELETE FROM reviews WHERE id=$rid AND user_id=$uid");
    header("Location: detail.php?id=$did");
    exit();
}

header("Location: ../index.php");
exit();
?>