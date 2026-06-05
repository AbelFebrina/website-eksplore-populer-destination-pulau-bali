<?php
session_start();
include "../backend/koneksi.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../loginWeb.php");
    exit();
}

$total_dest  = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS t FROM destinations"))['t'];
$total_users = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS t FROM tb_akun WHERE role='user'"))['t'];
$total_kat   = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS t FROM kategori"))['t'];
$total_rev   = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS t FROM reviews"))['t'];

$recent_users = mysqli_query($koneksi,
    "SELECT * FROM tb_akun WHERE role='user' ORDER BY created_at DESC LIMIT 5"
);

$recent_dest = mysqli_query($koneksi,
    "SELECT d.*, k.nama AS kat_nama FROM destinations d
     LEFT JOIN kategori k ON d.kategori_id = k.id
     ORDER BY d.id DESC LIMIT 5"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Explore Bali</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f0f2f5;
            display: flex;
            min-height: 100vh;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: 240px;
            background: #1a1a2e;
            color: white;
            padding: 0;
            position: fixed;
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }

        .sidebar-brand {
            padding: 22px 24px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .sidebar-brand h2 {
            font-size: 15px;
            font-weight: 700;
            color: #4DD0C4;
        }

        .sidebar-brand p {
            font-size: 11px;
            color: #888;
            margin-top: 3px;
        }

        .sidebar-menu {
            padding: 16px 0;
            flex: 1;
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 24px;
            color: #aaa;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }

        .menu-item:hover,
        .menu-item.active {
            background: rgba(77, 208, 196, 0.1);
            color: #4DD0C4;
            border-left-color: #4DD0C4;
        }

        .menu-icon {
            font-size: 16px;
            width: 20px;
            text-align: center;
        }

        .sidebar-footer {
            padding: 20px 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }

        .btn-back-web {
            display: block;
            background: #009688;
            color: white;
            padding: 10px;
            border-radius: 8px;
            text-align: center;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            transition: background 0.3s;
        }

        .btn-back-web:hover {
            background: #00796b;
        }

        /* ===== MAIN ===== */
        .main {
            margin-left: 240px;
            flex: 1;
            padding: 28px;
        }

        /* ===== TOPBAR ===== */
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
        }

        .topbar h1 {
            font-size: 22px;
            font-weight: 700;
            color: #1a1a2e;
        }

        .admin-badge {
            background: #e0f2f1;
            color: #009688;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        /* ===== STAT CARDS ===== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
            margin-bottom: 28px;
        }

        .stat-card {
            border-radius: 14px;
            padding: 22px;
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            font-size: 32px;
        }

        .stat-label {
            font-size: 12px;
            font-weight: 500;
            opacity: 0.9;
        }

        .stat-num {
            font-size: 32px;
            font-weight: 700;
        }

        .stat-blue   { background: linear-gradient(135deg,  #009688, #009688); }
        .stat-green  { background: linear-gradient(135deg,  #009688,  #009688); }
        .stat-yellow { background: linear-gradient(135deg,  #009688,  #009688); }
        .stat-red    { background: linear-gradient(135deg,  #009688,  #009688); }

        /* ===== TABLES ===== */
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .table-card {
            background: white;
            border-radius: 14px;
            box-shadow: 0 3px 14px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }

        .table-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 22px;
            border-bottom: 1px solid #f0f0f0;
        }

        .table-card-header h3 {
            font-size: 15px;
            font-weight: 700;
            color: #1a1a2e;
        }

        .table-card-header a {
            color: #009688;
            font-size: 12px;
            text-decoration: none;
            font-weight: 600;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            padding: 12px 18px;
            text-align: left;
            font-size: 11px;
            font-weight: 600;
            color: #888;
            text-transform: uppercase;
            background: #f8fafc;
        }

        td {
            padding: 12px 18px;
            font-size: 13px;
            color: #333;
            border-bottom: 1px solid #f5f5f5;
            vertical-align: middle;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover {
            background: #fafafa;
        }

        .dest-thumb {
            width: 44px;
            height: 36px;
            object-fit: cover;
            border-radius: 6px;
        }

        .badge {
            display: inline-block;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-teal { background: #e0f2f1; color: #009688; }

        .av-mini {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: linear-gradient(135deg, #009688, #4DD0C4);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 12px;
        }

        @media (max-width: 1000px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .grid-2     { grid-template-columns: 1fr; }
        }

        @media (max-width: 700px) {
            .sidebar { display: none; }
            .main    { margin-left: 0; padding: 16px; }
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="sidebar-brand">
        <h2> Explore Bali</h2>
        <p>Admin Panel</p>
    </div>
    <div class="sidebar-menu">
        <a href="index.php" class="menu-item active">
            <span class="menu-icon"></span> Dashboard
        </a>
        <a href="kelola_destinasi.php" class="menu-item">
            <span class="menu-icon"></span> Destinasi
        </a>
        <a href="kelola_kategori.php" class="menu-item">
            <span class="menu-icon"></span> Kategori
        </a>
        <a href="users.php" class="menu-item">
            <span class="menu-icon"></span> Pengguna
        </a>
    </div>
    <div class="sidebar-footer">
        <a href="../index.php" class="btn-back-web">< Kembali ke Website</a>
    </div>
</div>

<!-- MAIN -->
<div class="main">

    <div class="topbar">
        <h1>Dashboard</h1>
        <span class="admin-badge"> Hello, <?= htmlspecialchars($_SESSION['username']) ?></span>
    </div>

    <!-- STATS -->
    <div class="stats-grid">
        <div class="stat-card stat-blue">
            <div class="stat-icon"></div>
            <div class="stat-label">Destinasi</div>
            <div class="stat-num"><?= $total_dest ?></div>
        </div>
        <div class="stat-card stat-green">
            <div class="stat-icon"></div>
            <div class="stat-label">Pengguna</div>
            <div class="stat-num"><?= $total_users ?></div>
        </div>
        <div class="stat-card stat-yellow">
            <div class="stat-icon"></div>
            <div class="stat-label">Kategorii</div>
            <div class="stat-num"><?= $total_kat ?></div>
        </div>
        <div class="stat-card stat-red">
            <div class="stat-icon"></div>
            <div class="stat-label">Ulasan</div>
            <div class="stat-num"><?= $total_rev ?></div>
        </div>
    </div>

    <!-- RECENT TABLES -->
    <div class="grid-2">

        <!-- Recent Destinations -->
        <div class="table-card">
            <div class="table-card-header">
                <h3> Destinasi Terbaru</h3>
                <a href="kelola_destinasi.php">Kelola Semua ></a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Nama</th>
                        <th>Kategori</th>
                        <th>Rating</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($d = mysqli_fetch_assoc($recent_dest)): ?>
                    <tr>
                        <td>
                            <img src="../assets/<?= htmlspecialchars($d['gambar']) ?>"
                                 class="dest-thumb"
                                 onerror="this.src='../assets/background.bali.png'">
                        </td>
                        <td><?= htmlspecialchars($d['nama']) ?></td>
                        <td>
                            <span class="badge badge-teal">
                                <?= htmlspecialchars($d['kat_nama'] ?? $d['kategori']) ?>
                            </span>
                        </td>
                        <td>⭐ <?= $d['rating'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Recent Users -->
        <div class="table-card">
            <div class="table-card-header">
                <h3> Pengguna Terbaru</h3>
                <a href="users.php">Lihat Semua ></a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Joined</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($u = mysqli_fetch_assoc($recent_users)): ?>
                    <tr>
                        <td>
                            <div style="display:flex; align-items:center; gap:8px">
                                <div class="av-mini">
                                    <?= strtoupper(substr($u['username'], 0, 1)) ?>
                                </div>
                                <?= htmlspecialchars($u['username']) ?>
                            </div>
                        </td>
                        <td style="font-size:12px; color:#888">
                            <?= htmlspecialchars($u['email']) ?>
                        </td>
                        <td style="font-size:12px; color:#888">
                            <?= isset($u['created_at']) ? date('d M Y', strtotime($u['created_at'])) : '-' ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

</body>
</html>