<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "soffi_12pplg1_inventaris"; // pastikan nama database tanpa spasi

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
?>