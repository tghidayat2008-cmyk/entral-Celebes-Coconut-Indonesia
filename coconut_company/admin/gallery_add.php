<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . "/../config/koneksi.php";

$username = $_SESSION["admin_username"];

$error = "";


/* =========================
   PROSES TAMBAH GALLERY
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $status = $_POST["status"] ?? "active";


    /* =========================
       VALIDASI DATA
    ========================= */

    if ($title === "") {

        $error = "Judul foto wajib diisi.";

    } elseif (!in_array($status, ["active", "inactive"], true)) {

        $error = "Status gallery tidak valid.";

    } elseif (
        !isset($_FILES["image"]) ||
        $_FILES["image"]["error"] === UPLOAD_ERR_NO_FILE
    ) {

        $error = "Foto wajib diupload.";

    } else {

        $file_error = $_FILES["image"]["error"];


        /* =========================
           CEK ERROR UPLOAD
        ========================= */

        if ($file_error !== UPLOAD_ERR_OK) {

            $error =
                "Upload foto gagal. Kode error: " .
                $file_error;

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


            /* =========================
               CEK FORMAT
            ========================= */

            if (!in_array($extension, $allowed, true)) {

                $error =
                    "Format foto harus JPG, JPEG, PNG, atau WEBP.";

            }


            /* =========================
               CEK UKURAN
            ========================= */

            elseif ($file_size > 2 * 1024 * 1024) {

                $error =
                    "Ukuran foto maksimal 2 MB.";

            }


            /* =========================
               PROSES UPLOAD
            ========================= */

            else {

                $upload_dir = __DIR__ . "/../uploads/";


                if (!is_dir($upload_dir)) {

                    mkdir(
                        $upload_dir,
                        0777,
                        true
                    );
                }


                /* Nama file unik */

                $image_name =
                    time() .
                    "_" .
                    uniqid() .
                    "." .
                    $extension;


                $upload_path =
                    $upload_dir . $image_name;


                /* Pindahkan file */

                if (
                    move_uploaded_file(
                        $file_tmp,
                        $upload_path
                    )
                ) {


                    /* =========================
                       SIMPAN KE DATABASE
                    ========================= */

                    $query = "
                        INSERT INTO gallery
                        (
                            title,
                            category,
                            image,
                            status
                        )
                        VALUES (?, ?, ?, ?)
                    ";

                    $stmt = mysqli_prepare(
                        $koneksi,
                        $query
                    );


                    if (!$stmt) {

                        if (file_exists($upload_path)) {
                            unlink($upload_path);
                        }

                        $error =
                            "Query database gagal: " .
                            mysqli_error($koneksi);

                    } else {

                        mysqli_stmt_bind_param(
                            $stmt,
                            "ssss",
                            $title,
                            $category,
                            $image_name,
                            $status
                        );


                        if (
                            mysqli_stmt_execute($stmt)
                        ) {

                            header(
                                "Location: gallery.php"
                            );

                            exit;

                        } else {

                            /* Hapus file jika database gagal */

                            if (
                                file_exists($upload_path)
                            ) {

                                unlink($upload_path);
                            }

                            $error =
                                "Gagal menyimpan data gallery: " .
                                mysqli_error($koneksi);
                        }

                    }

                } else {

                    $error =
                        "Gagal mengupload foto. " .
                        "Pastikan folder uploads tersedia.";
                }
            }
        }
    }
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

    <title>
        Add Gallery - Coconut Company
    </title>

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

            <h2>
                Coconut Company
            </h2>

            <p>
                ADMIN PANEL
            </p>

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
            Add Gallery
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
                    Add Gallery
                </h1>

                <p>
                    Add a new photo to the company gallery.
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
                        value="<?= htmlspecialchars(
                            $_POST["title"] ?? ""
                        ); ?>"
                        placeholder="Example: Coconut Processing"
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
                        value="<?= htmlspecialchars(
                            $_POST["category"] ?? ""
                        ); ?>"
                        placeholder="Example: Production"
                    >

                </div>



                <!-- IMAGE -->

                <div class="form-group">

                    <label for="image">
                        Photo
                    </label>

                    <input
                        type="file"
                        id="image"
                        name="image"
                        accept=".jpg,.jpeg,.png,.webp"
                        required
                    >

                    <small>
                        JPG, JPEG, PNG, or WEBP. Maximum 2 MB.
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
                            <?= (
                                ($_POST["status"] ?? "active")
                                === "active"
                            )
                                ? "selected"
                                : ""; ?>
                        >
                            Active
                        </option>


                        <option
                            value="inactive"
                            <?= (
                                ($_POST["status"] ?? "")
                                === "inactive"
                            )
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
                        Save Gallery
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