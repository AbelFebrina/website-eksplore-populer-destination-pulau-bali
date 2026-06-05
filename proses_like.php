<?php
session_start();
include "koneksi.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) { echo json_encode(['success'=>false,'message'=>'Please login first!']); exit(); }

$user_id   = (int)$_SESSION['user_id'];
$review_id = (int)($_POST['review_id'] ?? 0);
if ($review_id <= 0) { echo json_encode(['success'=>false,'message'=>'Invalid']); exit(); }

$cek = mysqli_query($koneksi, "SELECT id FROM review_likes WHERE user_id=$user_id AND review_id=$review_id");
if (mysqli_num_rows($cek) > 0) {
    mysqli_query($koneksi, "DELETE FROM review_likes WHERE user_id=$user_id AND review_id=$review_id");
    $liked = false;
} else {
    mysqli_query($koneksi, "INSERT INTO review_likes (user_id, review_id) VALUES ($user_id, $review_id)");
    $liked = true;
}

$total = (int)mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM review_likes WHERE review_id=$review_id"))['total'];
echo json_encode(['success'=>true,'liked'=>$liked,'total'=>$total]);