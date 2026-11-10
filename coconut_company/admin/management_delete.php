<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . "/../config/koneksi.php";

$id = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);

if (!$id) {
    header("Location: management.php");
    exit;
}


/* =========================
   GET IMAGE
========================= */

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT image FROM management_structure WHERE id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$data = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$data) {
    header("Location: management.php");
    exit;
}


/* =========================
   DELETE DATABASE
========================= */

$delete = mysqli_prepare(
    $koneksi,
    "DELETE FROM management_structure WHERE id = ?"
);

mysqli_stmt_bind_param(
    $delete,
    "i",
    $id
);

if (mysqli_stmt_execute($delete)) {

    mysqli_stmt_close($delete);

    /* Delete image */

    if (!empty($data["image"])) {

        $image_file =
            __DIR__ .
            "/../uploads/" .
            $data["image"];

        if (file_exists($image_file)) {
            unlink($image_file);
        }

    }

    header(
        "Location: management.php?success=deleted"
    );

    exit;

}


mysqli_stmt_close($delete);

header("Location: management.php");

exit;