<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . "/../config/koneksi.php";

$username = $_SESSION["admin_username"];

/* =========================
   AMBIL DATA INQUIRIES
========================= */

$query = mysqli_query(
    $koneksi,
    "SELECT
        inquiries.*,
        products.name AS product_name
     FROM inquiries
     LEFT JOIN products
        ON inquiries.product_id = products.id
     ORDER BY inquiries.id DESC"
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

    <title>Inquiries - Coconut Company</title>

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


        <a
            href="inquiries.php"
            class="active"
        >
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
            Inquiries
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


    <!-- =========================
         CONTENT
    ========================= -->

    <section class="content">


        <!-- PAGE HEADER -->

        <div class="page-header">

            <div>

                <h1>
                    Customer Inquiries
                </h1>

                <p>
                    Manage customer quotation requests.
                </p>

            </div>

        </div>


        <!-- =========================
             TABLE
        ========================= -->

        <div class="table-card">

            <table>

                <thead>

                    <tr>

                        <th>
                            No.
                        </th>

                        <th>
                            Customer
                        </th>

                        <th>
                            Company
                        </th>

                        <th>
                            Country
                        </th>

                        <th>
                            Product
                        </th>

                        <th>
                            Quantity
                        </th>

                        <th>
                            Status
                        </th>

                        <th>
                            Date
                        </th>

                        <th>
                            Action
                        </th>

                    </tr>

                </thead>


                <tbody>


                <?php if (mysqli_num_rows($query) > 0): ?>

                    <?php

                    $no = 1;

                    while (
                        $inquiry =
                        mysqli_fetch_assoc($query)
                    ):

                    ?>

                    <tr>


                        <!-- NO -->

                        <td>

                            <?= $no++; ?>

                        </td>


                        <!-- CUSTOMER -->

                        <td>

                            <div class="customer-name">

                                <?= htmlspecialchars(
                                    $inquiry["full_name"]
                                ); ?>

                            </div>


                            <div class="customer-email">

                                <?= htmlspecialchars(
                                    $inquiry["email"]
                                ); ?>

                            </div>

                        </td>


                        <!-- COMPANY -->

                        <td>

                            <?= htmlspecialchars(
                                $inquiry["company_name"]
                                ?: "-"
                            ); ?>

                        </td>


                        <!-- COUNTRY -->

                        <td>

                            <?= htmlspecialchars(
                                $inquiry["country"]
                                ?: "-"
                            ); ?>

                        </td>


                        <!-- PRODUCT -->

                        <td>

                            <?= htmlspecialchars(
                                $inquiry["product_name"]
                                ?: "-"
                            ); ?>

                        </td>


                        <!-- QUANTITY -->

                        <td>

                            <?= htmlspecialchars(
                                $inquiry["quantity"]
                                ?: "-"
                            ); ?>

                        </td>


                        <!-- STATUS -->

                        <td>

                            <?php

                            $status_class =
                                "status-" .
                                $inquiry["status"];

                            ?>

                            <span
                                class="status <?= $status_class; ?>"
                            >

                                <?= ucfirst(
                                    $inquiry["status"]
                                ); ?>

                            </span>

                        </td>


                        <!-- DATE -->

                        <td>

                            <?= date(
                                "d M Y",
                                strtotime(
                                    $inquiry["created_at"]
                                )
                            ); ?>

                        </td>


                        <!-- ACTION -->

                        <td>

                            <a
                                href="inquiry_view.php?id=<?= $inquiry["id"]; ?>"
                                class="action-view"
                            >
                                View
                            </a>


                            <a
                                href="inquiry_delete.php?id=<?= $inquiry["id"]; ?>"
                                class="action-delete"
                                onclick="return confirm('Apakah Anda yakin ingin menghapus inquiry ini?');"
                            >
                                Delete
                            </a>

                        </td>


                    </tr>


                    <?php endwhile; ?>


                <?php else: ?>


                    <tr>

                        <td
                            colspan="9"
                            class="empty-data"
                        >

                            No customer inquiries yet.

                        </td>

                    </tr>


                <?php endif; ?>


                </tbody>

            </table>

        </div>


    </section>

</main>


</body>

</html>