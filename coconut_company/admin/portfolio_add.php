<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . "/../config/koneksi.php";

$username = $_SESSION["admin_username"];

$error = "";

$title = "";
$category = "";
$description = "";
$status = "active";


/* =========================
   PROCESS FORM
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $status = $_POST["status"] ?? "active";


    /* =========================
       VALIDATION
    ========================= */

    if ($title === "") {

        $error = "Portfolio title wajib diisi.";

    } elseif (!in_array($status, ["active", "inactive"], true)) {

        $error = "Status tidak valid.";

    }


    /* =========================
       UPLOAD IMAGE
    ========================= */

    $image_name = "";

    if ($error === "" && isset($_FILES["image"])) {

        if ($_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE) {

            if ($_FILES["image"]["error"] !== UPLOAD_ERR_OK) {

                $error = "Gagal mengupload gambar.";

            } else {

                $allowed_types = [
                    "image/jpeg",
                    "image/png",
                    "image/webp"
                ];

                $file_type = $_FILES["image"]["type"];

                $file_size = $_FILES["image"]["size"];

                if (!in_array($file_type, $allowed_types, true)) {

                    $error = "Format gambar harus JPG, PNG, atau WEBP.";

                } elseif ($file_size > 2 * 1024 * 1024) {

                    $error = "Ukuran gambar maksimal 2 MB.";

                } else {

                    $extension = strtolower(
                        pathinfo(
                            $_FILES["image"]["name"],
                            PATHINFO_EXTENSION
                        )
                    );

                    $image_name =
                        "portfolio_" .
                        time() .
                        "_" .
                        uniqid() .
                        "." .
                        $extension;

                    $upload_dir =
                        __DIR__ . "/../uploads/";

                    if (!is_dir($upload_dir)) {

                        mkdir(
                            $upload_dir,
                            0777,
                            true
                        );
                    }

                    $upload_path =
                        $upload_dir . $image_name;


                    if (
                        !move_uploaded_file(
                            $_FILES["image"]["tmp_name"],
                            $upload_path
                        )
                    ) {

                        $error =
                            "Gagal menyimpan gambar.";
                    }
                }
            }
        }
    }


    /* =========================
       INSERT DATABASE
    ========================= */

    if ($error === "") {

        $stmt = mysqli_prepare(
            $koneksi,
            "INSERT INTO portfolio
            (
                title,
                category,
                description,
                image,
                status
            )
            VALUES (?, ?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "sssss",
            $title,
            $category,
            $description,
            $image_name,
            $status
        );


        if (mysqli_stmt_execute($stmt)) {

            mysqli_stmt_close($stmt);

            header(
                "Location: portfolio.php?success=added"
            );

            exit;

        } else {

            if (
                $image_name !== "" &&
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
                "Gagal menyimpan portfolio.";
        }

        mysqli_stmt_close($stmt);
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
        Add Portfolio - Coconut Company
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
            href="portfolio.php"
            class="active"
        >
            Portfolio
        </a>


        <a href="products.php">
            Products
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
            Add Portfolio
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


    <!-- =========================
         CONTENT
    ========================= -->

    <section class="content">


        <!-- PAGE HEADER -->

        <div class="page-header">

            <div>

                <h1>
                    Add Portfolio
                </h1>

                <p>
                    Add a new company project or business activity.
                </p>

            </div>


            <a
                href="portfolio.php"
                class="btn-secondary"
            >
                Back to Portfolio
            </a>

        </div>


        <!-- ERROR -->

        <?php if ($error !== ""): ?>

            <div class="alert-error">

                <?= htmlspecialchars($error); ?>

            </div>

        <?php endif; ?>


        <!-- =========================
             FORM
        ========================= -->

        <form
            method="POST"
            enctype="multipart/form-data"
            class="form-card"
        >


            <!-- TITLE -->

            <div class="form-group">

                <label for="title">
                    Portfolio Title
                </label>

                <input
                    type="text"
                    name="title"
                    id="title"
                    value="<?= htmlspecialchars($title); ?>"
                    placeholder="Example: Coconut Husk Processing"
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
                    name="category"
                    id="category"
                    value="<?= htmlspecialchars($category); ?>"
                    placeholder="Example: Processing"
                >

            </div>


            <!-- DESCRIPTION -->

            <div class="form-group">

                <label for="description">
                    Description
                </label>

                <textarea
                    name="description"
                    id="description"
                    rows="6"
                    placeholder="Describe this portfolio..."
                ><?= htmlspecialchars($description); ?></textarea>

            </div>


            <!-- IMAGE -->

            <div class="form-group">

                <label for="image">
                    Portfolio Image
                </label>

                <input
                    type="file"
                    name="image"
                    id="image"
                    accept=".jpg,.jpeg,.png,.webp"
                >

                <p
                    style="
                        margin-top:7px;
                        color:#87908A;
                        font-size:11px;
                    "
                >
                    JPG, PNG, or WEBP. Maximum 2 MB.
                </p>

            </div>


            <!-- STATUS -->

            <div class="form-group">

                <label for="status">
                    Status
                </label>

                <select
                    name="status"
                    id="status"
                >

                    <option
                        value="active"
                        <?= $status === "active"
                            ? "selected"
                            : "";
                        ?>
                    >
                        Active
                    </option>

                    <option
                        value="inactive"
                        <?= $status === "inactive"
                            ? "selected"
                            : "";
                        ?>
                    >
                        Inactive
                    </option>

                </select>

            </div>


            <!-- ACTION -->

            <div class="form-actions">

                <a
                    href="portfolio.php"
                    class="btn-secondary"
                >
                    Cancel
                </a>


                <button
                    type="submit"
                    class="btn-save"
                >
                    Save Portfolio
                </button>

            </div>


        </form>


    </section>

</main>


</body>
</html>