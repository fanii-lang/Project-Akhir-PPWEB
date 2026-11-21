<?php
session_start();
require_once "../config/database.php";

// Pastikan dosen sudah login
if (!isset($_SESSION["id_dosen"])) {
    header("Location: login.php");
    exit;
}

$id_dosen = $_SESSION["id_dosen"];
$username = $_SESSION["username"];

// Ambil daftar kuis milik dosen yang login
$stmt = mysqli_prepare($conn,
    "SELECT id_quiz, judul_quiz, kode_quiz, durasi
     FROM kuis
     WHERE id_dosen = ?
     ORDER BY id_quiz DESC"
);

mysqli_stmt_bind_param($stmt, "i", $id_dosen);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Dosen - QuizKu</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #FFFDF8;
            color: #263238;
        }

        .navbar {
            background: white;
            padding: 20px 7%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.04);
        }

        .logo {
            font-size: 27px;
            font-weight: 800;
            color: #FF6B35;
        }

        .logo span {
            color: #2563EB;
        }

        .user {
            font-size: 14px;
            color: #555;
        }

        .container {
            max-width: 1100px;
            margin: 45px auto;
            padding: 0 25px;
        }

        .welcome {
            background: #FFF0C2;
            border-radius: 22px;
            padding: 32px;
            margin-bottom: 30px;
        }

        .welcome h1 {
            font-size: 29px;
            margin-bottom: 10px;
        }

        .welcome p {
            color: #74551D;
            font-size: 14px;
        }

        .welcome span {
            color: #FF6B35;
        }

        .section-title {
            font-size: 21px;
            margin-bottom: 20px;
        }

        .menu {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .menu-card {
            background: white;
            padding: 25px;
            border-radius: 19px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }

        .menu-icon {
            font-size: 35px;
            margin-bottom: 15px;
        }

        .menu-card h3 {
            margin-bottom: 9px;
        }

        .menu-card p {
            color: #777;
            font-size: 13px;
            line-height: 1.6;
            margin-bottom: 18px;
        }

        .btn {
            display: inline-block;
            text-decoration: none;
            padding: 11px 17px;
            border-radius: 10px;
            color: white;
            font-size: 13px;
            font-weight: bold;
        }

        .orange {
            background: #FF6B35;
        }

        .blue {
            background: #2563EB;
        }

        .quiz-list {
            background: white;
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }

        .quiz-item {
            padding: 18px 0;
            border-bottom: 1px solid #eee;
        }

        .quiz-item:last-child {
            border-bottom: none;
        }

        .quiz-item h3 {
            margin-bottom: 8px;
        }

        .quiz-info {
            color: #777;
            font-size: 13px;
        }

        .empty {
            text-align: center;
            color: #888;
            padding: 30px 10px;
            line-height: 1.7;
        }

        .logout {
            text-decoration: none;
            color: #FF6B35;
            font-weight: bold;
            font-size: 14px;
        }

        footer {
            text-align: center;
            padding: 25px;
            color: #999;
            font-size: 13px;
        }

        @media(max-width: 600px) {
            .welcome h1 {
                font-size: 23px;
            }

            .navbar {
                padding: 18px 5%;
            }
        }
    </style>
</head>

<body>

<nav class="navbar">
    <div class="logo">Quiz<span>Ku</span> ✨</div>

    <div class="user">
        Halo, <strong><?= htmlspecialchars($username) ?></strong>!
        &nbsp; | &nbsp;
        <a href="logout.php" class="logout">Logout</a>
    </div>
</nav>

<div class="container">

    <section class="welcome">
        <h1>Halo, <?= htmlspecialchars($username) ?>! 👋</h1>
        <p>
            Selamat datang di dashboard QuizKu.
            Yuk, buat kuis belajar yang seru dan interaktif!
        </p>
    </section>

    <h2 class="section-title">Menu Utama</h2>

    <section class="menu">

        <div class="menu-card">
            <div class="menu-icon">📝</div>
            <h3>Buat Kuis Baru</h3>
            <p>
                Buat kuis, tentukan judul, durasi,
                dan dapatkan kode kuis.
            </p>
            <a href="buat_kuis.php" class="btn orange">
                Buat Kuis →
            </a>
        </div>

        <div class="menu-card">
            <div class="menu-icon">📚</div>
            <h3>Kelola Soal</h3>
            <p>
                Tambahkan pertanyaan, pilihan jawaban,
                dan penjelasan soal.
            </p>
            <a href="daftar_kuis.php" class="btn blue">
                Kelola Soal →
            </a>
        </div>

        <div class="menu-card">
            <div class="menu-icon">🏆</div>
            <h3>Hasil Peserta</h3>
            <p>
                Lihat nilai dan hasil pengerjaan
                peserta kuis.
            </p>
            <a href="hasil.php" class="btn orange">
                Lihat Hasil →
            </a>
        </div>

    </section>

    <h2 class="section-title">Kuis Saya 📋</h2>

    <section class="quiz-list">

        <?php if (mysqli_num_rows($result) > 0): ?>

            <?php while ($kuis = mysqli_fetch_assoc($result)): ?>

                <div class="quiz-item">
                    <h3>
                        <?= htmlspecialchars($kuis["judul_quiz"]) ?>
                    </h3>

                    <div class="quiz-info">
                        Kode: <strong><?= htmlspecialchars($kuis["kode_quiz"]) ?></strong>
                        &nbsp; | &nbsp;
                        Durasi: <?= (int)$kuis["durasi"] ?> menit
                    </div>
                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <div class="empty">
                <div style="font-size:45px;">🎯</div>
                <h3>Belum Ada Kuis</h3>
                <p>
                    Kamu belum membuat kuis.
                    Yuk, buat kuis pertamamu!
                </p>
            </div>

        <?php endif; ?>

    </section>

</div>

<footer>
    © 2026 QuizKu. Belajar, Bermain, dan Berprestasi! ⭐
</footer>

</body>
</html>

<?php
mysqli_stmt_close($stmt);
?>