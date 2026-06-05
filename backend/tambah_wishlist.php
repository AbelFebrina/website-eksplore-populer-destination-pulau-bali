<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../loginWeb.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $uid = $_SESSION['user_id'];
    $did = (int) $_POST['destination_id'];

    $cek = mysqli_query($koneksi,
        "SELECT id FROM wishlists WHERE user_id = $uid AND destination_id = $did"
    );

    if (mysqli_num_rows($cek) > 0) {
        mysqli_query($koneksi,
            "DELETE FROM wishlists WHERE user_id = $uid AND destination_id = $did"
        );
    } else {
        mysqli_query($koneksi,
            "INSERT INTO wishlists (user_id, destination_id) VALUES ($uid, $did)"
        );
    }
}

header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../index.php'));
exit();
?>