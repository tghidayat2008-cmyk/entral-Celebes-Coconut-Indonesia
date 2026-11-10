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
   PROCESS ADD PRODUCT
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $status = $_POST["status"] ?? "active";


    /* =========================
       VALIDATION
    ========================= */

    if ($name === "") {

        $error = "Nama produk wajib diisi.";

    } elseif (
        $status !== "active" &&
        $status !== "inactive"
    ) {

        $error = "Status produk tidak valid.";

    }


    /* =========================
       IMAGE
    ========================= */

    $image_name = "";


    if (
        $error === "" &&
        isset($_FILES["image"]) &&
        $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE
    ) {

        /* CHECK UPLOAD ERROR */

        if (
            $_FILES["image"]["error"] !==
            UPLOAD_ERR_OK
        ) {

            $error =
                "Upload gambar gagal. Kode error: " .
                $_FILES["image"]["error"];

        } else {

            $file_name =
                $_FILES["image"]["name"];

            $file_tmp =
                $_FILES["image"]["tmp_name"];

            $file_size =
                $_FILES["image"]["size"];


            /* GET EXTENSION */

            $extension = strtolower(
                pathinfo(
                    $file_name,
                    PATHINFO_EXTENSION
                )
            );


            /* ALLOWED EXTENSIONS */

            $allowed = [
                "jpg",
                "jpeg",
                "png",
                "webp"
            ];


            /* CHECK FORMAT */

            if (!in_array($extension, $allowed)) {

                $error =
                    "Format gambar harus JPG, JPEG, PNG, atau WEBP.";

            }


            /* CHECK FILE SIZE */

            elseif (
                $file_size > 2 * 1024 * 1024
            ) {

                $error =
                    "Ukuran gambar maksimal 2 MB.";

            }


            /* UPLOAD */

            else {

                $upload_dir =
                    __DIR__ . "/../uploads/";


                /* CREATE UPLOAD DIRECTORY */

                if (!is_dir($upload_dir)) {

                    mkdir(
                        $upload_dir,
                        0777,
                        true
                    );

                }


                /* GENERATE UNIQUE FILE NAME */

                $image_name =
                    time() .
                    "_" .
                    uniqid() .
                    "." .
                    $extension;


                $upload_path =
                    $upload_dir .
                    $image_name;


                /* MOVE FILE */

                if (
                    !move_uploaded_file(
                        $file_tmp,
                        $upload_path
                    )
                ) {

                    $error =
                        "Gagal mengupload gambar. Pastikan folder uploads tersedia.";

                    $image_name = "";

                }

            }

        }

    }


    /* =========================
       INSERT PRODUCT
    ========================= */

    if ($error === "") {

        $query = "
            INSERT INTO products
            (
                name,
                description,
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

            $error =
                "Query database gagal.";

        } else {

            mysqli_stmt_bind_param(
                $stmt,
                "ssss",
                $name,
                $description,
                $image_name,
                $status
            );


            if (
                mysqli_stmt_execute($stmt)
            ) {

                header("Location: products.php");
                exit;

            } else {

                /*
                 * Jika database gagal setelah gambar
                 * berhasil di-upload, hapus file tersebut
                 * agar tidak menjadi file yatim.
                 */

                if (
                    !empty($image_name) &&
                    file_exists(
                        __DIR__ .
                        "/../uploads/" .
                        $image_name
                    )
                ) {

                    unlink(
                        __DIR__ .
                        "/../uploads/" .
                        $image_name
                    );

                }


                $error =
                    "Gagal menambahkan produk.";

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
        Add Product - Coconut Company
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/admin.css"
    >

</head>


<body>


<!-- =========================
     SIDEBAR
========================= -->

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


        <a
            href="products.php"
            class="active"
        >
            Products
        </a>


        <a href="portfolio.php">
            Portfolio
        </a>


        <a href="gallery.php">
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


<!-- =========================
     MAIN
========================= -->

<main class="main">


    <!-- TOPBAR -->

    <header class="topbar">

        <h1>
            Add Product
        </h1>


        <div class="admin-info">

            <div class="admin-avatar">

                <?= strtoupper(
                    substr($username, 0, 1)
                ); ?>

            </div>


            <div class="admin-name">

                <?= htmlspecialchars(
                    $username
                ); ?>

            </div>

        </div>

    </header>


    <!-- CONTENT -->

    <section class="content">


        <!-- PAGE HEADER -->

        <div class="page-header">

            <div>

                <h1>
                    Add Product
                </h1>

                <p>
                    Add a new coconut product to the catalog.
                </p>

            </div>


            <a
                href="products.php"
                class="btn-secondary"
            >
                Back to Products
            </a>

        </div>


        <!-- FORM -->

        <div class="form-card">


            <!-- ERROR -->

            <?php if ($error !== ""): ?>

                <div class="alert-error">

                    <?= htmlspecialchars($error); ?>

                </div>

            <?php endif; ?>


            <form
                method="POST"
                enctype="multipart/form-data"
            >


                <!-- PRODUCT NAME -->

                <div class="form-group">

                    <label for="name">
                        Product Name
                    </label>


                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="<?= htmlspecialchars(
                            $_POST["name"] ?? ""
                        ); ?>"
                        placeholder="Example: Coconut Oil"
                        required
                    >

                </div>


                <!-- DESCRIPTION -->

                <div class="form-group">

                    <label for="description">
                        Description
                    </label>


                    <textarea
                        id="description"
                        name="description"
                        placeholder="Enter product description..."
                    ><?= htmlspecialchars(
                        $_POST["description"] ?? ""
                    ); ?></textarea>

                </div>


                <!-- PRODUCT IMAGE -->

                <div class="form-group">

                    <label for="image">
                        Product Image
                    </label>


                    <input
                        type="file"
                        id="image"
                        name="image"
                        accept=".jpg,.jpeg,.png,.webp"
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
                        Save Product
                    </button>


                    <a
                        href="products.php"
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