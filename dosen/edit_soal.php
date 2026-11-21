<?php
session_start();
require_once "../config/database.php";

// Pastikan dosen sudah login
if (!isset($_SESSION["id_dosen"])) {
    header("Location: login.php");
    exit;
}

$id_dosen = (int) $_SESSION["id_dosen"];
$id_soal = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

if ($id_soal <= 0) {
    header("Location: daftar_kuis.php");
    exit;
}

// Ambil soal sekaligus memastikan soal milik kuis dosen
$stmt = $conn->prepare("
    SELECT s.*, k.id_quiz, k.judul_quiz
    FROM soal s
    JOIN kuis k ON s.id_quiz = k.id_quiz
    WHERE s.id_soal = ? AND k.id_dosen = ?
");

$stmt->bind_param("ii", $id_soal, $id_dosen);
$stmt->execute();

$soal = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$soal) {
    header("Location: daftar_kuis.php");
    exit;
}

$pesan_error = "";

// Proses update soal
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $pertanyaan = trim($_POST["pertanyaan"] ?? "");
    $opsi_a = trim($_POST["opsi_a"] ?? "");
    $opsi_b = trim($_POST["opsi_b"] ?? "");
    $opsi_c = trim($_POST["opsi_c"] ?? "");
    $opsi_d = trim($_POST["opsi_d"] ?? "");
    $jawaban_benar = $_POST["jawaban_benar"] ?? "";
    $penjelasan = trim($_POST["penjelasan"] ?? "");

    $is_surprise = isset($_POST["is_surprise"]) ? 1 : 0;
    $bonus_poin = $is_surprise === 1 ? 10 : 0;

    // Validasi input
    if (
        $pertanyaan === "" ||
        $opsi_a === "" ||
        $opsi_b === "" ||
        $opsi_c === "" ||
        $opsi_d === ""
    ) {
        $pesan_error = "Semua pertanyaan dan pilihan jawaban wajib diisi.";
    } elseif (!in_array($jawaban_benar, ["A", "B", "C", "D"], true)) {
        $pesan_error = "Pilih kunci jawaban yang benar.";
    } else {

        $stmt = $conn->prepare("
            UPDATE soal
            SET
                pertanyaan = ?,
                opsi_a = ?,
                opsi_b = ?,
                opsi_c = ?,
                opsi_d = ?,
                jawaban_benar = ?,
                penjelasan = ?,
                is_surprise = ?,
                bonus_poin = ?
            WHERE id_soal = ?
        ");

        $stmt->bind_param(
            "sssssssiii",
            $pertanyaan,
            $opsi_a,
            $opsi_b,
            $opsi_c,
            $opsi_d,
            $jawaban_benar,
            $penjelasan,
            $is_surprise,
            $bonus_poin,
            $id_soal
        );

        if ($stmt->execute()) {
            $stmt->close();

            header("Location: daftar_soal.php?id=" . (int)$soal["id_quiz"]);
            exit;
        } else {
            $pesan_error = "Soal gagal diperbarui. Silakan coba lagi.";
            $stmt->close();
        }
    }

    // Tampilkan kembali input terakhir jika validasi gagal
    $soal["pertanyaan"] = $pertanyaan;
    $soal["opsi_a"] = $opsi_a;
    $soal["opsi_b"] = $opsi_b;
    $soal["opsi_c"] = $opsi_c;
    $soal["opsi_d"] = $opsi_d;
    $soal["jawaban_benar"] = $jawaban_benar;
    $soal["penjelasan"] = $penjelasan;
    $soal["is_surprise"] = $is_surprise;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Edit Soal - QuizKu</title>

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
    margin-bottom: 8px;
}

.subtitle {
    color: #777;
    font-size: 14px;
    margin-bottom: 25px;
}

.form-group {
    margin-bottom: 18px;
}

label {
    display: block;
    font-weight: bold;
    margin-bottom: 8px;
    font-size: 14px;
}

input[type="text"],
textarea,
select {
    width: 100%;
    padding: 13px;
    border: 1px solid #ddd;
    border-radius: 10px;
    font-size: 14px;
    font-family: inherit;
}

textarea {
    min-height: 100px;
    resize: vertical;
}

input:focus,
textarea:focus,
select:focus {
    outline: none;
    border-color: #2563EB;
}

.checkbox-group {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 18px;
}

.checkbox-group label {
    margin: 0;
    cursor: pointer;
}

.checkbox-group input {
    width: 18px;
    height: 18px;
    accent-color: #FF6B35;
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

@media (max-width: 500px) {
    .card {
        padding: 20px;
    }
}
</style>
</head>

<body>

<nav class="navbar">
    <div class="logo">Quiz<span>Ku</span> ✨</div>
</nav>

<main class="container">

    <a
        href="daftar_soal.php?id=<?= (int)$soal["id_quiz"] ?>"
        class="back">
        ← Kembali ke Daftar Soal
    </a>

    <div class="card">

        <h1>✏️ Edit Soal</h1>

        <p class="subtitle">
            Kuis: <?= htmlspecialchars($soal["judul_quiz"]) ?>
        </p>

        <?php if ($pesan_error !== ""): ?>
            <div class="error">
                <?= htmlspecialchars($pesan_error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="form-group">
                <label>Pertanyaan</label>
                <textarea
                    name="pertanyaan"
                    required><?= htmlspecialchars($soal["pertanyaan"]) ?></textarea>
            </div>

            <div class="form-group">
                <label>Pilihan A</label>
                <input
                    type="text"
                    name="opsi_a"
                    value="<?= htmlspecialchars($soal["opsi_a"]) ?>"
                    required>
            </div>

            <div class="form-group">
                <label>Pilihan B</label>
                <input
                    type="text"
                    name="opsi_b"
                    value="<?= htmlspecialchars($soal["opsi_b"]) ?>"
                    required>
            </div>

            <div class="form-group">
                <label>Pilihan C</label>
                <input
                    type="text"
                    name="opsi_c"
                    value="<?= htmlspecialchars($soal["opsi_c"]) ?>"
                    required>
            </div>

            <div class="form-group">
                <label>Pilihan D</label>
                <input
                    type="text"
                    name="opsi_d"
                    value="<?= htmlspecialchars($soal["opsi_d"]) ?>"
                    required>
            </div>

            <div class="form-group">
                <label>Kunci Jawaban</label>

                <select name="jawaban_benar" required>
                    <option value="A" <?= $soal["jawaban_benar"] === "A" ? "selected" : "" ?>>A</option>
                    <option value="B" <?= $soal["jawaban_benar"] === "B" ? "selected" : "" ?>>B</option>
                    <option value="C" <?= $soal["jawaban_benar"] === "C" ? "selected" : "" ?>>C</option>
                    <option value="D" <?= $soal["jawaban_benar"] === "D" ? "selected" : "" ?>>D</option>
                </select>
            </div>

            <div class="form-group">
                <label>Pembahasan</label>
                <textarea name="penjelasan"><?= htmlspecialchars($soal["penjelasan"] ?? "") ?></textarea>
            </div>

            <div class="checkbox-group">
                <input
                    type="checkbox"
                    name="is_surprise"
                    id="is_surprise"
                    value="1"
                    <?= (int)$soal["is_surprise"] === 1 ? "checked" : "" ?>>

                <label for="is_surprise">
                    ⭐ Jadikan Surprise Question (+10 poin)
                </label>
            </div>

            <button type="submit" class="submit">
                Simpan Perubahan
            </button>

        </form>

    </div>

</main>

</body>
</html>