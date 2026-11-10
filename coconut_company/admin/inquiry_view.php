<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . "/../config/koneksi.php";

if (!isset($_GET["id"])) {
    header("Location: inquiries.php");
    exit;
}

$id = intval($_GET["id"]);

$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $status = $_POST["status"] ?? "";

    $allowed_status = [
        "new",
        "contacted",
        "quoted",
        "completed",
        "cancelled"
    ];

    if (in_array($status, $allowed_status, true)) {

        $stmt = mysqli_prepare(
            $koneksi,
            "UPDATE inquiries SET status = ? WHERE id = ?"
        );

        mysqli_stmt_bind_param($stmt, "si", $status, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $success = "Status inquiry berhasil diperbarui.";
    }
}


/* =========================
   AMBIL DATA INQUIRY
========================= */

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT 
        inquiries.*,
        products.name AS product_name
     FROM inquiries
     LEFT JOIN products
        ON inquiries.product_id = products.id
     WHERE inquiries.id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$inquiry = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$inquiry) {
    header("Location: inquiries.php");
    exit;
}


/* =========================
   DATA CUSTOMER
========================= */

$full_name    = $inquiry["full_name"] ?? "";
$company_name = $inquiry["company_name"] ?? "";
$email        = $inquiry["email"] ?? "";
$phone        = $inquiry["phone"] ?? "";
$country      = $inquiry["country"] ?? "";
$product_name = $inquiry["product_name"] ?? "General Inquiry";
$quantity     = $inquiry["quantity"] ?? "";
$message      = $inquiry["message"] ?? "";
$status       = $inquiry["status"] ?? "new";


/* =========================
   WHATSAPP
========================= */

/* =========================
   WHATSAPP
========================= */

$whatsapp_number = preg_replace("/[^0-9]/", "", $phone);

/*
   Jika nomor Indonesia ditulis:
   08123456789
   maka otomatis menjadi:
   628123456789

   Jika sudah:
   628123456789
   maka tidak diubah.

   Jika ditulis:
   +628123456789
   maka menjadi:
   628123456789
*/

if (substr($whatsapp_number, 0, 1) === "0") {

    $whatsapp_number = "62" . substr($whatsapp_number, 1);

}

$whatsapp_message = urlencode(
    "Hello " . $full_name . ", " .
    "thank you for contacting PT. Central Celebes Coconut Indonesia."
);

$whatsapp_url = "";

if (!empty($whatsapp_number)) {

    $whatsapp_url =
        "https://wa.me/" .
        $whatsapp_number .
        "?text=" .
        $whatsapp_message;

}


/* =========================
   EMAIL
========================= */

$email_subject = rawurlencode(
    "Inquiry - PT. Central Celebes Coconut Indonesia"
);

$email_body = rawurlencode(
    "Hello " . $full_name . ",\n\n" .
    "Thank you for contacting PT. Central Celebes Coconut Indonesia.\n\n" .
    "Regarding your inquiry for: " . $product_name . "\n" .
    "Quantity: " . $quantity . "\n\n" .
    "Best regards,\n" .
    "PT. Central Celebes Coconut Indonesia"
);

$email_url = "mailto:" . $email .
             "?subject=" . $email_subject .
             "&body=" . $email_body;

?>
<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Inquiry Detail | CCCI Admin</title>

    <link rel="stylesheet"
          href="../assets/css/admin.css">

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

        <div>
            <strong>CCCI</strong>
            <span>Admin Panel</span>
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

        <a href="inquiries.php" class="active">
            Inquiries
        </a>

        <a href="acoount.php">
            Account
        </a>

    </div>



</aside>


<!-- =========================
     MAIN
========================= -->

