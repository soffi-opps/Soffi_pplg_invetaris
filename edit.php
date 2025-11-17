<?php
include 'koneksi.php';

// Ambil ID barang
if (!isset($_GET['id'])) {
    die("ID barang tidak ditemukan.");
}

$id = $_GET['id'];

// Ambil data barang
$stmt = $pdo->prepare("SELECT * FROM barang WHERE id_barang = ?");
$stmt->execute([$id]);
$barang = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$barang) {
    die("Data barang tidak ditemukan.");
}

// Ambil data kategori
$kategoriQuery = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori ASC");
$kategoriList = $kategoriQuery->fetchAll(PDO::FETCH_ASSOC);

// Jika tombol update ditekan
$pesanError = "";
$pesanSukses = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_barang   = trim($_POST['nama_barang']);
    $kategori      = $_POST['id_kategori'];
    $stok          = $_POST['stok'];
    $harga_barang  = $_POST['harga_barang'];
    $tanggal_masuk = $_POST['tanggal_masuk'];
    $gambar_lama   = $barang['gambar'];

    // Validasi
    if ($nama_barang == "" || $kategori == "" || $stok == "" || $harga_barang == "" || $tanggal_masuk == "") {
        $pesanError = "Semua field wajib diisi!";
    } else {

        // Upload gambar baru
        $gambar_baru = $gambar_lama;

        if (!empty($_FILES['gambar']['name'])) {
            $namaFile = basename($_FILES['gambar']['name']);
            $targetFile = "uploads/" . $namaFile;

            if (move_uploaded_file($_FILES['gambar']['tmp_name'], $targetFile)) {
                // Hapus gambar lama jika ada
                if ($gambar_lama && file_exists("uploads/" . $gambar_lama)) {
                    unlink("uploads/" . $gambar_lama);
                }
                $gambar_baru = $namaFile;
            }
        }

        // Update data
        $update = $pdo->prepare("UPDATE barang SET 
            nama_barang = ?, 
            id_kategori = ?, 
            stok = ?, 
            harga_barang = ?, 
            tanggal_masuk = ?, 
            gambar = ?
            WHERE id_barang = ?");

        if ($update->execute([$nama_barang, $kategori, $stok, $harga_barang, $tanggal_masuk, $gambar_baru, $id])) {
            $pesanSukses = "Data berhasil diperbarui!";
            // Refresh data
            $stmt->execute([$id]);
            $barang = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $pesanError = "Gagal menyimpan perubahan.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Barang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #eef3fa;
        }
        .card {
            border-radius: 15px;
        }
        .preview-img {
            width: 130px;
            height: 130px;
            object-fit: cover;
            border-radius: 15px;
            border: 2px solid #d2d9e5;
        }
    </style>
</head>
<body>

<div class="container py-4">
    <h3 class="fw-bold mb-3">✏ Edit Data Barang</h3>

    <div class="card p-4 shadow-sm">

        <?php if ($pesanError): ?>
            <div class="alert alert-danger"><?= $pesanError ?></div>
        <?php endif; ?>

        <?php if ($pesanSukses): ?>
            <div class="alert alert-success"><?= $pesanSukses ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">

            <div class="mb-3">
                <label class="form-label">Nama Barang</label>
                <input type="text" name="nama_barang" class="form-control"
                       value="<?= $barang['nama_barang'] ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Kategori</label>
                <select name="id_kategori" class="form-control" required>
                    <option value="">-- Pilih Kategori --</option>
                    <?php foreach ($kategoriList as $k): ?>
                        <option value="<?= $k['id_kategori'] ?>"
                            <?= $barang['id_kategori'] == $k['id_kategori'] ? 'selected' : '' ?>>
                            <?= $k['nama_kategori'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Stok</label>
                    <input type="number" name="stok" class="form-control" 
                           value="<?= $barang['stok'] ?>" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Harga Barang</label>
                    <input type="number" name="harga_barang" class="form-control" 
                           value="<?= $barang['harga_barang'] ?>" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Tanggal Masuk</label>
                    <input type="date" name="tanggal_masuk" class="form-control"
                           value="<?= $barang['tanggal_masuk'] ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Gambar Sekarang</label><br>
                <?php if ($barang['gambar']): ?>
                    <img src="uploads/<?= $barang['gambar'] ?>" class="preview-img mb-2">
                <?php else: ?>
                    <p class="text-muted">Tidak ada gambar.</p>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label">Ganti Gambar (Opsional)</label>
                <input type="file" name="gambar" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="index.php" class="btn btn-secondary">Kembali</a>

        </form>
    </div>
</div>

</body>
</html>
