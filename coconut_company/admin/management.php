<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . "/../config/koneksi.php";

$username = $_SESSION["admin_username"];

/* =========================
   GET MANAGEMENT DATA
========================= */

$query = mysqli_query(
    $koneksi,
    "SELECT * FROM management_structure
     ORDER BY level ASC, sort_order ASC, id ASC"
);

if (!$query) {
    die("Query gagal: " . mysqli_error($koneksi));
}

$success = "";

if (isset($_GET["success"])) {
    if ($_GET["success"] === "added") {
        $success = "Data pengurus berhasil ditambahkan.";
    } elseif ($_GET["success"] === "updated") {
        $success = "Data pengurus berhasil diperbarui.";
    } elseif ($_GET["success"] === "deleted") {
        $success = "Data pengurus berhasil dihapus.";
    }
}

/* =========================
   LEVEL LABEL
========================= */

function levelLabel($level)
{
    switch ((int) $level) {
        case 1:
            return "Pimpinan";
        case 2:
            return "Manajemen";
        case 3:
            return "Operasional";
        default:
            return "Lainnya";
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

    <title>Management Structure - Coconut Company</title>

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

        <a
            href="management.php"
            class="active"
        >
            Management Structure
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
            Management Structure
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
                    Management Structure
                </h1>

                <p>
                    Manage the management structure and organizational members of CCCI.
                </p>

            </div>


            <a
                href="management_add.php"
                class="btn-add"
            >
                + Add Management
            </a>

        </div>


        <!-- SUCCESS -->

        <?php if ($success !== ""): ?>

            <div class="alert-success">
                <?= htmlspecialchars($success); ?>
            </div>

        <?php endif; ?>


        <!-- TABLE -->

        <div class="table-card">

            <table>

                <thead>

                    <tr>

                        <th>No.</th>

                        <th>Photo</th>

                        <th>Name</th>

                        <th>Position</th>

                        <th>Level</th>

                        <th>Order</th>

                        <th>Status</th>

                        <th>Action</th>

                    </tr>

                </thead>


                <tbody>

                <?php if (mysqli_num_rows($query) > 0): ?>

                    <?php $no = 1; ?>

                    <?php while ($management = mysqli_fetch_assoc($query)): ?>

                        <tr>

                            <td>
                                <?= $no++; ?>
                            </td>


                            <!-- PHOTO -->

                            <td>

                                <?php if (!empty($management["image"])): ?>

                                    <img
                                        src="../uploads/<?= htmlspecialchars($management["image"]); ?>"
                                        alt="<?= htmlspecialchars($management["name"]); ?>"
                                        style="
                                            width:60px;
                                            height:70px;
                                            object-fit:cover;
                                            border-radius:8px;
                                        "
                                    >

                                <?php else: ?>

                                    <div
                                        style="
                                            width:60px;
                                            height:70px;
                                            display:flex;
                                            align-items:center;
                                            justify-content:center;
                                            background:#eef2ef;
                                            border-radius:8px;
                                            color:#6b756f;
                                            font-size:11px;
                                        "
                                    >
                                        No Photo
                                    </div>

                                <?php endif; ?>

                            </td>


                            <!-- NAME -->

                            <td>

                                <strong>
                                    <?= htmlspecialchars($management["name"]); ?>
                                </strong>

                            </td>


                            <!-- POSITION -->

                            <td>

                                <?= htmlspecialchars($management["position"]); ?>

                            </td>


                            <!-- LEVEL -->

                            <td>

                                <?= htmlspecialchars(
                                    levelLabel($management["level"])
                                ); ?>

                            </td>


                            <!-- ORDER -->

                            <td>

                                <?= (int) $management["sort_order"]; ?>

                            </td>


                            <!-- STATUS -->

                            <td>

                                <?php if ($management["status"] === "active"): ?>

                                    <span class="status active">
                                        Active
                                    </span>

                                <?php else: ?>

                                    <span class="status inactive">
                                        Inactive
                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- ACTION -->

                            <td>

                                <div
                                    style="
                                        display:flex;
                                        gap:8px;
                                        flex-wrap:wrap;
                                    "
                                >

                                    <a
                                        href="management_edit.php?id=<?= (int) $management["id"]; ?>"
                                        class="action-edit"
                                    >
                                        Edit
                                    </a>


                                    <a
                                        href="management_delete.php?id=<?= (int) $management["id"]; ?>"
                                        class="action-delete"
                                        onclick="return confirm('Are you sure you want to delete this management member?');"
                                    >
                                        Delete
                                    </a>

                                </div>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>

                        <td
                            colspan="8"
                            style="text-align:center;"
                        >
                            No management data available.
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