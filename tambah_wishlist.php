<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['user_id'])) {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    } else {
        header("Location: ../loginWeb.php");
    }
    exit();
}

$uid = $_SESSION['user_id'];
$did = (int) ($_POST['destination_id'] ?? 0);

$cek = mysqli_query($koneksi,
    "SELECT id FROM wishlists WHERE user_id = $uid AND destination_id = $did"
);

if (mysqli_num_rows($cek) > 0) {
    mysqli_query($koneksi,
        "DELETE FROM wishlists WHERE user_id = $uid AND destination_id = $did"
    );
    $status = 'removed';
} else {
    mysqli_query($koneksi,
        "INSERT INTO wishlists (user_id, destination_id) VALUES ($uid, $did)"
    );
    $status = 'added';
}

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => $status]);
} else {
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../index.php'));
}
exit();
?>