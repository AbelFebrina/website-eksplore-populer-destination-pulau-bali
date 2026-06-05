<?php
session_start();
include "backend/koneksi.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: loginWeb.php");
    exit();
}

$uid = $_SESSION['user_id'];

if (isset($_GET['hapus'])) {
    $hapus_id = (int)$_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM wishlists WHERE id=$hapus_id AND user_id=$uid");
    header("Location: wishlist.php");
    exit();
}

$wishlist = mysqli_query($koneksi,
    "SELECT w.id AS wid, d.*, k.nama AS kat_nama, k.icon AS kat_icon
     FROM wishlists w
     JOIN destinations d ON w.destination_id = d.id
     LEFT JOIN kategori k ON d.kategori_id = k.id
     WHERE w.user_id = $uid
     ORDER BY w.created_at DESC"
);

$total = mysqli_num_rows($wishlist);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist - Explore Bali</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f8fafc;
        }

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
            z-index: 100;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .logo img {
            height: 34px;
        }

        .logo span {
            font-size: 18px;
            font-weight: 700;
            color: #009688;
        }

        nav ul {
            list-style: none;
            display: flex;
            gap: 28px;
        }

        nav ul li a {
            text-decoration: none;
            color: #555;
            font-weight: 500;
            font-size: 14px;
            transition: color 0.3s;
        }

        nav ul li a:hover,
        nav ul li a.active {
            color: #009688;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-av-placeholder {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #009688;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 12px;
        }

        .user-av {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #009688;
        }

        .user-name {
            font-size: 13px;
            font-weight: 600;
            color: #009688;
            text-decoration: none;
        }

        .btn-logout {
            background: #f44336;
            color: white;
            padding: 8px 14px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
        }

        /* ===== PAGE HERO ===== */
        .page-hero {
            background: linear-gradient(rgba(0, 0, 0, 0.52), rgba(0, 0, 0, 0.52)),
                        url('assets/background.bali.png') center / cover;
            color: white;
            padding: 60px;
            text-align: center;
        }

        .page-hero h1 {
            font-size: 34px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .page-hero p {
            font-size: 15px;
            opacity: 0.85;
        }

        /* ===== CONTAINER ===== */
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 40px;
        }

        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .count-badge {
            background: #e0f2f1;
            color: #009688;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }

        /* ===== EMPTY STATE ===== */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
        }

        .empty-state .icon {
            font-size: 70px;
            margin-bottom: 20px;
        }

        .empty-state p {
            font-size: 16px;
            color: #888;
            margin-bottom: 24px;
        }

        .empty-state a {
            background: #009688;
            color: white;
            padding: 12px 28px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: background 0.3s;
        }

        .empty-state a:hover {
            background: #00796b;
        }

        /* ===== WISH GRID ===== */
        .wish-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));
            gap: 24px;
        }

        .wish-card {
            background: white;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.07);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .wish-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
        }

        .wish-card img {
            width: 100%;
            height: 185px;
            object-fit: cover;
            display: block;
        }

        .wish-info {
            padding: 15px 16px 16px;
        }

        .wish-kat {
            font-size: 10px;
            font-weight: 600;
            color: #009688;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .wish-nama {
            font-size: 15px;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 4px;
        }

        .wish-lokasi {
            font-size: 12px;
            color: #888;
            margin-bottom: 10px;
        }

        .wish-rating {
            color: #ffb400;
            font-size: 13px;
            margin-bottom: 14px;
        }

        .wish-rating span {
            color: #888;
            font-size: 12px;
        }

        .wish-actions {
            display: flex;
            gap: 8px;
        }

        .btn-detail {
            flex: 1;
            background: #009688;
            color: white;
            border: none;
            padding: 9px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: background 0.3s;
            display: block;
        }

        .btn-detail:hover {
            background: #00796b;
        }

        .btn-remove {
            background: #ffebee;
            color: #e53935;
            border: none;
            padding: 9px 13px;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }

        .btn-remove:hover {
            background: #e53935;
            color: white;
        }

        /* ===== FOOTER ===== */
        footer {
            background: #0d1b1e;
            color: white;
            padding: 40px 60px 24px;
            display: flex;
            justify-content: space-around;
            margin-top: 60px;
            font-size: 13px;
        }

        footer h4 {
            margin-bottom: 10px;
            font-size: 14px;
        }

        footer a {
            color: #aaa;
            font-size: 12px;
            text-decoration: none;
            display: block;
            margin-bottom: 5px;
        }

        footer a:hover {
            color: #4DD0C4;
        }

        .footer-bottom {
            text-align: center;
            padding: 16px 60px;
            background: #0d1b1e;
            border-top: 1px solid #222;
            font-size: 12px;
            color: #555;
        }

        @media (max-width: 768px) {
            header     { padding: 12px 20px; }
            .page-hero { padding: 40px 20px; }
            .container { padding: 0 16px; }
            footer     { flex-direction: column; gap: 20px; padding: 30px 20px; }
        }
    </style>
