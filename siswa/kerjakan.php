<?php
session_start();
require_once "../config/database.php";

// Pastikan peserta sudah memasukkan nama dan kode kuis
if (
    !isset($_SESSION["nama_siswa"]) ||
    !isset($_SESSION["id_quiz"]) ||
    !isset($_SESSION["durasi"])
) {
    header("Location: index.php");
    exit;
}

$id_quiz = (int) $_SESSION["id_quiz"];
$nama_siswa = $_SESSION["nama_siswa"];
$durasi = (int) $_SESSION["durasi"];

// Ambil semua soal dari kuis yang dipilih
$stmt = $conn->prepare("
    SELECT id_soal, pertanyaan, opsi_a, opsi_b, opsi_c, opsi_d,
           jawaban_benar, penjelasan, is_surprise, bonus_poin
    FROM soal
    WHERE id_quiz = ?
    ORDER BY id_soal ASC
");
$stmt->bind_param("i", $id_quiz);
$stmt->execute();

$result = $stmt->get_result();
$soal = [];

while ($row = $result->fetch_assoc()) {
    $soal[] = $row;
}
$stmt->close();

$total_soal = count($soal);

if ($total_soal === 0) {
    echo "Maaf, kuis ini belum memiliki soal.";
    exit;
}

// Catat waktu mulai agar timer tidak kembali ke awal saat halaman di-refresh
if (!isset($_SESSION["waktu_mulai"])) {
    $_SESSION["waktu_mulai"] = time();
}

// Proses jawaban ketika peserta mengumpulkan kuis
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $jawaban_peserta = $_POST["jawaban"] ?? [];

    $jumlah_benar = 0;
    $jumlah_salah = 0;
    $bonus_total = 0;

    $streak = 0;
    $streak_tertinggi = 0;

    // Penampung jawaban yang sudah divalidasi
    $jawaban_tersimpan = [];

    foreach ($soal as $item) {
        $id_soal = (int) $item["id_soal"];

        // Ambil dan validasi jawaban peserta
        $jawaban_masuk = strtoupper(
            trim($jawaban_peserta[$id_soal] ?? "")
        );

        $jawaban = in_array(
            $jawaban_masuk,
            ["A", "B", "C", "D"],
            true
        ) ? $jawaban_masuk : "";

        $jawaban_tersimpan[$id_soal] = $jawaban;

        $benar = strtoupper($item["jawaban_benar"]);

        if ($jawaban === $benar) {
            $jumlah_benar++;
            $streak++;

            if ($streak > $streak_tertinggi) {
                $streak_tertinggi = $streak;
            }

            // Bonus tambahan untuk Surprise Question
            if ((int) $item["is_surprise"] === 1) {
                $bonus_total += (int) $item["bonus_poin"];
            }

        } else {
            $jumlah_salah++;
            $streak = 0;
        }
    }

    // Nilai dasar berdasarkan persentase jawaban benar
    // Bonus Surprise Question ditambahkan, maksimal nilai 100
    $nilai_dasar = ($jumlah_benar / $total_soal) * 100;
    $skor_akhir = min(100, $nilai_dasar + $bonus_total);
    $skor_akhir = round($skor_akhir, 2);

    // Simpan nama peserta, hasil kuis, dan detail jawaban
    $conn->begin_transaction();

    try {
        // Simpan data peserta
        $stmt_siswa = $conn->prepare("
            INSERT INTO siswa (nama_siswa)
            VALUES (?)
        ");

        $stmt_siswa->bind_param("s", $nama_siswa);
        $stmt_siswa->execute();

        $id_siswa = $conn->insert_id;
        $stmt_siswa->close();

        // Simpan hasil kuis
        $stmt_hasil = $conn->prepare("
            INSERT INTO hasil
            (id_quiz, id_siswa, skor_akhir, jumlah_benar,
             jumlah_salah, streak_tertinggi)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt_hasil->bind_param(
            "iidiii",
            $id_quiz,
            $id_siswa,
            $skor_akhir,
            $jumlah_benar,
            $jumlah_salah,
            $streak_tertinggi
        );

        $stmt_hasil->execute();

        $id_hasil = $conn->insert_id;
        $stmt_hasil->close();

        // Simpan jawaban setiap soal ke tabel detail_hasil
        $stmt_detail = $conn->prepare("
            INSERT INTO detail_hasil
            (id_hasil, id_soal, jawaban_peserta, is_benar)
            VALUES (?, ?, ?, ?)
        ");

        foreach ($soal as $item) {
            $id_soal = (int) $item["id_soal"];

            $jawaban = $jawaban_tersimpan[$id_soal];

            // Jawaban kosong disimpan sebagai NULL
            $jawaban_db = $jawaban !== "" ? $jawaban : null;

            $is_benar = (
                $jawaban !== "" &&
                $jawaban === strtoupper($item["jawaban_benar"])
            ) ? 1 : 0;

            $stmt_detail->bind_param(
                "iisi",
                $id_hasil,
                $id_soal,
                $jawaban_db,
                $is_benar
            );

            $stmt_detail->execute();
        }

        $stmt_detail->close();

        // Semua data berhasil disimpan
        $conn->commit();

        // Simpan data untuk halaman hasil peserta
        $_SESSION["id_hasil"] = $id_hasil;
        $_SESSION["bonus_total"] = $bonus_total;
        $_SESSION["jawaban_peserta"] = $jawaban_tersimpan;

        unset($_SESSION["waktu_mulai"]);
        unset($_SESSION["nama_siswa"]);
        unset($_SESSION["id_quiz"]);
        unset($_SESSION["judul_quiz"]);
        unset($_SESSION["durasi"]);

        header("Location: hasil_siswa.php");
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        $pesan_error = "Terjadi kesalahan saat menyimpan hasil kuis.";
    }
}

// Hitung sisa waktu berdasarkan waktu mulai
$waktu_total = $durasi * 60;
$waktu_berjalan = time() - $_SESSION["waktu_mulai"];
$sisa_waktu = max(0, $waktu_total - $waktu_berjalan);

$judul_quiz = $_SESSION["judul_quiz"] ?? "Kuis QuizKu";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kerjakan Kuis - QuizKu</title>

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

        .timer {
            background: #FFD23F;
            padding: 12px 20px;
            border-radius: 12px;
            font-weight: bold;
        }

        .container {
            max-width: 800px;
            margin: 35px auto;
            padding: 0 20px;
        }

        .quiz-info {
            margin-bottom: 20px;
        }

        .quiz-info h2 {
            margin-bottom: 8px;
        }

        .progress-box {
            background: #eee;
            height: 9px;
            border-radius: 20px;
            margin-top: 15px;
            overflow: hidden;
        }

        .progress-bar {
            background: #2563EB;
            height: 100%;
            width: 0%;
            transition: width 0.3s;
        }

        .question-card {
            background: white;
            padding: 30px;
            border-radius: 18px;
            box-shadow: 0 5px 20px #0000000c;
        }

        .question-number {
            color: #2563EB;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .surprise {
            display: inline-block;
            background: #FFD23F;
            padding: 7px 12px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .question-text {
            font-size: 19px;
            line-height: 1.6;
            margin-bottom: 25px;
        }

        .option {
            display: flex;
            align-items: center;
            gap: 12px;
            border: 2px solid #eee;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 12px;
            cursor: pointer;
            transition: 0.2s;
        }

        .option:hover {
            border-color: #2563EB;
            background: #F1F5FF;
        }

        .option:has(input:checked) {
            border-color: #2563EB;
            background: #EFF4FF;
        }

        .option input {
            accent-color: #2563EB;
            width: 17px;
            height: 17px;
        }

        .navigation {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-top: 25px;
        }

        button {
            border: none;
            padding: 13px 22px;
            border-radius: 10px;
            font-weight: bold;
            cursor: pointer;
        }

        .btn-prev {
            background: #eee;
        }

        .btn-next {
            background: #2563EB;
            color: white;
        }

        .btn-submit {
            background: #FF6B35;
            color: white;
            display: none;
        }

        button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .error {
            background: #FFE4E0;
            color: #B42318;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 15px;
        }

        @media (max-width: 550px) {
            .navbar {
                padding: 15px 5%;
            }

            .container {
                margin-top: 25px;
            }

            .question-card {
                padding: 20px;
            }

            .navigation button {
                padding: 12px;
                font-size: 13px;
            }
        }
    </style>
</head>

<body>

<header class="navbar">
    <div class="logo">QuizKu</div>
    <div class="timer">
        ⏱️ <span id="timer">00:00</span>
    </div>
</header>

<main class="container">

    <div class="quiz-info">
        <h2><?= htmlspecialchars($judul_quiz) ?></h2>
        <p>Semangat mengerjakan, <?= htmlspecialchars($nama_siswa) ?>! 🎉</p>

        <div class="progress-box">
            <div class="progress-bar" id="progressBar"></div>
        </div>
    </div>

    <?php if (isset($pesan_error)): ?>
        <div class="error">
            <?= htmlspecialchars($pesan_error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" id="quizForm">

        <?php foreach ($soal as $index => $item): ?>

            <div class="question-card" style="<?= $index === 0 ? '' : 'display:none;' ?>">

                <div class="question-number">
                    Soal <?= $index + 1 ?> dari <?= $total_soal ?>
                </div>

                <?php if ((int) $item["is_surprise"] === 1): ?>
                    <div class="surprise">
                        ⭐ SURPRISE QUESTION
                        (+<?= (int) $item["bonus_poin"] ?> BONUS)
                    </div>
                <?php endif; ?>

                <div class="question-text">
                    <?= nl2br(htmlspecialchars($item["pertanyaan"])) ?>
                </div>

                <?php
                $opsi = [
                    "A" => $item["opsi_a"],
                    "B" => $item["opsi_b"],
                    "C" => $item["opsi_c"],
                    "D" => $item["opsi_d"]
                ];
                ?>

                <?php foreach ($opsi as $huruf => $isi): ?>
                    <label class="option">
                        <input
                            type="radio"
                            name="jawaban[<?= (int) $item["id_soal"] ?>]"
                            value="<?= $huruf ?>"
                        >
                        <span>
                            <strong><?= $huruf ?>.</strong>
                            <?= htmlspecialchars($isi) ?>
                        </span>
                    </label>
                <?php endforeach; ?>

            </div>

        <?php endforeach; ?>

        <div class="navigation">
            <button
                type="button"
                class="btn-prev"
                id="prevBtn"
                onclick="sebelumnya()"
            >
                ← Sebelumnya
            </button>

            <button
                type="button"
                class="btn-next"
                id="nextBtn"
                onclick="selanjutnya()"
            >
                Selanjutnya →
            </button>

            <button
                type="button"
                class="btn-submit"
                id="submitBtn"
                onclick="kumpulkanKuis()"
            >
                Kumpulkan Kuis ✓
            </button>
        </div>

    </form>

</main>

<script>
    const cards = document.querySelectorAll(".question-card");
    const totalSoal = cards.length;

    let nomorSekarang = 0;
    let sisaWaktu = <?= (int) $sisa_waktu ?>;
    let sudahDikirim = false;

    const timerElement = document.getElementById("timer");
    const progressBar = document.getElementById("progressBar");

    function tampilkanSoal() {
        cards.forEach((card, index) => {
            card.style.display =
                index === nomorSekarang ? "block" : "none";
        });

        document.getElementById("prevBtn").disabled =
            nomorSekarang === 0;

        document.getElementById("nextBtn").style.display =
            nomorSekarang === totalSoal - 1 ? "none" : "block";

        document.getElementById("submitBtn").style.display =
            nomorSekarang === totalSoal - 1 ? "block" : "none";

        progressBar.style.width =
            ((nomorSekarang + 1) / totalSoal * 100) + "%";
    }

    function selanjutnya() {
        if (nomorSekarang < totalSoal - 1) {
            nomorSekarang++;
            tampilkanSoal();
        }
    }

    function sebelumnya() {
        if (nomorSekarang > 0) {
            nomorSekarang--;
            tampilkanSoal();
        }
    }

    function kumpulkanKuis() {
        const semuaJawaban = document.querySelectorAll(
            'input[type="radio"]:checked'
        );

        if (semuaJawaban.length < totalSoal) {
            alert("Masih ada soal yang belum dijawab. Periksa kembali ya!");
            return;
        }

        if (confirm("Apakah kamu yakin ingin mengumpulkan jawaban?")) {
            kirimKuis();
        }
    }

    function kirimKuis() {
        if (sudahDikirim) return;

        sudahDikirim = true;
        document.getElementById("submitBtn").disabled = true;

        document.getElementById("quizForm").submit();
    }

    function jalankanTimer() {
        let menit = Math.floor(sisaWaktu / 60);
        let detik = sisaWaktu % 60;

        timerElement.textContent =
            String(menit).padStart(2, "0") + ":" +
            String(detik).padStart(2, "0");

        if (sisaWaktu <= 0) {
            timerElement.textContent = "00:00";
            alert("Waktu habis! Jawaban yang sudah dipilih akan dikumpulkan.");
            kirimKuis();
            return;
        }

        sisaWaktu--;
    }

    tampilkanSoal();
    jalankanTimer();

    setInterval(jalankanTimer, 1000);
</script>

</body>
</html>