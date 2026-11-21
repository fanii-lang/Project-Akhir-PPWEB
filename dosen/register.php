<?php
session_start();
require_once "../config/database.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    $konfirmasi = $_POST["konfirmasi"];

    if (strlen($username) < 4) {
        $error = "Username minimal 4 karakter!";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter!";
    } elseif ($password !== $konfirmasi) {
        $error = "Konfirmasi password tidak cocok!";
    } else {
        // Periksa apakah username sudah digunakan
        $cek = mysqli_prepare($conn,
            "SELECT id_dosen FROM dosen WHERE username = ?"
        );

        mysqli_stmt_bind_param($cek, "s", $username);
        mysqli_stmt_execute($cek);

        $hasil = mysqli_stmt_get_result($cek);

        if (mysqli_num_rows($hasil) > 0) {
            $error = "Username sudah digunakan!";
        } else {
            // Amankan password sebelum disimpan
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = mysqli_prepare($conn,
                "INSERT INTO dosen (username, password) VALUES (?, ?)"
            );

            mysqli_stmt_bind_param($stmt, "ss", $username, $password_hash);

            if (mysqli_stmt_execute($stmt)) {
                $success = "Pendaftaran berhasil! Silakan login.";
            } else {
                $error = "Pendaftaran gagal. Silakan coba lagi.";
            }

            mysqli_stmt_close($stmt);
        }

        mysqli_stmt_close($cek);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Dosen - QuizKu</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #FFFDF8;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .register-container {
            width: 100%;
            max-width: 430px;
            background: white;
            padding: 38px;
            border-radius: 25px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
        }

        .logo {
            text-align: center;
            font-size: 28px;
            font-weight: 800;
            color: #FF6B35;
        }

        .logo span {
            color: #2563EB;
        }

        .icon {
            text-align: center;
            font-size: 50px;
            margin: 20px 0 12px;
        }

        h2 {
            text-align: center;
            margin-bottom: 10px;
            color: #263238;
        }

        .description {
            text-align: center;
            color: #777;
            font-size: 14px;
            margin-bottom: 25px;
        }

        label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        input {
            width: 100%;
            padding: 13px;
            border: 1px solid #ddd;
            border-radius: 11px;
            font-size: 14px;
            margin-bottom: 17px;
            outline: none;
        }

        input:focus {
            border-color: #2563EB;
        }

        button {
            width: 100%;
            padding: 14px;
            background: #FF6B35;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #E95725;
        }

        .message {
            padding: 12px;
            border-radius: 9px;
            font-size: 13px;
            text-align: center;
            margin-bottom: 18px;
        }

        .error {
            background: #FFE5E0;
            color: #C0392B;
        }

        .success {
            background: #E1F7E9;
            color: #237A42;
        }

        .success a {
            display: block;
            margin-top: 8px;
            color: #237A42;
            font-weight: bold;
        }

        .login {
            text-align: center;
            font-size: 14px;
            margin-top: 22px;
            color: #777;
        }

        .login a {
            color: #2563EB;
            font-weight: bold;
            text-decoration: none;
        }

        .back {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #2563EB;
            text-decoration: none;
            font-size: 14px;
        }
    </style>
</head>

<body>

<div class="register-container">

    <div class="logo">Quiz<span>Ku</span> ✨</div>

    <div class="icon">📝</div>

    <h2>Buat Akun Dosen</h2>

    <p class="description">
        Daftar sekarang dan mulai buat kuis serumu!
    </p>

    <?php if ($error != ""): ?>
        <div class="message error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($success != ""): ?>
        <div class="message success">
            <?= htmlspecialchars($success) ?>
            <a href="login.php">Login sekarang →</a>
        </div>
    <?php endif; ?>

    <form method="POST">

        <label>Username</label>
        <input
            type="text"
            name="username"
            placeholder="Buat username"
            minlength="4"
            maxlength="50"
            required
        >

        <label>Password</label>
        <input
            type="password"
            name="password"
            placeholder="Buat password"
            minlength="6"
            required
        >

        <label>Konfirmasi Password</label>
        <input
            type="password"
            name="konfirmasi"
            placeholder="Ulangi password"
            minlength="6"
            required
        >

        <button type="submit">Daftar Akun →</button>

    </form>

    <div class="login">
        Sudah punya akun?
        <a href="login.php">Login di sini</a>
    </div>

    <a href="../index.php" class="back">
        ← Kembali ke halaman utama
    </a>

</div>

</body>
</html>