<main class="main">

    <!-- TOPBAR -->

    <header class="topbar">

        <div>
            <strong>
                Inquiry Detail
            </strong>
        </div>

        <div>
            Admin:
            <?= htmlspecialchars($_SESSION["admin_username"]); ?>
        </div>

    </header>


    <!-- CONTENT -->

    <section class="content">

        <!-- PAGE HEADER -->

        <div class="page-header">

            <div>

                <h1>
                    Inquiry Detail
                </h1>

                <p>
                    View and manage customer inquiry.
                </p>

            </div>

            <a
                href="inquiries.php"
                class="btn-secondary"
            >
                Back to Inquiries
            </a>

        </div>


        <!-- SUCCESS -->

        <?php if (!empty($success)): ?>

            <div class="alert-success">
                <?= htmlspecialchars($success); ?>
            </div>

        <?php endif; ?>


        <!-- DETAIL -->

        <div class="inquiry-detail">


            <!-- CUSTOMER INFORMATION -->

            <div class="panel">

                <div class="panel-head">

                    <h2>
                        Customer Information
                    </h2>

                </div>


                <div class="panel-body">

                    <div class="detail-item">

                        <div class="detail-label">
                            Full Name
                        </div>

                        <div class="detail-value">
                            <?= htmlspecialchars($full_name); ?>
                        </div>

                    </div>


                    <div class="detail-item">

                        <div class="detail-label">
                            Company
                        </div>

                        <div class="detail-value">

                            <?php if (!empty($company_name)): ?>

                                <?= htmlspecialchars($company_name); ?>

                            <?php else: ?>

                                -

                            <?php endif; ?>

                        </div>

                    </div>


                    <div class="detail-item">

                        <div class="detail-label">
                            Email
                        </div>

                        <div class="detail-value">
                            <?= htmlspecialchars($email); ?>
                        </div>

                    </div>


                    <div class="detail-item">

                        <div class="detail-label">
                            Phone
                        </div>

                        <div class="detail-value">

                            <?php if (!empty($phone)): ?>

                                <?= htmlspecialchars($phone); ?>

                            <?php else: ?>

                                -

                            <?php endif; ?>

                        </div>

                    </div>


                    <div class="detail-item">

                        <div class="detail-label">
                            Country
                        </div>

                        <div class="detail-value">

                            <?php if (!empty($country)): ?>

                                <?= htmlspecialchars($country); ?>

                            <?php else: ?>

                                -

                            <?php endif; ?>

                        </div>

                    </div>


                    <!-- CONTACT BUTTON -->

                    <div class="detail-item full">

                        <div class="detail-label">
                            Contact Customer
                        </div>

                        <div class="detail-value">

                            <div style="
                                display:flex;
                                gap:10px;
                                flex-wrap:wrap;
                                margin-top:8px;
                            ">

                                <?php if (!empty($whatsapp_url)): ?>

                                    <a
                                        href="<?= htmlspecialchars($whatsapp_url); ?>"
                                        target="_blank"
                                        class="btn-primary"
                                    >
                                        WhatsApp Customer
                                    </a>

                                <?php endif; ?>


                                <?php if (!empty($email)): ?>

                                    <a
                                        href="<?= htmlspecialchars($email_url); ?>"
                                        class="btn-secondary"
                                    >
                                        Email Customer
                                    </a>

                                <?php endif; ?>

                            </div>

                        </div>

                    </div>

                </div>

            </div>



            <!-- REQUEST INFORMATION -->

            <div class="panel">

                <div class="panel-head">

                    <h2>
                        Request Information
                    </h2>

                </div>


                <div class="panel-body">

                    <div class="detail-item">

                        <div class="detail-label">
                            Product
                        </div>

                        <div class="detail-value">
                            <?= htmlspecialchars($product_name); ?>
                        </div>

                    </div>


                    <div class="detail-item">

                        <div class="detail-label">
                            Quantity
                        </div>

                        <div class="detail-value">

                            <?php if (!empty($quantity)): ?>

                                <?= htmlspecialchars($quantity); ?>

                            <?php else: ?>

                                -

                            <?php endif; ?>

                        </div>

                    </div>


                    <div class="detail-item full">

                        <div class="detail-label">
                            Customer Message
                        </div>

                        <div class="message-box">

                            <?php if (!empty($message)): ?>

                                <?= htmlspecialchars($message); ?>

                            <?php else: ?>

                                No message provided.

                            <?php endif; ?>

                        </div>

                    </div>

                </div>

            </div>



            <!-- UPDATE STATUS -->

            <div class="panel">

                <div class="panel-head">

                    <h2>
                        Inquiry Status
                    </h2>

                </div>


                <div class="panel-body">

                    <form method="POST">

                        <div class="status-update">

                            <div class="form-group">

                                <label for="status">
                                    Update Status
                                </label>

                                <select
                                    name="status"
                                    id="status"
                                    required
                                >

                                    <option
                                        value="new"
                                        <?= $status === "new" ? "selected" : ""; ?>
                                    >
                                        New
                                    </option>

                                    <option
                                        value="contacted"
                                        <?= $status === "contacted" ? "selected" : ""; ?>
                                    >
                                        Contacted
                                    </option>

                                    <option
                                        value="quoted"
                                        <?= $status === "quoted" ? "selected" : ""; ?>
                                    >
                                        Quoted
                                    </option>

                                    <option
                                        value="completed"
                                        <?= $status === "completed" ? "selected" : ""; ?>
                                    >
                                        Completed
                                    </option>

                                    <option
                                        value="cancelled"
                                        <?= $status === "cancelled" ? "selected" : ""; ?>
                                    >
                                        Cancelled
                                    </option>

                                </select>

                            </div>


                            <div class="form-actions">

                                <button
                                    type="submit"
                                    class="btn-save"
                                >
                                    Update Status
                                </button>

                            </div>

                        </div>

                    </form>

                </div>

            </div>


        </div>

    </section>

</main>

</body>
</html>