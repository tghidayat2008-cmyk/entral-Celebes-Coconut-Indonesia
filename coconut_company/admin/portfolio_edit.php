<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . "/../config/koneksi.php";

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: portfolio.php");
    exit;
}

$id = intval($_GET["id"]);

$error = "";
$success = "";

/* =========================
   AMBIL DATA PORTFOLIO
========================= */

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT * FROM portfolio WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$portfolio = mysqli_fetch_assoc($result);

if (!$portfolio) {
    header("Location: portfolio.php");
    exit;
}

/* =========================
   PROSES UPDATE
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $status = $_POST["status"] ?? "active";

    if ($title === "") {
        $error = "Judul portfolio wajib diisi.";
    } elseif (!in_array($status, ["active", "inactive"])) {
        $error = "Status tidak valid.";
    }

    $new_image = $portfolio["image"];
    $uploaded_new_image = false;
    $new_image_path = "";

    /* =========================
       UPLOAD GAMBAR BARU
    ========================= */

    if ($error === "" && isset($_FILES["image"]) && $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE) {

        if ($_FILES["image"]["error"] !== UPLOAD_ERR_OK) {
            $error = "Gagal mengupload gambar.";
        } else {

            $allowed_types = [
                "image/jpeg",
                "image/png",
                "image/webp"
            ];

            $file_type = mime_content_type($_FILES["image"]["tmp_name"]);
            $file_size = $_FILES["image"]["size"];

            if (!in_array($file_type, $allowed_types)) {
                $error = "Format gambar harus JPG, PNG, atau WEBP.";
            } elseif ($file_size > 2 * 1024 * 1024) {
                $error = "Ukuran gambar maksimal 2 MB.";
            } else {

                $upload_dir = __DIR__ . "/../uploads/";

                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $extension = strtolower(
                    pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION)
                );

                $filename = "portfolio_" . time() . "_" . uniqid() . "." . $extension;

                $new_image_path = $upload_dir . $filename;

                if (move_uploaded_file(
                    $_FILES["image"]["tmp_name"],
                    $new_image_path
                )) {
                    $new_image = $filename;
                    $uploaded_new_image = true;
                } else {
                    $error = "Gagal menyimpan gambar.";
                }
            }
        }
    }

    /* =========================
       UPDATE DATABASE
    ========================= */

    if ($error === "") {

        $stmt_update = mysqli_prepare(
            $koneksi,
            "UPDATE portfolio
             SET title = ?,
                 category = ?,
                 description = ?,
                 image = ?,
                 status = ?
             WHERE id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt_update,
            "sssssi",
            $title,
            $category,
            $description,
            $new_image,
            $status,
            $id
        );

        if (mysqli_stmt_execute($stmt_update)) {

            /* Hapus gambar lama setelah database berhasil diupdate */
            if (
                $uploaded_new_image &&
                !empty($portfolio["image"])
            ) {
                $old_image_path = __DIR__ . "/../uploads/" . $portfolio["image"];

                if (file_exists($old_image_path)) {
                    unlink($old_image_path);
                }
            }

            header("Location: portfolio.php?success=updated");
            exit;

        } else {

            /* Jika database gagal, hapus gambar baru */
            if (
                $uploaded_new_image &&
                file_exists($new_image_path)
            ) {
                unlink($new_image_path);
            }

            $error = "Gagal memperbarui portfolio.";
        }
    }

    /* Agar form tetap menampilkan input terakhir */
    $portfolio["title"] = $title;
    $portfolio["category"] = $category;
    $portfolio["description"] = $description;
    $portfolio["status"] = $status;
    $portfolio["image"] = $new_image;
}

