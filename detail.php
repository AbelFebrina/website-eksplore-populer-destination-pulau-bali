<?php
session_start();
include "backend/koneksi.php";

$id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$dest = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT d.*, k.nama AS kat_nama, k.icon AS kat_icon
     FROM destinations d
     LEFT JOIN kategori k ON d.kategori_id = k.id
     WHERE d.id = $id"
));

if (!$dest) { header("Location: index.php"); exit(); }

$uid    = $_SESSION['user_id'] ?? 0;
$isWish = false;

if ($uid) {
    $cekW   = mysqli_query($koneksi, "SELECT id FROM wishlists WHERE user_id=$uid AND destination_id=$id");
    $isWish = mysqli_num_rows($cekW) > 0;
}

$reviews = mysqli_query($koneksi,
    "SELECT r.*, u.username, u.avatar
     FROM reviews r
     JOIN tb_akun u ON r.user_id = u.id
     WHERE r.destination_id = $id
     ORDER BY r.created_at DESC"
);
$total_reviews = mysqli_num_rows($reviews);

$avg_q = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT AVG(rating) AS avg_rating, COUNT(*) AS total
     FROM reviews WHERE destination_id=$id AND rating > 0"
));
$avg_rating   = $avg_q['avg_rating'] ? round($avg_q['avg_rating'], 1) : $dest['rating'];
$total_raters = (int)$avg_q['total'];

