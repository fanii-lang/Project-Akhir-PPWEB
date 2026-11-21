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

// Pastikan kuis milik dosen yang sedang login
$stmt = $conn->prepare("
    SELECT id_quiz, judul_quiz, kode_quiz
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

// Ambil seluruh soal dari kuis
$stmt = $conn->prepare("
    SELECT *
    FROM soal
    WHERE id_quiz = ?
    ORDER BY id_soal ASC
");

$stmt->bind_param("i", $id_quiz);
$stmt->execute();

$result = $stmt->get_result();

$daftar_soal = [];

while ($row = $result->fetch_assoc()) {
    $daftar_soal[] = $row;
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Daftar Soal - QuizKu</title>

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
    max-width: 900px;
    margin: 40px auto;
    padding: 0 20px;
}

.back {
    display: inline-block;
    margin-bottom: 22px;
    color: #2563EB;
    text-decoration: none;
}

.header {
    margin-bottom: 25px;
}

.header h1 {
    margin-bottom: 9px;
}

.header p {
    color: #777;
    font-size: 14px;
}

.quiz-info {
    background: #FFF0E8;
    padding: 16px 20px;
    border-radius: 13px;
    margin-bottom: 22px;
    font-size: 14px;
}

.question-card {
    background: white;
    padding: 23px;
    border-radius: 17px;
    margin-bottom: 18px;
    box-shadow: 0 5px 20px rgba(0,0,0,.05);
}

.question-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
}

.question-number {
    color: #2563EB;
    font-weight: bold;
}

.surprise {
    background: #FFF0B8;
    color: #9A6700;
    padding: 6px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
}

.question-text {
    font-size: 16px;
    font-weight: bold;
    margin-bottom: 17px;
    line-height: 1.6;
}

.options {
    display: grid;
    gap: 10px;
}

.option {
    background: #F8F9FC;
    padding: 12px 14px;
    border-radius: 10px;
    font-size: 14px;
    line-height: 1.5;
}

.correct {
    background: #E8F7ED;
    color: #187A3D;
    font-weight: bold;
}

.explanation {
    background: #F0F5FF;
    padding: 13px;
    border-radius: 10px;
    margin-top: 15px;
    font-size: 13px;
    line-height: 1.6;
}

.actions {
    display: flex;
    gap: 10px;
    margin-top: 18px;
}

.btn {
    display: inline-block;
    border: none;
    padding: 10px 16px;
    border-radius: 9px;
    color: white;
    text-decoration: none;
    font-size: 13px;
    font-weight: bold;
    cursor: pointer;
}

.edit {
    background: #2563EB;
}

.delete {
    background: #E5484D;
}

.add {
    background: #FF6B35;
    margin-bottom: 22px;
}

.empty {
    background: white;
    padding: 35px;
    text-align: center;
    border-radius: 17px;
    color: #777;
}

.empty p {
    margin-top: 10px;
}

@media (max-width: 500px) {
    .question-card {
        padding: 17px;
    }

    .actions {
        flex-direction: column;
    }

    .btn {
        text-align: center;
    }
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

    <section class="header">
        <h1>Daftar Soal 📚</h1>
        <p>
            Lihat dan kelola seluruh pertanyaan dalam kuis kamu.
        </p>
    </section>

    <div class="quiz-info">
        <strong><?= htmlspecialchars($kuis["judul_quiz"]) ?></strong><br>
        Kode Kuis:
        <?= htmlspecialchars($kuis["kode_quiz"]) ?><br>
        Total Soal: <?= count($daftar_soal) ?>
    </div>

    <a
        href="tambah_soal.php?id=<?= $id_quiz ?>"
        class="btn add">
        + Tambah Soal Baru
    </a>

    <?php if (count($daftar_soal) > 0): ?>

        <?php foreach ($daftar_soal as $index => $soal): ?>

            <div class="question-card">

                <div class="question-top">
                    <span class="question-number">
                        Soal <?= $index + 1 ?>
                    </span>

                    <?php if ((int)$soal["is_surprise"] === 1): ?>
                        <span class="surprise">
                            ⭐ Surprise Question
                        </span>
                    <?php endif; ?>
                </div>

                <div class="question-text">
                    <?= nl2br(htmlspecialchars($soal["pertanyaan"])) ?>
                </div>

                <div class="options">

                    <?php
                    $pilihan = [
                        "A" => $soal["opsi_a"],
                        "B" => $soal["opsi_b"],
                        "C" => $soal["opsi_c"],
                        "D" => $soal["opsi_d"]
                    ];
                    ?>

                    <?php foreach ($pilihan as $huruf => $isi): ?>

                        <div class="option <?= $soal["jawaban_benar"] === $huruf ? 'correct' : '' ?>">
                            <?= $huruf ?>.
                            <?= htmlspecialchars($isi) ?>

                            <?php if ($soal["jawaban_benar"] === $huruf): ?>
                                ✓ Jawaban benar
                            <?php endif; ?>
                        </div>

                    <?php endforeach; ?>

                </div>

                <?php if (!empty($soal["penjelasan"])): ?>
                    <div class="explanation">
                        <strong>💡 Pembahasan:</strong><br>
                        <?= nl2br(htmlspecialchars($soal["penjelasan"])) ?>
                    </div>
                <?php endif; ?>

                <?php if ((int)$soal["is_surprise"] === 1): ?>
                    <p style="margin-top:12px; font-size:13px; color:#9A6700;">
                        Bonus: <?= (int)$soal["bonus_poin"] ?> poin
                    </p>
                <?php endif; ?>

                <div class="actions">

                    <a
                        href="edit_soal.php?id=<?= (int)$soal["id_soal"] ?>"
                        class="btn edit">
                        ✏️ Edit Soal
                    </a>

                    <a
                        href="hapus_soal.php?id=<?= (int)$soal["id_soal"] ?>"
                        class="btn delete"
                        onclick="return confirm('Yakin ingin menghapus soal ini?')">
                        🗑 Hapus Soal
                    </a>

                </div>

            </div>

        <?php endforeach; ?>

    <?php else: ?>

        <div class="empty">
            <h2>Belum Ada Soal 🎯</h2>
            <p>Yuk, tambahkan soal pertama untuk kuis ini!</p>
        </div>

    <?php endif; ?>

</main>

</body>
</html>