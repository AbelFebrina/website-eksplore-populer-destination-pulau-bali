<?php
session_start();
include "../backend/koneksi.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../loginWeb.php");
    exit();
}

// ADD
if (isset($_POST['aksi']) && $_POST['aksi'] == 'tambah') {
    $nama   = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $icon   = mysqli_real_escape_string($koneksi, $_POST['icon']);
    $gambar = mysqli_real_escape_string($koneksi, $_POST['gambar']);
    mysqli_query($koneksi, "INSERT INTO kategori (nama, icon, gambar) VALUES ('$nama', '$icon', '$gambar')");
    header("Location: kelola_kategori.php?pesan=tambah"); exit();
}

// EDIT
if (isset($_POST['aksi']) && $_POST['aksi'] == 'edit') {
    $id     = (int)$_POST['id'];
    $nama   = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $icon   = mysqli_real_escape_string($koneksi, $_POST['icon']);
    $gambar = mysqli_real_escape_string($koneksi, $_POST['gambar']);
    mysqli_query($koneksi, "UPDATE kategori SET nama='$nama', icon='$icon', gambar='$gambar' WHERE id=$id");
    header("Location: kelola_kategori.php?pesan=edit"); exit();
}

// DELETE
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM kategori WHERE id=$id");
    header("Location: kelola_kategori.php?pesan=hapus"); exit();
}

