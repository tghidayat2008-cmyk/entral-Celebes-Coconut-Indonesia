<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . "/../config/koneksi.php";

$username = $_SESSION["admin_username"];

/* =========================
   STATISTICS
========================= */

$query_products = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total FROM products"
);

$data_products = mysqli_fetch_assoc($query_products);


$query_gallery = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total FROM gallery"
);

$data_gallery = mysqli_fetch_assoc($query_gallery);


$query_inquiries = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total
     FROM inquiries
     WHERE status = 'new'"
);

$data_inquiries = mysqli_fetch_assoc($query_inquiries);


$query_admin = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total
     FROM users
     WHERE role = 'admin'"
);

$data_admin = mysqli_fetch_assoc($query_admin);


/* =========================
   RECENT INQUIRIES
========================= */

$query_recent_inquiries = mysqli_query(
    $koneksi,
    "SELECT
        inquiries.*,
        products.name AS product_name
     FROM inquiries
     LEFT JOIN products
        ON inquiries.product_id = products.id
     ORDER BY inquiries.id DESC
     LIMIT 5"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Dashboard - Coconut Company</title>

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


        <a
            href="dashboard.php"
            class="active"
        >
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
            Dashboard
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
                    Dashboard
                </h1>

                <p>
                    Overview of your company website and business information.
                </p>

            </div>

        </div>


        <!-- WELCOME -->

        <div class="welcome">

            <div class="eyebrow">
                CCCI ADMIN PORTAL
            </div>


            <h2>
                Welcome back,
                <?= htmlspecialchars($username); ?>
            </h2>


            <p>
                Manage your coconut company website,
                products, portfolio, gallery, company
                information, and customer inquiries
                from this dashboard.
            </p>

        </div>


        <!-- STATISTICS -->

        <div class="stats-grid">


            <!-- PRODUCTS -->

            <div class="stat-card">

                <div class="stat-top">

                    <div class="stat-label">
                        Total Products
                    </div>

                    <div class="stat-mark">
                        PR
                    </div>

                </div>


                <div class="stat-value">

                    <?= $data_products["total"]; ?>

                </div>


                <div class="stat-note">
                    Products currently stored in the system
                </div>

            </div>


            <!-- GALLERY -->

            <div class="stat-card">

                <div class="stat-top">

                    <div class="stat-label">
                        Gallery Items
                    </div>

                    <div class="stat-mark">
                        GA
                    </div>

                </div>


                <div class="stat-value">

                    <?= $data_gallery["total"]; ?>

                </div>


                <div class="stat-note">
                    Photos available in the company gallery
                </div>

            </div>


            <!-- INQUIRIES -->

            <div class="stat-card">

                <div class="stat-top">

                    <div class="stat-label">
                        New Inquiries
                    </div>

                    <div class="stat-mark">
                        IN
                    </div>

                </div>


                <div class="stat-value">

                    <?= $data_inquiries["total"]; ?>

                </div>


                <div class="stat-note">
                    Customer inquiries waiting for response
                </div>

            </div>


            <!-- ADMINS -->

            <div class="stat-card">

                <div class="stat-top">

                    <div class="stat-label">
                        Admin Users
                    </div>

                    <div class="stat-mark">
                        AD
                    </div>

                </div>


                <div class="stat-value">

                    <?= $data_admin["total"]; ?>

                </div>


                <div class="stat-note">
                    Registered administrator accounts
                </div>

            </div>


        </div>


        <!-- DASHBOARD GRID -->

        <div class="dashboard-grid">


            <!-- RECENT INQUIRIES -->

            <div class="panel">

                <div class="panel-head">

                    <div>

                        <h3>
                            Recent Inquiries
                        </h3>

                        <p>
                            Latest customer inquiries received from the website.
                        </p>

                    </div>


                    <a
                        href="inquiries.php"
                        class="action-view"
                    >
                        View All
                    </a>

                </div>


                <div class="panel-body">

                    <?php if (
                        $query_recent_inquiries &&
                        mysqli_num_rows($query_recent_inquiries) > 0
                    ): ?>


                        <div class="table-card">

                            <table>

                                <thead>

                                    <tr>

                                        <th>
                                            Customer
                                        </th>

                                        <th>
                                            Product
                                        </th>

                                        <th>
                                            Status
                                        </th>

                                        <th>
                                            Date
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>

                                    <?php while (
                                        $inquiry =
                                        mysqli_fetch_assoc(
                                            $query_recent_inquiries
                                        )
                                    ): ?>


                                        <tr>

                                            <td>

                                                <strong>
                                                    <?= htmlspecialchars(
                                                        $inquiry["full_name"]
                                                    ); ?>
                                                </strong>


                                                <div class="customer-email">

                                                    <?= htmlspecialchars(
                                                        $inquiry["email"]
                                                    ); ?>

                                                </div>

                                            </td>


                                            <td>

                                                <?= htmlspecialchars(
                                                    $inquiry["product_name"]
                                                    ?? "-"
                                                ); ?>

                                            </td>


                                            <td>

                                                <?php

                                                $status =
                                                    $inquiry["status"];

                                                $status_class =
                                                    "status-" .
                                                    $status;

                                                ?>

                                                <span
                                                    class="status <?= $status_class; ?>"
                                                >

                                                    <?= ucfirst(
                                                        $status
                                                    ); ?>

                                                </span>

                                            </td>


                                            <td>

                                                <?= date(
                                                    "d M Y",
                                                    strtotime(
                                                        $inquiry["created_at"]
                                                    )
                                                ); ?>

                                            </td>

                                        </tr>


                                    <?php endwhile; ?>

                                </tbody>

                            </table>

                        </div>


                    <?php else: ?>


                        <div class="empty-data">

                            No inquiries available.

                        </div>


                    <?php endif; ?>

                </div>

            </div>


            <!-- QUICK ACTIONS -->

            <div class="panel">

                <div class="panel-head">

                    <div>

                        <h3>
                            Quick Actions
                        </h3>

                        <p>
                            Common management tasks.
                        </p>

                    </div>

                </div>


                <div class="panel-body">


                    <div class="quick-actions">


                        <a
                            href="product_add.php"
                            class="quick-action"
                        >

                            <strong>
                                Add Product
                            </strong>

                            <span>
                                Add a new coconut product.
                            </span>

                        </a>


                        <a
                            href="portfolio_add.php"
                            class="quick-action"
                        >

                            <strong>
                                Add Portfolio
                            </strong>

                            <span>
                                Add a new business portfolio.
                            </span>

                        </a>


                        <a
                            href="gallery_add.php"
                            class="quick-action"
                        >

                            <strong>
                                Add Gallery
                            </strong>

                            <span>
                                Upload a new company photo.
                            </span>

                        </a>


                        <a
                            href="company_profile.php"
                            class="quick-action"
                        >

                            <strong>
                                Company Profile
                            </strong>

                            <span>
                                Update company information.
                            </span>

                        </a>


                    </div>

                </div>

            </div>


        </div>


    </section>

</main>


</body>

</html>