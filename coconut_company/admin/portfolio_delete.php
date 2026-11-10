<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . "/../config/koneksi.php";

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: portfolio.php");
    exit;
}

$id = intval($_GET["id"]);

/* =========================
   AMBIL DATA GAMBAR
========================= */

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT image FROM portfolio WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$portfolio = mysqli_fetch_assoc($result);

if (!$portfolio) {
    header("Location: portfolio.php");
    exit;
}

/* =========================
   HAPUS DATA
========================= */

$stmt_delete = mysqli_prepare(
    $koneksi,
    "DELETE FROM portfolio WHERE id = ?"
);

mysqli_stmt_bind_param($stmt_delete, "i", $id);

if (mysqli_stmt_execute($stmt_delete)) {

    /* =========================
       HAPUS FILE GAMBAR
    ========================= */

    if (!empty($portfolio["image"])) {

        $image_path = __DIR__ . "/../uploads/" . $portfolio["image"];

        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }

    header("Location: portfolio.php?success=deleted");
    exit;

} else {

    header("Location: portfolio.php?error=delete");
    exit;
}
?>