<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>QuizKu - Belajar Jadi Lebih Seru</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<section class="hero">

  <!-- NAVBAR -->
  <nav class="navbar">
    <a href="index.php" class="logo"><span class="ic">🏆</span> QuizKu</a>
    <div class="nav-actions">
      <a href="login.php" class="link">Masuk</a>
      <a href="dosen/register.php" class="btn btn-orange btn-sm">Daftar</a>
    </div>
  </nav>

  <!-- ISI HERO -->
  <div class="hero-inner">
    <div class="hero-text">
      <h1>
        Belajar Jadi<br>Lebih Seru dengan
        <span class="brand">QuizKu!</span>
      </h1>
      <p>Platform kuis online yang menggabungkan belajar dan permainan. Yuk, uji pengetahuanmu kapan saja, di mana saja!</p>

      <div class="hero-buttons">
        <a href="siswa/index.php" class="btn btn-primary">▶ Mengerjakan Quiz</a>
        <a href="dosen/login.php" class="btn btn-outline">Mau Bikin Soal</a>
      </div>
    </div>

    <!-- ILUSTRASI PIALA + BUKU (SVG) -->
    <div class="hero-art">
      <svg viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg" aria-label="Piala kuis">
        <defs>
          <linearGradient id="gold" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0" stop-color="#fde68a"/>
            <stop offset="0.5" stop-color="#fbbf24"/>
            <stop offset="1" stop-color="#d97706"/>
          </linearGradient>
          <linearGradient id="book" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0" stop-color="#3b82f6"/>
            <stop offset="1" stop-color="#1e40af"/>
          </linearGradient>
        </defs>

        <!-- Buku -->
        <rect x="60" y="300" width="280" height="46" rx="8" fill="url(#book)"/>
        <rect x="70" y="346" width="260" height="10" rx="4" fill="#e0e7ff"/>
        <rect x="75" y="268" width="250" height="40" rx="8" fill="#2563eb"/>
        <rect x="85" y="308" width="230" height="8" rx="3" fill="#f1f5f9"/>

        <!-- Kaki piala -->
        <rect x="180" y="220" width="40" height="50" fill="url(#gold)"/>
        <rect x="140" y="250" width="120" height="22" rx="6" fill="url(#gold)"/>

        <!-- Pegangan -->
        <path d="M110 90 C40 90 50 170 130 180" fill="none" stroke="url(#gold)" stroke-width="16" stroke-linecap="round"/>
        <path d="M290 90 C360 90 350 170 270 180" fill="none" stroke="url(#gold)" stroke-width="16" stroke-linecap="round"/>

        <!-- Cawan -->
        <path d="M100 70 H300 V120 C300 190 260 230 200 230 C140 230 100 190 100 120 Z" fill="url(#gold)"/>
        <ellipse cx="200" cy="70" rx="100" ry="14" fill="#fde68a"/>

        <!-- Bintang -->
        <polygon points="200,100 212,132 246,134 219,155 229,188 200,168 171,188 181,155 154,134 188,132" fill="#fff7d6" opacity=".9"/>

        <!-- Konfeti -->
        <circle cx="50" cy="60" r="6" fill="#f97316"/>
        <rect x="330" y="40" width="10" height="10" fill="#3b82f6" transform="rotate(25 335 45)"/>
        <circle cx="350" cy="230" r="5" fill="#ef4444"/>
        <rect x="30" y="200" width="9" height="9" fill="#22c55e" transform="rotate(40 34 204)"/>
      </svg>
    </div>
  </div>

  <div class="scroll-down">⌄</div>
</section>

</body>
</html>