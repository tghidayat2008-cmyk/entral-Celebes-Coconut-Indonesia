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
   CHECK PRODUCT ID
========================= */

if (
    !isset($_GET["id"]) ||
    !is_numeric($_GET["id"])
) {
    header("Location: products.php");
    exit;
}

$id = (int) $_GET["id"];


/* =========================
   GET PRODUCT
========================= */

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT * FROM products WHERE id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$product = mysqli_fetch_assoc($result);


/* =========================
   PRODUCT NOT FOUND
========================= */

if (!$product) {
    header("Location: products.php");
    exit;
}


/* =========================
   UPDATE PRODUCT
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $status = $_POST["status"] ?? "active";


    /* VALIDATE NAME */

    if ($name === "") {

        $error = "Nama produk wajib diisi.";

    }


    /* VALIDATE STATUS */

    elseif (
        $status !== "active" &&
        $status !== "inactive"
    ) {

        $error = "Status produk tidak valid.";

    }


    /* =========================
       IMAGE UPLOAD
    ========================= */

    $image_name = $product["image"];


    if (
        $error === "" &&
        isset($_FILES["image"]) &&
        $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE
    ) {

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


            $extension = strtolower(
                pathinfo(
                    $file_name,
                    PATHINFO_EXTENSION
                )
            );


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


            /* CHECK SIZE */

            elseif (
                $file_size > 2 * 1024 * 1024
            ) {

                $error =
                    "Ukuran gambar maksimal 2 MB.";

            }


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


                /* NEW IMAGE NAME */

                $new_image_name =
                    time() .
                    "_" .
                    uniqid() .
                    "." .
                    $extension;


                $upload_path =
                    $upload_dir .
                    $new_image_name;


                /* MOVE NEW IMAGE */

                if (
                    move_uploaded_file(
                        $file_tmp,
                        $upload_path
                    )
                ) {

                    /* DELETE OLD IMAGE */

                    if (
                        !empty($product["image"]) &&
                        file_exists(
                            $upload_dir .
                            $product["image"]
                        )
                    ) {

                        unlink(
                            $upload_dir .
                            $product["image"]
                        );

                    }


                    $image_name =
                        $new_image_name;

                } else {

                    $error =
                        "Gagal mengupload gambar baru.";

                }

            }

        }

    }


    /* =========================
       UPDATE DATABASE
    ========================= */

    if ($error === "") {

        $query_update = "
            UPDATE products
            SET
                name = ?,
                description = ?,
                image = ?,
                status = ?
            WHERE id = ?
        ";


        $stmt_update = mysqli_prepare(
            $koneksi,
            $query_update
        );


        mysqli_stmt_bind_param(
            $stmt_update,
            "ssssi",
            $name,
            $description,
            $image_name,
            $status,
            $id
        );


        if (
            mysqli_stmt_execute(
                $stmt_update
            )
        ) {

            header("Location: products.php");
            exit;

        } else {

            $error =
                "Gagal mengupdate produk.";

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
        Edit Product - Coconut Company
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
            Edit Product
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
                    Edit Product
                </h1>

                <p>
                    Update product information and replace the product image.
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
                            $product["name"]
                        ); ?>"
                        placeholder="Enter product name"
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
                        placeholder="Enter product description"
                    ><?= htmlspecialchars(
                        $product["description"] ?? ""
                    ); ?></textarea>

                </div>


                <!-- CURRENT IMAGE -->

                <div class="form-group">

                    <label>
                        Current Image
                    </label>


                    <?php if (
                        !empty($product["image"])
                    ): ?>

                        <div class="current-image">

                            <img
                                src="../uploads/<?= htmlspecialchars(
                                    $product["image"]
                                ); ?>"
                                alt="Current Product Image"
                            >

                            <span>
                                Current product image
                            </span>

                        </div>

                    <?php else: ?>

                        <div class="current-image">

                            <div class="product-no-image">
                                No Image
                            </div>

                        </div>

                    <?php endif; ?>

                </div>


                <!-- REPLACE IMAGE -->

                <div class="form-group">

                    <label for="image">
                        Replace Image
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
                            <?= $product["status"] === "active"
                                ? "selected"
                                : ""; ?>
                        >
                            Active
                        </option>


                        <option
                            value="inactive"
                            <?= $product["status"] === "inactive"
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
                        Update Product
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