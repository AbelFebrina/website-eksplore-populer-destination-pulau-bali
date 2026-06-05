<?php
session_start();
include "backend/koneksi.php";

$search   = isset($_GET['search'])   ? mysqli_real_escape_string($koneksi, $_GET['search'])   : '';
$kategori = isset($_GET['kategori']) ? mysqli_real_escape_string($koneksi, $_GET['kategori']) : '';

$where = "WHERE 1=1";
if ($search)   $where .= " AND (nama LIKE '%$search%' OR lokasi LIKE '%$search%' OR kategori LIKE '%$search%')";
if ($kategori) $where .= " AND kategori = '$kategori'";

$rekomendasi  = mysqli_query($koneksi, "SELECT * FROM destinations $where ORDER BY rating DESC LIMIT 8");
$populer      = mysqli_query($koneksi, "SELECT * FROM destinations ORDER BY rating DESC LIMIT 6");

$wishlist_ids = [];
if (isset($_SESSION['user_id'])) {
    $wq = mysqli_query($koneksi, "SELECT destination_id FROM wishlists WHERE user_id=" . (int)$_SESSION['user_id']);
    while ($wr = mysqli_fetch_assoc($wq)) {
        $wishlist_ids[] = (int)$wr['destination_id'];
    }
}

$total_hasil = ($search || $kategori)
    ? mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM destinations $where"))
    : 0;

