CREATE DATABASE quizku;

USE quizku;

-- 1. TABEL DOSEN
CREATE TABLE dosen (
    id_dosen INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- 2. TABEL KUIS
CREATE TABLE kuis (
    id_quiz INT AUTO_INCREMENT PRIMARY KEY,
    id_dosen INT NOT NULL,
    judul_quiz VARCHAR(100) NOT NULL,
    kode_quiz VARCHAR(20) NOT NULL UNIQUE,
    durasi INT NOT NULL,
    FOREIGN KEY (id_dosen)
        REFERENCES dosen(id_dosen)
        ON DELETE CASCADE
);

-- 3. TABEL SOAL
CREATE TABLE soal (
    id_soal INT AUTO_INCREMENT PRIMARY KEY,
    id_quiz INT NOT NULL,
    pertanyaan TEXT NOT NULL,
    opsi_a VARCHAR(255) NOT NULL,
    opsi_b VARCHAR(255) NOT NULL,
    opsi_c VARCHAR(255) NOT NULL,
    opsi_d VARCHAR(255) NOT NULL,
    jawaban_benar CHAR(1) NOT NULL,
    penjelasan TEXT,
    is_surprise TINYINT(1) DEFAULT 0,
    bonus_poin INT DEFAULT 0,
    FOREIGN KEY (id_quiz)
        REFERENCES kuis(id_quiz)
        ON DELETE CASCADE
);

-- 4. TABEL SISWA
CREATE TABLE siswa (
    id_siswa INT AUTO_INCREMENT PRIMARY KEY,
    nama_siswa VARCHAR(100) NOT NULL
);

-- 5. TABEL HASIL
CREATE TABLE hasil (
    id_hasil INT AUTO_INCREMENT PRIMARY KEY,
    id_quiz INT NOT NULL,
    id_siswa INT NOT NULL,
    skor_akhir DECIMAL(5,2) DEFAULT 0,
    jumlah_benar INT DEFAULT 0,
    jumlah_salah INT DEFAULT 0,
    streak_tertinggi INT DEFAULT 0,
    waktu_selesai DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_quiz)
        REFERENCES kuis(id_quiz)
        ON DELETE CASCADE,
    FOREIGN KEY (id_siswa)
        REFERENCES siswa(id_siswa)
        ON DELETE CASCADE
);