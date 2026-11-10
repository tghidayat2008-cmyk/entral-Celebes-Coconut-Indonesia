<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . "/../config/koneksi.php";

$username = $_SESSION["admin_username"];


/* =========================
   GET PORTFOLIO DATA
========================= */

$query = mysqli_query(
    $koneksi,
    "SELECT *
     FROM portfolio
     ORDER BY id DESC"
);

$portfolio = [];

if ($query) {
    while ($row = mysqli_fetch_assoc($query)) {
        $portfolio[] = $row;
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
        Portfolio - Coconut Company
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/admin.css"
    >


    <style>

        /* =========================
           PORTFOLIO
        ========================= */

        .portfolio-grid {

            display: grid;

            grid-template-columns:
                repeat(3, minmax(0, 1fr));

            gap: 18px;

        }


        .portfolio-card {

            background: #ffffff;

            border: 1px solid var(--line);

            border-radius: 15px;

            overflow: hidden;

            box-shadow: var(--shadow-sm);

            transition: 0.2s ease;

        }


        .portfolio-card:hover {

            transform: translateY(-3px);

            box-shadow: var(--shadow);

        }


        .portfolio-image {

            width: 100%;

            height: 210px;

            object-fit: cover;

            background: #EEF2EE;

        }


        .portfolio-no-image {

            width: 100%;

            height: 210px;

            display: grid;

            place-items: center;

            background:
                linear-gradient(
                    145deg,
                    #EEF3EE,
                    #E3E9E3
                );

            color: #819087;

            font-size: 12px;

        }


        .portfolio-info {

            padding: 18px;

        }


        .portfolio-info h3 {

            margin: 0 0 5px;

            color: var(--ink);

            font-size: 15px;

            line-height: 1.4;

        }


        .portfolio-category {

            margin-bottom: 10px;

            color: var(--green);

            font-size: 11px;

            font-weight: 700;

            text-transform: uppercase;

            letter-spacing: 0.5px;

        }


        .portfolio-description {

            min-height: 58px;

            color: var(--muted);

            font-size: 12px;

            line-height: 1.6;

        }


        .portfolio-meta {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 10px;

            margin-top: 14px;

            padding-top: 13px;

            border-top: 1px solid var(--line);

        }


        .portfolio-actions {

            display: flex;

            gap: 6px;

            margin-top: 14px;

        }


        @media (max-width: 1200px) {

            .portfolio-grid {

                grid-template-columns:
                    repeat(2, minmax(0, 1fr));

            }

        }


        @media (max-width: 700px) {

            .portfolio-grid {

                grid-template-columns: 1fr;

            }

        }

    </style>

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


        <a href="products.php">
            Products
        </a>

        <a
            href="portfolio.php"
            class="active"
        >
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
            Portfolio
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
                    Portfolio
                </h1>

                <p>
                    Manage company projects, activities, and business portfolio.
                </p>

            </div>


            <a
                href="portfolio_add.php"
                class="btn-add"
            >
                Add Portfolio
            </a>

        </div>


        <!-- =========================
             PORTFOLIO GRID
        ========================= -->

        <?php if (!empty($portfolio)): ?>


            <div class="portfolio-grid">


                <?php foreach ($portfolio as $item): ?>


                    <div class="portfolio-card">


                        <!-- IMAGE -->

                        <?php if (
                            !empty($item["image"]) &&
                            file_exists(
                                __DIR__ . "/../uploads/" . $item["image"]
                            )
                        ): ?>

                            <img
                                src="../uploads/<?= htmlspecialchars(
                                    $item["image"]
                                ); ?>"
                                alt="<?= htmlspecialchars(
                                    $item["title"]
                                ); ?>"
                                class="portfolio-image"
                            >

                        <?php else: ?>

                            <div class="portfolio-no-image">
                                No Image
                            </div>

                        <?php endif; ?>


                        <!-- INFO -->

                        <div class="portfolio-info">


                            <h3>

                                <?= htmlspecialchars(
                                    $item["title"]
                                ); ?>

                            </h3>


                            <?php if (
                                !empty($item["category"])
                            ): ?>

                                <div class="portfolio-category">

                                    <?= htmlspecialchars(
                                        $item["category"]
                                    ); ?>

                                </div>

                            <?php endif; ?>


                            <div class="portfolio-description">

                                <?php

                                $description =
                                    trim(
                                        $item["description"] ?? ""
                                    );

                                if ($description !== "") {

                                    if (
                                        strlen($description) > 130
                                    ) {

                                        echo htmlspecialchars(
                                            substr(
                                                $description,
                                                0,
                                                130
                                            )
                                        ) . "...";

                                    } else {

                                        echo htmlspecialchars(
                                            $description
                                        );

                                    }

                                } else {

                                    echo "No description.";

                                }

                                ?>

                            </div>


                            <!-- META -->

                            <div class="portfolio-meta">


                                <?php if (
                                    $item["status"] === "active"
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


                            <!-- ACTIONS -->

                            <div class="portfolio-actions">


                                <a
                                    href="portfolio_edit.php?id=<?= (int)$item["id"]; ?>"
                                    class="action-edit"
                                >
                                    Edit
                                </a>


                                <a
                                    href="portfolio_delete.php?id=<?= (int)$item["id"]; ?>"
                                    class="action-delete"
                                    onclick="return confirm(
                                        'Are you sure you want to delete this portfolio?'
                                    );"
                                >
                                    Delete
                                </a>


                            </div>


                        </div>


                    </div>


                <?php endforeach; ?>


            </div>


        <?php else: ?>


            <!-- EMPTY -->

            <div class="table-card">

                <div class="empty-data">

                    <p>
                        No portfolio data available.
                    </p>

                    <br>

                    <a
                        href="portfolio_add.php"
                        class="btn-add"
                    >
                        Add Your First Portfolio
                    </a>

                </div>

            </div>


        <?php endif; ?>


    </section>

</main>


</body>
</html>