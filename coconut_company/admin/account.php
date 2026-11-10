<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . "/../config/koneksi.php";


/* =========================================================
   DATA ADMIN
========================================================= */

$admin_id = intval($_SESSION["admin_id"]);

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id, username, role, password
     FROM users
     WHERE id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $admin_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$admin = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


if (!$admin) {

    session_unset();
    session_destroy();

    header("Location: login.php");
    exit;
}


/* =========================================================
   VARIABLE
========================================================= */

$success = "";
$error = "";

$username = $admin["username"];


/* =========================================================
   UPDATE ACCOUNT
========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $new_username = trim($_POST["username"] ?? "");

    $current_password =
        $_POST["current_password"] ?? "";

    $new_password =
        $_POST["new_password"] ?? "";

    $confirm_password =
        $_POST["confirm_password"] ?? "";


    /* =====================================================
       VALIDASI USERNAME
    ===================================================== */

    if ($new_username === "") {

        $error = "Username tidak boleh kosong.";

    } elseif (strlen($new_username) < 3) {

        $error = "Username minimal 3 karakter.";

    } elseif (
        !preg_match(
            '/^[a-zA-Z0-9_.-]+$/',
            $new_username
        )
    ) {

        $error =
            "Username hanya boleh menggunakan huruf, angka, titik, garis bawah, atau tanda strip.";
    }


    /* =====================================================
       CEK APAKAH PASSWORD AKAN DIGANTI
    ===================================================== */

    $changing_password =
        (
            $current_password !== "" ||
            $new_password !== "" ||
            $confirm_password !== ""
        );


    if (
        empty($error) &&
        $changing_password
    ) {

        if ($current_password === "") {

            $error =
                "Password saat ini wajib diisi untuk mengganti password.";

        } elseif ($new_password === "") {

            $error =
                "Password baru wajib diisi.";

        } elseif (strlen($new_password) < 6) {

            $error =
                "Password baru minimal 6 karakter.";

        } elseif ($new_password !== $confirm_password) {

            $error =
                "Konfirmasi password tidak cocok.";
        }
    }


    /* =====================================================
       PROSES UPDATE
    ===================================================== */

    if (empty($error)) {


        /* =================================================
           CEK USERNAME DUPLIKAT
        ================================================= */

        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT id
             FROM users
             WHERE username = ?
             AND id != ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "si",
            $new_username,
            $admin_id
        );

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        $username_exists =
            mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);


        if ($username_exists) {

            $error =
                "Username tersebut sudah digunakan admin lain.";
        }
    }


    /* =====================================================
       UPDATE ACCOUNT
    ===================================================== */

    if (empty($error)) {


        /* =================================================
           UPDATE USERNAME + PASSWORD
        ================================================= */

        if ($changing_password) {


            /* =============================================
               VERIFIKASI PASSWORD LAMA
            ============================================== */

            if (
                !password_verify(
                    $current_password,
                    $admin["password"]
                )
            ) {

                $error =
                    "Password saat ini salah.";

            } else {


                /* =========================================
                   HASH PASSWORD BARU
                ========================================== */

                $hashed_password =
                    password_hash(
                        $new_password,
                        PASSWORD_DEFAULT
                    );


                /* =========================================
                   UPDATE DATABASE
                ========================================== */

                $stmt = mysqli_prepare(
                    $koneksi,
                    "UPDATE users
                     SET username = ?,
                         password = ?
                     WHERE id = ?"
                );

                mysqli_stmt_bind_param(
                    $stmt,
                    "ssi",
                    $new_username,
                    $hashed_password,
                    $admin_id
                );

                $updated =
                    mysqli_stmt_execute($stmt);

                mysqli_stmt_close($stmt);


                if ($updated) {

                    $_SESSION["admin_username"] =
                        $new_username;

                    $username =
                        $new_username;

                    $success =
                        "Username dan password berhasil diperbarui.";

                } else {

                    $error =
                        "Gagal memperbarui akun.";
                }
            }


        } else {


            /* =================================================
               HANYA UPDATE USERNAME
            ================================================= */

            $stmt = mysqli_prepare(
                $koneksi,
                "UPDATE users
                 SET username = ?
                 WHERE id = ?"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "si",
                $new_username,
                $admin_id
            );

            $updated =
                mysqli_stmt_execute($stmt);

            mysqli_stmt_close($stmt);


            if ($updated) {

                $_SESSION["admin_username"] =
                    $new_username;

                $username =
                    $new_username;

                $success =
                    "Username berhasil diperbarui.";

            } else {

                $error =
                    "Gagal memperbarui username.";
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
        Account Settings | CCCI Admin
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/admin.css"
    >

</head>


<body>


<!-- =========================================================
     SIDEBAR
========================================================= -->

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
                CCCI
            </h2>

            <p>
                ADMIN PANEL
            </p>

        </div>

    </div>


    <!-- =====================================================
         MAIN MENU
    ====================================================== -->

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


        <a href="gallery.php">
            Gallery
        </a>

        <a href="management.php">
            Management
        </a>

        <a href="portfolio.php">
            Portfolio
        </a>


        <a href="company_profile.php">
            Company Profile
        </a>


        <a href="inquiries.php">
            Inquiries
        </a>


        <a
            href="account.php"
            class="active"
        >
            Account
        </a>

    </div>


    <!-- =====================================================
         LOGOUT
         
         PENTING:
         Menggunakan class "logout", sama seperti halaman
         admin lainnya.
    ====================================================== -->

    <div class="logout">

        <a href="logout.php">
            Logout
        </a>

    </div>


</aside>



<!-- =========================================================
     MAIN CONTENT
========================================================= -->

<main class="main">


    <!-- =====================================================
         TOPBAR
    ====================================================== -->

    <header class="topbar">


        <div>

            <h1>
                Account Settings
            </h1>

        </div>


        <div class="admin-info">

            <div class="admin-avatar">

                <?= strtoupper(substr($username, 0, 1)); ?>

            </div>


            <div class="admin-name">

                <?= htmlspecialchars($username); ?>

            </div>

        </div>


    </header>



    <!-- =====================================================
         CONTENT
    ====================================================== -->

    <section class="content">


        <!-- PAGE HEADER -->

        <div class="page-header">

            <div>

                <h1>
                    Account Settings
                </h1>

                <p>
                    Manage your admin username and password.
                </p>

            </div>

        </div>



        <!-- =================================================
             SUCCESS MESSAGE
        ================================================== -->

        <?php if (!empty($success)): ?>

            <div class="alert-success">

                <?= htmlspecialchars($success); ?>

            </div>

        <?php endif; ?>



        <!-- =================================================
             ERROR MESSAGE
        ================================================== -->

        <?php if (!empty($error)): ?>

            <div class="alert-error">

                <?= htmlspecialchars($error); ?>

            </div>

        <?php endif; ?>



        <!-- =================================================
             ACCOUNT CARD
        ================================================== -->

        <div class="form-card">


            <!-- CARD HEADER -->

            <div class="form-card-head">

                <h2>
                    Account Information
                </h2>

                <p>
                    Update your login credentials.
                </p>

            </div>



            <!-- FORM -->

            <form
                method="POST"
                autocomplete="off"
            >


                <!-- =================================================
                     USERNAME
                ================================================== -->

                <div class="form-group">

                    <label for="username">
                        Username
                    </label>

                    <input
                        type="text"
                        id="username"
                        name="username"
                        value="<?= htmlspecialchars($username); ?>"
                        minlength="3"
                        maxlength="50"
                        required
                    >

                    <small>
                        Username minimal 3 karakter.
                    </small>

                </div>



                <!-- =================================================
                     CHANGE PASSWORD
                ================================================== -->

                <div class="account-divider">

                    <span>
                        Change Password
                    </span>

                </div>



                <!-- CURRENT PASSWORD -->

                <div class="form-group">

                    <label for="current_password">
                        Current Password
                    </label>

                    <input
                        type="password"
                        id="current_password"
                        name="current_password"
                        autocomplete="current-password"
                        placeholder="Masukkan password saat ini"
                    >

                    <small>
                        Wajib diisi jika ingin mengganti password.
                    </small>

                </div>



                <!-- NEW PASSWORD -->

                <div class="form-group">

                    <label for="new_password">
                        New Password
                    </label>

                    <input
                        type="password"
                        id="new_password"
                        name="new_password"
                        minlength="6"
                        autocomplete="new-password"
                        placeholder="Masukkan password baru"
                    >

                    <small>
                        Password baru minimal 6 karakter.
                    </small>

                </div>



                <!-- CONFIRM PASSWORD -->

                <div class="form-group">

                    <label for="confirm_password">
                        Confirm New Password
                    </label>

                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        minlength="6"
                        autocomplete="new-password"
                        placeholder="Ulangi password baru"
                    >

                </div>



                <!-- =================================================
                     BUTTON
                ================================================== -->

                <div class="form-actions">

                    <button
                        type="submit"
                        class="btn-save"
                    >
                        Update Account
                    </button>

                </div>


            </form>


        </div>


    </section>


</main>


</body>

</html>