$username = $_SESSION["admin_username"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Portfolio - CCCI Admin</title>

    <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body>

<!-- SIDEBAR -->
<aside class="sidebar">

    <div class="logo">
        <div class="logo-box">
            <img src="../assets/img/logo.png" alt="CCCI Logo">
        </div>

        <div>
            <div class="logo-title">CCCI</div>
            <div class="logo-subtitle">Admin Panel</div>
        </div>
    </div>

    <div class="menu">

        <div class="menu-title">Main Menu</div>

        <a href="dashboard.php">Dashboard</a>
        <a href="products.php">Products</a>
        <a href="portfolio.php" class="active">Portfolio</a>
        <a href="gallery.php">Gallery</a>
        <a href="management.php">
            Management
        </a>
        <a href="company_profile.php">Company Profile</a>
        <a href="inquiries.php">Inquiries</a>
        <a href="acoount.php">
            Account
        </a>

    </div>

    <div class="sidebar-bottom">

        <div class="sidebar-user">
            <strong><?= htmlspecialchars($username); ?></strong>
            <span>Administrator</span>
        </div>

        <a href="logout.php" class="logout-link">
            Logout
        </a>

    </div>

</aside>


<!-- MAIN -->
<main class="main">

    <!-- TOPBAR -->
    <header class="topbar">

        <div>
            <strong>Portfolio Management</strong>
            <span>Manage company portfolio</span>
        </div>

        <div class="topbar-user">
            <?= htmlspecialchars($username); ?>
        </div>

    </header>


    <!-- CONTENT -->
    <div class="content">

        <div class="page-header">

            <div>
                <h1>Edit Portfolio</h1>
                <p>Update portfolio information and image.</p>
            </div>

            <a href="portfolio.php" class="btn-secondary">
                Back to Portfolio
            </a>

        </div>


        <?php if ($error !== ""): ?>

            <div class="alert-error">
                <?= htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>


        <div class="form-card">

            <form method="POST" enctype="multipart/form-data">

                <div class="form-group">

                    <label for="title">
                        Portfolio Title
                    </label>

                    <input
                        type="text"
                        id="title"
                        name="title"
                        value="<?= htmlspecialchars($portfolio["title"]); ?>"
                        required
                    >

                </div>


                <div class="form-row">

                    <div class="form-group">

                        <label for="category">
                            Category
                        </label>

                        <input
                            type="text"
                            id="category"
                            name="category"
                            value="<?= htmlspecialchars($portfolio["category"] ?? ""); ?>"
                            placeholder="Example: Processing"
                        >

                    </div>


                    <div class="form-group">

                        <label for="status">
                            Status
                        </label>

                        <select id="status" name="status">

                            <option
                                value="active"
                                <?= $portfolio["status"] === "active" ? "selected" : ""; ?>
                            >
                                Active
                            </option>

                            <option
                                value="inactive"
                                <?= $portfolio["status"] === "inactive" ? "selected" : ""; ?>
                            >
                                Inactive
                            </option>

                        </select>

                    </div>

                </div>


                <div class="form-group">

                    <label for="description">
                        Description
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        rows="7"
                        placeholder="Portfolio description..."
                    ><?= htmlspecialchars($portfolio["description"] ?? ""); ?></textarea>

                </div>


                <div class="form-group">

                    <label>
                        Current Image
                    </label>

                    <?php if (!empty($portfolio["image"])): ?>

                        <div style="margin-top: 10px;">

                            <img
                                src="../uploads/<?= htmlspecialchars($portfolio["image"]); ?>"
                                alt="Current Portfolio Image"
                                style="
                                    width: 220px;
                                    height: 140px;
                                    object-fit: cover;
                                    border-radius: 12px;
                                    border: 1px solid #ddd;
                                "
                            >

                        </div>

                    <?php else: ?>

                        <div class="product-no-image">
                            No image uploaded
                        </div>

                    <?php endif; ?>

                </div>


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
                        Leave empty if you want to keep the current image.
                        Maximum 2 MB.
                    </small>

                </div>


                <div class="form-actions">

                    <a href="portfolio.php" class="btn-secondary">
                        Cancel
                    </a>

                    <button type="submit" class="btn-save">
                        Update Portfolio
                    </button>

                </div>

            </form>

        </div>

    </div>

</main>

</body>
</html>