<?php
session_start();
require_once "../config/database.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $stmt = mysqli_prepare($conn,
        "SELECT id_dosen, username, password FROM dosen WHERE username = ?"
    );

    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $dosen = mysqli_fetch_assoc($result);

    if ($dosen && password_verify($password, $dosen["password"])) {
        session_regenerate_id(true);

        $_SESSION["id_dosen"] = $dosen["id_dosen"];
        $_SESSION["username"] = $dosen["username"];

        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Username atau password salah!";
    }

    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Dosen - QuizKu</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body class="auth-page">

<div class="auth-card">
    <a href="../index.php" class="logo"><span class="ic">🏆</span> QuizKu</a>
    <h2>Selamat Datang, Dosen!</h2>
    <p class="sub">Silakan login untuk mulai membuat kuis.</p>

    <?php if ($error != ""): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" class="form-control" placeholder="Masukkan username" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Login Sekarang →</button>
    </form>

    <p class="auth-foot">Belum punya akun? <a href="register.php">Daftar di sini</a></p>
    <a href="../login.php" class="back-link">← Kembali</a>
</div>

</body>
</html>