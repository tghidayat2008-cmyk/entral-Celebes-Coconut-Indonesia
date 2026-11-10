<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . "/../config/koneksi.php";

if (!isset($_GET["id"])) {
    header("Location: inquiries.php");
    exit;
}

$id = intval($_GET["id"]);

$stmt = mysqli_prepare(
    $koneksi,
    "DELETE FROM inquiries WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

header("Location: inquiries.php?deleted=1");
exit;
?>