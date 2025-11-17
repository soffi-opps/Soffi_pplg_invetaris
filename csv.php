<?php
include 'koneksi.php';

// Nama file CSV saat didownload
$filename = "data_barang_" . date('Y-m-d_H-i-s') . ".csv";

// Header agar browser otomatis download
header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=$filename");

// Buka output ke memori
$output = fopen("php://output", "w");

// Tulis judul kolom CSV
fputcsv($output, ["Nama Barang", "Gambar", "Kategori", "Stok", "Harga", "Tanggal Masuk"]);

// Ambil data barang
$query = $pdo->query("
    SELECT b.*, k.nama_kategori 
    FROM barang b 
    LEFT JOIN kategori k ON b.id_kategori = k.id_kategori 
    ORDER BY b.id_barang DESC
");

while ($row = $query->fetch(PDO::FETCH_ASSOC)) {

    // Jika gambar kosong → isi dengan "-"
    $gambar = $row['gambar'] ? $row['gambar'] : "-";

    // Tulis satu baris data ke CSV
    fputcsv($output, [
        $row['nama_barang'],
        $gambar,
        $row['nama_kategori'],
        $row['stok'],
        $row['harga_barang'],
        $row['tanggal_masuk']
    ]);
}

fclose($output);
exit();
?>
