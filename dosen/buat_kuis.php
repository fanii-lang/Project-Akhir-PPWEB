<?php
session_start();
require_once "../config/database.php";

// Pastikan dosen sudah login
if (!isset($_SESSION["id_dosen"])) {
    header("Location: login.php");
    exit;
}

$error = "";
$success = "";
$kode_berhasil = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $judul = trim($_POST["judul_quiz"]);
    $durasi = (int) $_POST["durasi"];
    

    if ($judul == "") {
        $error = "Judul kuis tidak boleh kosong!";
    } elseif ($durasi < 1 || $durasi > 300) {
        $error = "Durasi harus antara 1 sampai 300 menit!";
    } else {

        // Membuat kode kuis otomatis dan unik
        do {
            $kode = strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));

            $cek = mysqli_prepare($conn,
                "SELECT id_quiz FROM kuis WHERE kode_quiz = ?"
            );

            mysqli_stmt_bind_param($cek, "s", $kode);
            mysqli_stmt_execute($cek);
            $hasil_cek = mysqli_stmt_get_result($cek);

            $kode_ada = mysqli_num_rows($hasil_cek) > 0;

            mysqli_stmt_close($cek);

        } while ($kode_ada);

        // Simpan kuis ke database
        $stmt = mysqli_prepare($conn,
            "INSERT INTO kuis (id_dosen, judul_quiz, kode_quiz, durasi)
             VALUES (?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "issi",
            $_SESSION["id_dosen"],
            $judul,
            $kode,
            $durasi
        );

        if (mysqli_stmt_execute($stmt)) {
            $success = "Kuis berhasil dibuat!";
            $kode_berhasil = $kode;
        } else {
            $error = "Kuis gagal dibuat. Silakan coba lagi.";
        }

        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Kuis - QuizKu</title>

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
            min-height: 100vh;
        }

        .navbar {
            background: white;
            padding: 20px 8%;
            box-shadow: 0 2px 10px rgba(0,0,0,0.04);
        }

        .logo {
            color: #FF6B35;
            font-size: 27px;
            font-weight: 800;
        }

        .logo span {
            color: #2563EB;
        }

        .container {
            max-width: 650px;
            margin: 45px auto;
            padding: 0 20px;
        }

        .back {
            display: inline-block;
            text-decoration: none;
            color: #2563EB;
            font-size: 14px;
            margin-bottom: 25px;
        }

        .card {
            background: white;
            padding: 35px;
            border-radius: 23px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.06);
        }

        .icon {
            font-size: 48px;
            text-align: center;
            margin-bottom: 15px;
        }

        h1 {
            text-align: center;
            font-size: 27px;
            margin-bottom: 10px;
        }

        .description {
            text-align: center;
            color: #777;
            font-size: 14px;
            margin-bottom: 30px;
        }

        label {
            display: block;
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 9px;
        }

        input {
            width: 100%;
            padding: 14px;
            border: 1px solid #ddd;
            border-radius: 12px;
            font-size: 14px;
            margin-bottom: 22px;
            outline: none;
        }

        input:focus {
            border-color: #2563EB;
        }

        button {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 12px;
            background: #FF6B35;
            color: white;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #E95725;
        }

        .message {
            padding: 14px;
            border-radius: 12px;
            text-align: center;
            font-size: 14px;
            margin-bottom: 22px;
        }

        .error {
            background: #FFE5E0;
            color: #C0392B;
        }

        .success {
            background: #E1F7E9;
            color: #237A42;
        }

        .kode {
            display: block;
            font-size: 30px;
            font-weight: 800;
            letter-spacing: 5px;
            margin: 15px 0;
            color: #2563EB;
        }

        .copy-btn {
            background: #2563EB;
            margin-top: 12px;
        }

        .copy-btn:hover {
            background: #194FC4;
        }

        .dashboard-link {
            display: block;
            text-align: center;
            color: #2563EB;
            text-decoration: none;
            margin-top: 22px;
            font-size: 14px;
        }

        @media(max-width: 500px) {
            .card {
                padding: 25px 20px;
            }
        }
    </style>
</head>

<body>

<nav class="navbar">
    <div class="logo">Quiz<span>Ku</span> ✨</div>
</nav>

<div class="container">

    <a href="dashboard.php" class="back">
        ← Kembali ke Dashboard
    </a>

    <div class="card">

        <div class="icon">📝</div>

        <h1>Buat Kuis Baru</h1>

        <p class="description">
            Buat kuis dan bagikan kode kepada peserta.
        </p>

        <?php if ($error != ""): ?>
            <div class="message error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success != ""): ?>

            <div class="message success">
                <strong><?= htmlspecialchars($success) ?></strong>

                <p style="margin-top:10px;">
                    Berikut kode kuis kamu:
                </p>

                <span class="kode" id="kodeKuis">
                    <?= htmlspecialchars($kode_berhasil) ?>
                </span>

                <p>Bagikan kode ini kepada peserta.</p>

                <button
                    type="button"
                    class="copy-btn"
                    onclick="salinKode()">
                    📋 Salin Kode Kuis
                </button>
            </div>

            <a href="daftar_kuis.php" class="dashboard-link">
                Lanjut Kelola Soal →
            </a>

            <a href="dashboard.php" class="dashboard-link">
                Kembali ke Dashboard
            </a>

        <?php else: ?>

            <form method="POST">

                <label>Judul Kuis</label>
                <input
                    type="text"
                    name="judul_quiz"
                    placeholder="Contoh: Kuis Pemrograman Web"
                    maxlength="100"
                    required
                >

                <label>Durasi Pengerjaan (Menit)</label>
                <input
                    type="number"
                    name="durasi"
                    placeholder="Contoh: 30"
                    min="1"
                    max="300"
                    required
                >

                <button type="submit">
                    Buat Kuis Sekarang →
                </button>

            </form>

        <?php endif; ?>

    </div>
</div>

<script>
function salinKode() {
    const kode = document.getElementById("kodeKuis").innerText.trim();

    navigator.clipboard.writeText(kode)
        .then(() => alert("Kode kuis berhasil disalin!"))
        .catch(() => alert("Gagal menyalin kode. Silakan salin secara manual."));
}
</script>

</body>
</html>