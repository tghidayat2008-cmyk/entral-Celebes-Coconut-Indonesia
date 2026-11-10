<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . "/../config/koneksi.php";

$username = $_SESSION["admin_username"];

// Ambil data gallery
$query = mysqli_query(
    $koneksi,
    "SELECT * FROM gallery ORDER BY id DESC"
);

if (!$query) {
    die("Query gagal: " . mysqli_error($koneksi));
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Gallery - Coconut Company</title>

    <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body>

<!-- SIDEBAR -->
<aside class="sidebar">

    <div class="logo">

        <div class="logo-box">
            <img src="../assets/img/logo.png" alt="CCCI Logo">
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

        <a href="gallery.php" class="active">
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
            Gallery
        </h1>

        <div class="admin-info">

            <div class="admin-avatar">
                <?= strtoupper(substr($username, 0, 1)); ?>
            </div>

            <div class="admin-name">
                <?= htmlspecialchars($username); ?>
            </div>

        </div>

    </header>


    <!-- CONTENT -->
    <section class="content">

        <div class="page-header">

            <div>

                <h1>
                    Gallery
                </h1>

                <p>
                    Manage photos displayed on the company website.
                </p>

            </div>

            <a
                href="gallery_add.php"
                class="btn-add"
            >
                + Add Gallery
            </a>

        </div>


        <!-- GALLERY GRID -->
        <div class="gallery-grid">

            <?php if (mysqli_num_rows($query) > 0): ?>

                <?php while ($gallery = mysqli_fetch_assoc($query)): ?>

                    <div class="gallery-card">

                        <!-- IMAGE -->
                        <?php if (!empty($gallery["image"])): ?>

                            <img
                                src="../uploads/<?= htmlspecialchars($gallery["image"]); ?>"
                                class="gallery-image"
                                alt="<?= htmlspecialchars($gallery["title"]); ?>"
                            >

                        <?php else: ?>

                            <div class="gallery-no-image">
                                No Image
                            </div>

                        <?php endif; ?>


                        <!-- INFO -->
                        <div class="gallery-info">

                            <h3>
                                <?= htmlspecialchars($gallery["title"]); ?>
                            </h3>


                            <div class="gallery-category">

                                Category:

                                <?= htmlspecialchars(
                                    $gallery["category"] ?? "-"
                                ); ?>

                            </div>


                            <!-- STATUS -->
                            <?php if ($gallery["status"] === "active"): ?>

                                <span class="status active">
                                    Active
                                </span>

                            <?php else: ?>

                                <span class="status inactive">
                                    Inactive
                                </span>

                            <?php endif; ?>


                            <!-- ACTIONS -->
                            <div class="gallery-actions">

                                <a
                                    href="gallery_edit.php?id=<?= $gallery["id"]; ?>"
                                    class="gallery-edit"
                                >
                                    Edit
                                </a>

                                <a
                                    href="gallery_delete.php?id=<?= $gallery["id"]; ?>"
                                    class="gallery-delete"
                                    onclick="return confirm('Are you sure you want to delete this gallery?');"
                                >
                                    Delete
                                </a>

                            </div>

                        </div>

                    </div>

                <?php endwhile; ?>

            <?php else: ?>

                <div class="empty-data">

                    <h3>
                        No Gallery Data
                    </h3>

                    <p>
                        There are currently no gallery photos available.
                    </p>

                </div>

            <?php endif; ?>

        </div>

    </section>

</main>

</body>
</html>