$kategoris = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY id");
$pesan     = $_GET['pesan'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Admin</title>
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
            color: white;
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
        }

        .main {
            margin-left: 240px;
            flex: 1;
            padding: 28px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .topbar h1 {
            font-size: 22px;
            font-weight: 700;
            color: #1a1a2e;
        }

        .btn-add {
            background: #009688;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            transition: background 0.3s;
        }

        .btn-add:hover {
            background: #00796b;
        }

        .alert {
            padding: 12px 18px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
            font-weight: 500;
        }

        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid #4CAF50;
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

        .kat-img {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            object-fit: cover;
        }

        .action-btns { display: flex; gap: 7px; }

        .btn-edit {
            background: #fff8e1;
            color: #f57c00;
            border: none;
            padding: 6px 13px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s;
        }

        .btn-edit:hover { background: #f57c00; color: white; }

        .btn-del {
            background: #ffebee;
            color: #c62828;
            border: none;
            padding: 6px 13px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s;
            text-decoration: none;
        }

        .btn-del:hover { background: #c62828; color: white; }

        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-overlay.show { display: flex; }

        .modal {
            background: white;
            border-radius: 16px;
            padding: 28px;
            width: 440px;
        }

        .modal h3 {
            font-size: 18px;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 20px;
        }

        .form-group { margin-bottom: 15px; }

        .form-group label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #555;
            margin-bottom: 5px;
        }

        .form-group input {
            width: 100%;
            padding: 10px 13px;
            border: 1.5px solid #e0e0e0;
            border-radius: 8px;
            font-size: 13px;
            font-family: 'Poppins', sans-serif;
            outline: none;
            transition: border-color 0.3s;
        }

        .form-group input:focus { border-color: #009688; }

        .img-hint {
            font-size: 11px;
            color: #aaa;
            margin-top: 4px;
        }

        .modal-footer {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 18px;
        }

        .btn-cancel {
            background: #f5f5f5;
            color: #666;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
        }

        .btn-save {
            background: #009688;
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            transition: background 0.3s;
        }

        .btn-save:hover { background: #00796b; }
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
        <a href="index.php" class="menu-item">
            <span class="menu-icon"></span> Dashboard
        </a>
        <a href="kelola_destinasi.php" class="menu-item">
            <span class="menu-icon"></span> Destinasi
        </a>
        <a href="kelola_kategori.php" class="menu-item active">
            <span class="menu-icon"></span> Kategori
        </a>
        <a href="users.php" class="menu-item">
            <span class="menu-icon"></span> Pengguna
        </a>
    </div>
    <div class="sidebar-footer">
        <a href="../index.php" class="btn-back-web">< Kembali Ke Website</a>
    </div>
</div>

<!-- MAIN -->
<div class="main">
    <div class="topbar">
        <h1> Kelola Kategori</h1>
        <button class="btn-add" onclick="document.getElementById('modal-add').classList.add('show')">
            + Tambah Kategori
        </button>
    </div>

    <?php if ($pesan == 'tambah'): ?>
        <div class="alert alert-success">✅ Category added!</div>
    <?php elseif ($pesan == 'edit'): ?>
        <div class="alert alert-success">✅ Category updated!</div>
    <?php elseif ($pesan == 'hapus'): ?>
        <div class="alert alert-success">✅ Category deleted!</div>
    <?php endif; ?>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Gambar</th>
                    <th>Kategori Nama</th>
                    <th>File Gambar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; while ($k = mysqli_fetch_assoc($kategoris)): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td>
                        <img src="../assets/<?= htmlspecialchars($k['gambar'] ?? 'background.bali.png') ?>"
                             class="kat-img"
                             onerror="this.src='../assets/background.bali.png'">
                    </td>
                    <td><strong><?= htmlspecialchars($k['nama']) ?></strong></td>
                    <td style="font-size:12px;color:#888"><?= htmlspecialchars($k['gambar'] ?? '-') ?></td>
                    <td>
                        <div class="action-btns">
                            <button class="btn-edit"
                                    onclick="openEditKat(<?= $k['id'] ?>, '<?= addslashes($k['nama']) ?>', '<?= addslashes($k['icon']) ?>', '<?= addslashes($k['gambar'] ?? '') ?>')">
                                 Edit
                            </button>
                            <a href="kelola_kategori.php?hapus=<?= $k['id'] ?>"
                               class="btn-del"
                               onclick="return confirm('Delete this category?')">
                                 Delete
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL ADD -->
<div class="modal-overlay" id="modal-add">
    <div class="modal">
        <h3> Tambah Kategori</h3>
        <form action="kelola_kategori.php" method="POST">
            <input type="hidden" name="aksi" value="tambah">
            <div class="form-group">
                <label>Kategori Nama *</label>
                <input type="text" name="nama" placeholder="e.g. Beach" required>
            </div>
            <div class="form-group">
                <label>Ikon (emoji) *</label>
                <input type="text" name="icon" placeholder="e.g. " required>
            </div>
            <div class="form-group">
                <label>File Gambar nama</label>
                <input type="text" name="gambar" placeholder="e.g. PantaiKuta.png">
                <div class="img-hint">Image must exist in assets/ folder.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel"
                        onclick="document.getElementById('modal-add').classList.remove('show')">
                    Batal
                </button>
                <button type="submit" class="btn-save"> Save</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT -->
<div class="modal-overlay" id="modal-edit-kat">
    <div class="modal">
        <h3>Edit Kategori</h3>
        <form action="kelola_kategori.php" method="POST">
            <input type="hidden" name="aksi" value="edit">
            <input type="hidden" name="id" id="ek-id">
            <div class="form-group">
                <label>Kategori Nama *</label>
                <input type="text" name="nama" id="ek-nama" required>
            </div>
            <div class="form-group">
                <label>Ikon (emoji) *</label>
                <input type="text" name="icon" id="ek-icon" required>
            </div>
            <div class="form-group">
                <label>File Gambar nama</label>
                <input type="text" name="gambar" id="ek-gambar">
                <div class="img-hint">Image must exist in assets/ folder.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel"
                        onclick="document.getElementById('modal-edit-kat').classList.remove('show')">
                    Batal
                </button>
                <button type="submit" class="btn-save">Perbarui</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditKat(id, nama, icon, gambar) {
        document.getElementById('ek-id').value     = id;
        document.getElementById('ek-nama').value   = nama;
        document.getElementById('ek-icon').value   = icon;
        document.getElementById('ek-gambar').value = gambar;
        document.getElementById('modal-edit-kat').classList.add('show');
    }

    document.querySelectorAll('.modal-overlay').forEach(el => {
        el.addEventListener('click', e => {
            if (e.target === el) el.classList.remove('show');
        });
    });
</script>
</body>
</html>