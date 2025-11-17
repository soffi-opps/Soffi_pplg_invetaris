<?php
session_start();
include 'koneksi.php';

// ambil list kategori
$kategoriStmt = $pdo->query("SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori ASC");
$kategoriList = $kategoriStmt->fetchAll(PDO::FETCH_ASSOC);

// folder uploads
$uploadDir = __DIR__. '/uploads';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

function flash($k, $v = null){
    if ($v === null) { $m = $_SESSION[$k] ?? null; unset($_SESSION[$k]); return $m; }
    $_SESSION[$k] = $v;
}

// proses form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nama_barang = trim($_POST['nama_barang'] ?? '');
        $id_kategori = trim($_POST['id_kategori'] ?? '');
        $stok = trim($_POST['stok'] ?? '');
        $harga = trim($_POST['harga_barang'] ?? '');
        $tanggal_masuk = trim($_POST['tanggal_masuk'] ?? '');

        if ($nama_barang === '' || $id_kategori === '' || $stok === '' || $harga === '' || $tanggal_masuk === '') {
            throw new Exception('Semua field wajib diisi.');
        }
        if (!is_numeric($stok) || (int)$stok < 0) throw new Exception('Stok harus angka >= 0.');
        if (!is_numeric($harga) || (float)$harga < 0) throw new Exception('Harga harus angka >= 0.');

        // validasi gambar
        if (!isset($_FILES['gambar']) || $_FILES['gambar']['error'] === UPLOAD_ERR_NO_FILE) {
            throw new Exception('Gambar wajib diupload.');
        }
        $file = $_FILES['gambar'];
        if ($file['error'] !== UPLOAD_ERR_OK) throw new Exception('Upload error code: ' . $file['error']);

        $allowed = ['image/jpeg','image/jpg','image/png'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mime, $allowed)) throw new Exception('Tipe file hanya JPG/JPEG/PNG.');
        if ($file['size'] > 2*1024*1024) throw new Exception('Ukuran file maksimal 2MB.');

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $safeName = 'img_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $target = $uploadDir . '/' . $safeName;
        if (!move_uploaded_file($file['tmp_name'], $target)) throw new Exception('Gagal menyimpan gambar.');

        // simpan ke database
        $stmt = $pdo->prepare("INSERT INTO barang (nama_barang, id_kategori, stok, harga_barang, tanggal_masuk, gambar) 
                               VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nama_barang, $id_kategori, (int)$stok, (float)$harga, $tanggal_masuk, $safeName]);

        flash('success','Data barang berhasil ditambahkan.');
        header('Location: index.php'); exit;

    } catch (Exception $e) {
        flash('error',$e->getMessage());
        header('Location: input.php'); exit;
    }
}
?>

<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tambah Barang</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
  <h3 class="mb-4">➕ Tambah Barang Baru</h3>

  <?php if($m = flash('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?= htmlspecialchars($m) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
  <?php if($m = flash('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?= htmlspecialchars($m) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <div class="card shadow-sm p-4">
    <form method="post" enctype="multipart/form-data">
      <div class="mb-3">
        <label class="form-label">Nama Barang</label>
        <input type="text" name="nama_barang" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Kategori</label>
        <select name="id_kategori" class="form-select" required>
          <option value="">Pilih Kategori</option>
          <?php foreach($kategoriList as $kat): ?>
            <option value="<?= htmlspecialchars($kat['id_kategori']) ?>"><?= htmlspecialchars($kat['nama_kategori']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label">Stok</label>
        <input type="number" name="stok" class="form-control" min="0" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Harga</label>
        <input type="number" step="0.01" name="harga_barang" class="form-control" min="0" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Tanggal Masuk</label>
        <input type="date" name="tanggal_masuk" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Gambar (JPG/PNG, max 2MB)</label>
        <input type="file" name="gambar" class="form-control" accept="image/*" required>
      </div>
      <div class="d-flex gap-2">
        <a href="index.php" class="btn btn-secondary">Kembali</a>
        <button type="submit" class="btn btn-success">Simpan</button>
      </div>
    </form>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
