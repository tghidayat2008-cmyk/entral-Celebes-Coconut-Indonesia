<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . "/../config/koneksi.php";


// Cek ID produk
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: products.php");
    exit;
}

$id = (int) $_GET["id"];


// Ambil data produk
$query = mysqli_query(
    $koneksi,
    "SELECT * FROM products WHERE id = $id"
);

$product = mysqli_fetch_assoc($query);


// Jika produk tidak ditemukan
if (!$product) {
    header("Location: products.php");
    exit;
}


// Hapus gambar dari folder uploads
if (!empty($product["image"])) {

    $image_path = __DIR__ . "/../uploads/" . $product["image"];

    if (file_exists($image_path)) {
        unlink($image_path);
    }
}


// Hapus data produk dari database
$delete = mysqli_query(
    $koneksi,
    "DELETE FROM products WHERE id = $id"
);


if ($delete) {

    header("Location: products.php");
    exit;

} else {

    die(
        "Gagal menghapus produk: "
        . mysqli_error($koneksi)
    );

}

?>