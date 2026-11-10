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
   GET DATA
========================= */

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT * FROM management_structure WHERE id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$management = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$management) {
    header("Location: management.php");
    exit;
}


$error = "";


/* =========================
   UPDATE
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $position = trim($_POST["position"] ?? "");
    $level = (int) ($_POST["level"] ?? 1);
    $sort_order = (int) ($_POST["sort_order"] ?? 1);
    $status = $_POST["status"] ?? "active";

    $allowed_status = [
        "active",
        "inactive"
    ];


    if ($name === "" || $position === "") {

        $error = "Nama dan jabatan wajib diisi.";

    } elseif (!in_array($level, [1, 2, 3], true)) {

        $error = "Level tidak valid.";

    } elseif (!in_array($status, $allowed_status, true)) {

        $error = "Status tidak valid.";

    } else {

        $old_image = $management["image"];

        $new_image = $old_image;


        /* =========================
           NEW IMAGE
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

                    $error =
                        "Format foto harus JPG, PNG, atau WEBP.";

                } elseif ($file_size > 2 * 1024 * 1024) {

                    $error =
                        "Ukuran foto maksimal 2 MB.";

                } else {

                    $upload_dir =
                        __DIR__ .
                        "/../uploads/";

                    if (!is_dir($upload_dir)) {
                        mkdir(
                            $upload_dir,
                            0777,
                            true
                        );
                    }

                    $extension = pathinfo(
                        $_FILES["image"]["name"],
                        PATHINFO_EXTENSION
                    );

                    $new_image =
                        "management_" .
                        time() .
                        "_" .
                        bin2hex(random_bytes(4)) .
                        "." .
                        strtolower($extension);

                    $target =
                        $upload_dir .
                        $new_image;

                    if (!move_uploaded_file(
                        $_FILES["image"]["tmp_name"],
                        $target
                    )) {

                        $error =
                            "Foto gagal disimpan.";

                        $new_image = $old_image;

                    }

                }

            }

        }


        /* =========================
           UPDATE DATABASE
        ========================= */

        if ($error === "") {

            $update = mysqli_prepare(
                $koneksi,
                "UPDATE management_structure
                 SET
                    name = ?,
                    position = ?,
                    image = ?,
                    level = ?,
                    sort_order = ?,
                    status = ?
                 WHERE id = ?"
            );

            mysqli_stmt_bind_param(
                $update,
                "sssiisi",
                $name,
                $position,
                $new_image,
                $level,
                $sort_order,
                $status,
                $id
            );


            if (mysqli_stmt_execute($update)) {

                /* Delete old image */

                if (
                    $new_image !== $old_image &&
                    !empty($old_image)
                ) {

                    $old_file =
                        __DIR__ .
                        "/../uploads/" .
                        $old_image;

                    if (file_exists($old_file)) {
                        unlink($old_file);
                    }

                }


                mysqli_stmt_close($update);

                header(
                    "Location: management.php?success=updated"
                );

                exit;

            } else {

                /* Delete newly uploaded image */

                if (
                    $new_image !== $old_image &&
                    !empty($new_image)
                ) {

                    $new_file =
                        __DIR__ .
                        "/../uploads/" .
                        $new_image;

                    if (file_exists($new_file)) {
                        unlink($new_file);
                    }

                }

                $error =
                    "Gagal memperbarui data: " .
                    mysqli_stmt_error($update);

                mysqli_stmt_close($update);

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

    <title>Edit Management - Coconut Company</title>

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
            Edit Management
        </h1>

    </header>


    <section class="content">

        <div class="page-header">

            <div>

                <h1>
                    Edit Management Member
                </h1>

                <p>
                    Update management member information.
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
                    Update the information below.
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
                            value="<?= htmlspecialchars($management["name"]); ?>"
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
                            value="<?= htmlspecialchars($management["position"]); ?>"
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

                            <option
                                value="1"
                                <?= $management["level"] == 1 ? "selected" : ""; ?>
                            >
                                Level 1 - Pimpinan
                            </option>

                            <option
                                value="2"
                                <?= $management["level"] == 2 ? "selected" : ""; ?>
                            >
                                Level 2 - Manajemen
                            </option>

                            <option
                                value="3"
                                <?= $management["level"] == 3 ? "selected" : ""; ?>
                            >
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
                            value="<?= (int) $management["sort_order"]; ?>"
                            min="1"
                        >

                    </div>

                </div>


                <div class="form-group">

                    <label>
                        Current Photo
                    </label>

                    <?php if (!empty($management["image"])): ?>

                        <div style="margin-bottom:12px;">

                            <img
                                src="../uploads/<?= htmlspecialchars($management["image"]); ?>"
                                alt="<?= htmlspecialchars($management["name"]); ?>"
                                style="
                                    width:100px;
                                    height:120px;
                                    object-fit:cover;
                                    border-radius:10px;
                                    border:1px solid #ddd;
                                "
                            >

                        </div>

                    <?php else: ?>

                        <p>
                            No photo uploaded.
                        </p>

                    <?php endif; ?>

                </div>


                <div class="form-group">

                    <label>
                        Replace Photo
                    </label>

                    <input
                        type="file"
                        name="image"
                        accept=".jpg,.jpeg,.png,.webp"
                    >

                    <small>
                        Leave empty if you do not want to change the photo.
                        Maximum 2 MB.
                    </small>

                </div>


                <div class="form-group">

                    <label>
                        Status
                    </label>

                    <select name="status">

                        <option
                            value="active"
                            <?= $management["status"] === "active" ? "selected" : ""; ?>
                        >
                            Active
                        </option>

                        <option
                            value="inactive"
                            <?= $management["status"] === "inactive" ? "selected" : ""; ?>
                        >
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
                        Update Management
                    </button>

                </div>

            </form>

        </div>

    </section>

</main>

</body>

</html>