</head>
<body>

<!-- HEADER -->
<header>
    <a href="index.php" class="logo">
        <img src="assets/Gemini_Generated_Image_h0z3whh0z3whh0z3-removebg-preview 2.png"
             alt="Logo"
             onerror="this.style.display='none'">
        <span>Eksplore</span>
    </a>
    <nav>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="index.php#rekomendasi">Recommendations</a></li>
             <li><a href="index.php#populer">Populer</a></li>
            <li><a href="wishlist.php" class="active">Wishlist ❤</a></li>
        </ul>
    </nav>
    <div class="header-right">
        <?php if (!empty($_SESSION['avatar'])): ?>
            <img src="upload_gambar/<?= htmlspecialchars($_SESSION['avatar']) ?>" class="user-av" alt="avatar">
        <?php else: ?>
            <div class="user-av-placeholder">
                <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
            </div>
        <?php endif; ?>
        <a href="profile.php" class="user-name"><?= htmlspecialchars($_SESSION['username']) ?></a>
        <a href="backend/logout.php" class="btn-logout">Logout</a>
    </div>
</header>

<!-- PAGE HERO -->
<div class="page-hero">
    <h1>❤ My Wishlist</h1>
    <p>Destinations you want to visit</p>
</div>

<!-- CONTENT -->
<div class="container">
    <div class="section-title">
        Saved Destinations
        <span class="count-badge"><?= $total ?></span>
    </div>

    <?php if ($total == 0): ?>
    <div class="empty-state">
        <div class="icon"></div>
        <p>No saved destinations yet.<br>Explore and add your favorites!</p>
        <a href="index.php">Explore Now</a>
    </div>

    <?php else: ?>
    <div class="wish-grid">
        <?php while ($w = mysqli_fetch_assoc($wishlist)): ?>
        <div class="wish-card">
            <img src="assets/<?= htmlspecialchars($w['gambar']) ?>"
                 alt="<?= htmlspecialchars($w['nama']) ?>"
                 onerror="this.src='assets/background.bali.png'">
            <div class="wish-info">
                <div class="wish-kat">
                    <?= htmlspecialchars($w['kat_icon'] ?? '') ?>
                    <?= htmlspecialchars($w['kat_nama'] ?? $w['kategori']) ?>
                </div>
                <div class="wish-nama"><?= htmlspecialchars($w['nama']) ?></div>
                <div class="wish-lokasi">📍 <?= htmlspecialchars($w['lokasi']) ?></div>
                <div class="wish-rating">
                    <?php
                    $r = round($w['rating']);
                    for ($i = 1; $i <= 5; $i++) echo $i <= $r ? '★' : '☆';
                    ?>
                    <span>(<?= $w['rating'] ?>)</span>
                </div>
                <div class="wish-actions">
                    <a href="detail.php?id=<?= $w['id'] ?>" class="btn-detail">View Details</a>
                    <a href="wishlist.php?hapus=<?= $w['wid'] ?>"
                       class="btn-remove"
                       onclick="return confirm('Remove from wishlist?')"></a>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <?php endif; ?>
</div>

<!-- FOOTER -->
<footer>
    <div>
        <h4>Support</h4>
        <a href="mailto:support@explorebali.com">support@explorebali.com</a>
    </div>
    <div>
        <h4>Services</h4>
        <a href="index.php#rekomendasi">Recommendations</a>
        <a href="wishlist.php">Wishlist</a>
    </div>
    <div>
        <h4>Contact</h4>
        <a href="#">Instagram</a>
    </div>
</footer>
<div class="footer-bottom">© 2025 Eksplore Bali. All rights reserved.</div>

</body>
</html>