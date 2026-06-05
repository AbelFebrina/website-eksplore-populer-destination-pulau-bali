<?php
session_start();
include "../backend/koneksi.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../loginWeb.php");
    exit();
}

$users = mysqli_query($koneksi,
    "SELECT u.*,
        (SELECT COUNT(*) FROM wishlists WHERE user_id = u.id) AS total_wish,
        (SELECT COUNT(*) FROM reviews   WHERE user_id = u.id) AS total_rev
     FROM tb_akun u
     WHERE u.role = 'user'
     ORDER BY u.created_at DESC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f0f2f5;
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 240px;
            background: #1a1a2e;
            position: fixed;
            height: 100vh;
            display: flex;
            flex-direction: column;
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

        .menu-icon { font-size: 16px; width: 20px; text-align: center; }

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
        }

        .main { margin-left: 240px; flex: 1; padding: 28px; }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .topbar h1 { font-size: 22px; font-weight: 700; color: #1a1a2e; }

        .count-badge {
            background: #e0f2f1;
            color: #009688;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .table-card {
            background: white;
            border-radius: 14px;
            box-shadow: 0 3px 14px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }

        table { width: 100%; border-collapse: collapse; }

        thead { background: #f8fafc; }

        th {
            padding: 13px 18px;
            text-align: left;
            font-size: 11px;
            font-weight: 600;
            color: #888;
            text-transform: uppercase;
        }

        td {
            padding: 13px 18px;
            font-size: 13px;
            color: #333;
            border-bottom: 1px solid #f5f5f5;
            vertical-align: middle;
        }

        tr:last-child td { border-bottom: none; }
        tr:hover { background: #fafafa; }

        .av-mini {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, #009688, #4DD0C4);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 13px;
        }

        .av-real {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #009688;
        }

        .user-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-brand">
        <h2> Explore Bali</h2>
        <p>Admin Panel</p>
    </div>
    <div class="sidebar-menu">
        <a href="index.php" class="menu-item">
            <span class="menu-icon"></span> Dashboard
        </a>
        <a href="kelola_destinasi.php" class="menu-item">
            <span class="menu-icon"></span> Destinasi
        </a>
        <a href="kelola_kategori.php" class="menu-item">
            <span class="menu-icon"></span> Kategori
        </a>
        <a href="users.php" class="menu-item active">
            <span class="menu-icon"></span> Pengguna
        </a>
    </div>
    <div class="sidebar-footer">
        <a href="../index.php" class="btn-back-web">< Kembali Ke Website</a>
    </div>
</div>

<div class="main">
    <div class="topbar">
        <h1> Users</h1>
        <span class="count-badge"><?= mysqli_num_rows($users) ?> User</span>
    </div>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>User</th>
                    <th>Email</th>
                    <th>Wishlist</th>
                    <th>Reviews</th>
                    <th>Joined</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; while ($u = mysqli_fetch_assoc($users)): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td>
                        <div class="user-cell">
                            <?php if (!empty($u['avatar'])): ?>
                                <img src="../upload_gambar/<?= htmlspecialchars($u['avatar']) ?>"
                                     class="av-real"
                                     alt="avatar"
                                     onerror="this.style.display='none'">
                            <?php else: ?>
                                <div class="av-mini">
                                    <?= strtoupper(substr($u['username'], 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                            <strong><?= htmlspecialchars($u['username']) ?></strong>
                        </div>
                    </td>
                    <td style="font-size:12px;color:#888"><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= $u['total_wish'] ?></td>
                    <td><?= $u['total_rev'] ?></td>
                    <td style="font-size:12px;color:#888">
                        <?= isset($u['created_at']) ? date('d M Y', strtotime($u['created_at'])) : '-' ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>