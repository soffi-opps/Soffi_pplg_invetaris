<?php
// index.php - versi final
session_start();
include 'koneksi.php';

// buat folder uploads jika belum ada
$uploadDir = __DIR__. '/uploads';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

// helper
function e($v){ return htmlspecialchars($v ?? '', ENT_QUOTES); }
function flash($k, $v = null){
    if ($v === null) { $m = $_SESSION[$k] ?? null; unset($_SESSION[$k]); return $m; }
    $_SESSION[$k] = $v;
}

// --- EXPORT CSV ---
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $sql = "SELECT b.id_barang, b.nama_barang, k.nama_kategori, b.stok, b.harga_barang, b.tanggal_masuk, b.gambar
            FROM barang b
            LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
            ORDER BY b.id_barang ASC";
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=data_barang_' . date('Ymd_His') . '.csv');

    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
    fputcsv($out, ['id_barang','nama_barang','nama_kategori','stok','harga_barang','tanggal_masuk','gambar']);
    foreach ($rows as $r) {
        fputcsv($out, [
            $r['id_barang'],
            $r['nama_barang'],
            $r['nama_kategori'],
            $r['stok'],
            $r['harga_barang'],
            $r['tanggal_masuk'],
            $r['gambar']
        ]);
    }
    fclose($out);
    exit;
}

// --- HANDLE POST ACTIONS (update, delete) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    try {
        if ($action === 'update') {
            $id = $_POST['id_barang'] ?? '';
            $nama = trim($_POST['nama_barang'] ?? '');
            $id_kategori = trim($_POST['id_kategori'] ?? '');
            $stok = trim($_POST['stok'] ?? '');
            $harga = trim($_POST['harga_barang'] ?? '');
            $tanggal = trim($_POST['tanggal_masuk'] ?? '');

            if ($id==='' || $nama==='' || $id_kategori==='' || $stok==='' || $harga==='' || $tanggal==='') throw new Exception('Semua field wajib diisi.');
            if (!is_numeric($stok) || (int)$stok<0) throw new Exception('Stok harus angka >= 0.');
            if (!is_numeric($harga) || (float)$harga<0) throw new Exception('Harga harus angka >= 0.');

            $old = $pdo->prepare("SELECT gambar FROM barang WHERE id_barang = ?");
            $old->execute([$id]);
            $oldRow = $old->fetch(PDO::FETCH_ASSOC);
            $oldImage = $oldRow['gambar'] ?? null;
            $newImage = $oldImage;

            if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] !== UPLOAD_ERR_NO_FILE) {
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
                if (!move_uploaded_file($file['tmp_name'], $target)) throw new Exception('Gagal menyimpan gambar baru.');
                if ($oldImage) {
                    $oldPath = $uploadDir . '/' . $oldImage;
                    if (is_file($oldPath)) @unlink($oldPath);
                }
                $newImage = $safeName;
            }

            $sql = "UPDATE barang SET nama_barang=?, id_kategori=?, stok=?, harga_barang=?, tanggal_masuk=?, gambar=? WHERE id_barang=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nama, $id_kategori, (int)$stok, (float)$harga, $tanggal, $newImage, $id]);

            flash('success','Data berhasil diperbarui.');
            header('Location: index.php'); exit;
        }

        if ($action === 'delete') {
            $id = $_POST['id_barang'] ?? '';
            if ($id==='') throw new Exception('ID tidak ditemukan.');

            $old = $pdo->prepare("SELECT gambar FROM barang WHERE id_barang = ?");
            $old->execute([$id]);
            $oldRow = $old->fetch(PDO::FETCH_ASSOC);
            $oldImage = $oldRow['gambar'] ?? null;

            $stmt = $pdo->prepare("DELETE FROM barang WHERE id_barang = ?");
            $stmt->execute([$id]);

            if ($oldImage) {
                $oldPath = $uploadDir . '/' . $oldImage;
                if (is_file($oldPath)) @unlink($oldPath);
            }

            flash('success','Data berhasil dihapus.');
            header('Location: index.php'); exit;
        }
    } catch (Exception $e) {
        flash('error', $e->getMessage());
        header('Location: index.php'); exit;
    }
}

