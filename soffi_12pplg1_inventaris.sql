-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 18 Nov 2025 pada 02.27
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `soffi_12pplg1_inventaris`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `barang`
--

CREATE TABLE `barang` (
  `id_barang` int(60) NOT NULL,
  `nama_barang` varchar(150) NOT NULL,
  `id_kategori` int(120) NOT NULL,
  `stok` int(110) NOT NULL,
  `harga_barang` decimal(15,2) NOT NULL,
  `tanggal_masuk` date NOT NULL,
  `gambar` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `barang`
--

INSERT INTO `barang` (`id_barang`, `nama_barang`, `id_kategori`, `stok`, `harga_barang`, `tanggal_masuk`, `gambar`) VALUES
(1, 'Buku Catatan', 1, 12, 200000.00, '2025-11-17', 'img_1763349843_46c9b326a1ff.jpg'),
(2, 'Laptop Axioo Hype 5', 2, 12, 50000.00, '2025-11-12', 'img_1763356390_87e50cce847e.jpg'),
(3, 'Handpone andoroid', 2, 12, 700000.00, '2025-02-12', 'img_1763356497_1d8318c26132.jpg'),
(4, 'peper clip', 1, 12, 700000.00, '2025-11-04', 'img_1763365910_0867578a83ce.jpg'),
(5, 'Meja Kayu Jati', 3, 12, 2000000.00, '2025-11-10', 'img_1763365969_8789716549cf.jpg'),
(6, 'Kursi Kayu Jati', 3, 12, 100000000.00, '2025-11-06', 'img_1763366021_80862b9fc40b.jpg'),
(7, 'Dispenser', 2, 12, 2000000.00, '2025-11-12', 'img_1763366061_38f77d896274.jpg'),
(8, 'Gantungan kunci', 4, 12, 500000.00, '2025-11-06', 'img_1763366096_f040ecc9531f.jpg');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` int(120) NOT NULL,
  `nama_kategori` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`) VALUES
(1, 'Alat Tulis'),
(2, 'Elektronik'),
(3, 'Furnitur'),
(4, 'Aksesoris');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `barang`
--
ALTER TABLE `barang`
  ADD PRIMARY KEY (`id_barang`);

--
-- Indeks untuk tabel `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `barang`
--
ALTER TABLE `barang`
  MODIFY `id_barang` int(60) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int(120) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
