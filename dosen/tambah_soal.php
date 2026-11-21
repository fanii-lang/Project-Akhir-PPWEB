<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["id_dosen"])) {
    header("Location: login.php");
    exit;
}

$id_quiz = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
$id_dosen = $_SESSION["id_dosen"];

// Pastikan kuis benar-benar milik dosen yang login
$stmt = mysqli_prepare($conn,
    "SELECT id_quiz, judul_quiz
     FROM kuis
     WHERE id_quiz = ? AND id_dosen = ?"
);

mysqli_stmt_bind_param($stmt, "ii", $id_quiz, $id_dosen);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$kuis = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$kuis) {
    die("Kuis tidak ditemukan atau bukan milik akun kamu.");
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $pertanyaan = trim($_POST["pertanyaan"]);
    $opsi_a = trim($_POST["opsi_a"]);
    $opsi_b = trim($_POST["opsi_b"]);
    $opsi_c = trim($_POST["opsi_c"]);
    $opsi_d = trim($_POST["opsi_d"]);
    $jawaban = $_POST["jawaban_benar"];
    $penjelasan = trim($_POST["penjelasan"]);

    $is_surprise = isset($_POST["is_surprise"]) ? 1 : 0;
    $bonus_poin = $is_surprise ? 10 : 0;

    if (
        $pertanyaan == "" ||
        $opsi_a == "" ||
        $opsi_b == "" ||
        $opsi_c == "" ||
        $opsi_d == "" ||
        $penjelasan == ""
    ) {
        $error = "Semua bagian soal wajib diisi!";
    } elseif (!in_array($jawaban, ["A", "B", "C", "D"], true)) {
        $error = "Pilih jawaban yang benar!";
    } else {

        $insert = mysqli_prepare($conn,
            "INSERT INTO soal
            (id_quiz, pertanyaan, opsi_a, opsi_b, opsi_c, opsi_d,
             jawaban_benar, penjelasan, is_surprise, bonus_poin)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $insert,
            "isssssssii",
            $id_quiz,
            $pertanyaan,
            $opsi_a,
            $opsi_b,
            $opsi_c,
            $opsi_d,
            $jawaban,
            $penjelasan,
            $is_surprise,
            $bonus_poin
        );

        if (mysqli_stmt_execute($insert)) {
            $success = "Soal berhasil ditambahkan!";
        } else {
            $error = "Gagal menyimpan soal. Silakan coba lagi.";
        }

        mysqli_stmt_close($insert);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tambah Soal - QuizKu</title>

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
    padding: 20px 8%;
    box-shadow: 0 2px 10px rgba(0,0,0,.04);
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
    max-width: 750px;
    margin: 40px auto;
    padding: 0 20px;
}

.back {
    display: inline-block;
    color: #2563EB;
    text-decoration: none;
    margin-bottom: 25px;
}

.card {
    background: white;
    padding: 30px;
    border-radius: 20px;
    box-shadow: 0 5px 25px rgba(0,0,0,.05);
}

h1 {
    font-size: 26px;
    margin-bottom: 8px;
}

.subtitle {
    color: #777;
    font-size: 14px;
    margin-bottom: 25px;
}

label {
    display: block;
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 8px;
}

input[type="text"], textarea, select {
    width: 100%;
    padding: 13px;
    border: 1px solid #ddd;
    border-radius: 11px;
    margin-bottom: 20px;
    font-family: inherit;
    font-size: 14px;
    outline: none;
}

textarea {
    resize: vertical;
    min-height: 90px;
}

input:focus, textarea:focus, select:focus {
    border-color: #2563EB;
}

.option-label {
    background: #FFF0C2;
    display: inline-block;
    padding: 5px 10px;
    border-radius: 7px;
    margin-bottom: 8px;
}

.surprise {
    background: #FFF8DD;
    padding: 16px;
    border-radius: 12px;
    margin-bottom: 22px;
    font-size: 14px;
}

.surprise input {
    margin-right: 7px;
}

button {
    width: 100%;
    background: #FF6B35;
    color: white;
    border: none;
    padding: 15px;
    border-radius: 12px;
    font-weight: bold;
    font-size: 15px;
    cursor: pointer;
}

button:hover {
    background: #E95725;
}

.message {
    padding: 13px;
    border-radius: 10px;
    margin-bottom: 20px;
    font-size: 14px;
}

.error {
    background: #FFE5E0;
    color: #C0392B;
}

.success {
    background: #E1F7E9;
    color: #237A42;
}
</style>
</head>

<body>

<nav class="navbar">
    <div class="logo">Quiz<span>Ku</span> ✨</div>
</nav>

<div class="container">

    <a href="daftar_kuis.php" class="back">
        ← Kembali ke Daftar Kuis
    </a>

    <div class="card">

        <h1>Tambah Soal ✏️</h1>

        <p class="subtitle">
            Kuis: <strong><?= htmlspecialchars($kuis["judul_quiz"]) ?></strong>
        </p>

        <?php if ($error != ""): ?>
            <div class="message error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success != ""): ?>
            <div class="message success">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <label>Pertanyaan</label>
            <textarea
                name="pertanyaan"
                placeholder="Tuliskan pertanyaan kuis..."
                required
            ></textarea>

            <label><span class="option-label">A</span> Pilihan A</label>
            <input type="text" name="opsi_a" placeholder="Masukkan pilihan A" required>

            <label><span class="option-label">B</span> Pilihan B</label>
            <input type="text" name="opsi_b" placeholder="Masukkan pilihan B" required>

            <label><span class="option-label">C</span> Pilihan C</label>
            <input type="text" name="opsi_c" placeholder="Masukkan pilihan C" required>

            <label><span class="option-label">D</span> Pilihan D</label>
            <input type="text" name="opsi_d" placeholder="Masukkan pilihan D" required>

            <label>Jawaban yang Benar</label>
            <select name="jawaban_benar" required>
                <option value="">-- Pilih Jawaban --</option>
                <option value="A">A</option>
                <option value="B">B</option>
                <option value="C">C</option>
                <option value="D">D</option>
            </select>

            <label>Penjelasan Jawaban</label>
            <textarea
                name="penjelasan"
                placeholder="Berikan penjelasan singkat mengenai jawaban..."
                required
            ></textarea>

            <div class="surprise">
                <label>
                    <input type="checkbox" name="is_surprise" value="1">
                    ⭐ Jadikan Surprise Question (+10 poin)
                </label>

                <p style="color:#777; margin-top:7px;">
                    Soal khusus yang memberikan bonus poin jika dijawab benar.
                </p>
            </div>

            <button type="submit">
                Simpan Soal →
            </button>

        </form>

    </div>
</div>

</body>
</html>