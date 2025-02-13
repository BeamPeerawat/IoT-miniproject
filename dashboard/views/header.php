<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ระบบบ้านอัจฉริยะ | Smart Home Dashboard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500&display=swap">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<nav class="navbar">
    <div class="navbar-content">
        <a href="#" class="logo">
            <i class="fas fa-home"></i>
            Smart Home
        </a>
        <div class="nav-links">
            <a href="#sensors" class="nav-link"><i class="fas fa-thermometer-half"></i> เซนเซอร์</a>
            <a href="#devices" class="nav-link"><i class="fas fa-lightbulb"></i> อุปกรณ์</a>
            <a href="#activity" class="nav-link"><i class="fas fa-history"></i> ประวัติ</a>
            <button id="theme-toggle" class="theme-toggle-btn">
                <i class="fas fa-moon"></i> <!-- ไอคอนโหมดมืด -->
                <i class="fas fa-sun" style="display: none;"></i> <!-- ไอคอนโหมดสว่าง -->
            </button>
        </div>
    </div>
</nav>
  <div class="container">