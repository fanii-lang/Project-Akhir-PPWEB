<?php
session_start();
require_once "../config/database.php";

// Pastikan dosen sudah login
if (!isset($_SESSION["id_dosen"])) {
    header("Location: login.php");
    exit;
}

// Hanya menerima metode POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: daftar_kuis.php");
    exit;
}

// Periksa token keamanan
if (
    !isset($_POST["csrf_token"], $_SESSION["csrf_token"]) ||
    !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])
) {
    header("Location: daftar_kuis.php?status=hapus_gagal");
    exit;
}

$id_dosen = (int) $_SESSION["id_dosen"];
$id_quiz = isset($_POST["id_quiz"]) ? (int) $_POST["id_quiz"] : 0;

if ($id_quiz <= 0) {
    header("Location: daftar_kuis.php?status=hapus_gagal");
    exit;
}

// Pastikan kuis memang milik dosen yang sedang login
$stmt = mysqli_prepare($conn,
    "SELECT id_quiz
     FROM kuis
     WHERE id_quiz = ? AND id_dosen = ?"
);

mysqli_stmt_bind_param($stmt, "ii", $id_quiz, $id_dosen);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$kuis = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$kuis) {
    header("Location: daftar_kuis.php?status=hapus_gagal");
    exit;
}

try {
    $conn->begin_transaction();

    // 1. Hapus detail jawaban dari seluruh hasil kuis
    $stmt = mysqli_prepare($conn,
        "DELETE dh
         FROM detail_hasil dh
         INNER JOIN hasil h ON dh.id_hasil = h.id_hasil
         WHERE h.id_quiz = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $id_quiz);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // 2. Hapus riwayat hasil kuis
    $stmt = mysqli_prepare($conn,
        "DELETE FROM hasil WHERE id_quiz = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $id_quiz);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // 3. Hapus semua soal dalam kuis
    $stmt = mysqli_prepare($conn,
        "DELETE FROM soal WHERE id_quiz = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $id_quiz);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // 4. Hapus kuis milik dosen tersebut
    $stmt = mysqli_prepare($conn,
        "DELETE FROM kuis
         WHERE id_quiz = ? AND id_dosen = ?"
    );

    mysqli_stmt_bind_param($stmt, "ii", $id_quiz, $id_dosen);
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_affected_rows($stmt) !== 1) {
        throw new Exception("Kuis gagal dihapus.");
    }

    mysqli_stmt_close($stmt);

    // Simpan semua perubahan
    $conn->commit();

    header("Location: daftar_kuis.php?status=hapus_berhasil");
    exit;

} catch (Throwable $e) {

    // Batalkan semua perubahan jika terjadi kesalahan
    $conn->rollback();

    header("Location: daftar_kuis.php?status=hapus_gagal");
    exit;
}
?>