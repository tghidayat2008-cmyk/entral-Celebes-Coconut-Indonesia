<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . "/../config/koneksi.php";

$username = $_SESSION["admin_username"];

$error = "";

$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

if ($id <= 0) {
    header("Location: gallery.php");
    exit;
}


/* =========================
   AMBIL DATA GALLERY
========================= */

$query = mysqli_prepare(
    $koneksi,
    "SELECT * FROM gallery WHERE id = ?"
);

mysqli_stmt_bind_param($query, "i", $id);
mysqli_stmt_execute($query);

$result = mysqli_stmt_get_result($query);
$gallery = mysqli_fetch_assoc($result);

if (!$gallery) {
    header("Location: gallery.php");
    exit;
}


/* =========================
   PROSES UPDATE
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $status = $_POST["status"] ?? "active";

    if ($title === "") {

        $error = "Judul foto wajib diisi.";

    } elseif (!in_array($status, ["active", "inactive"], true)) {

        $error = "Status gallery tidak valid.";

    } else {

        $image_name = $gallery["image"];


        /* =========================
           UPLOAD GAMBAR BARU
        ========================= */

        if (
            isset($_FILES["image"]) &&
            $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE
        ) {

            if ($_FILES["image"]["error"] !== UPLOAD_ERR_OK) {

                $error =
                    "Upload gambar gagal. Kode error: " .
                    $_FILES["image"]["error"];

            } else {

                $file_name = $_FILES["image"]["name"];
                $file_tmp = $_FILES["image"]["tmp_name"];
                $file_size = $_FILES["image"]["size"];

                $extension = strtolower(
                    pathinfo($file_name, PATHINFO_EXTENSION)
                );

                $allowed = [
                    "jpg",
                    "jpeg",
                    "png",
                    "webp"
                ];


                if (!in_array($extension, $allowed, true)) {

                    $error =
                        "Format gambar harus JPG, JPEG, PNG, atau WEBP.";

                } elseif ($file_size > 2 * 1024 * 1024) {

                    $error =
                        "Ukuran gambar maksimal 2 MB.";

                } else {

                    $upload_dir = __DIR__ . "/../uploads/";

                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }

                    $new_image_name =
                        time() .
                        "_" .
                        uniqid() .
                        "." .
                        $extension;

                    $upload_path =
                        $upload_dir . $new_image_name;


                    if (
                        move_uploaded_file(
                            $file_tmp,
                            $upload_path
                        )
                    ) {

                        /* Hapus gambar lama */

                        if (
                            !empty($gallery["image"]) &&
                            file_exists(
                                $upload_dir . $gallery["image"]
                            )
                        ) {

                            unlink(
                                $upload_dir . $gallery["image"]
                            );
                        }

                        $image_name = $new_image_name;

                    } else {

                        $error =
                            "Gagal mengupload gambar. " .
                            "Pastikan folder uploads tersedia.";
                    }
                }
            }
        }


        /* =========================
           UPDATE DATABASE
        ========================= */

        if ($error === "") {

            $query_update = "
                UPDATE gallery
                SET
                    title = ?,
                    category = ?,
                    image = ?,
                    status = ?
                WHERE id = ?
            ";

            $stmt = mysqli_prepare(
                $koneksi,
                $query_update
            );

            mysqli_stmt_bind_param(
                $stmt,
                "ssssi",
                $title,
                $category,
                $image_name,
                $status,
                $id
            );


            if (mysqli_stmt_execute($stmt)) {

                header("Location: gallery.php");
                exit;

            } else {

                $error =
                    "Gagal memperbarui data: " .
                    mysqli_error($koneksi);
            }
        }
    }


    /*
     * Agar input tetap muncul jika terjadi error.
     */
    $gallery["title"] = $title;
    $gallery["category"] = $category;
    $gallery["status"] = $status;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Edit Gallery - Coconut Company</title>

    <link
        rel="stylesheet"
        href="../assets/css/admin.css"
    >

</head>


<body>


<!-- SIDEBAR -->

