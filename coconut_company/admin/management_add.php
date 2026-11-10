<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . "/../config/koneksi.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $position = trim($_POST["position"] ?? "");
    $level = (int) ($_POST["level"] ?? 1);
    $sort_order = (int) ($_POST["sort_order"] ?? 1);
    $status = $_POST["status"] ?? "active";

    $allowed_status = ["active", "inactive"];

    if ($name === "" || $position === "") {

        $error = "Nama dan jabatan wajib diisi.";

    } elseif (!in_array($level, [1, 2, 3], true)) {

        $error = "Level tidak valid.";

    } elseif (!in_array($status, $allowed_status, true)) {

        $error = "Status tidak valid.";

    } else {

        $image_name = "";

        /* =========================
           UPLOAD IMAGE
        ========================= */

        if (
            isset($_FILES["image"]) &&
            $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE
        ) {

            if ($_FILES["image"]["error"] !== UPLOAD_ERR_OK) {

                $error = "Upload foto gagal.";

            } else {

                $allowed_types = [
                    "image/jpeg",
                    "image/png",
                    "image/webp"
                ];

                $file_type = mime_content_type(
                    $_FILES["image"]["tmp_name"]
                );

                $file_size = $_FILES["image"]["size"];

                if (!in_array($file_type, $allowed_types, true)) {

                    $error = "Format foto harus JPG, PNG, atau WEBP.";

                } elseif ($file_size > 2 * 1024 * 1024) {

                    $error = "Ukuran foto maksimal 2 MB.";

                } else {

                    $upload_dir = __DIR__ . "/../uploads/";

                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }

                    $extension = pathinfo(
                        $_FILES["image"]["name"],
                        PATHINFO_EXTENSION
                    );

                    $image_name =
                        "management_" .
                        time() .
                        "_" .
                        bin2hex(random_bytes(4)) .
                        "." .
                        strtolower($extension);

                    $target = $upload_dir . $image_name;

                    if (!move_uploaded_file(
                        $_FILES["image"]["tmp_name"],
                        $target
                    )) {

                        $error = "Foto gagal disimpan.";
                        $image_name = "";

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
                "INSERT INTO management_structure
                (name, position, image, level, sort_order, status)
                VALUES (?, ?, ?, ?, ?, ?)"
            );

            if ($stmt) {

                mysqli_stmt_bind_param(
                    $stmt,
                    "sssiis",
                    $name,
                    $position,
                    $image_name,
                    $level,
                    $sort_order,
                    $status
                );

                if (mysqli_stmt_execute($stmt)) {

                    header(
                        "Location: management.php?success=added"
                    );

                    exit;

                } else {

                    if ($image_name !== "") {

                        $uploaded_file =
                            __DIR__ .
                            "/../uploads/" .
                            $image_name;

                        if (file_exists($uploaded_file)) {
                            unlink($uploaded_file);
                        }

                    }

                    $error =
                        "Gagal menyimpan data: " .
                        mysqli_stmt_error($stmt);

                }

                mysqli_stmt_close($stmt);

            } else {

                $error =
                    "Gagal membuat query: " .
                    mysqli_error($koneksi);

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

    <title>Add Management - Coconut Company</title>

    <link
        rel="stylesheet"
        href="../assets/css/admin.css"
    >

</head>

<body>

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

        <a href="dashboard.php">Dashboard</a>

        <a href="products.php">Products</a>

        <a href="portfolio.php">Portfolio</a>

        <a href="gallery.php">Gallery</a>

        <a
            href="management.php"
            class="active"
        >
            Management Structure
        </a>

        <a href="company_profile.php">Company Profile</a>

        <a href="inquiries.php">Inquiries</a>

        <a href="account.php">Account</a>

    </div>


    <div class="logout">

        <a href="logout.php">
            Logout
        </a>

    </div>

</aside>


<main class="main">

    <header class="topbar">

        <h1>
            Add Management
        </h1>

    </header>


    <section class="content">

        <div class="page-header">

            <div>

                <h1>
                    Add Management Member
                </h1>

                <p>
                    Add a new member to the CCCI management structure.
                </p>

            </div>

        </div>


        <?php if ($error !== ""): ?>

            <div class="alert-error">

                <?= htmlspecialchars($error); ?>

            </div>

        <?php endif; ?>


        <div class="form-card">

            <div class="form-card-head">

                <h2>
                    Management Information
                </h2>

                <p>
                    Fill in the management member information below.
                </p>

            </div>


            <form
                method="POST"
                enctype="multipart/form-data"
            >

                <div class="form-row">

                    <div class="form-group">

                        <label>
                            Name
                        </label>

                        <input
                            type="text"
                            name="name"
                            value="<?= htmlspecialchars($_POST["name"] ?? ""); ?>"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Position
                        </label>

                        <input
                            type="text"
                            name="position"
                            value="<?= htmlspecialchars($_POST["position"] ?? ""); ?>"
                            required
                        >

                    </div>

                </div>


                <div class="form-row">

                    <div class="form-group">

                        <label>
                            Structure Level
                        </label>

                        <select name="level">

                            <option value="1">
                                Level 1 - Pimpinan
                            </option>

                            <option value="2">
                                Level 2 - Manajemen
                            </option>

                            <option value="3">
                                Level 3 - Operasional
                            </option>

                        </select>

                    </div>


                    <div class="form-group">

                        <label>
                            Sort Order
                        </label>

                        <input
                            type="number"
                            name="sort_order"
                            value="<?= htmlspecialchars($_POST["sort_order"] ?? "1"); ?>"
                            min="1"
                        >

                        <small>
                            Digunakan untuk mengatur urutan posisi pengurus.
                        </small>

                    </div>

                </div>


                <div class="form-group">

                    <label>
                        Photo
                    </label>

                    <input
                        type="file"
                        name="image"
                        accept=".jpg,.jpeg,.png,.webp"
                    >

                    <small>
                        Format JPG, PNG, atau WEBP. Maksimal 2 MB.
                    </small>

                </div>


                <div class="form-group">

                    <label>
                        Status
                    </label>

                    <select name="status">

                        <option value="active">
                            Active
                        </option>

                        <option value="inactive">
                            Inactive
                        </option>

                    </select>

                </div>


                <div class="form-actions">

                    <a
                        href="management.php"
                        class="btn-secondary"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="btn-save"
                    >
                        Save Management
                    </button>

                </div>

            </form>

        </div>

    </section>

</main>

</body>

</html>