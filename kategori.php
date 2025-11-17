<?php
include 'koneksi.php';

// Tambah kategori
if (isset($_POST['simpan'])) {
    $nama_kategori = $_POST['nama_kategori'];
    try {
        $stmt = $pdo->prepare("INSERT INTO kategori (nama_kategori) VALUES (:nama_kategori)");
        $stmt->bindParam(':nama_kategori', $nama_kategori);
        $stmt->execute();
        echo "<script>alert('Kategori berhasil ditambahkan!'); window.location='kategori.php';</script>";
    } catch (PDOException $e) {
        echo "<script>alert('Gagal menambahkan kategori: ".$e->getMessage()."'); window.location='kategori.php';</script>";
    }
}

// Edit kategori
if (isset($_POST['update'])) {
    $id_kategori = $_POST['id_kategori'];
    $nama_kategori = $_POST['nama_kategori'];
    try {
        $stmt = $pdo->prepare("UPDATE kategori SET nama_kategori = :nama_kategori WHERE id_kategori = :id_kategori");
        $stmt->bindParam(':nama_kategori', $nama_kategori);
        $stmt->bindParam(':id_kategori', $id_kategori);
        $stmt->execute();
        echo "<script>alert('Kategori berhasil diperbarui!'); window.location='kategori.php';</script>";
    } catch (PDOException $e) {
        echo "<script>alert('Gagal memperbarui kategori: ".$e->getMessage()."'); window.location='kategori.php';</script>";
    }
}

// Hapus kategori
if (isset($_GET['hapus'])) {
    $id_kategori = $_GET['hapus'];
    try {
        $stmt = $pdo->prepare("DELETE FROM kategori WHERE id_kategori = :id_kategori");
        $stmt->bindParam(':id_kategori', $id_kategori);
        $stmt->execute();
        echo "<script>alert('Kategori berhasil dihapus!'); window.location='kategori.php';</script>";
    } catch (PDOException $e) {
        echo "<script>alert('Gagal menghapus kategori: ".$e->getMessage()."'); window.location='kategori.php';</script>";
    }
}

// Ambil semua kategori
$stmt = $pdo->query("SELECT * FROM kategori ORDER BY id_kategori ASC");
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"
</title> Data Inventaris Barang </title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
<h3 class="mb-4 text-center"> 📦 Data Kategori</h3>

<!-- Tombol Tambah Kategori -->
<div class="text-end mb-3">
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahModal">+ Tambah Kategori</button>
</div>

<!-- Tabel Kategori -->
<div class="card shadow-sm">
    <div class="card-body">
        <table class="table table-bordered table-striped">
            <thead class="table-secondary text-center">
                <tr>
                    <th>ID Kategori</th>
                    <th>Nama Kategori</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($data) > 0): ?>
                    <?php foreach($data as $row): ?>
                        <tr class="text-center">
                            <td><?= $row['id_kategori'] ?></td>
                            <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
                            <td>
                                <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id_kategori'] ?>">Edit</button>
                                <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $row['id_kategori'] ?>">Hapus</button>
                            </td>
                        </tr>

                        <!-- Modal Edit Kategori -->
                        <div class="modal fade" id="editModal<?= $row['id_kategori'] ?>">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST">
                                        <div class="modal-header bg-info text-white">
                                            <h5 class="modal-title">Edit Kategori</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="id_kategori" value="<?= $row['id_kategori'] ?>">
                                            <div class="mb-3">
                                                <label class="form-label">Nama Kategori</label>
                                                <input type="text" name="nama_kategori" class="form-control" value="<?= htmlspecialchars($row['nama_kategori']) ?>" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" name="update" class="btn btn-primary">Simpan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Hapus Kategori -->
                        <div class="modal fade" id="deleteModal<?= $row['id_kategori'] ?>">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title">Hapus Kategori</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        Yakin ingin menghapus kategori <b><?= htmlspecialchars($row['nama_kategori']) ?></b>?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <a href="kategori.php?hapus=<?= $row['id_kategori'] ?>" class="btn btn-danger">Hapus</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="3" class="text-center text-muted">Belum ada data kategori.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah Kategori -->
<div class="modal fade" id="tambahModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">Tambah Kategori</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Nama Kategori</label>
                <input type="text" name="nama_kategori" class="form-control" required>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" name="simpan" class="btn btn-success">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
