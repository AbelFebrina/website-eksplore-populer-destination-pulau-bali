<?php
session_start();
include "backend/koneksi.php";

$kat = isset($_GET['kat']) ? mysqli_real_escape_string($koneksi, $_GET['kat']) : '';
if (!$kat) { header("Location: index.php"); exit(); }

$info = [
    'Pantai'  => ['icon'=>'', 'desc'=>'Nikmati keindahan pantai-pantai eksotis Bali dengan pasir putih dan air biru jernih yang memukau.', 'bg'=>'assets/PantaiKuta.png'],
    'Gunung'  => ['icon'=>'', 'desc'=>'Rasakan petualangan mendaki gunung-gunung megah Bali dengan pemandangan alam yang menakjubkan.', 'bg'=>'assets/GunungAgung.png'],
    'Reguler' => ['icon'=>'', 'desc'=>'Kunjungi destinasi reguler Bali seperti pura-pura bersejarah dan tempat wisata ikonik.', 'bg'=>'assets/PuraTanahLot.png'],
    'Budaya'  => ['icon'=>'', 'desc'=>'Selami kekayaan budaya dan tradisi Bali yang unik — dari desa adat hingga upacara keagamaan.', 'bg'=>'assets/DesaPanglipuran.png'],
    'Alam'    => ['icon'=>'', 'desc'=>'Jelajahi keajaiban alam Bali — dari danau biru, sawah terasering, hingga hutan tropis yang asri.', 'bg'=>'assets/danau beratan.png'],
];
$meta = isset($info[$kat]) ? $info[$kat] : ['icon'=>'📍','desc'=>'Temukan destinasi terbaik di Bali.','bg'=>'assets/background.bali.png'];
$result = mysqli_query($koneksi, "SELECT * FROM destinations WHERE kategori='$kat' ORDER BY rating DESC");
$total = mysqli_num_rows($result);
$cats = ['Pantai'=>'','Gunung'=>'','Reguler'=>'','Budaya'=>'','Alam'=>''];
$imgs = ['Pantai'=>'assets/PantaiKuta.png','Gunung'=>'assets/GunungAgung.png','Reguler'=>'assets/PuraTanahLot.png','Budaya'=>'assets/DesaPanglipuran.png','Alam'=>'assets/danau beratan.png'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategori <?= htmlspecialchars($kat) ?> - Eksplore Bali</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #f8fafc; }
        header { position: fixed; top: 0; width: 100%; z-index: 100; background: rgba(255,255,255,0.97); backdrop-filter: blur(10px); padding: 12px 60px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 20px rgba(0,0,0,0.08); }
        .logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .logo img { height: 38px; }
        .logo span { font-size: 20px; font-weight: 700; color: #009688; }
        nav ul { list-style: none; display: flex; gap: 35px; }
        nav ul li a { text-decoration: none; color: #555; font-weight: 500; font-size: 14px; }
        nav ul li a:hover { color: #009688; }
        .header-right { display: flex; align-items: center; gap: 15px; }
        .btn-login { background: #009688; color: white; padding: 9px 22px; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 600; }
        .btn-logout { background: #e53935; color: white; padding: 9px 18px; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 600; }
        .user-greeting { font-size: 14px; font-weight: 600; color: #009688; }
        .cat-hero { margin-top: 68px; height: 320px; background: linear-gradient(135deg, rgba(0,50,45,0.78) 0%, rgba(0,0,0,0.45) 100%), url('<?= $meta['bg'] ?>') center/cover no-repeat; display: flex; align-items: center; padding: 0 60px; color: white; }
        .cat-hero-content h1 { font-size: 42px; font-weight: 700; margin-bottom: 12px; }
        .cat-hero-content p { font-size: 16px; opacity: 0.88; max-width: 550px; line-height: 1.7; }
        .badge-count { display: inline-block; background: rgba(0,150,136,0.85); padding: 6px 18px; border-radius: 20px; font-size: 13px; font-weight: 600; margin-bottom: 15px; }
        .cat-tabs { background: white; padding: 18px 60px; display: flex; gap: 12px; flex-wrap: wrap; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        .cat-tab { padding: 9px 20px; border-radius: 25px; text-decoration: none; font-size: 13px; font-weight: 600; border: 2px solid #e0e0e0; color: #666; transition: 0.3s; }
        .cat-tab:hover { border-color: #009688; color: #009688; }
        .cat-tab.active { background: #009688; color: white; border-color: #009688; }
        .main { max-width: 1300px; margin: 40px auto; padding: 0 60px; }
        .section-title { font-size: 22px; font-weight: 700; color: #1a1a2e; margin-bottom: 8px; }
        .section-sub { font-size: 14px; color: #888; margin-bottom: 30px; }
        .cards-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 22px; }
        .dest-card { background: white; border-radius: 14px; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.08); transition: transform 0.3s, box-shadow 0.3s; display: flex; flex-direction: column; }
        .dest-card:hover { transform: translateY(-6px); box-shadow: 0 12px 30px rgba(0,0,0,0.13); }
        .dest-card img { width: 100%; height: 180px; object-fit: cover; display: block; }
        .dest-card .card-body { padding: 14px 16px 16px; display: flex; flex-direction: column; flex: 1; }
        .card-kategori { font-size: 11px; font-weight: 600; color: #009688; text-transform: uppercase; margin-bottom: 4px; }
        .card-nama { font-size: 15px; font-weight: 700; color: #1a1a2e; margin-bottom: 4px; }
        .card-lokasi { font-size: 12px; color: #888; margin-bottom: 10px; flex: 1; }
        .card-footer { display: flex; justify-content: space-between; align-items: center; margin-top: auto; }
        .rating { color: #ffb400; font-size: 13px; }
        .btn-card { background: #e0f2f1; color: #009688; border: none; padding: 7px 13px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer; transition: 0.3s; text-decoration: none; display: inline-block; }
        .btn-card:hover { background: #009688; color: white; }
        .empty-state { text-align: center; padding: 80px 20px; grid-column: 1/-1; }
        .empty-state .icon { font-size: 64px; margin-bottom: 16px; }
        .other-cats { margin-top: 60px; padding-top: 40px; border-top: 2px solid #f0f0f0; margin-bottom: 60px; }
        .other-cats h3 { font-size: 20px; font-weight: 700; color: #1a1a2e; margin-bottom: 20px; }
        .other-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
        .other-card { background: white; border-radius: 14px; overflow: hidden; box-shadow: 0 4px 14px rgba(0,0,0,0.07); text-decoration: none; transition: transform 0.3s; display: block; }
        .other-card:hover { transform: translateY(-4px); }
        .other-card img { width: 100%; height: 130px; object-fit: cover; }
        .other-card .other-label { padding: 12px 14px; font-size: 14px; font-weight: 700; color: #1a1a2e; }
        footer { background: #0d1b1e; color: white; padding: 30px 60px; }
        .footer-bottom { text-align: center; font-size: 13px; color: #666; padding-top: 16px; border-top: 1px solid #333; margin-top: 16px; }
        @media(max-width:900px) { .cards-grid { grid-template-columns: repeat(2,1fr); } .other-grid { grid-template-columns: repeat(2,1fr); } }
        @media(max-width:600px) { .main { padding: 0 20px; } header { padding: 12px 20px; } .cat-hero { padding: 0 20px; } .cat-tabs { padding: 15px 20px; } .cards-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<header>
    <a href="index.php" class="logo">
        <img src="assets/Gemini_Generated_Image_h0z3whh0z3whh0z3-removebg-preview 2.png" alt="Logo" onerror="this.style.display='none'">
        <span>Eksplore</span>
    </a>
    <nav>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="index.php#rekomendasi">Rekomendasi</a></li>
            <li><a href="index.php#populer">Populer</a></li>
            <?php if(isset($_SESSION['user_id'])): ?>
            <li><a href="wishlist.php">Wishlist ❤</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    <div class="header-right">
        <?php if(isset($_SESSION['user_id'])): ?>
            <span class="user-greeting">Halo, <?= htmlspecialchars($_SESSION['username']) ?>!</span>
            <a href="backend/logout.php" class="btn-logout">Logout</a>
        <?php else: ?>
            <a href="loginWeb.php" class="btn-login">Login</a>
        <?php endif; ?>
    </div>
</header>

<div class="cat-hero">
    <div class="cat-hero-content">
        <div class="badge-count"><?= $meta['icon'] ?> <?= $total ?> Destinasi <?= htmlspecialchars($kat) ?></div>
        <h1><?= $meta['icon'] ?> <?= htmlspecialchars($kat) ?> Bali</h1>
        <p><?= $meta['desc'] ?></p>
    </div>
</div>

<div class="cat-tabs">
    <?php foreach($cats as $c => $icon): ?>
    <a href="kategori.php?kat=<?= $c ?>" class="cat-tab <?= $kat==$c?'active':'' ?>"><?= $icon ?> <?= $c ?></a>
    <?php endforeach; ?>
    <a href="index.php" class="cat-tab"> Semua</a>
</div>

<div class="main">
    <div class="section-title">Destinasi <?= htmlspecialchars($kat) ?> Terbaik di Bali</div>
    <div class="section-sub">Diurutkan berdasarkan rating tertinggi · <?= $total ?> destinasi</div>

    <div class="cards-grid">
        <?php if($result && mysqli_num_rows($result) > 0):
            while($d = mysqli_fetch_assoc($result)): ?>
        <div class="dest-card">
            <a href="detail.php?id=<?= $d['id'] ?>" style="display:block">
                <img src="assets/<?= htmlspecialchars($d['gambar']) ?>" alt="<?= htmlspecialchars($d['nama']) ?>" onerror="this.src='assets/background.bali.png'">
            </a>
            <div class="card-body">
                <div class="card-kategori"><?= $meta['icon'] ?> <?= htmlspecialchars($d['kategori']) ?></div>
                <a href="detail.php?id=<?= $d['id'] ?>" style="text-decoration:none;color:inherit">
                    <div class="card-nama"><?= htmlspecialchars($d['nama']) ?></div>
                </a>
                <div class="card-lokasi">📍 <?= htmlspecialchars($d['lokasi']) ?></div>
                <div class="card-footer">
                    <div class="rating">
                        <?php $r=round($d['rating']); for($i=1;$i<=5;$i++) echo $i<=$r?"★":"☆"; ?>
                        <span style="color:#666;font-size:12px">(<?= $d['rating'] ?>)</span>
                    </div>
                    <?php if(isset($_SESSION['user_id'])): ?>
                    <form action="backend/tambah_wishlist.php" method="POST">
                        <input type="hidden" name="destination_id" value="<?= $d['id'] ?>">
                        <button type="submit" class="btn-card">+ Wishlist</button>
                    </form>
                    <?php else: ?>
                    <a href="loginWeb.php" class="btn-card">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endwhile; else: ?>
        <div class="empty-state">
            <div class="icon"><?= $meta['icon'] ?></div>
            <h3>Belum ada destinasi <?= htmlspecialchars($kat) ?></h3>
            <p>Cek kategori lain atau tambahkan via admin!</p>
        </div>
        <?php endif; ?>
    </div>

    <div class="other-cats">
        <h3>Jelajahi Kategori Lainnya</h3>
        <div class="other-grid">
            <?php foreach(array_filter(array_keys($cats), fn($c) => $c !== $kat) as $c): ?>
            <a href="kategori.php?kat=<?= $c ?>" class="other-card">
                <img src="<?= $imgs[$c] ?>" alt="<?= $c ?>" onerror="this.src='assets/background.bali.png'">
                <div class="other-label"><?= $cats[$c] ?> <?= $c ?></div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<footer>
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:20px">
        <span style="font-size:18px;font-weight:700;color:#4DD0C4"> Eksplore Bali</span>
        <a href="index.php" style="color:#4DD0C4;text-decoration:none;font-size:14px;font-weight:600">← Kembali ke Beranda</a>
    </div>
    <div class="footer-bottom">© 2025 Eksplore Bali. All rights reserved.</div>
</footer>
</body>
</html>