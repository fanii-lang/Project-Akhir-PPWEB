<?php
session_start();
require_once "../config/database.php";

// Pastikan dosen sudah login
if (!isset($_SESSION["id_dosen"])) {
    header("Location: login.php");
    exit;
}

$id_dosen = (int) $_SESSION["id_dosen"];

// Ambil ID hasil dari URL
$id_hasil = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

if ($id_hasil <= 0) {
    header("Location: hasil.php");
    exit;
}

// Ambil informasi hasil peserta
// Pastikan hasil tersebut milik dosen yang sedang login
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
    WHERE h.id_hasil = ?
    AND k.id_dosen = ?
");

$stmt->bind_param("ii", $id_hasil, $id_dosen);
$stmt->execute();

$result = $stmt->get_result();
$data_hasil = $result->fetch_assoc();
$stmt->close();

// Jika hasil tidak ditemukan atau bukan milik dosen ini
if (!$data_hasil) {
    echo "Data hasil kuis tidak ditemukan.";
    exit;
}

// Ambil detail jawaban peserta
$stmt_detail = $conn->prepare("
    SELECT
        d.id_detail,
        d.jawaban_peserta,
        d.is_benar,
        so.pertanyaan,
        so.opsi_a,
        so.opsi_b,
        so.opsi_c,
        so.opsi_d,
        so.jawaban_benar,
        so.penjelasan,
        so.is_surprise,
        so.bonus_poin
    FROM detail_hasil d
    JOIN soal so ON d.id_soal = so.id_soal
    WHERE d.id_hasil = ?
    ORDER BY d.id_detail ASC
");

$stmt_detail->bind_param("i", $id_hasil);
$stmt_detail->execute();

$result_detail = $stmt_detail->get_result();

$detail_soal = [];
$bonus_total = 0;

while ($row = $result_detail->fetch_assoc()) {
    $detail_soal[] = $row;

    // Hitung bonus dari Surprise Question yang dijawab benar
    if (
        (int) $row["is_benar"] === 1 &&
        (int) $row["is_surprise"] === 1
    ) {
        $bonus_total += (int) $row["bonus_poin"];
    }
}

$stmt_detail->close();

$total_soal = count($detail_soal);

function tampilkanJawaban($huruf, $item) {
    if (!$huruf) {
        return "Tidak dijawab";
    }

    $kolom = "opsi_" . strtolower($huruf);

    return $huruf . ". " . ($item[$kolom] ?? "");
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Detail Hasil Peserta - QuizKu</title>

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
            max-width: 950px;
            margin: 35px auto;
            padding: 0 20px;
        }

        .back {
            display: inline-block;
            margin-bottom: 20px;
            color: #2563EB;
            text-decoration: none;
            font-weight: bold;
        }

        .back:hover {
            text-decoration: underline;
        }

        .page-title {
            margin-bottom: 20px;
        }

        .page-title h1 {
            font-size: 27px;
            margin-bottom: 8px;
        }

        .page-title p {
            color: #777;
        }

        .info-card {
            background: white;
            padding: 25px;
            border-radius: 18px;
            box-shadow: 0 5px 20px #0000000c;
            margin-bottom: 25px;
        }

        .info-card h2 {
            color: #FF6B35;
            margin-bottom: 18px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 18px;
        }

        .info-item span {
            display: block;
            color: #777;
            font-size: 13px;
            margin-bottom: 6px;
        }

        .info-item strong {
            font-size: 16px;
        }

        .score-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }

        .score-card {
            background: white;
            padding: 20px 12px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 20px #0000000c;
        }

        .score-card span {
            display: block;
            color: #777;
            font-size: 13px;
            margin-bottom: 10px;
        }

        .score-card strong {
            font-size: 25px;
        }

        .score-card.nilai strong {
            color: #FF6B35;
        }

        .score-card.benar strong {
            color: #16A34A;
        }

        .score-card.salah strong {
            color: #DC2626;
        }

        .score-card.streak strong {
            color: #2563EB;
        }

        .question-card {
            background: white;
            padding: 25px;
            border-radius: 18px;
            margin-bottom: 18px;
            box-shadow: 0 5px 20px #0000000c;
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
            background: #FFD23F;
            padding: 7px 11px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: bold;
        }

        .question-text {
            font-size: 17px;
            line-height: 1.6;
            margin-bottom: 18px;
        }

        .answer-box {
            padding: 13px 15px;
            border-radius: 10px;
            margin-bottom: 10px;
            line-height: 1.5;
        }

        .answer-box span {
            font-weight: bold;
        }

        .answer-box.peserta {
            background: #EFF4FF;
            border: 1px solid #C7D7FE;
        }

        .answer-box.benar {
            background: #DCFCE7;
            border: 1px solid #86EFAC;
        }

        .answer-box.salah {
            background: #FEE2E2;
            border: 1px solid #FCA5A5;
        }

        .answer-box.kosong {
            background: #FFF7ED;
            border: 1px solid #FED7AA;
        }

        .status {
            display: inline-block;
            margin-top: 8px;
            padding: 6px 11px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .status.benar {
            background: #DCFCE7;
            color: #15803D;
        }

        .status.salah {
            background: #FEE2E2;
            color: #B91C1C;
        }

        .status.kosong {
            background: #FFF7ED;
            color: #C2410C;
        }

        .explanation {
            background: #FFF9E5;
            border-left: 4px solid #FFD23F;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            line-height: 1.6;
        }

        .explanation strong {
            display: block;
            margin-bottom: 6px;
        }

        .bonus-text {
            color: #D97706;
            font-weight: bold;
            margin-top: 10px;
        }

        .empty {
            background: white;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
        }

        @media (max-width: 650px) {
            .navbar {
                padding: 15px 5%;
            }

            .container {
                margin-top: 25px;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .score-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .question-card {
                padding: 20px;
            }

            .page-title h1 {
                font-size: 23px;
            }
        }
    </style>
</head>

<body>

<header class="navbar">
    <div class="logo">QuizKu</div>
</header>

<main class="container">

    <a href="hasil.php" class="back">
        ← Kembali ke Hasil Kuis
    </a>

    <div class="page-title">
        <h1>Detail Hasil Peserta 📋</h1>
        <p>Lihat jawaban dan pembahasan setiap soal peserta.</p>
    </div>

    <section class="info-card">
        <h2>Informasi Peserta</h2>

        <div class="info-grid">
            <div class="info-item">
                <span>Nama Peserta</span>
                <strong>
                    <?= htmlspecialchars($data_hasil["nama_siswa"]) ?>
                </strong>
            </div>

            <div class="info-item">
                <span>Judul Kuis</span>
                <strong>
                    <?= htmlspecialchars($data_hasil["judul_quiz"]) ?>
                </strong>
            </div>

            <div class="info-item">
                <span>Kode Kuis</span>
                <strong>
                    <?= htmlspecialchars($data_hasil["kode_quiz"]) ?>
                </strong>
            </div>

            <div class="info-item">
                <span>Waktu Selesai</span>
                <strong>
                    <?= htmlspecialchars($data_hasil["waktu_selesai"]) ?>
                </strong>
            </div>
        </div>
    </section>

    <div class="score-grid">

        <div class="score-card nilai">
            <span>Nilai Akhir</span>
            <strong>
                <?= number_format((float) $data_hasil["skor_akhir"], 2) ?>
            </strong>
        </div>

        <div class="score-card benar">
            <span>Jawaban Benar</span>
            <strong>
                <?= (int) $data_hasil["jumlah_benar"] ?>
            </strong>
        </div>

        <div class="score-card salah">
            <span>Jawaban Salah</span>
            <strong>
                <?= (int) $data_hasil["jumlah_salah"] ?>
            </strong>
        </div>

        <div class="score-card streak">
            <span>Streak Tertinggi</span>
            <strong>
                <?= (int) $data_hasil["streak_tertinggi"] ?>
            </strong>
        </div>

    </div>

    <section class="info-card">
        <h2>Informasi Tambahan ⭐</h2>

        <p>
            Total soal:
            <strong><?= $total_soal ?></strong>
        </p>

        <p style="margin-top: 10px;">
            Bonus Surprise Question:
            <strong style="color:#FF6B35;">
                +<?= $bonus_total ?> poin
            </strong>
        </p>
    </section>

    <div class="page-title">
        <h1>Review Jawaban Peserta</h1>
        <p>Berikut jawaban yang diberikan peserta untuk setiap soal.</p>
    </div>

    <?php if ($total_soal > 0): ?>

        <?php foreach ($detail_soal as $index => $item): ?>

            <?php
                $jawaban_peserta = $item["jawaban_peserta"];
                $jawaban_benar = $item["jawaban_benar"];

                $status_benar = (int) $item["is_benar"] === 1;

                $kelas_status = $status_benar
                    ? "benar"
                    : ($jawaban_peserta === null ? "kosong" : "salah");

                $teks_status = $status_benar
                    ? "Benar"
                    : ($jawaban_peserta === null ? "Tidak Dijawab" : "Salah");
            ?>

            <article class="question-card">

                <div class="question-top">
                    <div class="question-number">
                        Soal <?= $index + 1 ?>
                    </div>

                    <?php if ((int) $item["is_surprise"] === 1): ?>
                        <div class="surprise">
                            ⭐ SURPRISE QUESTION
                        </div>
                    <?php endif; ?>
                </div>

                <div class="question-text">
                    <?= nl2br(htmlspecialchars($item["pertanyaan"])) ?>
                </div>

                <div class="answer-box peserta">
                    <span>Jawaban Peserta:</span><br>

                    <?= htmlspecialchars(
                        tampilkanJawaban($jawaban_peserta, $item)
                    ) ?>
                </div>

                <div class="answer-box benar">
                    <span>Kunci Jawaban:</span><br>

                    <?= htmlspecialchars(
                        tampilkanJawaban($jawaban_benar, $item)
                    ) ?>
                </div>

                <span class="status <?= $kelas_status ?>">
                    <?= $teks_status ?>
                </span>

                <?php if (
                    (int) $item["is_surprise"] === 1 &&
                    $status_benar
                ): ?>
                    <div class="bonus-text">
                        ⭐ Peserta mendapatkan bonus
                        +<?= (int) $item["bonus_poin"] ?> poin!
                    </div>
                <?php endif; ?>

                <?php if (!empty($item["penjelasan"])): ?>
                    <div class="explanation">
                        <strong>💡 Pembahasan:</strong>

                        <?= nl2br(htmlspecialchars($item["penjelasan"])) ?>
                    </div>
                <?php endif; ?>

            </article>

        <?php endforeach; ?>

    <?php else: ?>

        <div class="empty">
            Belum ada detail jawaban yang tersimpan untuk hasil ini.
        </div>

    <?php endif; ?>

</main>

</body>
</html>