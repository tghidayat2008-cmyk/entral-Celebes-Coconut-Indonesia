<?php

require_once __DIR__ . "/config/koneksi.php";

$username = "admin2";
$password_baru = "admin123";

$hash = password_hash($password_baru, PASSWORD_DEFAULT);

$stmt = mysqli_prepare(
    $koneksi,
    "UPDATE users SET password = ? WHERE username = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "ss",
    $hash,
    $username
);

if (mysqli_stmt_execute($stmt)) {
    echo "Password berhasil direset.<br>";
    echo "Username: " . htmlspecialchars($username) . "<br>";
    echo "Password baru: " . htmlspecialchars($password_baru);
} else {
    echo "Gagal mereset password: " . mysqli_error($koneksi);
}