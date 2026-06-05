<?php
session_start();
include "backend/koneksi.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: loginWeb.php");
    exit();
}

$uid  = $_SESSION['user_id'];

$user = mysqli_fetch_assoc(
    mysqli_query($koneksi, "SELECT * FROM tb_akun WHERE id=$uid")
);
$my_reviews = mysqli_query($koneksi,
    "SELECT r.*, d.nama AS dest_nama, d.gambar AS dest_gambar
     FROM reviews r
     JOIN destinations d ON r.destination_id = d.id
     WHERE r.user_id = $uid
     ORDER BY r.created_at DESC"
);

$total_wish = mysqli_fetch_assoc(
    mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM wishlists WHERE user_id=$uid")
)['total'];

$total_reviews = mysqli_num_rows($my_reviews);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Explore Bali</title>
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

        nav ul li a:hover {
            color: #009688;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 12px;
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

        /* ===== PROFILE HERO ===== */
        .profile-hero {
            background: linear-gradient(135deg, #009688, #00BCD4);
            padding: 50px 60px 70px;
            color: white;
        }

        .profile-hero-inner {
            max-width: 900px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            gap: 30px;
        }

        .avatar-wrap {
            position: relative;
        }

        .avatar-img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
        }

        .avatar-placeholder {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 38px;
            font-weight: 700;
            border: 4px solid white;
        }

        .profile-info h1 {
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .profile-info p {
            font-size: 14px;
            opacity: 0.85;
            margin-bottom: 14px;
        }

        .profile-stats {
            display: flex;
            gap: 24px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-item .num {
            font-size: 22px;
            font-weight: 700;
        }

        .stat-item .lbl {
            font-size: 12px;
            opacity: 0.8;
        }

        /* ===== CONTAINER ===== */
        .container {
            max-width: 900px;
            margin: -30px auto 60px;
            padding: 0 20px;
        }

        /* ===== TABS ===== */
        .tabs {
            display: flex;
            background: white;
            border-radius: 14px 14px 0 0;
            overflow: hidden;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.07);
        }

        .tab-btn {
            flex: 1;
            padding: 16px;
            border: none;
            background: none;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            font-weight: 600;
            color: #888;
            cursor: pointer;
            transition: all 0.3s;
            border-bottom: 3px solid transparent;
        }

        .tab-btn.active {
            color: #009688;
            border-bottom-color: #009688;
            background: #f8fafc;
        }

        .tab-btn:hover {
            color: #009688;
            background: #f8fafc;
        }

        .tab-content {
            background: white;
            border-radius: 0 0 14px 14px;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.07);
            padding: 28px;
        }

        .tab-panel {
            display: none;
        }

        .tab-panel.active {
            display: block;
        }

        /* ===== EDIT PROFILE FORM ===== */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #555;
            margin-bottom: 6px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 11px 14px;
            border: 1.5px solid #e0e0e0;
            border-radius: 9px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            outline: none;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #009688;
        }

        .form-group textarea {
            resize: vertical;
            height: 90px;
        }

        .avatar-preview-wrap {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 20px;
        }

        .avatar-preview {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #009688;
        }

        .avatar-preview-placeholder {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: linear-gradient(135deg, #009688, #4DD0C4);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 26px;
            font-weight: 700;
            border: 3px solid #009688;
        }

        .file-label {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f5f5f5;
            padding: 9px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            color: #555;
            cursor: pointer;
            transition: background 0.3s;
        }

        .file-label:hover {
            background: #e0f2f1;
            color: #009688;
        }

        .file-label input {
            display: none;
        }

        .btn-save {
            background: #009688;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 9px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            font-family: 'Poppins', sans-serif;
        }

        .btn-save:hover {
            background: #00796b;
        }

        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid #4CAF50;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
        }

        /* ===== MY REVIEWS ===== */
        .reviews-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .review-mini {
            background: #f8fafc;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e0e0e0;
        }

        .review-mini img {
            width: 100%;
            height: 140px;
            object-fit: cover;
            display: block;
        }

        .review-mini .rm-body {
            padding: 12px 14px;
        }

        .review-mini .rm-dest {
            font-size: 12px;
            font-weight: 600;
            color: #009688;
            margin-bottom: 4px;
        }

        .review-mini .rm-cap {
            font-size: 12px;
            color: #555;
            line-height: 1.5;
            margin-bottom: 8px;
        }

        .review-mini .rm-foot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            color: #888;
        }

        .rm-delete {
            color: #e53935;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
        }

        .rm-delete:hover {
            text-decoration: underline;
        }

        .empty-tab {
            text-align: center;
            padding: 40px;
            color: #aaa;
        }

        .empty-tab .icon {
            font-size: 44px;
            margin-bottom: 12px;
        }

        /* ===== FOOTER ===== */
        footer {
            background: #0d1b1e;
            color: white;
            padding: 40px 60px 24px;
            display: flex;
            justify-content: space-around;
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
            header          { padding: 12px 20px; }
            .profile-hero   { padding: 40px 20px 60px; }
            .profile-hero-inner { flex-direction: column; text-align: center; }
            .profile-stats  { justify-content: center; }
            .reviews-grid   { grid-template-columns: 1fr; }
            footer          { flex-direction: column; gap: 20px; padding: 30px 20px; }
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
            <li><a href="wishlist.php">Wishlist ❤</a></li>
            <li><a href="profile.php" style="color:#009688">Profile</a></li>
        </ul>
    </nav>
    <div class="header-right">
        <a href="backend/logout.php" class="btn-logout">Logout</a>
    </div>
</header>

<!-- PROFILE HERO -->
<div class="profile-hero">
    <div class="profile-hero-inner">
        <div class="avatar-wrap">
            <?php if (!empty($user['avatar'])): ?>
                <img src="upload_gambar/<?= htmlspecialchars($user['avatar']) ?>"
                     class="avatar-img"
                     alt="Avatar">
            <?php else: ?>
                <div class="avatar-placeholder">
                    <?= strtoupper(substr($user['username'], 0, 1)) ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="profile-info">
            <h1><?= htmlspecialchars($user['username']) ?></h1>
            <p><?= !empty($user['bio']) ? htmlspecialchars($user['bio']) : 'Bali travel enthusiast ' ?></p>
            <div class="profile-stats">
                <div class="stat-item">
                    <div class="num"><?= $total_wish ?></div>
                    <div class="lbl">Wishlist</div>
                </div>
                <div class="stat-item">
                    <div class="num"><?= $total_reviews ?></div>
                    <div class="lbl">Reviews</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TABS -->
<div class="container">

    <?php if (isset($_GET['sukses'])): ?>
    <div class="alert-success" style="margin-bottom:16px">
        ✅ Profile updated successfully!
    </div>
    <?php endif; ?>

    <div class="tabs">
        <button class="tab-btn active" onclick="showTab('edit')"> Edit Profile</button>
        <button class="tab-btn" onclick="showTab('reviews')"> My Reviews</button>
        <button class="tab-btn" onclick="showTab('wishlist')">Wishlist</button>
    </div>

    <div class="tab-content">

        <!-- EDIT PROFILE -->
        <div class="tab-panel active" id="tab-edit">
            <form action="backend/edit_profile.php" method="POST" enctype="multipart/form-data">
                <div class="avatar-preview-wrap">
                    <?php if (!empty($user['avatar'])): ?>
                        <img src="upload_gambar/<?= htmlspecialchars($user['avatar']) ?>"
                             class="avatar-preview"
                             id="av-prev"
                             alt="Avatar">
                    <?php else: ?>
                        <div class="avatar-preview-placeholder" id="av-prev">
                            <?= strtoupper(substr($user['username'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    <label class="file-label">
                        📷 Change Photo
                        <input type="file" name="avatar" accept="image/*"
                               onchange="previewAvatar(this)">
                    </label>
                </div>
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="username"
                           value="<?= htmlspecialchars($user['username']) ?>"
                           required>
                </div>
                <div class="form-group">
                    <label>Email (cannot be changed)</label>
                    <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled
                           style="background:#f5f5f5; color:#888">
                </div>
                <div class="form-group">
                    <label>Bio</label>
                    <textarea name="bio"
                              placeholder="Tell others about yourself..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                </div>
                <button type="submit" class="btn-save"> Save Changes</button>
            </form>
        </div>

        <!-- MY REVIEWS -->
        <div class="tab-panel" id="tab-reviews">
            <?php if ($total_reviews == 0): ?>
            <div class="empty-tab">
                <div class="icon">📸</div>
                <p>No reviews yet. Visit a destination and share your experience!</p>
            </div>
            <?php else: ?>
            <div class="reviews-grid">
                <?php while ($rv = mysqli_fetch_assoc($my_reviews)): ?>
                <div class="review-mini">
                    <?php if (!empty($rv['foto'])): ?>
                        <img src="upload_gambar/<?= htmlspecialchars($rv['foto']) ?>"
                             alt="review"
                             onerror="this.style.display='none'">
                    <?php endif; ?>
                    <div class="rm-body">
                        <div class="rm-dest">📍 <?= htmlspecialchars($rv['dest_nama']) ?></div>
                        <?php if (!empty($rv['caption'])): ?>
                        <div class="rm-cap"><?= htmlspecialchars(mb_strimwidth($rv['caption'], 0, 80, '...')) ?></div>
                        <?php endif; ?>
                        <div class="rm-foot">
                            <span>❤ <?= $rv['likes'] ?> likes</span>
                            <a href="backend/proses_review.php?aksi=hapus&id=<?= $rv['id'] ?>&dest=<?= $rv['destination_id'] ?>"
                               class="rm-delete"
                               onclick="return confirm('Delete this review?')">
                                Delete
                            </a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- WISHLIST SHORTCUT -->
        <div class="tab-panel" id="tab-wishlist">
            <div class="empty-tab">
                <div class="icon">❤</div>
                <p style="margin-bottom:20px">You have <strong><?= $total_wish ?></strong> saved destinations.</p>
                <a href="wishlist.php"
                   style="background:#009688;color:white;padding:10px 24px;border-radius:8px;text-decoration:none;font-weight:600;font-size:14px">
                    View My Wishlist →
                </a>
            </div>
        </div>

    </div>
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

<script>
    function showTab(name) {
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.getElementById('tab-' + name).classList.add('active');
        event.target.classList.add('active');
    }

    function previewAvatar(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                const prev = document.getElementById('av-prev');
                if (prev.tagName === 'IMG') {
                    prev.src = e.target.result;
                } else {
                    const img = document.createElement('img');
                    img.src       = e.target.result;
                    img.className = 'avatar-preview';
                    img.id        = 'av-prev';
                    prev.replaceWith(img);
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
</body>
</html>