<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . "/../config/koneksi.php";

$username = $_SESSION["admin_username"];

$error = "";
$success = "";


/* =========================
   AMBIL DATA COMPANY PROFILE
========================= */

$query = mysqli_query(
    $koneksi,
    "SELECT * FROM company_profile
     ORDER BY id ASC
     LIMIT 1"
);

if (!$query) {
    die("Query gagal: " . mysqli_error($koneksi));
}

$company = mysqli_fetch_assoc($query);


/* =========================
   PROSES SIMPAN / UPDATE
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $company_name = trim($_POST["company_name"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $address = trim($_POST["address"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $whatsapp = trim($_POST["whatsapp"] ?? "");
    $operating_hours = trim($_POST["operating_hours"] ?? "");


    /* =========================
       VALIDASI
    ========================= */

    if ($company_name === "") {

        $error = "Nama perusahaan wajib diisi.";

    } else {


        /* =========================
           INSERT JIKA BELUM ADA
        ========================= */

        if (!$company) {

            $stmt = mysqli_prepare(
                $koneksi,
                "INSERT INTO company_profile
                (
                    company_name,
                    description,
                    address,
                    email,
                    phone,
                    whatsapp,
                    operating_hours
                )
                VALUES (?, ?, ?, ?, ?, ?, ?)"
            );


            if (!$stmt) {

                $error =
                    "Gagal menyiapkan query: " .
                    mysqli_error($koneksi);

            } else {

                mysqli_stmt_bind_param(
                    $stmt,
                    "sssssss",
                    $company_name,
                    $description,
                    $address,
                    $email,
                    $phone,
                    $whatsapp,
                    $operating_hours
                );


                if (mysqli_stmt_execute($stmt)) {

                    $success =
                        "Company Profile berhasil disimpan.";

                } else {

                    $error =
                        "Gagal menyimpan data: " .
                        mysqli_error($koneksi);
                }

                mysqli_stmt_close($stmt);
            }


        /* =========================
           UPDATE JIKA SUDAH ADA
        ========================= */

        } else {

            $id = $company["id"];


            $stmt = mysqli_prepare(
                $koneksi,
                "UPDATE company_profile
                 SET
                    company_name = ?,
                    description = ?,
                    address = ?,
                    email = ?,
                    phone = ?,
                    whatsapp = ?,
                    operating_hours = ?
                 WHERE id = ?"
            );


            if (!$stmt) {

                $error =
                    "Gagal menyiapkan query: " .
                    mysqli_error($koneksi);

            } else {

                mysqli_stmt_bind_param(
                    $stmt,
                    "sssssssi",
                    $company_name,
                    $description,
                    $address,
                    $email,
                    $phone,
                    $whatsapp,
                    $operating_hours,
                    $id
                );


                if (mysqli_stmt_execute($stmt)) {

                    $success =
                        "Company Profile berhasil diperbarui.";

                } else {

                    $error =
                        "Gagal memperbarui data: " .
                        mysqli_error($koneksi);
                }

                mysqli_stmt_close($stmt);
            }
        }


        /* =========================
           AMBIL DATA TERBARU
        ========================= */

        $query = mysqli_query(
            $koneksi,
            "SELECT * FROM company_profile
             ORDER BY id ASC
             LIMIT 1"
        );

        if ($query) {
            $company = mysqli_fetch_assoc($query);
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
        Company Profile - Coconut Company
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


    <!-- LOGO -->

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



    <!-- MENU -->

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


        <a href="gallery.php">
            Gallery
        </a>

        <a href="management.php">
            Management
        </a>

        <a
            href="company_profile.php"
            class="active"
        >
            Company Profile
        </a>


        <a href="inquiries.php">
            Inquiries
        </a>

        <a href="account.php">
            Account
        </a>

    </div>



    <!-- LOGOUT -->

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
            Company Profile
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
                    Company Profile
                </h1>

                <p>
                    Manage your company information displayed on the website.
                </p>

            </div>

        </div>



        <!-- SUCCESS MESSAGE -->

        <?php if ($success !== ""): ?>

            <div class="alert-success">

                <?= htmlspecialchars($success); ?>

            </div>

        <?php endif; ?>



        <!-- ERROR MESSAGE -->

        <?php if ($error !== ""): ?>

            <div class="alert-error">

                <?= htmlspecialchars($error); ?>

            </div>

        <?php endif; ?>



        <!-- FORM CARD -->

        <div class="form-card">

            <form method="POST">


                <!-- COMPANY NAME -->

                <div class="form-group">

                    <label for="company_name">
                        Company Name
                    </label>

                    <input
                        type="text"
                        id="company_name"
                        name="company_name"
                        value="<?= htmlspecialchars(
                            $company["company_name"] ?? ""
                        ); ?>"
                        placeholder="Enter company name"
                        required
                    >

                </div>



                <!-- DESCRIPTION -->

                <div class="form-group">

                    <label for="description">
                        Company Description
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        placeholder="Describe your company..."
                    ><?= htmlspecialchars(
                        $company["description"] ?? ""
                    ); ?></textarea>

                </div>



                <!-- ADDRESS -->

                <div class="form-group">

                    <label for="address">
                        Address
                    </label>

                    <textarea
                        id="address"
                        name="address"
                        placeholder="Enter company address"
                    ><?= htmlspecialchars(
                        $company["address"] ?? ""
                    ); ?></textarea>

                </div>



                <!-- EMAIL + PHONE -->

                <div class="form-row">


                    <div class="form-group">

                        <label for="email">
                            Email
                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="<?= htmlspecialchars(
                                $company["email"] ?? ""
                            ); ?>"
                            placeholder="company@email.com"
                        >

                    </div>


                    <div class="form-group">

                        <label for="phone">
                            Phone
                        </label>

                        <input
                            type="text"
                            id="phone"
                            name="phone"
                            value="<?= htmlspecialchars(
                                $company["phone"] ?? ""
                            ); ?>"
                            placeholder="+62..."
                        >

                    </div>

                </div>



                <!-- WHATSAPP + OPERATING HOURS -->

                <div class="form-row">


                    <div class="form-group">

                        <label for="whatsapp">
                            WhatsApp
                        </label>

                        <input
                            type="text"
                            id="whatsapp"
                            name="whatsapp"
                            value="<?= htmlspecialchars(
                                $company["whatsapp"] ?? ""
                            ); ?>"
                            placeholder="+62..."
                        >

                    </div>


                    <div class="form-group">

                        <label for="operating_hours">
                            Operating Hours
                        </label>

                        <input
                            type="text"
                            id="operating_hours"
                            name="operating_hours"
                            value="<?= htmlspecialchars(
                                $company["operating_hours"] ?? ""
                            ); ?>"
                            placeholder="Monday - Friday, 08:00 - 17:00"
                        >

                    </div>

                </div>



                <!-- ACTION -->

                <div class="form-actions">

                    <button
                        type="submit"
                        class="btn-save"
                    >
                        Save Company Profile
                    </button>

                </div>


            </form>

        </div>


    </section>


</main>


</body>
</html>