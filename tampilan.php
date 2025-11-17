<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Suplyaer Barang </title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #e3f2fd, #ffffff);
      color: #333;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    .navbar {
      background: linear-gradient(90deg, #007bff, #00bcd4);
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .navbar-brand {
      color: #fff !important;
      font-weight: 600;
      font-size: 1.25rem;
      letter-spacing: 0.5px;
    }

    .welcome-section {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
      padding: 40px 20px;
    }

    .card {
      border: none;
      border-radius: 20px;
      background: #fff;
      box-shadow: 0 10px 25px rgba(0,0,0,0.1);
      padding: 40px 50px;
      max-width: 700px;
      width: 100%;
      transition: all 0.3s ease;
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 35px rgba(0,0,0,0.12);
    }

    .title {
      color: #007bff;
      font-weight: 700;
      font-size: 1.9rem;
    }

    .subtitle {
      color: #6c757d;
      font-size: 1rem;
      margin-bottom: 25px;
    }

    .btn-menu {
      padding: 14px 24px;
      border-radius: 12px;
      font-weight: 600;
      font-size: 1rem;
      color: #fff;
      transition: 0.3s ease;
      width: 180px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    .btn-menu:hover {
      transform: scale(1.05);
      opacity: 0.95;
    }

    .btn-guru { background-color: #28a745; }
    .btn-siswa { background-color: #007bff; }
    .btn-mapel { background-color: #17a2b8; }
    .btn-nilai { background-color: #ffc107; color: #333; }

    footer {
      background-color: #f8f9fa;
      padding: 12px;
      text-align: center;
      color: #666;
      font-size: 0.9rem;
      border-top: 1px solid #dee2e6;
    }
  </style>
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg">
    <div class="container">
      <a class="navbar-brand" href="#">📘 Data Invetaris Barang </a>
    </div>
  </nav>

  <!-- Welcome Section -->
  <div class="welcome-section">
    <div class="card mx-auto">
      <h3 class="title mb-2">✨ Selamat Datang di Data Invetaris Barang </h3>
      <p class="subtitle"> Tampilan Data Barang Modren <br>(CRUD • View • Trigger)</p>

      <div class="d-flex flex-wrap justify-content-center gap-3 mt-3">
        <a href="index.php" class="btn btn-menu btn-guru">
          Data Barang 
        </a>
        <a href="kategori.php" class="btn btn-menu btn-siswa">
          Kategori Barang 
       
        </a>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer>
    © 2025 Data Invetaris Barang  | Dibuat dengan 💙 untuk Ujian Kompetensi Keahlian 
  </footer>

</body>
</html>
