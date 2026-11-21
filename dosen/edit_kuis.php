<?php
session_start();
require_once "../config/database.php";

// Pastikan dosen sudah login
if (!isset($_SESSION["id_dosen"])) {
    header("Location: login.php");
    exit;
}

$id_dosen = (int) $_SESSION["id_dosen"];
$id_quiz = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

if ($id_quiz <= 0) {
    header("Location: daftar_kuis.php");
    exit;
}

// Ambil data kuis milik dosen
$stmt = $conn->prepare("
    SELECT id_quiz, judul_quiz, kode_quiz, durasi
    FROM kuis
    WHERE id_quiz = ? AND id_dosen = ?
");

$stmt->bind_param("ii", $id_quiz, $id_dosen);
$stmt->execute();

$kuis = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$kuis) {
    header("Location: daftar_kuis.php");
    exit;
}

$pesan_error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $judul_quiz = trim($_POST["judul_quiz"] ?? "");
    $durasi = (int) ($_POST["durasi"] ?? 0);

    if ($judul_quiz === "") {
        $pesan_error = "Judul kuis wajib diisi.";
    } elseif ($durasi < 1 || $durasi > 300) {
        $pesan_error = "Durasi harus antara 1 sampai 300 menit.";
    } else {

        $stmt = $conn->prepare("
            UPDATE kuis
            SET judul_quiz = ?, durasi = ?
            WHERE id_quiz = ? AND id_dosen = ?
        ");

        $stmt->bind_param(
            "siii",
            $judul_quiz,
            $durasi,
            $id_quiz,
            $id_dosen
        );

        if ($stmt->execute()) {
            $stmt->close();

            header("Location: daftar_kuis.php");
            exit;
        } else {
            $pesan_error = "Data kuis gagal diperbarui.";
            $stmt->close();
        }
    }

    $kuis["judul_quiz"] = $judul_quiz;
    $kuis["durasi"] = $durasi;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Edit Kuis - QuizKu</title>

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
    max-width: 650px;
    margin: 45px auto;
    padding: 0 20px;
}

.back {
    display: inline-block;
    margin-bottom: 22px;
    color: #2563EB;
    text-decoration: none;
}

.card {
    background: white;
    padding: 28px;
    border-radius: 18px;
    box-shadow: 0 5px 20px rgba(0,0,0,.05);
}

h1 {
    margin-bottom: 10px;
}

.subtitle {
    color: #777;
    font-size: 14px;
    margin-bottom: 25px;
}

.form-group {
    margin-bottom: 20px;
}

label {
    display: block;
    font-weight: bold;
    margin-bottom: 9px;
    font-size: 14px;
}

input {
    width: 100%;
    padding: 13px;
    border: 1px solid #ddd;
    border-radius: 10px;
    font-size: 14px;
}

input:focus {
    outline: none;
    border-color: #2563EB;
}

.info {
    background: #F0F5FF;
    padding: 13px;
    border-radius: 10px;
    margin-bottom: 20px;
    font-size: 13px;
}

.error {
    background: #FDECEC;
    color: #C62828;
    padding: 12px;
    border-radius: 10px;
    margin-bottom: 18px;
    font-size: 14px;
}

.submit {
    width: 100%;
    padding: 14px;
    background: #FF6B35;
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 15px;
    font-weight: bold;
    cursor: pointer;
}

.submit:hover {
    background: #E85A27;
}
</style>
</head>

<body>

<nav class="navbar">
    <div class="logo">Quiz<span>Ku</span> ✨</div>
</nav>

<main class="container">

    <a href="daftar_kuis.php" class="back">
        ← Kembali ke Daftar Kuis
    </a>

    <div class="card">

        <h1>✏️ Edit Data Kuis</h1>

        <p class="subtitle">
            Ubah informasi kuis sesuai kebutuhanmu.
        </p>

        <div class="info">
            Kode kuis:
            <strong><?= htmlspecialchars($kuis["kode_quiz"]) ?></strong>
            <br>
            Kode ini tidak berubah ketika data kuis diedit.
        </div>

        <?php if ($pesan_error !== ""): ?>
            <div class="error">
                <?= htmlspecialchars($pesan_error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="form-group">
                <label>Judul Kuis</label>
                <input
                    type="text"
                    name="judul_quiz"
                    maxlength="100"
                    value="<?= htmlspecialchars($kuis["judul_quiz"]) ?>"
                    required>
            </div>

            <div class="form-group">
                <label>Durasi (menit)</label>
                <input
                    type="number"
                    name="durasi"
                    min="1"
                    max="300"
                    value="<?= (int)$kuis["durasi"] ?>"
                    required>
            </div>

            <button type="submit" class="submit">
                Simpan Perubahan
            </button>

        </form>

    </div>

</main>

</body>
</html>