// Fetch kategori list
$kategoriStmt = $pdo->query("SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori ASC");
$kategoriList = $kategoriStmt->fetchAll(PDO::FETCH_ASSOC);

// Pagination & filtering
$perPage = 6;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$keyword = trim($_GET['keyword'] ?? '');
$filterKategori = trim($_GET['kategori'] ?? '');

$where = " WHERE 1=1 ";
$params = [];
if ($keyword !== '') { $where .= " AND b.nama_barang LIKE ? "; $params[] = "%$keyword%"; }
if ($filterKategori !== '') { $where .= " AND b.id_kategori=? "; $params[] = $filterKategori; }

// total count
$countSql = "SELECT COUNT(*) FROM barang b LEFT JOIN kategori k ON b.id_kategori = k.id_kategori " . $where;
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$totalPages = max(1,(int)ceil($total/$perPage));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page-1)*$perPage;

// fetch current page
$sql = "SELECT b.id_barang, b.nama_barang, b.id_kategori, b.stok, b.harga_barang, b.tanggal_masuk, b.gambar, k.nama_kategori
        FROM barang b
        LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
        ".$where." ORDER BY b.id_barang DESC LIMIT ? OFFSET ?";
$paramsPaged = array_merge($params, [$perPage,$offset]);
$stmt = $pdo->prepare($sql);
$bindIndex = 1;
foreach($params as $p) { $stmt->bindValue($bindIndex++,$p); }
$stmt->bindValue($bindIndex++, (int)$perPage, PDO::PARAM_INT);
$stmt->bindValue($bindIndex++, (int)$offset, PDO::PARAM_INT);
$stmt->execute();
$barangList = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Inventaris - Data Barang</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
:root { --soft-blue:#eef6ff; --accent:#3b82f6; }
body { background:linear-gradient(180deg,#f6f9ff 0%,#fbfcff 100%); font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,"Helvetica Neue",Arial; }
.card.custom { border-radius:14px; box-shadow:0 6px 22px rgba(15,23,42,0.06); border:none; }
.table thead th { background: var(--soft-blue); border-bottom:none; }
.thumb { width:100px; height:100px; object-fit:cover; border-radius:8px; border:1px solid #eef4ff; }
.btn-soft { background:#f1f5ff; color:var(--accent); border:1px solid rgba(59,130,246,0.12); }
.alert-fixed { position:relative; z-index:5; }
</style>
</head>
<body>
<div class="container py-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h3 class="mb-0">📦 Data Barang</h3>
      <small class="text-muted">Kelola Data inventaris & Supplier Barang</small>
    </div>
    <div class="d-flex gap-2">
      <a class="btn btn-outline-primary" href="index.php?export=csv">⬇ Export CSV</a>
      <a class="btn btn-success" href="tambah.php">➕ Tambah Barang</a>
    </div>
  </div>

  <!-- Alerts -->
  <div class="mb-3 alert-fixed">
    <?php if ($m = flash('success')): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= e($m) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>
    <?php if ($m = flash('error')): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= e($m) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>
  </div>

  <div class="card custom p-4 mb-4">
    <form class="row g-3 align-items-end" method="get">
      <div class="col-md-5">
        <label class="form-label">Cari nama barang</label>
        <input type="text" name="keyword" class="form-control" placeholder="mis. pulpen, meja..." value="<?= e($keyword) ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Filter kategori</label>
        <select name="kategori" class="form-select">
          <option value="">Semua kategori</option>
          <?php foreach ($kategoriList as $kat): ?>
            <option value="<?= e($kat['id_kategori']) ?>" <?= ($filterKategori==$kat['id_kategori'])?'selected':'' ?>>
              <?= e($kat['nama_kategori']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3 d-flex gap-2">
        <button type="submit" class="btn btn-primary w-100">🔍 Cari</button>
        <a href="index.php" class="btn btn-soft w-100">Reset</a>
      </div>
    </form>
  </div>

  <div class="card custom">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead>
            <tr class="text-muted small">
              <th style="width:4%">No</th>
              <th>Nama Barang</th>
              <th>Gambar</th>
              <th>Kategori</th>
              <th>Stok</th>
              <th class="text-end">Harga</th>
              <th class="text-center">Tanggal Masuk</th>
              <th class="text-center" style="width:12%">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($barangList): $no=$offset+1; ?>
              <?php foreach($barangList as $row): ?>
                <tr>
                  <td class="small text-muted"><?= $no++ ?></td>
                  <td style="min-width:200px"><?= e($row['nama_barang']) ?></td>
                  <td>
                    <?php if(!empty($row['gambar']) && is_file($uploadDir.'/'.$row['gambar'])): ?>
                      <img src="uploads/<?= e($row['gambar']) ?>" class="thumb" alt="">
                    <?php else: ?>
                      <div class="text-muted small">No image</div>
                    <?php endif; ?>
                  </td>
                  <td><?= e($row['nama_kategori']??'-') ?></td>
                  <td class="text-center"><?= e($row['stok']) ?></td>
                  <td class="text-end">Rp <?= number_format($row['harga_barang']??0,0,',','.') ?></td>
                  <td class="text-center"><?= e($row['tanggal_masuk']) ?></td>
                  <td class="text-center">
                    <a href="edit.php?id=<?= e($row['id_barang']) ?>" class="btn btn-sm btn-outline-warning me-1">Edit</a>
                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalDelete<?= $row['id_barang'] ?>">Hapus</button>
                  </td>
                </tr>

                <!-- Delete Modal -->
                <div class="modal fade" id="modalDelete<?= $row['id_barang'] ?>" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                      <form method="post">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id_barang" value="<?= e($row['id_barang']) ?>">
                        <div class="modal-header bg-danger text-white">
                          <h5 class="modal-title">Hapus Barang</h5>
                          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                          <p>Yakin ingin menghapus <strong><?= e($row['nama_barang']) ?></strong>?</p>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                          <button type="submit" class="btn btn-danger">Hapus</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>

              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="8" class="text-center text-muted py-4">Belum ada data barang.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="d-flex justify-content-between align-items-center p-3">
        <div class="text-muted small">Menampilkan <?= ($total>0?($offset+1):0) ?> - <?= min($offset+$perPage,$total) ?> dari <?= $total ?> data</div>
        <div>
          <ul class="pagination mb-0">
            <?php
            $queryBase = $_GET;
            unset($queryBase['export']);
            ?>
            <li class="page-item <?= ($page<=1)?'disabled':'' ?>">
              <?php $queryBase['page']=$page-1; ?>
              <a class="page-link" href="index.php?<?= http_build_query($queryBase) ?>">Sebelumnya</a>
            </li>
            <?php for($i=1;$i<=$totalPages;$i++): ?>
              <?php $queryBase['page']=$i; ?>
              <li class="page-item <?= ($i==$page)?'active':'' ?>"><a class="page-link" href="index.php?<?= http_build_query($queryBase) ?>"><?= $i ?></a></li>
            <?php endfor; ?>
            <li class="page-item <?= ($page>=$totalPages)?'disabled':'' ?>">
              <?php $queryBase['page']=$page+1; ?>
              <a class="page-link" href="index.php?<?= http_build_query($queryBase) ?>">Berikutnya</a>
            </li>
          </ul>
        </div>
      </div>

    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// auto-dismiss alerts
document.addEventListener('DOMContentLoaded', ()=>{
  setTimeout(()=>{
    document.querySelectorAll('.alert').forEach(a=>{
      if(a.classList.contains('show')){
        let bs=bootstrap.Alert.getOrCreateInstance(a); bs.close();
      }
    });
  },4000);
});
</script>
</body>
</html>