$like_ids = [];
if ($uid) {
    $lq = mysqli_query($koneksi, "SELECT review_id FROM review_likes WHERE user_id=$uid");
    while ($lr = mysqli_fetch_assoc($lq)) $like_ids[] = (int)$lr['review_id'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($dest['nama']) ?> - Explore Bali</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body { font-family: 'Poppins', sans-serif; background: #f8fafc; color: #333; }

        /* ===== HEADER ===== */
        header {
            background: white;
            padding: 14px 60px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
            position: sticky;
            top: 0;
            z-index: 200;
        }

        .logo { display: flex; align-items: center; gap: 8px; text-decoration: none; }
        .logo img { height: 34px; }
        .logo span { font-size: 18px; font-weight: 700; color: #009688; }

        nav ul { list-style: none; display: flex; gap: 28px; }
        nav ul li a { text-decoration: none; color: #555; font-weight: 500; font-size: 14px; transition: color 0.3s; }
        nav ul li a:hover { color: #009688; }

        .header-right { display: flex; align-items: center; gap: 12px; }

        .btn-login {
            background: #009688;
            color: white;
            padding: 8px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
        }

        /* ===== DROPDOWN ===== */
        .profile-dropdown { position: relative; }

        .profile-trigger {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 25px;
            transition: background 0.3s;
        }

        .profile-trigger:hover { background: #f0f0f0; }

        .header-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #009688;
        }

        .header-avatar-placeholder {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, #009688, #4DD0C4);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 13px;
        }

        .header-username { font-size: 13px; font-weight: 600; color: #009688; }

        .dropdown-arrow { font-size: 11px; color: #888; transition: transform 0.3s; }

        .profile-dropdown.open .dropdown-arrow { transform: rotate(180deg); }

        .dropdown-menu {
            display: none;
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
            min-width: 180px;
            z-index: 1000;
            overflow: hidden;
            animation: fadeDown 0.2s ease;
        }

        .profile-dropdown.open .dropdown-menu { display: block; }

        @keyframes fadeDown {
            from { opacity: 0; transform: translateY(-8px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            font-size: 13px;
            font-weight: 500;
            color: #333;
            text-decoration: none;
            transition: background 0.2s;
        }

        .dropdown-item:hover { background: #f8fafc; color: #009688; }

        .dropdown-divider { height: 1px; background: #f0f0f0; margin: 4px 0; }

        .dropdown-logout { color: #e53935; }
        .dropdown-logout:hover { background: #ffebee; color: #e53935; }

        /* ===== HERO ===== */
        .hero-img-wrap { position: relative; height: 460px; overflow: hidden; }

        .hero-img-wrap img { width: 100%; height: 100%; object-fit: cover; display: block; }

        .hero-img-wrap::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom, transparent 40%, rgba(0, 0, 0, 0.65));
        }

        .hero-info { position: absolute; bottom: 28px; left: 60px; color: white; z-index: 2; }

        .hero-kat {
            font-size: 12px;
            font-weight: 600;
            background: #009688;
            padding: 4px 12px;
            border-radius: 20px;
            display: inline-block;
            margin-bottom: 10px;
        }

        .hero-info h1 { font-size: 36px; font-weight: 700; margin-bottom: 6px; }

        .hero-info .lokasi { font-size: 14px; opacity: 0.9; }

        /* ===== CONTAINER ===== */
        .container { max-width: 980px; margin: 0 auto; padding: 0 30px; }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #009688;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            margin: 26px 0 20px;
        }

        .back-btn:hover { text-decoration: underline; }

        /* ===== LAYOUT ===== */
        .detail-layout { display: flex; gap: 26px; margin-bottom: 40px; }

        .detail-main { flex: 1; }

        .detail-side { width: 265px; flex-shrink: 0; }

        /* ===== CARDS ===== */
        .card {
            background: white;
            border-radius: 14px;
            padding: 24px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
            margin-bottom: 18px;
        }

        .card h2 { font-size: 17px; font-weight: 700; color: #1a1a2e; margin-bottom: 12px; }
        .card h3 { font-size: 15px; font-weight: 700; color: #1a1a2e; margin-bottom: 14px; }

        .card p { font-size: 14px; color: #555; line-height: 1.8; }

        /* ===== RATING OVERVIEW ===== */
        .rating-overview {
            display: flex;
            align-items: center;
            gap: 22px;
        }

        .rating-big { text-align: center; flex-shrink: 0; }

        .rating-big .num { font-size: 44px; font-weight: 700; color: #1a1a2e; line-height: 1; }

        .rating-big .stars { color: #ffb400; font-size: 18px; margin: 5px 0; }

        .rating-big .count { font-size: 12px; color: #888; }

        .rating-bars { flex: 1; }

        .bar-row { display: flex; align-items: center; gap: 8px; margin-bottom: 5px; }

        .bar-row .lbl { font-size: 12px; color: #555; width: 12px; text-align: right; }

        .bar-track { flex: 1; height: 6px; background: #f0f0f0; border-radius: 3px; overflow: hidden; }

        .bar-fill { height: 100%; background: #ffb400; border-radius: 3px; }

        .bar-row .cnt { font-size: 11px; color: #888; width: 18px; }

        /* ===== INFO GRID ===== */
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 18px; }

        .info-item {
            background: white;
            border-radius: 11px;
            padding: 15px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
        }

        .info-label { font-size: 10px; font-weight: 600; color: #009688; text-transform: uppercase; margin-bottom: 5px; }

        .info-value { font-size: 13px; font-weight: 600; color: #1a1a2e; }

        /* ===== SIDE BUTTONS ===== */
        .btn-full {
            width: 100%;
            padding: 11px;
            border-radius: 10px;
            border: none;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 10px;
            text-decoration: none;
            transition: all 0.3s;
        }

        .btn-teal { background: #009688; color: white; }
        .btn-teal:hover { background: #00796b; }

        .btn-wish-side { background: #e0f2f1; color: #009688; }
        .btn-wish-side:hover { background: #009688; color: white; }
        .btn-wish-side.active { background: #ffebee; color: #e53935; }
        .btn-wish-side.active:hover { background: #e53935; color: white; }

        .btn-maps { background: #1565c0; color: white; }
        .btn-maps:hover { background: #0d47a1; }

        /* ===== REVIEWS ===== */
        .reviews-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .reviews-header h2 { font-size: 20px; font-weight: 700; color: #1a1a2e; }

        /* ===== POST REVIEW ===== */
        .post-card {
            background: white;
            border-radius: 14px;
            padding: 22px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
            margin-bottom: 22px;
        }

        .post-card h3 { font-size: 15px; font-weight: 700; color: #1a1a2e; margin-bottom: 16px; }

        /* Star Input */
        .star-row { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; }

        .star-row span { font-size: 13px; font-weight: 600; color: #555; }

        .stars-input { display: flex; flex-direction: row-reverse; gap: 3px; }

        .stars-input input { display: none; }

        .stars-input label {
            font-size: 30px;
            color: #ddd;
            cursor: pointer;
            transition: color 0.15s;
            font-weight: normal;
        }

        .stars-input label:hover,
        .stars-input label:hover ~ label,
        .stars-input input:checked ~ label {
            color: #ffb400;
        }

        /* Photo upload */
        .photo-drop {
            border: 2px dashed #e0e0e0;
            border-radius: 11px;
            padding: 18px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 12px;
            position: relative;
        }

        .photo-drop:hover { border-color: #009688; background: #f8fafc; }

        .photo-drop input { display: none; }

        .photo-drop-preview {
            width: 100%;
            max-height: 200px;
            object-fit: cover;
            border-radius: 8px;
            display: none;
        }

        .photo-drop-placeholder { color: #bbb; font-size: 13px; }

        .photo-drop-placeholder .icon { font-size: 34px; margin-bottom: 6px; }

        .caption-box {
            width: 100%;
            padding: 11px 14px;
            border: 1.5px solid #e0e0e0;
            border-radius: 10px;
            font-size: 13px;
            font-family: 'Poppins', sans-serif;
            outline: none;
            resize: vertical;
            min-height: 80px;
            transition: border-color 0.3s;
            margin-bottom: 12px;
        }

        .caption-box:focus { border-color: #009688; }

        .btn-post {
            width: 100%;
            background: #009688;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 9px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            transition: background 0.3s;
        }

        .btn-post:hover { background: #00796b; }

        /* ===== REVIEW GRID ===== */
        .reviews-grid { display: grid; 
            /* grid-template-columns: 1fr 1fr;  */
            gap: 16px; }

        .review-card {
            background: white;
            border-radius: 13px;
            overflow: hidden;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.07);
        }

        .rv-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 13px 14px 9px;
        }

        .rv-av {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #009688;
            flex-shrink: 0;
        }

        .rv-av-placeholder {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #009688, #4DD0C4);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 13px;
            flex-shrink: 0;
        }

        .rv-username { font-size: 13px; font-weight: 700; color: #1a1a2e; }

        .rv-stars { color: #ffb400; font-size: 12px; }

        .rv-time { font-size: 11px; color: #aaa; }

        .rv-delete {
            margin-left: auto;
            color: #ddd;
            font-size: 17px;
            text-decoration: none;
            transition: color 0.3s;
            flex-shrink: 0;
        }

        .rv-delete:hover { color: #e53935; }

        .rv-photo {
            /* width: 100%; */
            max-height: 5px;
            max-width: 5px;
            object-fit: cover;
            display: block;
            cursor: zoom-in;
        }

        .rv-body { padding: 11px 14px 14px; }

        .rv-caption { font-size: 13px; color: #444; line-height: 1.6; margin-bottom: 10px; }

        .rv-actions { display: flex; align-items: center; gap: 12px; }

        .btn-like {
            background: none;
            border: none;
            font-size: 22px;
            cursor: pointer;
            transition: transform 0.2s;
            padding: 0;
            line-height: 1;
        }

        .btn-like:hover { transform: scale(1.2); }

        .like-count { font-size: 12px; color: #888; font-weight: 600; }

        /* ===== EMPTY / PROMPT ===== */
        .empty-reviews { text-align: center; padding: 50px 20px; color: #aaa; }
        .empty-reviews .icon { font-size: 50px; margin-bottom: 12px; }

        .login-prompt {
            background: #e0f2f1;
            border-radius: 13px;
            padding: 22px;
            text-align: center;
            margin-bottom: 22px;
        }

        .login-prompt p { font-size: 14px; color: #555; margin-bottom: 12px; }

        .login-prompt a {
            background: #009688;
            color: white;
            padding: 10px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
        }

        /* ===== LIGHTBOX ===== */
        .lightbox {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.92);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        .lightbox.show { display: flex; }

        .lightbox img { max-width: 92vw; max-height: 90vh; border-radius: 8px; }

        .lightbox-close {
            position: absolute;
            top: 18px;
            right: 22px;
            color: white;
            font-size: 30px;
            cursor: pointer;
            background: none;
            border: none;
        }

        /* ===== FOOTER ===== */
        footer {
            background: #0d1b1e;
            color: white;
            padding: 38px 60px 22px;
            display: flex;
            justify-content: space-around;
            font-size: 13px;
        }

        footer h4 { margin-bottom: 10px; font-size: 14px; }

        footer a { color: #aaa; font-size: 12px; text-decoration: none; display: block; margin-bottom: 5px; }

        footer a:hover { color: #4DD0C4; }

        .footer-bottom {
            text-align: center;
            padding: 14px 60px;
            background: #0d1b1e;
            border-top: 1px solid #222;
            font-size: 12px;
            color: #555;
        }

        @media (max-width: 768px) {
            header            { padding: 12px 20px; }
            .hero-info        { left: 20px; }
            .hero-info h1     { font-size: 24px; }
            .container        { padding: 0 16px; }
            .detail-layout    { flex-direction: column; }
            .detail-side      { width: 100%; }
            .reviews-grid     { grid-template-columns: 1fr; }
            footer            { flex-direction: column; gap: 20px; padding: 28px 20px; }
        }
    </style>
</head>
<body>

<!-- HEADER -->
<header>
    <a href="index.php" class="logo">
        <img src="assets/Gemini_Generated_Image_h0z3whh0z3whh0z3-removebg-preview 2.png"
             alt="Logo" onerror="this.style.display='none'">
        <span>Eksplore</span>
    </a>
    <nav>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="index.php#rekomendasi">Recommendations</a></li>
            <li><a href="index.php#populer">Popular</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="wishlist.php">Wishlist ❤</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    <div class="header-right">
        <?php if (isset($_SESSION['user_id'])): ?>
        <div class="profile-dropdown" id="profileDropdown">
            <div class="profile-trigger" onclick="toggleDropdown()">
                <?php if (!empty($_SESSION['avatar'])): ?>
                    <img src="upload_gambar/<?= htmlspecialchars($_SESSION['avatar']) ?>"
                         class="header-avatar" alt="avatar">
                <?php else: ?>
                    <div class="header-avatar-placeholder">
                        <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
                    </div>
                <?php endif; ?>
                <span class="header-username"><?= htmlspecialchars($_SESSION['username']) ?></span>
                <span class="dropdown-arrow">▾</span>
            </div>
            <div class="dropdown-menu">
                <a href="profile.php" class="dropdown-item"> Edit Profile</a>
                <a href="wishlist.php" class="dropdown-item">❤ My Wishlist</a>
                <div class="dropdown-divider"></div>
                <a href="backend/logout.php" class="dropdown-item dropdown-logout"> Logout</a>
            </div>
        </div>
        <?php else: ?>
            <a href="loginWeb.php" class="btn-login">Login</a>
        <?php endif; ?>
    </div>
</header>

<!-- HERO -->
<div class="hero-img-wrap">
    <img src="assets/<?= htmlspecialchars($dest['gambar']) ?>"
         alt="<?= htmlspecialchars($dest['nama']) ?>"
         onerror="this.src='assets/background.bali.png'">
    <div class="hero-info">
        <div class="hero-kat">
    <?= htmlspecialchars($dest['kat_nama'] ?? $dest['kategori']) ?>
</div>
        <h1><?= htmlspecialchars($dest['nama']) ?></h1>
        <div class="lokasi"> <?= htmlspecialchars($dest['lokasi']) ?></div>
    </div>
</div>

<div class="container">
    <a href="javascript:history.back()" class="back-btn">< Back</a>

    <div class="detail-layout">

        <!-- MAIN -->
        <div class="detail-main">

            <div class="card">
                <h2>About <?= htmlspecialchars($dest['nama']) ?></h2>
                <p><?= nl2br(htmlspecialchars($dest['deskripsi'])) ?></p>
            </div>

            <!-- RATING OVERVIEW -->
            <div class="card">
                <div class="rating-overview">
                    <div class="rating-big">
                        <div class="num"><?= $avg_rating ?></div>
                        <div class="stars">
                            <?php $r = round($avg_rating);
                            for ($i = 1; $i <= 5; $i++) echo $i <= $r ? '★' : '☆'; ?>
                        </div>
                        <div class="count"><?= $total_raters ?> ratings</div>
                    </div>
                    <div class="rating-bars">
                        <?php for ($s = 5; $s >= 1; $s--):
                            $cnt_q = mysqli_fetch_assoc(mysqli_query($koneksi,
                                "SELECT COUNT(*) AS c FROM reviews WHERE destination_id=$id AND rating=$s"));
                            $cnt   = (int)$cnt_q['c'];
                            $pct   = $total_raters > 0 ? ($cnt / $total_raters * 100) : 0;
                        ?>
                        <div class="bar-row">
                            <span class="lbl"><?= $s ?></span>
                            <div class="bar-track">
                                <div class="bar-fill" style="width:<?= $pct ?>%"></div>
                            </div>
                            <span class="cnt"><?= $cnt ?></span>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>

            <!-- INFO GRID -->
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label"> Location</div>
                    <div class="info-value"><?= htmlspecialchars($dest['lokasi']) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label"> Category</div>
                    <div class="info-value">
                        <?= htmlspecialchars($dest['kat_nama'] ?? $dest['kategori']) ?>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">⭐ Avg Rating</div>
                    <div class="info-value"><?= $avg_rating ?> / 5</div>
                </div>
                <div class="info-item">
                    <div class="info-label"> Reviews</div>
                    <div class="info-value"><?= $total_reviews ?> posts</div>
                </div>
            </div>

        </div>

        <!-- SIDEBAR -->
        <div class="detail-side">
            <div class="card">
                <h3>Actions</h3>
                <?php if (isset($_SESSION['user_id'])): ?>
                <form action="backend/tambah_wishlist.php" method="POST">
                    <input type="hidden" name="destination_id" value="<?= $dest['id'] ?>">
                    <button type="submit"
                            class="btn-full btn-wish-side <?= $isWish ? 'active' : '' ?>">
                        <?= $isWish ? '❤️ Saved to Wishlist' : '🤍 Add to Wishlist' ?>
                    </button>
                </form>
                <?php else: ?>
                <a href="loginWeb.php" class="btn-full btn-wish-side">🤍 Login to Wishlist</a>
                <?php endif; ?>
                <a href="https://maps.google.com/?q=<?= urlencode($dest['nama'] . ' Bali') ?>"
                   target="_blank" class="btn-full btn-maps">
                     View on Maps
                </a>
            </div>

            <div class="card">
                <h3> Quick Info</h3>
                <p style="font-size:13px;color:#666;line-height:2.1">
                     Open daily<br>
                     <?= htmlspecialchars($dest['lokasi']) ?><br>
                     <?= $avg_rating ?>/5 avg<br>
                     <?= $total_reviews ?> reviews
                </p>
            </div>
        </div>

    </div>

    <!-- REVIEWS -->
    <div style="margin-bottom:60px">
        <div class="reviews-header">
            <h2> Traveller Reviews</h2>
            <span style="font-size:13px;color:#888"><?= $total_reviews ?> posts</span>
        </div>

        <?php if (isset($_SESSION['user_id'])): ?>
        <div class="post-card">
            <h3>Share Your Experience</h3>
            <form action="proses_review.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="aksi" value="post">
                <input type="hidden" name="destination_id" value="<?= $dest['id'] ?>">

                <div class="star-row">
                    <span>Your Rating:</span>
                    <div class="stars-input">
                        <?php for ($s = 5; $s >= 1; $s--): ?>
                        <input type="radio" name="rating"
                               id="s<?= $s ?>" value="<?= $s ?>"
                               <?= $s == 5 ? 'checked' : '' ?>>
                        <label for="s<?= $s ?>">★</label>
                        <?php endfor; ?>
                    </div>
                </div>

                <div class="photo-drop" onclick="document.getElementById('fotoInput').click()">
                    <input type="file" id="fotoInput" name="foto"
                           accept="image/*" onchange="prevPhoto(this)">
                    <img id="photoPrev" class="photo-drop-preview" alt="preview">
                    <div class="photo-drop-placeholder" id="photoPlaceholder">
                        <div class="icon"></div>
                        <p>Click to upload photo (optional)</p>
                    </div>
                </div>

                <textarea class="caption-box" name="caption"
                          placeholder="Write your experience..."></textarea>

                <button type="submit" class="btn-post"> Post Review</button>
            </form>
        </div>

        <?php else: ?>
        <div class="login-prompt">
            <p>Login to share your experience!</p>
            <a href="loginWeb.php">Login Now</a>
        </div>
        <?php endif; ?>

        <?php if ($total_reviews > 0): ?>
        <div class="reviews-grid">
            <?php
            mysqli_data_seek($reviews, 0);
            while ($rv = mysqli_fetch_assoc($reviews)):
                $liked   = in_array((int)$rv['id'], $like_ids);
                $is_mine = (int)$rv['user_id'] === (int)$uid;
                $time    = date('M d, Y', strtotime($rv['created_at']));
                $rv_star = (int)($rv['rating'] ?? 5);
            ?>
            <div class="review-card">
                <div class="rv-header">
                    <?php if (!empty($rv['avatar'])): ?>
                        <img src="upload_gambar/<?= htmlspecialchars($rv['avatar']) ?>"
                             class="rv-av" alt="av" onerror="this.style.display='none'">
                    <?php else: ?>
                        <div class="rv-av-placeholder">
                            <?= strtoupper(substr($rv['username'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    <div style="flex:1">
                        <div class="rv-username"><?= htmlspecialchars($rv['username']) ?></div>
                        <div style="display:flex;align-items:center;gap:8px">
                            <span class="rv-stars">
                                <?php for ($i = 1; $i <= 5; $i++) echo $i <= $rv_star ? '★' : '☆'; ?>
                            </span>
                            <span class="rv-time"><?= $time ?></span>
                        </div>
                    </div>
                    <?php if ($is_mine): ?>
                    <a href="proses_review.php?aksi=hapus&id=<?= $rv['id'] ?>&dest=<?= $dest['id'] ?>"
                       class="rv-delete"
                       onclick="return confirm('Delete this review?')">🗑</a>
                    <?php endif; ?>
                </div>

                <?php if (!empty($rv['foto'])): ?>
                <img src="upload_gambar/<?= htmlspecialchars($rv['foto']) ?>"
                     class="rv-photo" alt="review"
                     onclick="openLightbox(this.src)"
                     onerror="this.style.display='none'">
                <?php endif; ?>

                <div class="rv-body">
                    <?php if (!empty($rv['caption'])): ?>
                    <div class="rv-caption">
                        <strong><?= htmlspecialchars($rv['username']) ?></strong>
                        <?= nl2br(htmlspecialchars($rv['caption'])) ?>
                    </div>
                    <?php endif; ?>
                    <div class="rv-actions">
                        <?php if (isset($_SESSION['user_id'])): ?>
                        <form action="proses_review.php" method="POST" style="display:inline">
                            <input type="hidden" name="aksi" value="like">
                            <input type="hidden" name="review_id" value="<?= $rv['id'] ?>">
                            <input type="hidden" name="destination_id" value="<?= $dest['id'] ?>">
                            <p><?php echo $rv['komentar']?></p>
                            <img src="upload_gambar/<?php echo $rv['foto_review']?>">
                            <button type="submit" class="btn-like">
                                <?= $liked ? '❤️' : '🤍' ?>
                            </button>
                        </form>
                        <?php else: ?>
                            <span style="font-size:20px">🤍</span>
                        <?php endif; ?>
                        <span class="like-count"><?= $rv['likes'] ?? ""; ?> likes</span>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

        <?php else: ?>
        <div class="empty-reviews">
            <div></div>
            <p>No reviews yet. Be the first to share!</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- LIGHTBOX -->
<div class="lightbox" id="lightbox" onclick="closeLightbox()">
    <button class="lightbox-close">✕</button>
    <img id="lightboxImg" src="" alt="photo">
</div>

<footer>
    <div><h4>Support</h4><a href="mailto:support@explorebali.com">support@explorebali.com</a></div>
    <div><h4>Services</h4><a href="index.php#rekomendasi">Recommendations</a><a href="wishlist.php">Wishlist</a></div>
    <div><h4>Contact</h4><a href="#">Instagram</a></div>
</footer>
<div class="footer-bottom">© 2025 Eksplore Bali. All rights reserved.</div>

<script>
    function toggleDropdown() {
        document.getElementById('profileDropdown').classList.toggle('open');
    }

    document.addEventListener('click', function(e) {
        const d = document.getElementById('profileDropdown');
        if (d && !d.contains(e.target)) d.classList.remove('open');
    });

    function prevPhoto(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                const img = document.getElementById('photoPrev');
                const ph  = document.getElementById('photoPlaceholder');
                img.src            = e.target.result;
                img.style.display  = 'block';
                ph.style.display   = 'none';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function openLightbox(src) {
        document.getElementById('lightboxImg').src = src;
        document.getElementById('lightbox').classList.add('show');
    }

    function closeLightbox() {
        document.getElementById('lightbox').classList.remove('show');
    }
</script>
</body>
</html>