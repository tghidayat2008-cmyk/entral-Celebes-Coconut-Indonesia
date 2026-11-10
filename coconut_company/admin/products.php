<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . "/../config/koneksi.php";

$username = $_SESSION["admin_username"];


/* =========================
   GET PRODUCTS
========================= */

$query = mysqli_query(
    $koneksi,
    "SELECT * FROM products ORDER BY id DESC"
);

if (!$query) {
    die("Query gagal: " . mysqli_error($koneksi));
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

    <title>Products - Coconut Company</title>

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

        <a href="account.php">
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
            Products
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
                    Products
                </h1>

                <p>
                    Manage coconut products displayed on the company website.
                </p>

            </div>


            <a
                href="product_add.php"
                class="btn-add"
            >
                + Add Product
            </a>

        </div>


        <!-- PRODUCTS -->

        <?php if (mysqli_num_rows($query) > 0): ?>


            <div class="product-grid">


                <?php while ($product = mysqli_fetch_assoc($query)): ?>


                    <div class="product-card">


                        <!-- IMAGE -->

                        <div class="product-image">

                            <?php if (!empty($product["image"])): ?>

                                <img
                                    src="../uploads/<?= htmlspecialchars($product["image"]); ?>"
                                    alt="<?= htmlspecialchars($product["name"]); ?>"
                                >

                            <?php else: ?>

                                <div class="product-no-image">
                                    No Image
                                </div>

                            <?php endif; ?>

                        </div>


                        <!-- INFO -->

                        <div class="product-info">


                            <div class="product-header">

                                <h3>

                                    <?= htmlspecialchars(
                                        $product["name"]
                                    ); ?>

                                </h3>


                                <?php if (
                                    $product["status"] === "active"
                                ): ?>

                                    <span class="status active">
                                        Active
                                    </span>

                                <?php else: ?>

                                    <span class="status inactive">
                                        Inactive
                                    </span>

                                <?php endif; ?>

                            </div>


                            <p class="product-description">

                                <?php

                                $description =
                                    $product["description"] ?? "";

                                if (strlen($description) > 120) {

                                    echo htmlspecialchars(
                                        substr($description, 0, 120)
                                    ) . "...";

                                } else {

                                    echo htmlspecialchars(
                                        $description
                                    );

                                }

                                ?>

                            </p>


                            <div class="product-meta">

                                <span>
                                    Added
                                    <?= date(
                                        "d M Y",
                                        strtotime(
                                            $product["created_at"]
                                        )
                                    ); ?>
                                </span>

                            </div>


                            <!-- ACTIONS -->

                            <div class="product-actions">


                                <a
                                    href="product_edit.php?id=<?= $product["id"]; ?>"
                                    class="action-edit"
                                >
                                    Edit
                                </a>


                                <a
                                    href="product_delete.php?id=<?= $product["id"]; ?>"
                                    class="action-delete"
                                    onclick="return confirm('Are you sure you want to delete this product?');"
                                >
                                    Delete
                                </a>


                            </div>


                        </div>


                    </div>


                <?php endwhile; ?>


            </div>


        <?php else: ?>


            <div class="empty-data">

                <h3>
                    No Products Available
                </h3>

                <p>
                    There are currently no products in the system.
                </p>


                <a
                    href="product_add.php"
                    class="btn-add"
                >
                    + Add Product
                </a>

            </div>


        <?php endif; ?>


    </section>

</main>


</body>

</html>