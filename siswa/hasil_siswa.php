<?php
session_start();
require_once "../config/database.php";

// Pastikan hasil kuis tersedia
if (!isset($_SESSION["id_hasil"])) {
    header("Location: index.php");
    exit;
}

$id_hasil = (int) $_SESSION["id_hasil"];
$bonus_total = (int) ($_SESSION["bonus_total"] ?? 0);
$jawaban_peserta = $_SESSION["jawaban_peserta"] ?? [];

// Ambil data hasil
$stmt = $conn->prepare("
    SELECT h.*, s.nama_siswa, k.judul_quiz
    FROM hasil h
    JOIN siswa s ON h.id_siswa = s.id_siswa
    JOIN kuis k ON h.id_quiz = k.id_quiz
    WHERE h.id_hasil = ?
");

$stmt->bind_param("i", $id_hasil);
$stmt->execute();

$result = $stmt->get_result();
$hasil = $result->fetch_assoc();

$stmt->close();

if (!$hasil) {
    echo "Data hasil kuis tidak ditemukan.";
    exit;
}

// Ambil soal dan pembahasan
$id_quiz = (int) $hasil["id_quiz"];

$stmt = $conn->prepare("
    SELECT id_soal, pertanyaan, opsi_a, opsi_b, opsi_c, opsi_d,
           jawaban_benar, penjelasan, is_surprise
    FROM soal
    WHERE id_quiz = ?
    ORDER BY id_soal ASC
");

$stmt->bind_param("i", $id_quiz);
$stmt->execute();

$result_soal = $stmt->get_result();
$daftar_soal = [];

while ($row = $result_soal->fetch_assoc()) {
    $daftar_soal[] = $row;
}

$stmt->close();

$skor = (float) $hasil["skor_akhir"];
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
            padding-bottom: 40px;
        }

        .navbar {
            background: white;
            padding: 18px 7%;
            box-shadow: 0 2px 10px #00000010;
        }

        .logo {
            color: #FF6B35;
            font-size: 25px;
            font-weight: bold;
        }

        .container {
            max-width: 800px;
            margin: 35px auto;
            padding: 0 20px;
        }

        .result-card {
            background: white;
            padding: 35px 25px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 5px 20px #0000000c;
            margin-bottom: 25px;
        }

        .trophy {
            font-size: 55px;
            margin-bottom: 12px;
        }

        .result-card h1 {
            color: #FF6B35;
            margin-bottom: 10px;
        }

        .score {
            color: #2563EB;
            font-size: 55px;
            font-weight: bold;
            margin: 20px 0;
        }

        .score-label {
            color: #777;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-top: 25px;
        }

        .stat {
            padding: 18px 10px;
            border-radius: 12px;
            background: #FFF9F1;
        }

        .stat strong {
            display: block;
            font-size: 24px;
            margin-bottom: 7px;
        }

        .correct {
            color: #16A34A;
        }

        .wrong {
            color: #DC2626;
        }

        .streak {
            color: #2563EB;
        }

        .bonus {
            display: inline-block;
            background: #FFD23F;
            padding: 10px 16px;
            border-radius: 10px;
            margin-top: 20px;
            font-weight: bold;
        }

        .review-title {
            margin: 30px 0 15px;
        }

        .review-card {
            background: white;
            padding: 22px;
            border-radius: 15px;
            margin-bottom: 15px;
            box-shadow: 0 3px 12px #00000008;
        }

        .review-card h3 {
            margin-bottom: 15px;
            line-height: 1.5;
        }

        .answer-info {
            margin: 9px 0;
            line-height: 1.5;
        }

        .answer-correct {
            color: #16A34A;
            font-weight: bold;
        }

        .answer-wrong {
            color: #DC2626;
            font-weight: bold;
        }

        .explanation {
            background: #F1F5FF;
            padding: 14px;
            border-radius: 10px;
            margin-top: 15px;
            line-height: 1.6;
        }

        .home-button {
            display: block;
            width: fit-content;
            margin: 30px auto;
            padding: 14px 25px;
            background: #FF6B35;
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: bold;
        }

        @media (max-width: 500px) {
            .stats {
                grid-template-columns: 1fr;
            }

            .result-card {
                padding: 25px 18px;
            }
        }
    </style>
</head>

<body>

<header class="navbar">
    <div class="logo">QuizKu</div>
</header>

<main class="container">

    <section class="result-card">

        <div class="trophy">🏆</div>

        <h1>Kuis Selesai!</h1>

        <p>
            Hebat, <?= htmlspecialchars($hasil["nama_siswa"]) ?>!
            Kamu sudah menyelesaikan kuis.
        </p>

        <p style="margin-top: 10px;">
            <?= htmlspecialchars($hasil["judul_quiz"]) ?>
        </p>

        <div class="score">
            <?= number_format($skor, 0) ?>
        </div>

        <div class="score-label">Nilai Akhir Kamu</div>

        <div class="stats">

            <div class="stat">
                <strong class="correct">
                    <?= (int) $hasil["jumlah_benar"] ?>
                </strong>
                Jawaban Benar
            </div>

            <div class="stat">
                <strong class="wrong">
                    <?= (int) $hasil["jumlah_salah"] ?>
                </strong>
                Jawaban Salah
            </div>

            <div class="stat">
                <strong class="streak">
                    <?= (int) $hasil["streak_tertinggi"] ?> 🔥
                </strong>
                Streak Tertinggi
            </div>

        </div>

        <div class="bonus">
            ⭐ Bonus Surprise: +<?= $bonus_total ?> poin
        </div>

    </section>

    <h2 class="review-title">📚 Pembahasan Jawaban</h2>

    <?php foreach ($daftar_soal as $index => $item): ?>

        <?php
        $id_soal = (string) $item["id_soal"];

        // Ambil jawaban peserta berdasarkan ID soal
        $pilihan = strtoupper(
            (string) ($jawaban_peserta[$id_soal] ?? "")
        );

        $huruf_benar = strtoupper($item["jawaban_benar"]);

        $opsi = [
            "A" => $item["opsi_a"],
            "B" => $item["opsi_b"],
            "C" => $item["opsi_c"],
            "D" => $item["opsi_d"]
        ];

        $benar = ($pilihan !== "" && $pilihan === $huruf_benar);
        ?>

        <section class="review-card">

            <h3>
                Soal <?= $index + 1 ?>.
                <?= htmlspecialchars($item["pertanyaan"]) ?>
            </h3>

            <?php if ((int) $item["is_surprise"] === 1): ?>
                <div class="bonus">⭐ Surprise Question</div>
            <?php endif; ?>

            <p class="answer-info">
                Jawaban kamu:

                <?php if (isset($opsi[$pilihan])): ?>

                    <strong class="<?= $benar ? 'answer-correct' : 'answer-wrong' ?>">
                        <?= $pilihan ?>.
                        <?= htmlspecialchars($opsi[$pilihan]) ?>
                        <?= $benar ? '✓' : '✗' ?>
                    </strong>

                <?php else: ?>

                    <strong class="answer-wrong">
                        Tidak dijawab
                    </strong>

                <?php endif; ?>
            </p>

            <p class="answer-info">
                Jawaban benar:

                <strong class="answer-correct">
                    <?= $huruf_benar ?>.
                    <?= htmlspecialchars($opsi[$huruf_benar] ?? "") ?>
                </strong>
            </p>

            <div class="explanation">
                <strong>💡 Pembahasan:</strong><br>

                <?= nl2br(htmlspecialchars(
                    $item["penjelasan"] ?: "Belum ada pembahasan untuk soal ini."
                )) ?>
            </div>

        </section>

    <?php endforeach; ?>

    <a href="index.php" class="home-button">
        Kembali ke Beranda
    </a>

</main>

</body>
</html>