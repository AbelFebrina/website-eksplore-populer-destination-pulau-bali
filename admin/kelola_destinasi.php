<?php
session_start();
include "../backend/koneksi.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../loginWeb.php");
    exit();
}

$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$where  = $search ? "WHERE d.nama LIKE '%$search%' OR d.lokasi LIKE '%$search%'" : "";

$destinations = mysqli_query($koneksi,
    "SELECT d.*, k.nama AS kat_nama, k.icon AS kat_icon
     FROM destinations d
     LEFT JOIN kategori k ON d.kategori_id = k.id
     $where
     ORDER BY d.id DESC"
);

$kategoris = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY id");
$pesan     = $_GET['pesan'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Destinations - Admin</title>
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

        .topbar-right {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .search-input {
            padding: 9px 14px;
            border: 1.5px solid #e0e0e0;
            border-radius: 8px;
            font-size: 13px;
            font-family: 'Poppins', sans-serif;
            outline: none;
            width: 220px;
            transition: border-color 0.3s;
        }

        .search-input:focus {
            border-color: #009688;
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

        /* ===== TABLE ===== */
        .table-card {
            background: white;
            border-radius: 14px;
            box-shadow: 0 3px 14px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f8fafc;
        }

        th {
            padding: 13px 18px;
            text-align: left;
            font-size: 11px;
            font-weight: 600;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 13px 18px;
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
            width: 60px;
            height: 46px;
            object-fit: cover;
            border-radius: 8px;
        }

        .badge {
            display: inline-block;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            background: #e0f2f1;
            color: #009688;
        }

        .action-btns {
            display: flex;
            gap: 7px;
        }

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

        .btn-edit:hover {
            background: #f57c00;
            color: white;
        }

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
            display: inline-block;
        }

        .btn-del:hover {
            background: #c62828;
            color: white;
        }

        /* ===== MODAL ===== */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-overlay.show {
            display: flex;
        }

        .modal {
            background: white;
            border-radius: 16px;
            padding: 28px;
            width: 540px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal h3 {
            font-size: 18px;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 22px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #555;
            margin-bottom: 5px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 13px;
            border: 1.5px solid #e0e0e0;
            border-radius: 8px;
            font-size: 13px;
            font-family: 'Poppins', sans-serif;
            outline: none;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #009688;
        }

        .form-group textarea {
            height: 90px;
            resize: vertical;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .upload-box {
            border: 2px dashed #e0e0e0;
            border-radius: 10px;
            padding: 16px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 10px;
        }

        .upload-box:hover {
            border-color: #009688;
            background: #f8fafc;
        }

        .upload-box-preview {
            max-width: 100%;
            max-height: 120px;
            border-radius: 8px;
            display: none;
            margin: 0 auto 8px;
        }

        .upload-box-text {
            color: #aaa;
            font-size: 13px;
        }

        .divider-or {
            text-align: center;
            font-size: 12px;
            color: #aaa;
            margin: 8px 0;
        }

        .img-hint {
            font-size: 11px;
            color: #aaa;
            margin-top: 4px;
        }

        .modal-footer {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
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

        .btn-save:hover {
            background: #00796b;
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
        <a href="index.php" class="menu-item">
            <span class="menu-icon"></span> Dashboard
        </a>
        <a href="kelola_destinasi.php" class="menu-item active">
            <span class="menu-icon"></span>  Destinasi
        </a>
        <a href="kelola_kategori.php" class="menu-item">
            <span class="menu-icon"></span>  Kategori
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
        <h1> Kelola Destinasi</h1>
        <div class="topbar-right">
            <form action="kelola_destinasi.php" method="GET" style="display:inline">
                <input type="text" name="search" class="search-input"
                       placeholder="Search destination..."
                       value="<?= htmlspecialchars($search) ?>">
            </form>
            <button class="btn-add" onclick="openModal('add')">+ Tambah Destinasi</button>
        </div>
    </div>

    <?php if ($pesan == 'tambah'): ?>
        <div class="alert alert-success">✅ Destination added successfully!</div>
    <?php elseif ($pesan == 'edit'): ?>
        <div class="alert alert-success">✅ Destination updated successfully!</div>
    <?php elseif ($pesan == 'hapus'): ?>
        <div class="alert alert-success">✅ Destination deleted successfully!</div>
    <?php endif; ?>

    <!-- TABLE -->
    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Foto</th>
                    <th>Nama</th>
                    <th>Kategori</th>
                    <th>Lokasi</th>
                    <th>Rating</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                if ($destinations && mysqli_num_rows($destinations) > 0):
                    while ($d = mysqli_fetch_assoc($destinations)):
                        $gambar_src = strpos($d['gambar'], 'destinations/') === 0
                            ? '../upload_gambar/' . $d['gambar']
                            : '../assets/' . $d['gambar'];
                ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td>
                        <img src="<?= htmlspecialchars($gambar_src) ?>"
                             class="dest-thumb"
                             onerror="this.src='../assets/background.bali.png'">
                    </td>
                    <td><strong><?= htmlspecialchars($d['nama']) ?></strong></td>
                    <td>
                        <span class="badge">
    <?= htmlspecialchars($d['kat_nama'] ?? $d['kategori']) ?>
</span>
                    </td>
                    <td><?= htmlspecialchars($d['lokasi']) ?></td>
                    <td>⭐ <?= $d['rating'] ?></td>
                    <td>
                        <div class="action-btns">
                            <button class="btn-edit" onclick="openEdit(
                                <?= $d['id'] ?>,
                                '<?= addslashes($d['nama']) ?>',
                                <?= (int)$d['kategori_id'] ?>,
                                '<?= addslashes($d['lokasi']) ?>',
                                '<?= addslashes($d['deskripsi']) ?>',
                                '<?= addslashes($d['gambar']) ?>'
                            )"> Edit</button>
                            <a href="proses_destinasi.php?aksi=hapus&id=<?= $d['id'] ?>"
                               class="btn-del"
                               onclick="return confirm('Delete this destination?')">
                                 Hapus
                            </a>
                        </div>
                    </td>
                </tr>
                <?php
                    endwhile;
                else:
                ?>
                <tr>
                    <td colspan="7" style="text-align:center;padding:40px;color:#aaa">
                        Tidak ada destinasi ditemukan.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ===== MODAL ADD ===== -->
<div class="modal-overlay" id="modal-add">
    <div class="modal">
        <h3> Tambah Destinasi Baru</h3>
        <form action="proses_destinasi.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="aksi" value="tambah">

            <div class="form-group">
                <label>Nama Destinasi *</label>
                <input type="text" name="nama" placeholder="e.g. Kuta Beach" required>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label>Kategori *</label>
                    <select name="kategori_id" required>
                        <option value="">Pilih Kategori</option>
                        <?php
                        mysqli_data_seek($kategoris, 0);
                        while ($k = mysqli_fetch_assoc($kategoris)):
                        ?>
                        <option value="<?= $k['id'] ?>">
    <?= htmlspecialchars($k['nama']) ?>
</option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Lokasi *</label>
                    <input type="text" name="lokasi" placeholder="e.g. Kuta, Badung" required>
                </div>
            </div>

            <div class="form-group">
                <label>Deskripsi *</label>
                <textarea name="deskripsi" placeholder="Describe the destination..." required></textarea>
            </div>

            <div class="form-group">
                <label>Gambar Destinasi</label>
                <div class="upload-box" onclick="document.getElementById('addFile').click()">
                    <input type="file" id="addFile" name="gambar_file"
                           accept="image/*" style="display:none"
                           onchange="previewImg(this,'addPrev','addTxt')">
                    <img id="addPrev" class="upload-box-preview" src="" alt="preview">
                    <div id="addTxt" class="upload-box-text">
                        <div style="font-size:28px;margin-bottom:6px">📷</div>
                        Klik untuk mengunggah gambar
                    </div>
                </div>
                <div class="divider-or">ATAU ketik nama file dari folder assets/ folder —</div>
                <input type="text" name="gambar_manual" placeholder="e.g. PantaiKuta.png">
                <div class="img-hint">Upload new image OR type existing filename from assets/</div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal('add')">Batal</button>
                <button type="submit" class="btn-save">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- ===== MODAL EDIT ===== -->
<div class="modal-overlay" id="modal-edit">
    <div class="modal">
        <h3> Edit Destinasi</h3>
        <form action="proses_destinasi.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="aksi" value="edit">
            <input type="hidden" name="id" id="e-id">
            <input type="hidden" name="gambar_existing" id="e-gambar-existing">

            <div class="form-group">
                <label>Nama Destinasi *</label>
                <input type="text" name="nama" id="e-nama" required>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label>Kategori *</label>
                    <select name="kategori_id" id="e-kat" required>
                        <?php
                        mysqli_data_seek($kategoris, 0);
                        while ($k = mysqli_fetch_assoc($kategoris)):
                        ?>
                        <option value="<?= $k['id'] ?>">
                            <?= $k['icon'] . ' ' . htmlspecialchars($k['nama']) ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Lokasi *</label>
                    <input type="text" name="lokasi" id="e-lokasi" required>
                </div>
            </div>

            <div class="form-group">
                <label>Deskripsi *</label>
                <textarea name="deskripsi" id="e-deskripsi" required></textarea>
            </div>

            <div class="form-group">
                <label>Gambar Destinasi</label>
                <div style="margin-bottom:10px">
                    <img id="e-current-img" src=""
                         style="width:100%;height:110px;object-fit:cover;border-radius:8px;display:none">
                </div>
                <div class="upload-box" onclick="document.getElementById('editFile').click()">
                    <input type="file" id="editFile" name="gambar_file"
                           accept="image/*" style="display:none"
                           onchange="previewImg(this,'editPrev','editTxt')">
                    <img id="editPrev" class="upload-box-preview" src="" alt="preview">
                    <div id="editTxt" class="upload-box-text">
                        <div style="font-size:24px;margin-bottom:4px">📷</div>
                        Klik untuk mengganti gambar
                    </div>
                </div>
                <div class="divider-or">— ATAU pertahankan/ganti nama file —</div>
                <input type="text" name="gambar_manual" id="e-gambar-manual"
                       placeholder="e.g. PantaiKuta.png">
                <div class="img-hint">Upload new file OR edit filename. Kosongkan jika ingin memakai gambar lama.</div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal('edit')">Cancel</button>
                <button type="submit" class="btn-save">Perbarui</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(name) {
        document.getElementById('modal-' + name).classList.add('show');
    }

    function closeModal(name) {
        document.getElementById('modal-' + name).classList.remove('show');
    }

    function previewImg(input, previewId, textId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                const img = document.getElementById(previewId);
                const txt = document.getElementById(textId);
                img.src            = e.target.result;
                img.style.display  = 'block';
                txt.style.display  = 'none';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function openEdit(id, nama, katId, lokasi, deskripsi, gambar) {
        document.getElementById('e-id').value              = id;
        document.getElementById('e-nama').value            = nama;
        document.getElementById('e-kat').value             = katId;
        document.getElementById('e-lokasi').value          = lokasi;
        document.getElementById('e-deskripsi').value       = deskripsi;
        document.getElementById('e-gambar-existing').value = gambar;
        document.getElementById('e-gambar-manual').value   = gambar;

        // Tampilkan gambar saat ini
        const curImg = document.getElementById('e-current-img');
        if (gambar) {
            const src = gambar.startsWith('destinations/')
                ? '../upload_gambar/' + gambar
                : '../assets/' + gambar;
            curImg.src           = src;
            curImg.style.display = 'block';
            curImg.onerror       = () => { curImg.style.display = 'none'; };
        } else {
            curImg.style.display = 'none';
        }

        // Reset upload preview
        document.getElementById('editPrev').style.display = 'none';
        document.getElementById('editTxt').style.display  = 'block';
        document.getElementById('editFile').value         = '';

        openModal('edit');
    }

    // Tutup modal klik di luar
    document.querySelectorAll('.modal-overlay').forEach(el => {
        el.addEventListener('click', e => {
            if (e.target === el) el.classList.remove('show');
        });
    });
</script>
</body>
</html>