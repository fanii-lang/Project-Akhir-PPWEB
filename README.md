# QuizKu - Platform Kuis Interaktif

QuizKu adalah platform kuis online yang menggabungkan unsur permainan (*game*) dan pembelajaran (*learning*). Website ini dirancang untuk membuat kegiatan mengerjakan kuis menjadi lebih menarik, interaktif, dan menyenangkan.

QuizKu memiliki dua jenis pengguna, yaitu **Dosen** sebagai pembuat kuis dan **Siswa** sebagai peserta yang mengerjakan kuis.

---

## Tujuan Proyek
1. Memudahkan dosen dalam membuat dan mengelola kuis secara online.
2. Memudahkan siswa dalam mengikuti kuis menggunakan kode yang diberikan dosen.
3. Menghadirkan pengalaman belajar yang lebih menarik melalui fitur permainan.
4. Membantu siswa mengetahui hasil dan memahami pembahasan dari soal yang dikerjakan.

---

## Fitur Utama

### Fitur Dosen
* Registrasi akun dosen.
* Login menggunakan username dan password.
* Dashboard dosen.
* Membuat dan mengelola kuis.
* Menambahkan pertanyaan dan pilihan jawaban.
* Mengatur durasi pengerjaan kuis.
* Mendapatkan kode kuis secara otomatis.
* Melihat hasil pengerjaan siswa.

### STUDENT Fitur Siswa
* Mengikuti kuis tanpa membuat akun.
* Masuk menggunakan nama dan kode kuis.
* Mengerjakan soal pilihan ganda.
* Melihat waktu pengerjaan.
* Melihat skor akhir dan pembahasan soal.

### Fitur Game dan Pembelajaran
* **Surprise Question**: Pertanyaan kejutan yang dapat memberikan poin bonus.
* **Answer Streak**: Menghitung rangkaian jawaban benar secara berturut-turut.
* **Pembahasan Soal**: Memberikan penjelasan agar siswa dapat belajar dari jawaban yang dikerjakan.

---

## Teknologi yang Digunakan
* HTML / CSS / JavaScript
* PHP Native
* MySQL & MariaDB
* Visual Studio Code
* XAMPP
* GitHub

---

## Rancangan Database
Database QuizKu dirancang menggunakan empat tabel utama:

| Tabel | Fungsi |
| :--- | :--- |
| `admins` | Menyimpan akun dosen |
| `quizzes` | Menyimpan data kuis |
| `questions` | Menyimpan pertanyaan dan pilihan jawaban |
| `results` | Menyimpan hasil pengerjaan siswa |

---

## Alur Penggunaan

### Dosen:
1. Membuat akun.
2. Login ke QuizKu.
3. Membuat kuis dan menambahkan soal.
4. Mendapatkan kode kuis.
5. Membagikan kode kepada siswa.
6. Melihat hasil pengerjaan siswa.

### Siswa:
1. Membuka QuizKu.
2. Memasukkan nama dan kode kuis.
3. Mengerjakan soal.
4. Mendapatkan poin dan mengikuti tantangan kuis.
5. Melihat skor akhir dan pembahasan.

---

## Status Pengembangan
**On Development**  
Proyek QuizKu masih dalam tahap pengembangan secara bertahap. Fitur dan tampilan website akan terus diperbarui sesuai proses pengerjaan.

---

## Pengembang
Proyek ini dikembangkan sebagai bagian dari tugas perkuliahan Pemrograman Web.

* **Program Studi:** Pendidikan Teknik Informatika  
* **Mata Kuliah:** Pemrograman Web (2026)  
* **Tim Pengembang:**
  1. Gusti Fani (`25076066`)
  2. Richa Lettisia Oktaveri (`25076066`)