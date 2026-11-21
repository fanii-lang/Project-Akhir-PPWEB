<?php
session_start();
require_once "../config/database.php";

// Pastikan dosen sudah login
if (!isset($_SESSION["id_dosen"])) {
    header("Location: login.php");
    exit;
}

$id_dosen = (int) $_SESSION["id_dosen"];

// Ambil seluruh hasil kuis milik dosen yang sedang login
$stmt = $conn->prepare("
    SELECT
        h.id_hasil,
        h.skor_akhir,
        h.jumlah_benar,
        h.jumlah_salah,
        h.streak_tertinggi,
        h.waktu_selesai,
        s.nama_siswa,
        k.judul_quiz,
        k.kode_quiz
    FROM hasil h
    JOIN siswa s ON h.id_siswa = s.id_siswa
    JOIN kuis k ON h.id_quiz = k.id_quiz
    WHERE k.id_dosen = ?
    ORDER BY h.waktu_selesai DESC
");

$stmt->bind_param("i", $id_dosen);
$stmt->execute();

$result = $stmt->get_result();

$daftar_hasil = [];

while ($row = $result->fetch_assoc()) {
    $daftar_hasil[] = $row;
}

$stmt->close();

// Hitung ringkasan hasil
$total_peserta = count($daftar_hasil);
$total_nilai = 0;

foreach ($daftar_hasil as $item) {
    $total_nilai += (float) $item["skor_akhir"];
}

$rata_rata = $total_peserta > 0
    ? $total_nilai / $total_peserta
    : 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Hasil Kuis - QuizKu</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            background: #FFF9F1;
            color: #263238;
        }

        .navbar {
            background: white;
            padding: 18px 7%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px #00000010;
        }

        .logo {
            color: #FF6B35;
            font-size: 25px;
            font-weight: bold;
        }

        .container {
            max-width: 1100px;
            margin: 35px auto;
            padding: 0 20px;
        }

        .header {
            margin-bottom: 25px;
        }

        .header h1 {
            margin-bottom: 8px;
        }

        .header p {
            color: #777;
        }

        .summary {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 18px;
            margin-bottom: 25px;
        }

        .summary-card {
            background: white;
            padding: 22px;
            border-radius: 15px;
            box-shadow: 0 3px 12px #00000008;
        }

        .summary-card p {
            color: #777;
            margin-bottom: 10px;
        }

        .summary-card h2 {
            font-size: 30px;
            color: #2563EB;
        }

        .table-card {
            background: white;
            padding: 22px;
            border-radius: 15px;
            box-shadow: 0 3px 12px #00000008;
            overflow: hidden;
        }

        .table-card h2 {
            margin-bottom: 20px;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 950px;
        }

        th, td {
            padding: 14px 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #FFF9F1;
            color: #555;
            font-size: 14px;
        }

        td {
            font-size: 14px;
        }

        .score {
            color: #2563EB;
            font-weight: bold;
        }

        .detail-button {
            display: inline-block;
            padding: 9px 13px;
            background: #2563EB;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: bold;
            white-space: nowrap;
            transition: 0.2s;
        }

        .detail-button:hover {
            background: #1D4ED8;
        }

        .empty {
            text-align: center;
            padding: 35px;
            color: #777;
        }

        .back-button {
            display: inline-block;
            margin-top: 25px;
            padding: 13px 20px;
            background: #FF6B35;
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: bold;
        }

        @media (max-width: 600px) {
            .navbar {
                padding: 16px 5%;
            }

            .container {
                margin-top: 25px;
            }

            .summary {
                grid-template-columns: 1fr;
            }

            .table-card {
                padding: 15px;
            }
        }
    </style>
</head>

<body>

<header class="navbar">
    <div class="logo">QuizKu</div>
</header>

<main class="container">

    <section class="header">
        <h1>📊 Hasil Kuis Peserta</h1>
        <p>
            Lihat perkembangan dan nilai peserta yang mengerjakan kuis kamu.
        </p>
    </section>

    <section class="summary">

        <div class="summary-card">
            <p>Total Peserta Mengerjakan</p>
            <h2><?= $total_peserta ?></h2>
        </div>

        <div class="summary-card">
            <p>Rata-rata Nilai</p>
            <h2><?= number_format($rata_rata, 2) ?></h2>
        </div>

    </section>

    <section class="table-card">

        <h2>Daftar Hasil Peserta</h2>

        <div class="table-wrapper">

            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Peserta</th>
                        <th>Judul Kuis</th>
                        <th>Kode Kuis</th>
                        <th>Nilai</th>
                        <th>Benar</th>
                        <th>Salah</th>
                        <th>Streak 🔥</th>
                        <th>Waktu Selesai</th>
                        <th>Detail</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if ($total_peserta > 0): ?>

                        <?php foreach ($daftar_hasil as $index => $item): ?>

                            <tr>
                                <td><?= $index + 1 ?></td>

                                <td>
                                    <?= htmlspecialchars($item["nama_siswa"]) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($item["judul_quiz"]) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($item["kode_quiz"]) ?>
                                </td>

                                <td class="score">
                                    <?= number_format((float) $item["skor_akhir"], 2) ?>
                                </td>

                                <td><?= (int) $item["jumlah_benar"] ?></td>

                                <td><?= (int) $item["jumlah_salah"] ?></td>

                                <td>
                                    <?= (int) $item["streak_tertinggi"] ?>
                                </td>

                                <td>
                                    <?= date(
                                        "d-m-Y H:i",
                                        strtotime($item["waktu_selesai"])
                                    ) ?>
                                </td>

                                <td>
                                    <a
                                        href="detail_hasil.php?id=<?= (int) $item["id_hasil"] ?>"
                                        class="detail-button"
                                    >
                                        Lihat Detail
                                    </a>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="10" class="empty">
                                Belum ada peserta yang mengerjakan kuis.
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>
            </table>

        </div>

    </section>

    <a href="dashboard.php" class="back-button">
        ← Kembali ke Dashboard
    </a>

</main>

</body>
</html>