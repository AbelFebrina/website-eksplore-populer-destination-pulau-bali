<?php
session_start();
include "../backend/koneksi.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../loginWeb.php");
    exit();
}

$aksi = $_POST['aksi'] ?? $_GET['aksi'] ?? '';

function handleGambar($file_key, $manual_key, $existing = '') {
    global $koneksi;

    if (!empty($_FILES[$file_key]['name']) && $_FILES[$file_key]['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES[$file_key]['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $nama = 'dest_' . time() . '.' . $ext;
            $dir  = '../upload_gambar/destinations/';
            if (!is_dir($dir)) mkdir($dir, 0777, true);
            if (move_uploaded_file($_FILES[$file_key]['tmp_name'], $dir . $nama)) {
                return 'destinations/' . $nama;
            }
        }
    }

    $manual = trim($_POST[$manual_key] ?? '');
    if ($manual !== '') return $manual;

    return $existing;
}

/* ── ADD ── */
if ($aksi == 'tambah') {
    $nama      = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $kat_id    = (int)$_POST['kategori_id'];
    $lokasi    = mysqli_real_escape_string($koneksi, $_POST['lokasi']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $gambar    = mysqli_real_escape_string($koneksi, handleGambar('gambar_file', 'gambar_manual'));

    $kat      = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT nama FROM kategori WHERE id=$kat_id"));
    $kat_nama = $kat ? $kat['nama'] : '';

    mysqli_query($koneksi,
        "INSERT INTO destinations (nama, kategori, kategori_id, lokasi, deskripsi, rating, gambar)
         VALUES ('$nama', '$kat_nama', $kat_id, '$lokasi', '$deskripsi', 4.0, '$gambar')"
    );

    header("Location: kelola_destinasi.php?pesan=tambah");
    exit();
}

/* ── EDIT ── */
if ($aksi == 'edit') {
    $id        = (int)$_POST['id'];
    $nama      = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $kat_id    = (int)$_POST['kategori_id'];
    $lokasi    = mysqli_real_escape_string($koneksi, $_POST['lokasi']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $existing  = $_POST['gambar_existing'] ?? '';
    $gambar    = mysqli_real_escape_string($koneksi, handleGambar('gambar_file', 'gambar_manual', $existing));

    $kat      = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT nama FROM kategori WHERE id=$kat_id"));
    $kat_nama = $kat ? $kat['nama'] : '';

    mysqli_query($koneksi,
        "UPDATE destinations
         SET nama='$nama', kategori='$kat_nama', kategori_id=$kat_id,
             lokasi='$lokasi', deskripsi='$deskripsi', gambar='$gambar'
         WHERE id=$id"
    );

    header("Location: kelola_destinasi.php?pesan=edit");
    exit();
}

/* ── DELETE ── */
if ($aksi == 'hapus') {
    $id = (int)$_GET['id'];
    mysqli_query($koneksi, "DELETE FROM wishlists    WHERE destination_id=$id");
    mysqli_query($koneksi, "DELETE FROM reviews      WHERE destination_id=$id");
    mysqli_query($koneksi, "DELETE FROM destinations WHERE id=$id");
    header("Location: kelola_destinasi.php?pesan=hapus");
    exit();
}

header("Location: kelola_destinasi.php");
exit();
?>