<aside class="sidebar">

    <div class="logo">

        <div class="logo-box">

            <img
                src="../assets/img/logo.png"
                alt="CCCI Logo"
            >

        </div>

        <div class="logo-text">

            <h2>Coconut Company</h2>

            <p>ADMIN PANEL</p>

        </div>

    </div>


    <div class="menu">

        <div class="menu-title">
            Main Menu
        </div>


        <a href="dashboard.php">
            Dashboard
        </a>


        <a href="products.php">
            Products
        </a>

        <a href="portfolio.php">
    Portfolio
</a>


        <a
            href="gallery.php"
            class="active"
        >
            Gallery
        </a>

        <a href="management.php">
            Management
        </a>

        <a href="company_profile.php">
            Company Profile
        </a>


        <a href="inquiries.php">
            Inquiries
        </a>

        <a href="acoount.php">
            Account
        </a>

    </div>


    <div class="logout">

        <a href="logout.php">
            Logout
        </a>

    </div>

</aside>



<!-- MAIN -->

<main class="main">


    <!-- TOPBAR -->

    <header class="topbar">

        <h1>
            Edit Gallery
        </h1>


        <div class="admin-info">

            <div class="admin-avatar">

                <?= strtoupper(
                    substr($username, 0, 1)
                ); ?>

            </div>


            <div class="admin-name">

                <?= htmlspecialchars($username); ?>

            </div>

        </div>

    </header>



    <!-- CONTENT -->

    <section class="content">


        <!-- PAGE HEADER -->

        <div class="page-header">

            <div>

                <h1>
                    Edit Gallery
                </h1>

                <p>
                    Update gallery information and photo.
                </p>

            </div>

        </div>



        <!-- ERROR -->

        <?php if ($error !== ""): ?>

            <div class="alert-error">

                <?= htmlspecialchars($error); ?>

            </div>

        <?php endif; ?>



        <!-- FORM -->

        <div class="form-card">

            <form
                method="POST"
                enctype="multipart/form-data"
            >


                <!-- TITLE -->

                <div class="form-group">

                    <label for="title">
                        Photo Title
                    </label>

                    <input
                        type="text"
                        id="title"
                        name="title"
                        value="<?= htmlspecialchars($gallery["title"]); ?>"
                        placeholder="Enter photo title"
                        required
                    >

                </div>



                <!-- CATEGORY -->

                <div class="form-group">

                    <label for="category">
                        Category
                    </label>

                    <input
                        type="text"
                        id="category"
                        name="category"
                        value="<?= htmlspecialchars($gallery["category"] ?? ""); ?>"
                        placeholder="Example: Production, Warehouse, Export"
                    >

                </div>



                <!-- CURRENT IMAGE -->

                <div class="form-group">

                    <label>
                        Current Photo
                    </label>


                    <?php if (!empty($gallery["image"])): ?>

                        <div class="current-image">

                            <img
                                src="../uploads/<?= htmlspecialchars($gallery["image"]); ?>"
                                alt="<?= htmlspecialchars($gallery["title"]); ?>"
                            >

                        </div>

                    <?php else: ?>

                        <div class="gallery-no-image">
                            No Image
                        </div>

                    <?php endif; ?>

                </div>



                <!-- REPLACE IMAGE -->

                <div class="form-group">

                    <label for="image">
                        Replace Photo
                    </label>

                    <input
                        type="file"
                        id="image"
                        name="image"
                        accept=".jpg,.jpeg,.png,.webp"
                    >

                    <small>
                        Leave empty if you don't want to change
                        the current photo. Maximum 2 MB.
                    </small>

                </div>



                <!-- STATUS -->

                <div class="form-group">

                    <label for="status">
                        Status
                    </label>

                    <select
                        id="status"
                        name="status"
                    >

                        <option
                            value="active"
                            <?= $gallery["status"] === "active"
                                ? "selected"
                                : ""; ?>
                        >
                            Active
                        </option>


                        <option
                            value="inactive"
                            <?= $gallery["status"] === "inactive"
                                ? "selected"
                                : ""; ?>
                        >
                            Inactive
                        </option>

                    </select>

                </div>



                <!-- ACTIONS -->

                <div class="form-actions">

                    <button
                        type="submit"
                        class="btn-save"
                    >
                        Update Gallery
                    </button>


                    <a
                        href="gallery.php"
                        class="btn-secondary"
                    >
                        Cancel
                    </a>

                </div>


            </form>

        </div>


    </section>


</main>


</body>
</html>