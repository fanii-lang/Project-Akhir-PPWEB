<?php
session_start();
require_once "../config/database.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nama = trim($_POST["nama_siswa"]);
    $kode = strtoupper(trim($_POST["kode_quiz"]));

    if ($nama == "" || $kode == "") {
        $error = "Nama dan kode kuis wajib diisi!";
    } else {

        // Cari kuis berdasarkan kode
        $stmt = mysqli_prepare($conn,
            "SELECT id_quiz, judul_quiz, durasi
             FROM kuis
             WHERE kode_quiz = ?"
        );

        mysqli_stmt_bind_param($stmt, "s", $kode);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $kuis = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);

        if (!$kuis) {
            $error = "Kode kuis tidak ditemukan! Periksa kembali kode kamu.";
        } else {

            // Pastikan kuis sudah memiliki soal
            $cek = mysqli_prepare($conn,
                "SELECT COUNT(*) AS total
                 FROM soal
                 WHERE id_quiz = ?"
            );

            mysqli_stmt_bind_param($cek, "i", $kuis["id_quiz"]);
            mysqli_stmt_execute($cek);

            $hasil = mysqli_stmt_get_result($cek);
            $jumlah = mysqli_fetch_assoc($hasil);

            mysqli_stmt_close($cek);

            if ($jumlah["total"] < 1) {
                $error = "Kuis ini belum memiliki soal. Silakan hubungi dosen.";
            } else {

                // Simpan identitas peserta sementara di session
                session_regenerate_id(true);

                $_SESSION["nama_siswa"] = $nama;
                $_SESSION["id_quiz"] = $kuis["id_quiz"];
                $_SESSION["judul_quiz"] = $kuis["judul_quiz"];
                $_SESSION["durasi"] = $kuis["durasi"];

                header("Location: kerjakan.php");
                exit;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ikut Kuis - QuizKu</title>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Arial, sans-serif;
    background: #FFFDF8;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
    color: #263238;
}

.container {
    width: 100%;
    max-width: 460px;
    background: white;
    padding: 38px;
    border-radius: 25px;
    box-shadow: 0 8px 30px rgba(0,0,0,.07);
}

.logo {
    text-align: center;
    font-size: 29px;
    font-weight: 800;
    color: #FF6B35;
}

.logo span {
    color: #2563EB;
}

.icon {
    text-align: center;
    font-size: 60px;
    margin: 22px 0 12px;
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
    line-height: 1.6;
    margin-bottom: 28px;
}

label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 8px;
}

input {
    width: 100%;
    padding: 14px;
    border: 1px solid #ddd;
    border-radius: 12px;
    font-size: 14px;
    margin-bottom: 20px;
    outline: none;
}

input:focus {
    border-color: #2563EB;
}

button {
    width: 100%;
    padding: 15px;
    background: #FF6B35;
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 15px;
    font-weight: bold;
    cursor: pointer;
}

button:hover {
    background: #E95725;
}

.error {
    background: #FFE5E0;
    color: #C0392B;
    padding: 12px;
    border-radius: 10px;
    font-size: 13px;
    text-align: center;
    margin-bottom: 20px;
}

.back {
    display: block;
    text-align: center;
    margin-top: 22px;
    color: #2563EB;
    text-decoration: none;
    font-size: 14px;
}
</style>
</head>

<body>

<div class="container">

    <div class="logo">Quiz<span>Ku</span> ✨</div>

    <div class="icon">🎯</div>

    <h1>Siap Ikut Kuis?</h1>

    <p class="description">
        Masukkan nama dan kode kuis yang diberikan dosen.
        Bersiaplah untuk tantangan seru!
    </p>

    <?php if ($error != ""): ?>
        <div class="error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <label>Nama Peserta</label>
        <input
            type="text"
            name="nama_siswa"
            placeholder="Masukkan nama kamu"
            maxlength="100"
            required
        >

        <label>Kode Kuis</label>
        <input
            type="text"
            name="kode_quiz"
            placeholder="Contoh: A1B2C3"
            maxlength="20"
            required
        >

        <button type="submit">
            Mulai Tantangan →
        </button>

    </form>

    <a href="../index.php" class="back">
        ← Kembali ke halaman utama
    </a>

</div>

</body>
</html>