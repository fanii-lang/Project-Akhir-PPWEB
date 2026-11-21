<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["id_dosen"])) {
    header("Location: login.php");
    exit;
}

$id_dosen = (int) $_SESSION["id_dosen"];

// Token keamanan untuk proses hapus kuis
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$csrf_token = $_SESSION["csrf_token"];

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
<title>Daftar Kuis - QuizKu</title>

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
    margin: 45px auto;
    padding: 0 20px;
}

.back {
    display: inline-block;
    margin-bottom: 25px;
    color: #2563EB;
    text-decoration: none;
}

h1 {
    margin-bottom: 10px;
}

.subtitle {
    color: #777;
    margin-bottom: 28px;
    font-size: 14px;
}

.quiz-card {
    background: white;
    padding: 23px;
    border-radius: 18px;
    margin-bottom: 18px;
    box-shadow: 0 5px 20px rgba(0,0,0,.05);
}

.quiz-card h3 {
    margin-bottom: 12px;
}

.info {
    color: #777;
    font-size: 13px;
    margin-bottom: 18px;
}

.actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.btn {
    display: inline-block;
    color: white;
    text-decoration: none;
    padding: 11px 17px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: bold;
}

.btn-blue {
    background: #2563EB;
}

.btn-orange {
    background: #FF6B35;
}

.btn-red {
    background: #DC3545;
    border: none;
    cursor: pointer;
    font-family: inherit;
}

.btn-red:hover {
    background: #B02A37;
}

.alert {
    padding: 13px 16px;
    border-radius: 10px;
    margin-bottom: 20px;
    font-size: 14px;
}

.alert-success {
    background: #D1FAE5;
    color: #065F46;
}

.alert-error {
    background: #FEE2E2;
    color: #991B1B;
}

.empty {
    background: white;
    padding: 35px;
    border-radius: 18px;
    text-align: center;
    color: #777;
}

.empty a {
    display: inline-block;
    margin-top: 18px;
    background: #FF6B35;
    color: white;
    padding: 12px 18px;
    border-radius: 10px;
    text-decoration: none;
}

@media (max-width: 500px) {
    .actions {
        flex-direction: column;
    }

    .btn {
        text-align: center;
        width: 100%;
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

    <h1>Kelola Soal Kuis 📚</h1>

    <p class="subtitle">
        Pilih kuis yang ingin kamu lihat, edit, atau hapus.
    </p>

    <?php if (isset($_GET["status"]) && $_GET["status"] === "hapus_berhasil"): ?>
        <div class="alert alert-success">
            Kuis berhasil dihapus beserta soal dan riwayat hasilnya.
        </div>
    <?php elseif (isset($_GET["status"]) && $_GET["status"] === "hapus_gagal"): ?>
        <div class="alert alert-error">
            Kuis gagal dihapus. Silakan coba lagi.
        </div>
    <?php endif; ?>

    <?php if (mysqli_num_rows($result) > 0): ?>

        <?php while ($kuis = mysqli_fetch_assoc($result)): ?>

            <div class="quiz-card">

                <h3>
                    <?= htmlspecialchars($kuis["judul_quiz"]) ?>
                </h3>

                <div class="info">
                    Kode:
                    <strong><?= htmlspecialchars($kuis["kode_quiz"]) ?></strong>
                    &nbsp; | &nbsp;
                    Durasi: <?= (int)$kuis["durasi"] ?> menit
                </div>

                <div class="actions">

                    <a
                        href="edit_kuis.php?id=<?= (int)$kuis["id_quiz"] ?>"
                        class="btn btn-blue">
                        ✏️ Edit Kuis
                    </a>

                    <a
                        href="daftar_soal.php?id=<?= (int)$kuis["id_quiz"] ?>"
                        class="btn btn-blue">
                        Lihat & Edit Soal
                    </a>

                    <form
                        action="hapus_kuis.php"
                        method="POST"
                        onsubmit="return confirm('Yakin ingin menghapus kuis ini? Semua soal dan riwayat hasil peserta juga akan terhapus permanen.');">

                        <input
                            type="hidden"
                            name="id_quiz"
                            value="<?= (int)$kuis["id_quiz"] ?>">

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= htmlspecialchars($csrf_token) ?>">

                        <button type="submit" class="btn btn-red">
                            🗑️ Hapus Kuis
                        </button>

                    </form>

                </div>

            </div>

        <?php endwhile; ?>

    <?php else: ?>

        <div class="empty">
            <h2>Belum Ada Kuis 🎯</h2>
            <p>Kamu belum membuat kuis.</p>

            <a href="buat_kuis.php">Buat Kuis Sekarang</a>
        </div>

    <?php endif; ?>

</div>

</body>
</html>

<?php
mysqli_stmt_close($stmt);
?>