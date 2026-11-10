<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . "/../config/koneksi.php";

$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

if ($id <= 0) {
    header("Location: gallery.php");
    exit;
}

/* Ambil nama file gambar */
$query = mysqli_prepare(
    $koneksi,
    "SELECT image FROM gallery WHERE id = ?"
);

mysqli_stmt_bind_param($query, "i", $id);
mysqli_stmt_execute($query);

$result = mysqli_stmt_get_result($query);
$gallery = mysqli_fetch_assoc($result);

if (!$gallery) {
    header("Location: gallery.php");
    exit;
}

/* Hapus file gambar dari folder uploads */
if (!empty($gallery["image"])) {

    $image_path = __DIR__ . "/../uploads/" . $gallery["image"];

    if (file_exists($image_path)) {
        unlink($image_path);
    }
}

/* Hapus data dari database */
$stmt = mysqli_prepare(
    $koneksi,
    "DELETE FROM gallery WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

/* Kembali ke halaman Gallery */
header("Location: gallery.php");
exit;

?>