$heroImg = file_exists("assets/hero_config.txt")
    ? trim(file_get_contents("assets/hero_config.txt"))
    : "background.bali.png";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eksplore - Popular Destinations Pulau Bali</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: #333;
            background: #f8fafc;
        }

        /* ===== HEADER ===== */
        header {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 100;
            background: rgba(255, 255, 255, 0.97);
            backdrop-filter: blur(10px);
            padding: 12px 60px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .logo img {
            height: 38px;
        }

        .logo span {
            font-size: 20px;
            font-weight: 700;
            color: #009688;
        }

        nav ul {
            list-style: none;
            display: flex;
            gap: 35px;
        }

        nav ul li a {
            text-decoration: none;
            color: #555;
            font-weight: 500;
            font-size: 14px;
            transition: color 0.3s;
        }

        nav ul li a:hover {
            color: #009688;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .btn-login {
            background: #009688;
            color: white;
            padding: 9px 22px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }

        .btn-logout {
            background: #e53935;
            color: white;
            padding: 9px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
        }

        .user-greeting {
            font-size: 14px;
            font-weight: 600;
            color: #009688;
        }
        /* ===== PROFILE DROPDOWN ===== */
.profile-dropdown {
    position: relative;
}

.profile-trigger {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    padding: 5px 10px;
    border-radius: 25px;
    transition: background 0.3s;
}

.profile-trigger:hover {
    background: #f0f0f0;
}

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

.header-username {
    font-size: 13px;
    font-weight: 600;
    color: #009688;
}

.dropdown-arrow {
    font-size: 11px;
    color: #888;
    transition: transform 0.3s;
}

.profile-dropdown.open .dropdown-arrow {
    transform: rotate(180deg);
}

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

.profile-dropdown.open .dropdown-menu {
    display: block;
}

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

.dropdown-item:hover {
    background: #f8fafc;
    color: #009688;
}

.dropdown-divider {
    height: 1px;
    background: #f0f0f0;
    margin: 4px 0;
}

.dropdown-logout {
    color: #e53935;
}

.dropdown-logout:hover {
    background: #ffebee;
    color: #e53935;
}

        /* ===== HERO ===== */
        .hero {
            min-height: 100vh;
            background: linear-gradient(135deg, rgba(0, 50, 45, 0.75) 0%, rgba(0, 0, 0, 0.4) 100%),
                        url('assets/<?= htmlspecialchars($heroImg) ?>') center / cover no-repeat;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 120px 60px 60px;
            color: white;
        }

        .hero-badge {
            background: rgba(0, 150, 136, 0.8);
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 20px;
            letter-spacing: 1px;
            width: fit-content;
        }

        .hero h1 {
            font-size: 52px;
            font-weight: 700;
            line-height: 1.15;
            margin-bottom: 20px;
            max-width: 600px;
        }

        .hero h1 span {
            color: #4DD0C4;
        }

        .hero p {
            font-size: 16px;
            opacity: 0.85;
            max-width: 500px;
            margin-bottom: 35px;
            line-height: 1.7;
        }

        .hero-btns {
            display: flex;
            gap: 15px;
            margin-bottom: 50px;
        }

        .btn-primary {
            background: #009688;
            color: white;
            padding: 14px 28px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            transition: background 0.3s, transform 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
            cursor: pointer;
        }

        .btn-primary:hover {
            background: #00796b;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.4);
            color: white;
            padding: 14px 28px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            backdrop-filter: blur(5px);
            transition: background 0.3s;
            display: inline-flex;
            align-items: center;
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.25);
        }

        /* ===== SEARCH CARD ===== */
        .search-card {
            background: white;
            border-radius: 16px;
            padding: 20px 25px;
            display: flex;
            gap: 15px;
            align-items: flex-end;
            max-width: 750px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
        }

        .search-group {
            flex: 1;
        }

        .search-group label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            color: #009688;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .search-group input,
        .search-group select {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            color: #333;
            outline: none;
            transition: border-color 0.3s;
            font-family: 'Poppins', sans-serif;
        }

        .search-group input:focus,
        .search-group select:focus {
            border-color: #009688;
        }

        .btn-search {
            background: #009688;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            transition: background 0.3s;
            font-family: 'Poppins', sans-serif;
        }

        .btn-search:hover {
            background: #00796b;
        }

        /* ===== SEARCH RESULT BANNER ===== */
        .search-result-banner {
            background: #e0f2f1;
            border-left: 4px solid #009688;
            padding: 14px 24px;
            margin: 80px 60px 0;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .search-result-banner span {
            font-size: 14px;
            font-weight: 600;
            color: #00695c;
        }

        .search-result-banner a {
            color: #e53935;
            font-size: 13px;
            text-decoration: none;
            font-weight: 600;
        }
     /* ===== GALLERY EXPERIENCE ===== */
        .experience{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:80px;

    padding:100px 100px;
    background:#fff;
}

.exp-text{
    flex:1;
    max-width:500px;

    margin-left:40px; /* geser ke kanan */
    margin-top:-50px;
}

.exp-text h2{
    font-size:40px;
    line-height:1.1;
    margin-bottom:20px;
}

.exp-text p{
    font-size:20px;
    line-height:1.8;
    margin-bottom:25px;
}

.exp-list{
    margin-bottom:35px; /* tombol turun */
}

.btn-primary{
    display:inline-block;
    margin-top:15px;
}

.exp-images{
    flex:1;

    display:grid;
    grid-template-columns:1.5fr 1fr 1fr;
    grid-template-rows:170px 170px;
    gap:5px;
}

.exp-images img{
    width:100%;
    height:100%;
    object-fit:cover;
    border-radius:7px;
    box-shadow:0 4px 10px rgba(0,0,0,.12);
}

.img1{
    grid-column:1 / 3;
}

.img2{
    grid-column:3;
}

.img3{
    grid-column:1;
}

.img4{
    grid-column:2;
}

.img5{
    grid-column:3;
}
        /* ===== CATEGORIES ===== */
        .search-section {
            padding: 70px 60px;
            background: #f8fafc;
            text-align: center;
        }

        .search-section h2 {
            font-size: 32px;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 10px;
        }

        .search-section p {
            font-size: 15px;
            color: #888;
            margin-bottom: 40px;
        }

        .category-grid {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
            margin-bottom: 10px;
        }

        .cat-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            transition: transform 0.3s;
            text-decoration: none;
        }

        .cat-item:hover {
            transform: translateY(-5px);
        }

        .cat-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            overflow: hidden;
            border: 3px solid #e0f2f1;
            transition: border-color 0.3s;
        }

        .cat-item:hover .cat-icon {
            border-color: #009688;
        }

        .cat-icon img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .cat-item span {
            font-size: 13px;
            font-weight: 600;
            color: #444;
        }

        /* ===== RECOMMENDATIONS ===== */
        .rekomendasi {
            padding: 70px 60px;
            background: white;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 35px;
        }

        .section-header h2 {
            font-size: 28px;
            font-weight: 700;
            color: #1a1a2e;
        }

        .section-header a {
            color: #009688;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }

        .cards-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 22px;
        }

        .dest-card {
            background: white;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s, box-shadow 0.3s;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
        }

        .dest-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.13);
        }

        .dest-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            display: block;
            flex-shrink: 0;
        }

        .dest-card .card-body {
            padding: 14px 16px 16px;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .dest-card .card-kategori {
            font-size: 11px;
            font-weight: 600;
            color: #009688;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .dest-card .card-nama {
            font-size: 15px;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 4px;
        }

        .dest-card .card-lokasi {
            font-size: 12px;
            color: #888;
            margin-bottom: 10px;
            flex: 1;
        }

        .dest-card .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
        }

        .dest-card .rating {
            color: #ffb400;
            font-size: 13px;
        }

        .btn-card {
            background: #e0f2f1;
            color: #009688;
            border: none;
            padding: 7px 13px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s, color 0.3s;
            text-decoration: none;
            display: inline-block;
            white-space: nowrap;
        }

        .btn-card:hover {
            background: #009688;
            color: white;
        }

        .btn-card-wish {
            background: #e53935 !important;
            color: white !important;
        }

        .btn-card-wish:hover {
            background: #c62828 !important;
        }

        /* ===== REVIEWS ===== */
        .testimoni {
            padding: 70px 60px;
            background: #f8fafc;
            text-align: center;
        }

        .testimoni h2 {
            font-size: 32px;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 10px;
        }

        .testimoni .sub {
            font-size: 15px;
            color: #888;
            margin-bottom: 50px;
        }

        .testi-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            max-width: 1000px;
            margin: 0 auto;
        }

        .testi-card {
            background: white;
            border-radius: 16px;
            padding: 30px 25px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            text-align: left;
            transition: transform 0.3s;
        }

        .testi-card:hover {
            transform: translateY(-5px);
        }

        .testi-card .stars {
            color: #ffb400;
            font-size: 16px;
            margin-bottom: 15px;
        }

        .testi-card p {
            font-size: 14px;
            color: #666;
            line-height: 1.7;
            margin-bottom: 20px;
            font-style: italic;
        }

        .testi-author {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .testi-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: linear-gradient(135deg, #009688, #4DD0C4);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 16px;
            flex-shrink: 0;
        }

        .testi-name {
            font-size: 14px;
            font-weight: 700;
            color: #1a1a2e;
        }

        .testi-from {
            font-size: 12px;
            color: #888;
        }

        /* ===== POPULAR ===== */
        .populer-section {
            padding: 70px 60px;
            background: #1a1a2e;
            color: white;
        }

        .populer-section .section-header h2 {
            color: white;
        }

        .populer-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .pop-card {
            position: relative;
            border-radius: 16px;
            overflow: hidden;
            cursor: pointer;
            height: 220px;
            text-decoration: none;
            display: block;
        }

        .pop-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s;
            display: block;
        }

        .pop-card:hover img {
            transform: scale(1.08);
        }

        .pop-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 20px;
            background: linear-gradient(transparent, rgba(0, 0, 0, 0.8));
            color: white;
        }

        .pop-overlay h4 {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .pop-overlay span {
            font-size: 12px;
            opacity: 0.85;
        }

        .pop-rating {
            position: absolute;
            top: 12px;
            right: 12px;
            background: rgba(255, 184, 0, 0.9);
            color: #333;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }

       /* ===== FAQ ===== */
.faq {
    padding: 100px 80px;
    background: #f7f7f7;
    display: flex;
    gap: 80px;
    align-items: flex-start;
}

.faq-left {
    width: 35%;
}

.faq-badge {
    font-size: 11px;
    color: #ff6b35;
    text-transform: uppercase;
    font-weight: 600;
    margin-bottom: 10px;
}

.faq-left h2 {
    font-size: 42px;
    line-height: 1.1;
    font-weight: 700;
    color: #082c44;
    margin-bottom: 40px;
}

.faq-left img {
    width: 100%;
    max-width: 320px;
    height: 320px;
    object-fit: cover;
    border-radius: 0;
    display: block;
}

.faq-right {
    flex: 1;
    margin-top: 90px;
}

/* FAQ CARD */
details {
    background: white;
    margin-bottom: 12px;
    border: none;
}

summary {
    padding: 22px 28px;
    cursor: pointer;
    list-style: none;
    font-size: 13px;
    font-weight: 600;
    color: #082c44;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

summary::-webkit-details-marker {
    display: none;
}

summary::after {
    content: "⌄";
    font-size: 18px;
    color: #666;
}

details[open] summary::after {
    transform: rotate(180deg);
}

details p {
    padding: 0 28px 20px;
    font-size: 14px;
    color: #666;
    line-height: 1.8;
}

/* garis hijau bawah FAQ aktif */
details[open] {
    border-bottom: 3px solid #11b8b2;
}

/* ===== FOOTER ===== */
footer{
    background:#0d1b1e;
    color:white;
    padding:50px 60px 30px;
}

.footer-grid{
    display:grid;
    grid-template-columns:2fr 1fr 1fr 1fr;
    gap:40px;
    margin-bottom:40px;
}

.footer-brand p{
    font-size:14px;
    color:#aaa;
    line-height:1.7;
    margin-top:12px;
}

.footer-col h4{
    font-size:15px;
    font-weight:700;
    margin-bottom:15px;
    color:white;
}

.footer-col a{
    display:block;
    font-size:13px;
    color:#aaa;
    text-decoration:none;
    margin-bottom:8px;
    transition:.3s;
}

.footer-col a:hover{
    color:#4DD0C4;
}

.footer-bottom{
    border-top:1px solid #333;
    padding-top:25px;
    text-align:center;
    font-size:13px;
    color:#666;
}
        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            header                  { padding: 12px 20px; }
            .hero                   { padding: 100px 20px 50px; }
            .hero h1                { font-size: 32px; }
            .search-card            { flex-direction: column; }
            .experience, .faq       { flex-direction: column; padding: 50px 20px; }
            .rekomendasi,
            .populer-section,
            .search-section,
            .testimoni              { padding: 50px 20px; }
            .cards-grid             { grid-template-columns: 1fr 1fr; }
            .testi-grid             { grid-template-columns: 1fr; }
            .populer-grid           { grid-template-columns: 1fr 1fr; }
            .footer-grid            { grid-template-columns: 1fr 1fr; }
            .search-result-banner   { margin: 80px 20px 0; }
        }

        @media (max-width: 480px) {
            .cards-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<script>
    function toggleDropdown() {
        document.getElementById('profileDropdown').classList.toggle('open');
    }

    document.addEventListener('click', function(e) {
        const dropdown = document.getElementById('profileDropdown');
        if (dropdown && !dropdown.contains(e.target)) {
            dropdown.classList.remove('open');
        }
    });

    function toggleWishlistCard(btn) {
        const destId = btn.dataset.dest;
        const fd     = new FormData();
        fd.append('destination_id', destId);
        fetch('backend/tambah_wishlist.php', {
            method:  'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body:    fd
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'added') {
                btn.textContent = '❤ ';
                btn.classList.add('btn-card-wish');
            } else {
                btn.textContent = '🤍';
                btn.classList.remove('btn-card-wish');
            }
        })
        .catch(() => alert('Failed, please try again.'));
    }
</script>
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
            <li><a href="#rekomendasi">Recommendations</a></li>
            <li><a href="#populer">Popular</a></li>
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
            <div class="dropdown-menu" id="dropdownMenu">
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
<section class="hero">
    <div class="hero-badge"> BEST DESTINATIONS</div>
    <h1>Popular Destination<br><span>Pulau Bali</span></h1>
    <p>Discover the hidden beauty of Bali — from exotic beaches and majestic volcanoes to rich and authentic culture.</p>
    <div class="hero-btns">
        <a href="#rekomendasi" class="btn-primary">Explore Now</a>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="loginWeb.php" class="btn-secondary">Login / Register</a>
        <?php else: ?>
            <a href="wishlist.php" class="btn-secondary">❤ My Wishlist</a>
        <?php endif; ?>
    </div>
    <form action="index.php" method="GET">
        <div class="search-card">
            <div class="search-group">
                <label>Search Destinations</label>
                <input type="text" name="search"
                       placeholder="Pantai Kuta, Gunung Agung..."
                       value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="search-group">
                <label>Category</label>
                <select name="kategori">
                    <option value="">All</option>
                    <option value="Pantai"  <?= $kategori == 'Pantai'  ? 'selected' : '' ?>> Pantai</option>
                    <option value="Gunung"  <?= $kategori == 'Gunung'  ? 'selected' : '' ?>>Gunung</option>
                    <option value="Reguler" <?= $kategori == 'Reguler' ? 'selected' : '' ?>> Reguler</option>
                    <option value="Budaya"  <?= $kategori == 'Budaya'  ? 'selected' : '' ?>> Budaya</option>
                    <option value="Alam"    <?= $kategori == 'Alam'    ? 'selected' : '' ?>> Alam</option>
                </select>
            </div>
            <button type="submit" class="btn-search"> Search</button>
        </div>
    </form>
</section>

<!-- SEARCH RESULT BANNER -->
<?php if ($search || $kategori): ?>
<div class="search-result-banner">
    <span>
        🔍 Results for:
        <?php if ($search)   echo '"' . htmlspecialchars($search) . '"'; ?>
        <?php if ($kategori) echo ' | Category: <strong>' . htmlspecialchars($kategori) . '</strong>'; ?>
        — <strong><?= $total_hasil ?> found</strong>
    </span>
    <a href="index.php">✕ Clear Filter</a>
</div>
<?php endif; ?>

<!-- EXPERIENCE -->
<section class="experience">

    <div class="exp-text">
        <div class="section-badge">✦ ABOUT BALI</div>

        <h2>
            Experience the World<br>
            With Bali
        </h2>

        <p>
            Bali is more than just an island — it is a soulful experience.
            Discover the perfect blend of culture, nature, and unmatched hospitality.
        </p>

        <ul class="exp-list">
            <li>More than 100+ top tourist destinations</li>
            <li>Trusted & up-to-date travel guides</li>
            <li>The best travel experience for everyone</li>
        </ul>

        <a href="#rekomendasi" class="btn-primary">
            Explore Destinations >
        </a>
    </div>

    <div class="exp-images">
        <img src="assets/sawah1.jpg" class="img1">
        <img src="assets/pantai2.jpg" class="img2">
        <img src="assets/PantaiMelasti.png" class="img3">
        <img src="assets/img9.png" class="img4">
        <img src="assets/lucu.jpg" class="img5">
    </div>

</section>

<!-- CATEGORIES -->
<section class="search-section">
    <div class="section-badge">✦ CATEGORIES</div>
    <h2>Search the Best Place in the World</h2>
    <p>Click a category to explore destinations that match your interest</p>
    <div class="category-grid">
        <a href="index.php?kategori=Pantai" class="cat-item">
            <div class="cat-icon">
                <img src="assets/PantaiKuta.png" alt="Pantai" onerror="this.src='assets/background.bali.png'">
            </div>
            <span>Beach</span>
        </a>
        <a href="index.php?kategori=Gunung" class="cat-item">
            <div class="cat-icon">
                <img src="assets/GunungAgung.png" alt="Gunung" onerror="this.src='assets/background.bali.png'">
            </div>
            <span> Mountain</span>
        </a>
        <a href="index.php?kategori=Reguler" class="cat-item">
            <div class="cat-icon">
                <img src="assets/PuraTanahLot.png" alt="Reguler" onerror="this.src='assets/background.bali.png'">
            </div>
            <span> Regular</span>
        </a>
        <a href="index.php?kategori=Budaya" class="cat-item">
            <div class="cat-icon">
                <img src="assets/DesaPanglipuran.png" alt="Budaya" onerror="this.src='assets/background.bali.png'">
            </div>
            <span>Culture</span>
        </a>
        <a href="index.php?kategori=Alam" class="cat-item">
            <div class="cat-icon">
                <img src="assets/danau beratan.png" alt="Alam" onerror="this.src='assets/background.bali.png'">
            </div>
            <span> Nature</span>
        </a>
    </div>
</section>

<!-- RECOMMENDATIONS -->
<section class="rekomendasi" id="rekomendasi">
    <div class="section-header">
        <div>
            <div class="section-badge">✦ RECOMMENDATIONS</div>
            <h2><?= ($search || $kategori) ? 'Search Results' : 'Perfect Recommendations For Your Next Trip' ?></h2>
        </div>
        <?php if ($search || $kategori): ?>
            <a href="index.php">View All →</a>
        <?php endif; ?>
    </div>
    <div class="cards-grid">
        <?php
        if ($rekomendasi && mysqli_num_rows($rekomendasi) > 0):
            while ($d = mysqli_fetch_assoc($rekomendasi)):
                $in_wish = in_array((int)$d['id'], $wishlist_ids);
        ?>
        <div class="dest-card">
            <a href="detail.php?id=<?= $d['id'] ?>" style="text-decoration:none; color:inherit">
                <img src="assets/<?= htmlspecialchars($d['gambar']) ?>"
                     alt="<?= htmlspecialchars($d['nama']) ?>"
                     onerror="this.src='assets/background.bali.png'">
            </a>
            <div class="card-body">
                <div class="card-kategori"><?= htmlspecialchars($d['kategori']) ?></div>
                <a href="detail.php?id=<?= $d['id'] ?>" style="text-decoration:none; color:inherit">
                    <div class="card-nama"><?= htmlspecialchars($d['nama']) ?></div>
                </a>
                <div class="card-lokasi">📍 <?= htmlspecialchars($d['lokasi']) ?></div>
                <div class="card-footer">
                    <div class="rating">
                        <?php
                        $r = round($d['rating']);
                        for ($i = 1; $i <= 5; $i++) echo $i <= $r ? '★' : '☆';
                        ?>
                        <span style="color:#666; font-size:12px">(<?= $d['rating'] ?>)</span>
                    </div>
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <button class="btn-card <?= $in_wish ? 'btn-card-wish' : '' ?>"
                            data-dest="<?= $d['id'] ?>"
                            onclick="toggleWishlistCard(this)">
                        <?= $in_wish ? '❤ ' : '🤍 ' ?>
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
            endwhile;
        else:
        ?>
        <div style="grid-column:1/-1; text-align:center; padding:60px 20px">
            <div style="font-size:48px; margin-bottom:15px">🔍</div>
            <p style="color:#888; font-size:16px; font-weight:600">No destinations found</p>
            <a href="index.php" class="btn-primary" style="display:inline-flex; margin-top:20px">View All</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- REVIEWS -->
<section class="testimoni">
    <div class="section-badge">✦ REVIEWS</div>
    <h2>What Our Travelers Say About Us</h2>
    <p class="sub">Real experiences from travelers who have visited Bali</p>
    <div class="testi-grid">
        <div class="testi-card">
            <div class="stars">★★★★★</div>
            <p>"Bali is a hidden paradise. Kuta Beach and Tanah Lot truly captivated my heart!"</p>
            <div class="testi-author">
                <div class="testi-avatar">A</div>
                <div>
                    <div class="testi-name">Andi Pratama</div>
                    <div class="testi-from">Jakarta, Indonesia</div>
                </div>
            </div>
        </div>
        <div class="testi-card">
            <div class="stars">★★★★★</div>
            <p>"Climbing Mount Agung was absolutely amazing. The view from the top is unforgettable."</p>
            <div class="testi-author">
                <div class="testi-avatar" style="background:linear-gradient(135deg,#e91e63,#ff5722)">S</div>
                <div>
                    <div class="testi-name">Sarah Wijaya</div>
                    <div class="testi-from">Surabaya, Indonesia</div>
                </div>
            </div>
        </div>
        <div class="testi-card">
            <div class="stars">★★★★☆</div>
            <p>"Penglipuran Village is the cleanest place I have ever visited. The culture is beautifully preserved."</p>
            <div class="testi-author">
                <div class="testi-avatar" style="background:linear-gradient(135deg,#ff9800,#f44336)">R</div>
                <div>
                    <div class="testi-name">Raka Mahendra</div>
                    <div class="testi-from">Bali, Indonesia</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- POPULAR -->
<section class="populer-section" id="populer">
    <div class="section-header">
        <div>
            <div class="section-badge" style="color:#4DD0C4">✦ POPULAR</div>
            <h2>Unveiling the World's Top Travel Hotspots</h2>
        </div>
    </div>
    <div class="populer-grid">
        <?php
        if ($populer && mysqli_num_rows($populer) > 0):
            while ($p = mysqli_fetch_assoc($populer)):
        ?>
        <a class="pop-card" href="detail.php?id=<?= $p['id'] ?>">
            <img src="assets/<?= htmlspecialchars($p['gambar']) ?>"
                 alt="<?= htmlspecialchars($p['nama']) ?>"
                 onerror="this.src='assets/background.bali.png'">
            <div class="pop-rating">★ <?= $p['rating'] ?></div>
            <div class="pop-overlay">
                <h4><?= htmlspecialchars($p['nama']) ?></h4>
                <span>📍 <?= htmlspecialchars($p['lokasi']) ?></span>
            </div>
        </a>
        <?php
            endwhile;
        else:
        ?>
        <p style="color:#aaa; grid-column:1/-1; text-align:center; padding:40px">No data available.</p>
        <?php endif; ?>
    </div>
</section>

<!-- FAQ -->
<section class="faq">

    <div class="faq-left">
        <div class="faq-badge">FAQ</div>

        <h2>
            Unpacking Your Travel
            Questions
        </h2>

        <img src="assets/FAQ1.jpg" alt="">
    </div>

    <div class="faq-right">

        <details open>
            <summary>
                What destinations do you offer tours to
            </summary>

            <p>
                Explore Bali's most iconic destinations including
                Ubud, Kuta Beach, Tanah Lot, Uluwatu Temple,
                Nusa Penida, GWK Cultural Park, and many more.
            </p>
        </details>

        <details>
            <summary>
                What types of tours are available?
            </summary>

            <p>
                We provide cultural tours, beach tours,
                adventure tours, and custom travel packages.
            </p>
        </details>

        <details>
            <summary>
                What is included in the tour package?
            </summary>

            <p>
                Transportation, guide services, entrance tickets,
                and selected meals depending on the package.
            </p>
        </details>

        <details>
            <summary>
                What should I pack for my trip?
            </summary>

            <p>
                Comfortable clothing, sunscreen, sunglasses,
                and personal necessities.
            </p>
        </details>

    </div>

</section>

<!-- FOOTER -->
<footer>
    <div class="footer-grid">
        <div class="footer-brand">
            <a href="index.php" class="logo">
                <img src="assets/Gemini_Generated_Image_h0z3whh0z3whh0z3-removebg-preview 2.png"
                     alt="Logo"
                     style="height:35px"
                     onerror="this.style.display='none'">
                <span style="color:white; font-size:18px; font-weight:700">Eksplore</span>
            </a>
            <p>The best platform to discover and plan your travel journey to the island of Bali.</p>
        </div>
        <div class="footer-col">
            <h4>Destinations</h4>
            <a href="index.php?kategori=Pantai"> Beach</a>
            <a href="index.php?kategori=Gunung"> Mountain</a>
            <a href="index.php?kategori=Reguler"> Regular</a>
            <a href="index.php?kategori=Budaya"> Culture</a>
            <a href="index.php?kategori=Alam"> Nature</a>
        </div>
        <div class="footer-col">
            <h4>Services</h4>
            <a href="#rekomendasi">Recommendations</a>
            <a href="#populer">Popular Destinations</a>
            <a href="wishlist.php">Wishlist</a>
            <a href="loginWeb.php">Login</a>
        </div>
        <div class="footer-col">
            <h4>Contact</h4>
            <a href="mailto:support@explorebali.com">support@explorebali.com</a>
            <a href="#">Instagram</a>
        </div>
    </div>
    <div class="footer-bottom">© 2025 Eksplore Bali. All rights reserved.</div>
</footer>

<script>
    function toggleWishlistCard(btn) {
        const destId = btn.dataset.dest;
        const fd     = new FormData();

        fd.append('destination_id', destId);

        fetch('backend/tambah_wishlist.php', {
            method:  'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body:    fd
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'added') {
                btn.textContent = '❤ ';
                btn.classList.add('btn-card-wish');
            } else {
                btn.textContent = '🤍';
                btn.classList.remove('btn-card-wish');
            }
        })
        .catch(() => alert('Failed, please try again.'));
    }
</script>
</body>
</html>