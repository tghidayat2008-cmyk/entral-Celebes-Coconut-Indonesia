<?php
require_once __DIR__ . "/config/koneksi.php";

/*
|--------------------------------------------------------------------------
| PT. CENTRAL CELEBES COCONUT INDONESIA (CCCI)
| Public Company Website
|--------------------------------------------------------------------------
| Data dinamis:
| - company_profile
| - products
| - gallery
| - inquiries
|
| Konten perusahaan disusun berdasarkan Company Profile CCCI yang diberikan.
|--------------------------------------------------------------------------
*/

$success = "";
$error = "";

/* =========================
   COMPANY PROFILE
========================= */
$query_company = mysqli_query(
    $koneksi,
    "SELECT * FROM company_profile ORDER BY id ASC LIMIT 1"
);

$company = mysqli_fetch_assoc($query_company);

$company_name = $company["company_name"] ?? "PT. Central Celebes Coconut Indonesia";
$company_description = $company["description"] ?? "Perusahaan yang bergerak di bidang perdagangan buah kelapa biji dan pengolahan sabut kelapa, dengan komitmen terhadap kualitas, keberlanjutan, kemitraan, dan pemberdayaan masyarakat.";
$company_address = $company["address"] ?? "Desa Ipi, Kecamatan Bungku Tengah, Kabupaten Morowali, Sulawesi Tengah, Indonesia";
$company_email = $company["email"] ?? "ptcentralcelebescoconutindonesia@gmail.com";
$company_phone = $company["phone"] ?? "081140905151";
$company_whatsapp = $company["whatsapp"] ?? "082259591191";
$company_hours = $company["operating_hours"] ?? "Senin - Sabtu";

/* =========================
   GALLERY
========================= */
$query_gallery = mysqli_query(
    $koneksi,
    "SELECT * FROM gallery
     WHERE status = 'active'
     ORDER BY id DESC"
);

$gallery_items = [];

if ($query_gallery) {
    while ($row = mysqli_fetch_assoc($query_gallery)) {
        $gallery_items[] = $row;
    }
}

/* =========================
   PRODUCTS
========================= */
$query_products = mysqli_query(
    $koneksi,
    "SELECT * FROM products
     WHERE status = 'active'
     ORDER BY id DESC"
);

$products = [];

if ($query_products) {
    while ($row = mysqli_fetch_assoc($query_products)) {
        $products[] = $row;
    }
}

/*
| Produk yang secara eksplisit didukung oleh Company Profile:
| 1. Buah kelapa biji tanpa sabut
| 2. Sabut kelapa / produk olahan sabut kelapa
|
| Jika admin sudah memasukkan data products, data database akan ditampilkan.
*/
if (count($products) === 0) {
    $products = [
        [
            "id" => "",
            "name" => "Buah Kelapa Biji Tanpa Sabut",
            "description" => "Pasokan buah kelapa biji tanpa sabut untuk kebutuhan industri dengan pengadaan dari jaringan petani dan pengepul lokal.",
            "image" => ""
        ],
        [
            "id" => "",
            "name" => "Sabut Kelapa",
            "description" => "Komoditas sabut kelapa yang dikumpulkan, disortir, dan dipersiapkan untuk proses pengolahan menjadi produk bernilai tambah.",
            "image" => ""
        ]
    ];
}

/* =========================
   INQUIRY FORM
========================= */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["submit_inquiry"])) {

    $full_name = trim($_POST["full_name"] ?? "");
    $customer_company = trim($_POST["company_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $country = trim($_POST["country"] ?? "");
    $product_id = trim($_POST["product_id"] ?? "");
    $quantity = trim($_POST["quantity"] ?? "");
    $message = trim($_POST["message"] ?? "");

    if ($full_name === "" || $email === "") {
        $error = "Nama lengkap dan email wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } else {

        $product_id_value = ($product_id !== "" && ctype_digit($product_id))
            ? (int) $product_id
            : null;

        $stmt = mysqli_prepare(
            $koneksi,
            "INSERT INTO inquiries
            (full_name, company_name, email, phone, country, product_id, quantity, message, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'new')"
        );

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "sssssiss",
                $full_name,
                $customer_company,
                $email,
                $phone,
                $country,
                $product_id_value,
                $quantity,
                $message
            );

            if (mysqli_stmt_execute($stmt)) {
                $success = "Inquiry berhasil dikirim. Tim CCCI akan menghubungi Anda.";
                $_POST = [];
            } else {
                $error = "Inquiry gagal dikirim. Silakan coba kembali.";
            }

            mysqli_stmt_close($stmt);

        } else {
            $error = "Sistem sedang mengalami kendala. Silakan coba kembali.";
        }
    }
}

/* =========================
   HERO IMAGE
========================= */
$hero_image = "";

foreach ($gallery_items as $item) {
    if (!empty($item["image"])) {
        $hero_image = "uploads/" . $item["image"];
        break;
    }
}

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

function whatsapp_number($number)
{
    return preg_replace("/[^0-9]/", "", (string) $number);
}

$wa_number = whatsapp_number($company_whatsapp);
$wa_link = $wa_number !== ""
    ? "https://wa.me/" . $wa_number
    : "#contact";
?>
<?php
/* =========================
   IMAGE HELPERS (dari gallery database, folder uploads/)
========================= */
$gallery_pics = [];

foreach ($gallery_items as $item) {
    if (!empty($item["image"])) {
        $gallery_pics[] = "uploads/" . $item["image"];
    }
}

function pic($pics, $i)
{
    return count($pics) > 0 ? $pics[$i % count($pics)] : "";
}

$about_image = pic($gallery_pics, 1);
$sustain_image = pic($gallery_pics, 2);
$cta_image = pic($gallery_pics, 3);
$gallery_count_class = count($gallery_items) === 1 ? "n1" : (count($gallery_items) === 2 ? "n2" : "");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= e($company_name); ?> | Coconut Indonesia</title>

    <meta name="description" content="<?= e($company_description); ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --green: #0F5132;
            --deep: #123C2A;
            --off: #F7F5EF;
            --cream: #EEE9DD;
            --ink: #1D2521;
            --white: #FFFFFF;
            --husk: #B08A57;
            --muted: #5d6a62;
            --line: rgba(29, 37, 33, .14);
            --shadow: 0 24px 60px rgba(18, 60, 42, .14);
            --wrap: 1240px;
            --serif: "Playfair Display", Georgia, "Times New Roman", serif;
            --sans: "Manrope", "Segoe UI", Arial, sans-serif;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; scroll-padding-top: 72px; }
        body {
            font-family: var(--sans);
            background: var(--off);
            color: var(--ink);
            line-height: 1.7;
            overflow-x: hidden;
            overflow-x: clip;
            -webkit-font-smoothing: antialiased;
        }
        a { color: inherit; text-decoration: none; }
        img { display: block; max-width: 100%; }
        button, input, select, textarea { font: inherit; }
        :focus-visible { outline: 2px solid var(--husk); outline-offset: 3px; }

        .wrap { width: min(var(--wrap), 100% - 48px); margin: 0 auto; }
        h1, h2, h3 { font-family: var(--serif); font-weight: 500; line-height: 1.1; letter-spacing: -.01em; }
        h2 { font-size: clamp(2rem, 4.6vw, 3.9rem); }
        p { max-width: 62ch; }
        .label {
            display: inline-block;
            font-size: .74rem;
            font-weight: 700;
            letter-spacing: .18em;
            text-transform: uppercase;
            color: var(--husk);
            margin-bottom: 20px;
        }
        section { position: relative; }
        .pad { padding: clamp(72px, 10vw, 144px) 0; }

        /* BUTTONS */
        .btn {
            display: inline-flex; align-items: center; justify-content: center;
            min-height: 50px; padding: 0 30px;
            font-weight: 700; font-size: .95rem; letter-spacing: .01em;
            border: 1px solid transparent; border-radius: 3px; cursor: pointer;
            transition: background .25s, color .25s, border-color .25s, transform .25s;
        }
        .btn:hover { transform: translateY(-2px); }
        .btn-solid { background: var(--husk); color: var(--deep); }
        .btn-solid:hover { background: #c79d66; }
        .btn-green { background: var(--green); color: var(--white); }
        .btn-green:hover { background: var(--deep); }
        .btn-ghost { border-color: rgba(255, 255, 255, .55); color: var(--white); }
        .btn-ghost:hover { background: var(--white); color: var(--deep); }

        /* HEADER */
        header {
            position: fixed; inset: 0 0 auto 0; z-index: 50;
            color: var(--white);
            transition: background .3s, box-shadow .3s, color .3s;
        }
        header.scrolled, header.menu-open {
            background: rgba(247, 245, 239, .97);
            color: var(--ink);
            box-shadow: 0 1px 0 var(--line);
            backdrop-filter: blur(8px);
        }
        .nav { display: flex; align-items: center; justify-content: space-between; height: 76px; gap: 24px; transition: height .3s; }
        header.scrolled .nav { height: 66px; }
        .logo { display: flex; align-items: center; gap: 12px; flex-shrink: 0; }
        .logo-mark { background: var(--off); border-radius: 4px; padding: 4px; }
        .logo-mark img { height: 40px; width: auto; }
        .logo-name { font-size: .7rem; font-weight: 700; line-height: 1.3; letter-spacing: .08em; text-transform: uppercase; }
        .links { display: flex; align-items: center; gap: 30px; }
        .links a:not(.nav-quote) { font-size: .9rem; font-weight: 600; opacity: .9; position: relative; }
        .links a:not(.nav-quote)::after {
            content: ""; position: absolute; left: 0; bottom: -6px; width: 0; height: 1px;
            background: currentColor; transition: width .25s;
        }
        .links a:not(.nav-quote):hover::after { width: 100%; }
        .nav-quote { background: var(--husk); color: var(--deep); font-weight: 700; font-size: .88rem; padding: 12px 22px; border-radius: 3px; transition: background .25s; }
        .nav-quote:hover { background: #c79d66; }
        .burger { display: none; width: 46px; height: 46px; background: none; border: 0; color: inherit; cursor: pointer; position: relative; }
        .burger span { position: absolute; left: 11px; right: 11px; height: 2px; background: currentColor; transition: transform .25s, opacity .25s; }
        .burger span:nth-child(1) { top: 16px; }
        .burger span:nth-child(2) { top: 22px; }
        .burger span:nth-child(3) { top: 28px; }
        .burger.open span:nth-child(1) { transform: translateY(6px) rotate(45deg); }
        .burger.open span:nth-child(2) { opacity: 0; }
        .burger.open span:nth-child(3) { transform: translateY(-6px) rotate(-45deg); }
        .mobile-menu { display: none; }

        /* HERO */
        .hero {
            min-height: 100vh; min-height: 100svh;
            display: flex; flex-direction: column; justify-content: flex-end;
            color: var(--white);
            background-color: var(--deep);
            background-image: linear-gradient(180deg, rgba(18, 60, 42, .5) 0%, rgba(18, 40, 30, .38) 40%, rgba(14, 30, 22, .88) 100%), var(--img, none);
            background-size: cover; background-position: center;
        }
        .hero-inner { padding: 140px 0 48px; }
        .hero .label { color: #e2c48f; }
        .hero h1 { font-size: clamp(2.5rem, 8.6vw, 7.4rem); font-weight: 500; line-height: .98; letter-spacing: -.025em; max-width: 12ch; overflow-wrap: break-word; }
        .hero-sub { margin-top: 28px; font-size: clamp(1.02rem, 1.7vw, 1.25rem); max-width: 46ch; color: rgba(255, 255, 255, .88); }
        .hero-actions { display: flex; flex-wrap: wrap; gap: 14px; margin-top: 38px; }
        .hero-foot { display: flex; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-top: 72px; padding-top: 22px; border-top: 1px solid rgba(255, 255, 255, .28); font-size: .88rem; font-weight: 600; letter-spacing: .04em; }
        .hero-load > * { animation: rise .9s cubic-bezier(.2, .7, .2, 1) both; }
        .hero-load > *:nth-child(2) { animation-delay: .12s; }
        .hero-load > *:nth-child(3) { animation-delay: .24s; }
        .hero-load > *:nth-child(4) { animation-delay: .36s; }
        @keyframes rise { from { opacity: 0; transform: translateY(28px); } to { opacity: 1; transform: none; } }

        /* TRUST STRIP */
        .strip { background: var(--deep); color: var(--white); }
        .strip-in { display: grid; grid-template-columns: repeat(4, 1fr); }
        .strip-in div { padding: 30px 24px; border-left: 1px solid rgba(255, 255, 255, .16); font-family: var(--serif); font-size: clamp(1.05rem, 1.7vw, 1.4rem); line-height: 1.25; }
        .strip-in div:first-child { border-left: 0; padding-left: 0; }

        /* ABOUT */
        .about-grid { display: grid; grid-template-columns: 1.05fr 1fr; gap: clamp(40px, 7vw, 110px); align-items: center; }
        .about-media { position: relative; padding-bottom: 60px; }
        .about-photo {
            aspect-ratio: 4 / 5; width: 88%;
            background: var(--green) center / cover no-repeat;
            overflow: hidden;
        }
        .about-photo.empty { background: linear-gradient(150deg, var(--green), var(--deep)); }
        .about-note {
            position: absolute; right: 0; bottom: 0; width: min(330px, 70%);
            background: var(--cream); padding: 28px; box-shadow: var(--shadow);
        }
        .about-note strong { display: block; font-family: var(--serif); font-size: 2.6rem; line-height: 1; color: var(--green); font-weight: 500; }
        .about-note span { display: block; margin-top: 10px; font-size: .9rem; color: var(--muted); line-height: 1.55; }
        .about-text h2 { margin-bottom: 30px; }
        .about-text p { color: var(--muted); margin-bottom: 18px; }
        .about-text p:first-of-type { color: var(--ink); font-size: 1.08rem; }
        .vm { margin-top: 36px; display: grid; grid-template-columns: 1fr 1fr; gap: 28px; padding-top: 28px; border-top: 1px solid var(--line); }
        .vm h3 { font-size: 1.25rem; margin-bottom: 8px; color: var(--green); }
        .vm p { font-size: .9rem; margin: 0; }

        /* VALUES */
        .values { background: var(--cream); }
        .values-head { display: flex; justify-content: space-between; align-items: end; gap: 32px; margin-bottom: 56px; flex-wrap: wrap; }
        .values-list { display: grid; grid-template-columns: repeat(4, 1fr); border-top: 2px solid var(--green); }
        .value { padding: 32px 28px 8px 0; }
        .value + .value { padding-left: 28px; border-left: 1px solid var(--line); }
        .value h3 { font-size: 1.7rem; margin-bottom: 12px; color: var(--green); }
        .value p { font-size: .95rem; color: var(--muted); }

        /* SOURCING */
        .sourcing { background: var(--off); }
        .src-grid { display: grid; grid-template-columns: .9fr 1.1fr; gap: clamp(40px, 7vw, 110px); }
        .src-grid > div:first-child { position: sticky; top: 110px; align-self: start; }
        .src-grid h2 { margin-bottom: 26px; }
        .src-grid p { color: var(--muted); }
        .chain { list-style: none; margin-top: 40px; }
        .chain li { position: relative; padding: 0 0 22px 34px; font-family: var(--serif); font-size: 1.35rem; }
        .chain li::before { content: ""; position: absolute; left: 4px; top: 12px; width: 9px; height: 9px; border-radius: 50%; background: var(--husk); }
        .chain li::after { content: ""; position: absolute; left: 8px; top: 24px; bottom: 0; width: 1px; background: var(--line); }
        .chain li:last-child::after { display: none; }
        .zones { border-top: 1px solid var(--line); }
        .zone { display: grid; grid-template-columns: 1fr auto; gap: 8px 28px; padding: 34px 0; border-bottom: 1px solid var(--line); }
        .zone small { grid-column: 1 / -1; font-size: .78rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: var(--husk); }
        .zone h3 { font-size: clamp(1.7rem, 3vw, 2.5rem); }
        .zone strong { font-family: var(--serif); font-size: 1.5rem; font-weight: 500; color: var(--green); text-align: right; white-space: nowrap; }
        .zone strong span { display: block; font-family: var(--sans); font-size: .75rem; font-weight: 600; color: var(--muted); }
        .zone p { grid-column: 1 / -1; font-size: .95rem; color: var(--muted); }

        /* PRODUCTS */
        .products { background: var(--cream); }
        .products-head { display: flex; justify-content: space-between; align-items: end; gap: 32px; flex-wrap: wrap; margin-bottom: 56px; }
        .products-head p { color: var(--muted); }
        .pgrid { display: grid; grid-template-columns: repeat(12, 1fr); gap: 28px; }
        .pc { display: flex; flex-direction: column; background: var(--white); box-shadow: 0 10px 30px rgba(18, 60, 42, .07); overflow: hidden; }
        .pc-0 { grid-column: span 7; }
        .pc-1 { grid-column: span 5; }
        .pc-2 { grid-column: span 12; flex-direction: row; }
        .pc-img { flex: 1; min-height: 280px; overflow: hidden; background: linear-gradient(150deg, var(--green), var(--deep)); position: relative; }
        .pc-2 .pc-img { flex: 1.2; }
        .pc-img img { width: 100%; height: 100%; object-fit: cover; position: absolute; inset: 0; transition: transform .8s cubic-bezier(.2, .7, .2, 1); }
        .pc:hover .pc-img img { transform: scale(1.05); }
        .pc-img.empty img { width: 90px; height: auto; object-fit: contain; inset: 50% auto auto 50%; transform: translate(-50%, -50%); opacity: .35; }
        .pc:hover .pc-img.empty img { transform: translate(-50%, -50%); }
        .pc-body { padding: 32px 34px 36px; }
        .pc-2 .pc-body { flex: 1; display: flex; flex-direction: column; justify-content: center; padding: 48px; }
        .pc-body h3 { font-size: clamp(1.5rem, 2.4vw, 2.1rem); margin-bottom: 12px; }
        .pc-body p { color: var(--muted); font-size: .96rem; margin-bottom: 24px; }
        .product-link { align-self: flex-start; font-weight: 700; font-size: .92rem; color: var(--green); padding-bottom: 4px; border-bottom: 2px solid var(--husk); transition: color .2s, border-color .2s; }
        .product-link:hover { color: var(--deep); border-color: var(--green); }

        /* PRODUCT CTA */
        .pcta { background: var(--green); color: var(--white); padding: clamp(56px, 7vw, 88px) 0; }
        .pcta-in { display: flex; justify-content: space-between; align-items: center; gap: 36px; flex-wrap: wrap; }
        .pcta h2 { font-size: clamp(1.7rem, 3.4vw, 2.7rem); max-width: 18ch; }
        .pcta p { margin-top: 14px; color: rgba(255, 255, 255, .8); }

        /* PROCESS */
        .process-head { max-width: 760px; margin-bottom: 72px; }
        .steps { list-style: none; display: grid; grid-template-columns: repeat(5, 1fr); gap: 0 28px; position: relative; }
        .steps::before { content: ""; position: absolute; left: 0; right: 0; top: 11px; height: 1px; background: var(--green); opacity: .35; }
        .step { position: relative; padding-top: 44px; }
        .step::before { content: ""; position: absolute; top: 5px; left: 0; width: 13px; height: 13px; background: var(--off); border: 2px solid var(--green); border-radius: 50%; }
        .step .n { font-family: var(--serif); font-size: 3.4rem; line-height: 1; color: var(--husk); display: block; margin-bottom: 14px; }
        .step h3 { font-size: 1.4rem; margin-bottom: 10px; }
        .step p { font-size: .92rem; color: var(--muted); }

        /* CAPABILITY */
        .cap { background: var(--deep); color: var(--white); overflow: hidden; }
        .cap-grid { display: grid; grid-template-columns: 1.2fr 1fr; gap: clamp(40px, 7vw, 100px); align-items: end; }
        .cap-big { font-family: var(--serif); font-size: clamp(5rem, 15vw, 12.5rem); line-height: .9; letter-spacing: -.04em; color: #e2c48f; }
        .cap-big + p { margin-top: 18px; font-size: 1.15rem; color: rgba(255, 255, 255, .85); }
        .cap-facts { display: flex; gap: 48px; margin-top: 44px; flex-wrap: wrap; }
        .cap-facts strong { display: block; font-family: var(--serif); font-size: 2rem; font-weight: 500; }
        .cap-facts span { font-size: .85rem; color: rgba(255, 255, 255, .7); }
        .cap-list { list-style: none; }
        .cap-list li { display: flex; justify-content: space-between; gap: 16px; padding: 16px 0; border-bottom: 1px solid rgba(255, 255, 255, .18); }
        .cap-list li:first-child { border-top: 1px solid rgba(255, 255, 255, .18); }
        .cap-list b { font-family: var(--serif); font-weight: 500; font-size: 1.35rem; }
        .cap-list span { color: rgba(255, 255, 255, .75); align-self: center; }

        /* SUSTAINABILITY */
        .sus {
            color: var(--white);
            background-color: var(--deep);
            background-image: linear-gradient(90deg, rgba(18, 60, 42, .94) 0%, rgba(18, 60, 42, .72) 60%, rgba(18, 60, 42, .5) 100%), var(--img, none);
            background-size: cover; background-position: center; background-attachment: scroll;
        }
        .sus h2 { max-width: 14ch; margin-bottom: 64px; font-size: clamp(2.2rem, 5.4vw, 4.6rem); }
        .sus-pts { display: grid; grid-template-columns: 1fr 1fr; gap: 0 clamp(32px, 6vw, 96px); max-width: 980px; }
        .sus-pt { padding: 26px 0; border-top: 1px solid rgba(255, 255, 255, .32); }
        .sus-pt h3 { font-size: 1.55rem; margin-bottom: 8px; }
        .sus-pt p { font-size: .96rem; color: rgba(255, 255, 255, .85); }

        /* GALLERY */
        .gal-head { display: flex; justify-content: space-between; align-items: end; gap: 32px; flex-wrap: wrap; margin-bottom: 52px; }
        .gal-head p { color: var(--muted); }
        .gal { column-count: 3; column-gap: 20px; }
        .gal.n1 { column-count: 1; }
        .gal.n2 { column-count: 2; }
        .gi { position: relative; break-inside: avoid; margin-bottom: 20px; overflow: hidden; background: var(--cream); }
        .gi img { width: 100%; height: 100%; object-fit: cover; transition: transform .8s cubic-bezier(.2, .7, .2, 1); }
        .gi:hover img { transform: scale(1.05); }
        .gi:nth-child(6n+1) { aspect-ratio: 3 / 4; }
        .gi:nth-child(6n+2) { aspect-ratio: 1 / 1; }
        .gi:nth-child(6n+3) { aspect-ratio: 4 / 3; }
        .gi:nth-child(6n+4) { aspect-ratio: 1 / 1; }
        .gi:nth-child(6n+5) { aspect-ratio: 3 / 4; }
        .gi:nth-child(6n+6) { aspect-ratio: 4 / 3; }
        .gal.n1 .gi { aspect-ratio: 16 / 9; }
        .gi figcaption { position: absolute; inset: auto 0 0 0; padding: 40px 20px 16px; color: var(--white); background: linear-gradient(0deg, rgba(14, 30, 22, .75), transparent); font-weight: 600; font-size: .92rem; line-height: 1.4; }
        .gi figcaption small { display: block; font-weight: 500; opacity: .8; }
        .gal-empty { border-top: 1px solid var(--line); padding-top: 28px; color: var(--muted); }

        /* WHY */
        .why { background: var(--cream); }
        .why-grid { display: grid; grid-template-columns: .8fr 1.2fr; gap: clamp(40px, 7vw, 110px); }
        .why-grid > div:first-child { position: sticky; top: 110px; align-self: start; }
        .why-row { display: grid; grid-template-columns: 1fr 1.1fr; gap: 24px; align-items: baseline; padding: 30px 0; border-top: 1px solid var(--line); }
        .why-row:last-child { border-bottom: 1px solid var(--line); }
        .why-row h3 { font-size: clamp(1.5rem, 2.8vw, 2.2rem); color: var(--green); }
        .why-row p { font-size: .95rem; color: var(--muted); }

        /* CONTACT */
        .contact-grid { display: grid; grid-template-columns: .85fr 1.15fr; box-shadow: var(--shadow); }
        .contact-info { background: var(--deep); color: var(--white); padding: clamp(32px, 4.5vw, 64px); }
        .contact-info h2 { font-size: clamp(1.8rem, 3vw, 2.5rem); margin-bottom: 18px; }
        .contact-info > p { color: rgba(255, 255, 255, .78); margin-bottom: 36px; font-size: .96rem; }
        .ci { padding: 16px 0; border-top: 1px solid rgba(255, 255, 255, .18); overflow-wrap: anywhere; }
        .ci small { display: block; font-size: .74rem; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; color: #e2c48f; margin-bottom: 4px; }
        .ci a:hover { text-decoration: underline; }
        .form-panel { background: var(--white); padding: clamp(32px, 4.5vw, 64px); }
        .form-panel h3 { font-size: clamp(1.7rem, 3vw, 2.3rem); margin-bottom: 8px; }
        .form-panel > p { color: var(--muted); margin-bottom: 28px; font-size: .95rem; }
        .alert { padding: 14px 18px; margin-bottom: 22px; font-weight: 600; font-size: .92rem; border-left: 4px solid; }
        .alert.success { background: #e6f2ea; color: var(--green); border-color: var(--green); }
        .alert.error { background: #f8e8e6; color: #9b2c2c; border-color: #9b2c2c; }
        .fgrid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px 22px; }
        .fg.full { grid-column: 1 / -1; }
        .fg label { display: block; font-size: .82rem; font-weight: 700; margin-bottom: 6px; }
        .fg input, .fg select, .fg textarea {
            width: 100%; min-height: 50px; padding: 12px 14px;
            background: var(--off); color: var(--ink);
            border: 1px solid var(--line); border-radius: 3px;
            transition: border-color .2s, background .2s;
        }
        .fg textarea { min-height: 130px; resize: vertical; }
        .fg input:focus, .fg select:focus, .fg textarea:focus { outline: none; border-color: var(--green); background: var(--white); }

        /* FINAL CTA */
        .final {
            color: var(--white); text-align: center;
            background-color: var(--green);
            background-image: linear-gradient(rgba(15, 81, 50, .9), rgba(18, 60, 42, .94)), var(--img, none);
            background-size: cover; background-position: center;
        }
        .final h2 { font-size: clamp(2.2rem, 6vw, 5rem); max-width: 16ch; margin: 0 auto 22px; }
        .final p { margin: 0 auto 38px; color: rgba(255, 255, 255, .85); font-size: 1.12rem; }

        /* FOOTER */
        footer { background: #0d2a1e; color: rgba(255, 255, 255, .78); padding: 56px 0 24px; font-size: .92rem; }
        .foot-grid { display: grid; grid-template-columns: 1.4fr 1fr 1fr 1.2fr; gap: 40px; }
        .foot-brand .logo-mark { display: inline-block; margin-bottom: 16px; }
        .foot-brand p { max-width: 36ch; font-size: .9rem; }
        footer strong { display: block; color: var(--white); margin-bottom: 12px; font-size: .95rem; }
        footer .col a, footer .col span { display: block; margin-bottom: 6px; overflow-wrap: anywhere; }
        footer .col a:hover { color: #e2c48f; }
        .copy { display: flex; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-top: 40px; padding-top: 20px; border-top: 1px solid rgba(255, 255, 255, .14); font-size: .82rem; }

        .wa { position: fixed; right: 18px; bottom: 18px; z-index: 40; background: #25a55f; color: var(--white); font-weight: 700; font-size: .85rem; padding: 12px 18px; border-radius: 40px; box-shadow: 0 10px 26px rgba(0, 0, 0, .25); transition: transform .25s; }
        .wa:hover { transform: translateY(-3px); }

        /* LANGUAGE SWITCHER */
        .lang { display: inline-flex; align-items: center; gap: 2px; }
        .lang button { background: none; border: 0; color: inherit; cursor: pointer; font-size: .78rem; font-weight: 700; letter-spacing: .1em; padding: 6px 8px; min-height: 36px; min-width: 38px; opacity: .55; position: relative; transition: opacity .2s; }
        .lang button:hover { opacity: .9; }
        .lang button.active { opacity: 1; }
        .lang button.active::after { content: ""; position: absolute; left: 8px; right: 8px; bottom: 5px; height: 2px; background: var(--husk); }
        .lang i { font-style: normal; opacity: .35; font-size: .8rem; }

        /* REVEAL (satu efek, halus) */
        .js .reveal { opacity: 0; transform: translateY(26px); transition: opacity .8s ease, transform .8s cubic-bezier(.2, .7, .2, 1); }
        .js .reveal.in { opacity: 1; transform: none; }

        /* OPENING ANIMATION (CSS-only fallback: hilang sendiri walau JS gagal) */
        .intro {
            position: fixed; inset: 0; z-index: 9999;
            display: flex; align-items: center; justify-content: center;
            background: var(--off);
            color: var(--deep);
            animation: introOut .4s ease 1.1s forwards;
        }
        .intro.hide { opacity: 0; visibility: hidden; pointer-events: none; }
        .no-intro .intro { display: none; }
        .intro-in { display: flex; flex-direction: column; align-items: center; text-align: center; padding: 0 18px; max-width: 100%; }
        .intro-logo {
            width: 110px; height: 110px; object-fit: contain;
            opacity: 0; transform: scale(.92);
            animation: introLogo .6s cubic-bezier(.2, .7, .2, 1) .05s forwards;
        }
        .intro-name {
            margin-top: 22px; font-size: .8rem; font-weight: 700; letter-spacing: .2em; line-height: 1.5;
            opacity: 0; transform: translateY(10px);
            animation: introUp .5s ease .5s forwards;
        }
        .intro-line {
            width: 130px; height: 1px; margin-top: 18px; background: var(--husk);
            transform: scaleX(0); transform-origin: center;
            animation: introLine .5s ease .7s forwards;
        }
        .intro-on .hero-load > * { animation-delay: 1.1s; }
        .intro-on .hero-load > *:nth-child(2) { animation-delay: 1.22s; }
        .intro-on .hero-load > *:nth-child(3) { animation-delay: 1.34s; }
        .intro-on .hero-load > *:nth-child(4) { animation-delay: 1.46s; }
        @keyframes introLogo { to { opacity: 1; transform: scale(1); } }
        @keyframes introUp { to { opacity: 1; transform: none; } }
        @keyframes introLine { to { transform: scaleX(1); } }
        @keyframes introOut { to { opacity: 0; visibility: hidden; pointer-events: none; } }
        @media (max-width: 640px) {
            .intro { animation-duration: .3s; animation-delay: .85s; }
            .intro-logo { width: 84px; height: 84px; animation-duration: .45s; }
            .intro-name { margin-top: 18px; font-size: .62rem; letter-spacing: .14em; animation-duration: .4s; animation-delay: .35s; }
            .intro-line { width: 96px; margin-top: 14px; animation-duration: .4s; animation-delay: .5s; }
            .intro-on .hero-load > * { animation-delay: .85s; }
            .intro-on .hero-load > *:nth-child(2) { animation-delay: .95s; }
            .intro-on .hero-load > *:nth-child(3) { animation-delay: 1.05s; }
            .intro-on .hero-load > *:nth-child(4) { animation-delay: 1.15s; }
        }
        @media (prefers-reduced-motion: reduce) {
            .intro { display: none; }
            .intro-on .hero-load > * { animation-delay: 0s; }
        }

        /* RESPONSIVE */
        @media (max-width: 1180px) {
            .logo-name { display: none; }
        }
        @media (max-width: 1080px) {
            .links { gap: 16px; }
            .links a:not(.nav-quote) { font-size: .84rem; }
            .steps { grid-template-columns: repeat(3, 1fr); row-gap: 48px; }
            .steps::before { display: none; }
            .foot-grid { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 900px) {
            .links { display: none; }
            .burger { display: block; }
            .mobile-menu {
                display: none; padding: 8px 24px 22px; border-top: 1px solid var(--line);
                grid-template-columns: 1fr 1fr; gap: 0 20px;
            }
            .mobile-menu.open { display: grid; }
            .mobile-menu a { padding: 12px 0; font-weight: 600; border-bottom: 1px solid var(--line); font-size: .95rem; }
            .mobile-menu .lang { grid-column: 1 / -1; padding: 8px 0 4px; border-bottom: 1px solid var(--line); }
            .mobile-menu .lang button { min-height: 44px; min-width: 48px; font-size: .85rem; }
            .mobile-menu .mq { grid-column: 1 / -1; margin-top: 16px; border: 0; text-align: center; background: var(--husk); color: var(--deep); border-radius: 3px; font-weight: 700; }
            .about-grid, .src-grid, .cap-grid, .why-grid, .contact-grid { grid-template-columns: 1fr; }
            .src-grid > div:first-child, .why-grid > div:first-child { position: static; }
            .about-photo { width: 100%; aspect-ratio: 4 / 3; }
            .about-note { position: relative; margin: -50px 0 0 auto; }
            .about-media { padding-bottom: 0; }
            .values-list { grid-template-columns: 1fr 1fr; }
            .value:nth-child(3) { padding-left: 0; border-left: 0; }
            .value:nth-child(n+3) { border-top: 1px solid var(--line); margin-top: 24px; padding-top: 32px; }
            .pc-0, .pc-1 { grid-column: span 12; }
            .pc-2 { flex-direction: column; }
            .pc-2 .pc-body { padding: 32px 34px 36px; }
            .gal { column-count: 2; }
            .strip-in { grid-template-columns: 1fr 1fr; }
            .strip-in div:nth-child(3) { border-left: 0; padding-left: 0; }
            .strip-in div:nth-child(n+3) { border-top: 1px solid rgba(255, 255, 255, .16); }
        }
        @media (max-width: 640px) {
            .wrap { width: calc(100% - 36px); }
            .logo-name { display: none; }
            .hero-inner { padding-top: 110px; }
            .hero-foot { margin-top: 48px; }
            .hero-actions .btn { flex: 1 1 100%; }
            .vm, .fgrid, .sus-pts { grid-template-columns: 1fr; }
            .steps { grid-template-columns: 1fr; row-gap: 0; padding-left: 8px; }
            .steps::before { display: block; left: 14px; right: auto; top: 6px; bottom: 6px; width: 1px; height: auto; }
            .step { padding: 0 0 36px 44px; }
            .step::before { top: 8px; left: 8px; }
            .step .n { font-size: 2.4rem; }
            .values-list { grid-template-columns: 1fr; }
            .value, .value + .value { padding-left: 0; border-left: 0; }
            .value + .value { border-top: 1px solid var(--line); margin-top: 24px; padding-top: 32px; }
            .zone { grid-template-columns: 1fr; }
            .zone strong { text-align: left; }
            .why-row { grid-template-columns: 1fr; gap: 8px; }
            .cap-facts { gap: 28px; }
            .gal, .gal.n2 { column-count: 1; }
            .foot-grid { grid-template-columns: 1fr; gap: 28px; }
            .pc-body, .pc-2 .pc-body { padding: 26px 22px 30px; }
            .wa { right: 12px; bottom: 12px; padding: 10px 14px; }
            .pcta-in .btn { width: 100%; }
        }
        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            *, *::before, *::after { animation: none !important; transition: none !important; }
            .js .reveal { opacity: 1; transform: none; }
        }
    </style>
    <script>
        (function () {
            var d = document.documentElement;
            try {
                if (sessionStorage.getItem("ccci_intro")) { d.classList.add("no-intro"); }
                else { sessionStorage.setItem("ccci_intro", "1"); d.classList.add("intro-on"); }
            } catch (e) { d.classList.add("intro-on"); }
        })();
    </script>
</head>
<body>

<!-- OPENING ANIMATION -->
<div class="intro" id="intro" aria-hidden="true">
    <div class="intro-in">
        <img class="intro-logo" src="assets/img/logo.png" alt="">
        <div class="intro-name">PT. CENTRAL CELEBES COCONUT INDONESIA</div>
        <div class="intro-line"></div>
    </div>
</div>

<header id="top">
    <div class="wrap nav">
        <a href="#home" class="logo" aria-label="CCCI">
            <span class="logo-mark"><img src="assets/img/logo.png" alt="CCCI Logo"></span>
            <span class="logo-name">Central Celebes<br>Coconut Indonesia</span>
        </a>

        <nav class="links" aria-label="Main navigation">
            <a href="#about" data-i18n="nav_about">About</a>
            <a href="#products" data-i18n="nav_products">Products</a>
            <a href="#process" data-i18n="nav_process">Process</a>
            <a href="#sustainability" data-i18n="nav_sustainability">Sustainability</a>
            <a href="#gallery" data-i18n="nav_gallery">Gallery</a>
            <a href="#contact" data-i18n="nav_contact">Contact</a>
            <div class="lang" role="group" aria-label="Language"><button type="button" data-lang="en" aria-pressed="true" class="active">EN</button><i>|</i><button type="button" data-lang="id" aria-pressed="false">ID</button></div>
            <a href="#contact" class="nav-quote" data-i18n="rq">Request a Quote</a>
        </nav>

        <button class="burger" type="button" aria-label="Open menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
    </div>

    <div class="mobile-menu">
        <div class="lang" role="group" aria-label="Language"><button type="button" data-lang="en" aria-pressed="true" class="active">EN</button><i>|</i><button type="button" data-lang="id" aria-pressed="false">ID</button></div>
        <a href="#about" data-i18n="nav_about">About</a>
        <a href="#products" data-i18n="nav_products">Products</a>
        <a href="#process" data-i18n="nav_process">Process</a>
        <a href="#sustainability" data-i18n="nav_sustainability">Sustainability</a>
        <a href="#gallery" data-i18n="nav_gallery">Gallery</a>
        <a href="#contact" data-i18n="nav_contact">Contact</a>
        <a href="#contact" class="mq" data-i18n="rq">Request a Quote</a>
    </div>
</header>

<main>

<!-- HERO -->
<section id="home" class="hero" <?php if ($hero_image !== ""): ?>style="--img: url('<?= e($hero_image); ?>');"<?php endif; ?>>
    <div class="wrap hero-inner">
        <div class="hero-load">
            <span class="label" data-i18n="hero_eyebrow">PT. CENTRAL CELEBES COCONUT INDONESIA</span>
            <h1 data-i18n="hero_title">From Farmers<br>to Global Markets.</h1>
            <p class="hero-sub" data-i18n="hero_sub">Building a reliable and sustainable coconut supply chain from Indonesia to the world.</p>
            <div class="hero-actions">
                <a href="#products" class="btn btn-solid" data-i18n="hero_explore">Explore Products</a>
                <a href="#contact" class="btn btn-ghost" data-i18n="rq">Request a Quote</a>
            </div>
        </div>
        <div class="hero-foot">
            <span data-i18n="hero_loc">Central Sulawesi, Indonesia</span>
            <span data-i18n="hero_global">Global Supply</span>
        </div>
    </div>
</section>

<!-- TRUST STRIP -->
<section class="strip" aria-label="CCCI strengths">
    <div class="wrap strip-in">
        <div data-i18n="direct_sourcing">Direct Sourcing</div>
        <div data-i18n="qc_controlled">Quality Controlled</div>
        <div data-i18n="reliable_supply">Reliable Supply</div>
        <div data-i18n="sust_partner">Sustainable Partnership</div>
    </div>
</section>

<!-- ABOUT -->
<section id="about" class="pad">
    <div class="wrap about-grid">
        <div class="about-media reveal">
            <div class="about-photo <?= $about_image === "" ? "empty" : ""; ?>" <?php if ($about_image !== ""): ?>style="background-image: url('<?= e($about_image); ?>');"<?php endif; ?>></div>
            <div class="about-note">
                <strong>2026</strong>
                <span data-i18n="about_note">CCCI was founded by local sons and daughters of Morowali, committed to serving communities, the region, and the environment.</span>
            </div>
        </div>

        <div class="about-text reveal">
            <span class="label" data-i18n="about_label">WHO WE ARE</span>
            <h2 data-i18n="about_title">More than coconut.<br>We build the supply chain behind it.</h2>
            <p><?= e($company_description); ?></p>
            <p data-i18n="about_p2">CCCI sees coconut as more than a commodity. It is a livelihood and an economic opportunity for farmers, partners, local workers, and the communities around them.</p>

            <div class="vm">
                <div>
                    <h3 data-i18n="vision">Vision</h3>
                    <p data-i18n="vision_p">A leading coconut trading and husk processing company, globally competitive, adding value for communities and sustainable economic growth.</p>
                </div>
                <div>
                    <h3 data-i18n="mission">Mission</h3>
                    <p data-i18n="mission_p">Trade quality coconuts reliably, process husk efficiently, empower farmers and local workers, and build strategic, eco-friendly partnerships.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- VALUES -->
<section class="values pad">
    <div class="wrap">
        <div class="values-head reveal">
            <h2 data-i18n="values_title">What we stand on.</h2>
            <p style="color:var(--muted)" data-i18n="values_sub">The values behind every shipment, every farmer partnership, and every conversation with our buyers.</p>
        </div>
        <div class="values-list reveal">
            <div class="value">
                <h3 data-i18n="v_quality">Quality</h3>
                <p data-i18n="v_quality_p">High-quality products and services that meet what the market needs.</p>
            </div>
            <div class="value">
                <h3 data-i18n="v_sust">Sustainability</h3>
                <p data-i18n="v_sust_p">Running the business with care for the environment and the welfare of communities.</p>
            </div>
            <div class="value">
                <h3 data-i18n="v_part">Partnership</h3>
                <p data-i18n="v_part_p">Working closely with farmers, partners, customers, and every stakeholder.</p>
            </div>
            <div class="value">
                <h3 data-i18n="v_integrity">Integrity</h3>
                <p data-i18n="v_integrity_p">Honesty, transparency, and responsibility in all our business activities.</p>
            </div>
        </div>
    </div>
</section>

<!-- SOURCING -->
<section id="sourcing" class="sourcing pad">
    <div class="wrap src-grid">
        <div class="reveal">
            <h2 data-i18n="src_title">Rooted in Indonesia</h2>
            <p data-i18n="src_p">Our raw material network is organised into three sourcing zones, protecting supply availability, logistics efficiency, and continuity of procurement.</p>
            <ol class="chain">
                <li data-i18n="chain_farmers">Farmers</li>
                <li data-i18n="collection">Collection</li>
                <li data-i18n="qc">Quality Control</li>
                <li data-i18n="processing">Processing</li>
                <li data-i18n="chain_market">Global Market</li>
            </ol>
        </div>

        <div class="zones reveal">
            <div class="zone">
                <small data-i18n="zone1">Zone 1 &middot; Core supply</small>
                <h3>Morowali</h3>
                <strong>110,013<span data-i18n="kg_month">kg / month</span></strong>
                <p data-i18n="zone1_p">Farmer and collector networks in Morowali Regency, close to our base and with strong potential for continuous procurement.</p>
            </div>
            <div class="zone">
                <small data-i18n="zone2">Zone 2 &middot; Supply expansion</small>
                <h3 data-i18n="central_sulawesi">Central Sulawesi</h3>
                <strong>161,800<span data-i18n="kg_month">kg / month</span></strong>
                <p data-i18n="zone2_p">Supplier networks beyond Morowali, including Tojo Una-Una, Poso, Banggai, and other parts of Central Sulawesi.</p>
            </div>
            <div class="zone">
                <small data-i18n="zone3">Zone 3 &middot; Regional reserve</small>
                <h3 data-i18n="southeast_sulawesi">Southeast Sulawesi</h3>
                <strong>51,952<span data-i18n="kg_month">kg / month</span></strong>
                <p data-i18n="zone3_p">A regional area for diversifying raw material sources, including partner networks in North Konawe.</p>
            </div>
        </div>
    </div>
</section>

<!-- PRODUCTS (database) -->
<section id="products" class="products pad">
    <div class="wrap">
        <div class="products-head reveal">
            <h2 data-i18n="prod_title">Our Products</h2>
            <p data-i18n="prod_p">Coconut trading and husk processing, sourced through our partner farmers and collectors.</p>
        </div>

        <div class="pgrid">
            <?php foreach ($products as $pi => $product): ?>
                <article class="pc pc-<?= (int) ($pi % 3); ?> reveal">
                    <?php if (!empty($product["image"])): ?>
                        <div class="pc-img">
                            <img src="uploads/<?= e($product["image"]); ?>" alt="<?= e($product["name"]); ?>" loading="lazy">
                        </div>
                    <?php else: ?>
                        <div class="pc-img empty">
                            <img src="assets/img/logo.png" alt="" loading="lazy">
                        </div>
                    <?php endif; ?>
                    <div class="pc-body">
                        <h3><?= e($product["name"]); ?></h3>
                        <p><?= e($product["description"] ?? ""); ?></p>
                        <a href="#contact" class="product-link" data-i18n="rq_short" data-product="<?= e($product["name"]); ?>">Request Quote</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- PRODUCT CTA -->
<section class="pcta">
    <div class="wrap pcta-in">
        <div>
            <h2 data-i18n="pcta_title">Looking for a specific coconut product?</h2>
            <p data-i18n="pcta_p">Tell us what you need and our team will help you find the right supply solution.</p>
        </div>
        <a href="#contact" class="btn btn-solid" data-i18n="rq">Request a Quote</a>
    </div>
</section>

<!-- PROCESS -->
<section id="process" class="pad">
    <div class="wrap">
        <div class="process-head reveal">
            <h2 data-i18n="proc_title">From Farm to Global Market</h2>
        </div>
        <ol class="steps reveal">
            <li class="step">
                <span class="n">01</span>
                <h3 data-i18n="step_sourcing">Sourcing</h3>
                <p data-i18n="step1_p">Partnerships and procurement with local farmers.</p>
            </li>
            <li class="step">
                <span class="n">02</span>
                <h3 data-i18n="collection">Collection</h3>
                <p data-i18n="step2_p">Coconuts gathered through our network of collectors.</p>
            </li>
            <li class="step">
                <span class="n">03</span>
                <h3 data-i18n="processing">Processing</h3>
                <p data-i18n="step3_p">Coconut and husk prepared for industrial use, with husk processing in development.</p>
            </li>
            <li class="step">
                <span class="n">04</span>
                <h3 data-i18n="qc">Quality Control</h3>
                <p data-i18n="step4_p">Receiving and sorting at our warehouse points.</p>
            </li>
            <li class="step">
                <span class="n">05</span>
                <h3 data-i18n="step_distribution">Distribution</h3>
                <p data-i18n="step5_p">Shipped by land or sea, matched to what each buyer needs.</p>
            </li>
        </ol>
    </div>
</section>

<!-- SUPPLY CAPABILITY -->
<section class="cap pad">
    <div class="wrap cap-grid">
        <div class="reveal">
            <div class="cap-big">323K+</div>
            <p data-i18n="cap_p">Kilograms per month of mapped supply capacity across our partner farmer regions.</p>
            <div class="cap-facts">
                <div><strong>Indonesia</strong><span data-i18n="cap_region">Sourcing Region</span></div>
                <div><strong>Global</strong><span data-i18n="cap_reach">Market Reach</span></div>
            </div>
        </div>
        <ul class="cap-list reveal">
            <li><b>110,013</b><span>Morowali</span></li>
            <li><b>105,890</b><span>Tojo Una-Una</span></li>
            <li><b>51,952</b><span data-i18n="north_konawe">North Konawe</span></li>
            <li><b>31,350</b><span>Banggai</span></li>
            <li><b>24,560</b><span>Poso</span></li>
        </ul>
    </div>
</section>

<!-- SUSTAINABILITY -->
<section id="sustainability" class="sus pad" <?php if ($sustain_image !== ""): ?>style="--img: url('<?= e($sustain_image); ?>');"<?php endif; ?>>
    <div class="wrap">
        <h2 class="reveal" data-i18n="sus_title">Building a better coconut supply chain.</h2>
        <div class="sus-pts reveal">
            <div class="sus-pt">
                <h3 data-i18n="sus1">Responsible sourcing</h3>
                <p data-i18n="sus1_p">Raw materials come from areas with a clear origin, supported by documentation in the procurement process.</p>
            </div>
            <div class="sus-pt">
                <h3 data-i18n="sus2">Farmer partnership</h3>
                <p data-i18n="sus2_p">Fair purchasing, mentoring, and capacity building to support local farmers' welfare.</p>
            </div>
            <div class="sus-pt">
                <h3 data-i18n="sus3">Efficient processing</h3>
                <p data-i18n="sus3_p">Husk processing run efficiently and with innovation, making use of resources.</p>
            </div>
            <div class="sus-pt">
                <h3 data-i18n="sus4">Sustainable practices</h3>
                <p data-i18n="sus4_p">Long-term relationships with farmers, collectors, and customers, with care for the environment.</p>
            </div>
        </div>
    </div>
</section>

<!-- GALLERY (database) -->
<section id="gallery" class="pad">
    <div class="wrap">
        <div class="gal-head reveal">
            <h2 data-i18n="gal_title">Our Activities</h2>
            <p style="max-width:38ch" data-i18n="gal_p">Field visits, partnerships, warehouses, and the supply chain as it grows.</p>
        </div>

        <?php if (count($gallery_items) > 0): ?>
            <div class="gal <?= e($gallery_count_class); ?>">
                <?php foreach ($gallery_items as $gallery): ?>
                    <?php if (!empty($gallery["image"])): ?>
                        <figure class="gi">
                            <img src="uploads/<?= e($gallery["image"]); ?>" alt="<?= e($gallery["title"] ?? ""); ?>" loading="lazy">
                            <?php if (!empty($gallery["title"])): ?>
                                <figcaption>
                                    <?= e($gallery["title"]); ?>
                                    <?php if (!empty($gallery["category"])): ?>
                                        <small><?= e($gallery["category"]); ?></small>
                                    <?php endif; ?>
                                </figcaption>
                            <?php endif; ?>
                        </figure>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="gal-empty">
                <h3 data-i18n="gal_empty_t">Documentation will be updated.</h3>
                <p data-i18n="gal_empty_p">Photos of our activities will appear here soon.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- WHY CCCI -->
<section class="why pad">
    <div class="wrap why-grid">
        <div class="reveal">
            <h2 data-i18n="why_title">Why Partner With CCCI</h2>
        </div>
        <div class="reveal">
            <div class="why-row">
                <h3 data-i18n="direct_sourcing">Direct Sourcing</h3>
                <p data-i18n="why1_p">We buy through farmer and collector networks in our own region.</p>
            </div>
            <div class="why-row">
                <h3 data-i18n="qc">Quality Control</h3>
                <p data-i18n="why2_p">Every lot is received and sorted at our warehouse points.</p>
            </div>
            <div class="why-row">
                <h3 data-i18n="why_consistent">Consistent Supply</h3>
                <p data-i18n="why3_p">Three sourcing zones and 323K+ kg of mapped monthly capacity.</p>
            </div>
            <div class="why-row">
                <h3 data-i18n="why_traceable">Traceable Supply Chain</h3>
                <p data-i18n="why4_p">Raw materials come from areas with a clear origin and documentation.</p>
            </div>
            <div class="why-row">
                <h3 data-i18n="sust_partner">Sustainable Partnership</h3>
                <p data-i18n="why5_p">Long-term relationships with farmers, collectors, and buyers.</p>
            </div>
        </div>
    </div>
</section>

<!-- CONTACT (inquiry form) -->
<section id="contact" class="pad">
    <div class="wrap">
        <div class="contact-grid reveal">

            <div class="contact-info">
                <h2><?= e($company_name); ?></h2>
                <p data-i18n="contact_desc">Contact us for product information, raw material supply, and partnership opportunities.</p>

                <div class="ci">
                    <small data-i18n="c_address">Address</small>
                    <span><?= e($company_address); ?></span>
                </div>
                <div class="ci">
                    <small data-i18n="c_phone">Phone</small>
                    <a href="tel:<?= e($company_phone); ?>"><?= e($company_phone); ?></a>
                </div>
                <div class="ci">
                    <small>WhatsApp</small>
                    <a href="<?= e($wa_link); ?>" target="_blank" rel="noopener"><?= e($company_whatsapp); ?></a>
                </div>
                <div class="ci">
                    <small>Email</small>
                    <a href="mailto:<?= e($company_email); ?>"><?= e($company_email); ?></a>
                </div>
                <div class="ci">
                    <small data-i18n="c_hours">Operating Hours</small>
                    <span><?= e($company_hours); ?></span>
                </div>
            </div>

            <div class="form-panel">
                <h3 data-i18n="rq">Request a Quote</h3>
                <p data-i18n="form_p">Fill in the form and our team will follow up on your inquiry.</p>

                <?php if ($success !== ""): ?>
                    <div class="alert success"><?= e($success); ?></div>
                <?php endif; ?>

                <?php if ($error !== ""): ?>
                    <div class="alert error"><?= e($error); ?></div>
                <?php endif; ?>

                <form method="POST" action="#contact">
                    <div class="fgrid">

                        <div class="fg">
                            <label for="full_name" data-i18n="f_name">Full Name *</label>
                            <input type="text" id="full_name" name="full_name" value="<?= e($_POST["full_name"] ?? ""); ?>" required>
                        </div>

                        <div class="fg">
                            <label for="company_name" data-i18n="f_company">Company Name</label>
                            <input type="text" id="company_name" name="company_name" value="<?= e($_POST["company_name"] ?? ""); ?>">
                        </div>

                        <div class="fg">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" value="<?= e($_POST["email"] ?? ""); ?>" required>
                        </div>

                        <div class="fg">
                            <label for="phone" data-i18n="f_phone">Phone / WhatsApp</label>
                            <input type="text" id="phone" name="phone" value="<?= e($_POST["phone"] ?? ""); ?>">
                        </div>

                        <div class="fg">
                            <label for="country" data-i18n="f_country">Country</label>
                            <input type="text" id="country" name="country" value="<?= e($_POST["country"] ?? ""); ?>" placeholder="Indonesia" data-i18n-placeholder="ph_country">
                        </div>

                        <div class="fg">
                            <label for="product_id" data-i18n="f_product">Product</label>
                            <select id="product_id" name="product_id">
                                <option value="" data-i18n="f_select">Select Product</option>
                                <?php foreach ($products as $product): ?>
                                    <?php if (!empty($product["id"])): ?>
                                        <option
                                            value="<?= e($product["id"]); ?>"
                                            <?= (($_POST["product_id"] ?? "") == $product["id"]) ? "selected" : ""; ?>
                                        >
                                            <?= e($product["name"]); ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="fg full">
                            <label for="quantity" data-i18n="f_qty">Estimated Quantity</label>
                            <input type="text" id="quantity" name="quantity" value="<?= e($_POST["quantity"] ?? ""); ?>" placeholder="Example: 10 tons / month" data-i18n-placeholder="ph_qty">
                        </div>

                        <div class="fg full">
                            <label for="message" data-i18n="f_msg">Message / Requirement</label>
                            <textarea id="message" name="message" placeholder="Tell us about your requirement..." data-i18n-placeholder="ph_msg"><?= e($_POST["message"] ?? ""); ?></textarea>
                        </div>

                        <div class="fg full">
                            <button type="submit" name="submit_inquiry" class="btn btn-green" data-i18n="f_send">Send Inquiry</button>
                        </div>

                    </div>
                </form>
            </div>

        </div>
    </div>
</section>

<!-- FINAL CTA -->
<section class="final pad" <?php if ($cta_image !== ""): ?>style="--img: url('<?= e($cta_image); ?>');"<?php endif; ?>>
    <div class="wrap reveal">
        <h2 data-i18n="final_title">Let's build a reliable coconut supply partnership.</h2>
        <p data-i18n="final_p">Tell us about your requirements.</p>
        <a href="#contact" class="btn btn-solid" data-i18n="rq">Request a Quote</a>
    </div>
</section>

</main>

<!-- FOOTER -->
<footer>
    <div class="wrap">
        <div class="foot-grid">
            <div class="foot-brand">
                <span class="logo-mark"><img src="assets/img/logo.png" alt="CCCI Logo"></span>
                <strong>PT. Central Celebes Coconut Indonesia</strong>
                <p data-i18n="foot_desc">From farmers to global markets. A sustainable coconut value chain built in Morowali for Indonesia and the world.</p>
            </div>

            <div class="col">
                <strong data-i18n="foot_nav">Navigation</strong>
                <a href="#about" data-i18n="nav_about">About</a>
                <a href="#process" data-i18n="nav_process">Process</a>
                <a href="#sustainability" data-i18n="nav_sustainability">Sustainability</a>
                <a href="#gallery" data-i18n="nav_gallery">Gallery</a>
                <a href="#contact" data-i18n="nav_contact">Contact</a>
            </div>

            <div class="col">
                <strong data-i18n="foot_products">Products</strong>
                <?php foreach (array_slice($products, 0, 5) as $product): ?>
                    <a href="#products"><?= e($product["name"]); ?></a>
                <?php endforeach; ?>
            </div>

            <div class="col">
                <strong data-i18n="foot_contact">Contact</strong>
                <span><?= e($company_address); ?></span>
                <a href="mailto:<?= e($company_email); ?>"><?= e($company_email); ?></a>
                <a href="<?= e($wa_link); ?>" target="_blank" rel="noopener">WhatsApp <?= e($company_whatsapp); ?></a>
            </div>
        </div>

        <div class="copy">
            <span>&copy; <?= date("Y"); ?> PT. Central Celebes Coconut Indonesia. <span data-i18n="rights">All rights reserved.</span></span>
            <span data-i18n="foot_tag">Quality &middot; Sustainability &middot; Partnership</span>
        </div>
    </div>
</footer>

<a href="<?= e($wa_link); ?>" target="_blank" rel="noopener" class="wa">WhatsApp CCCI</a>

<script>
    document.documentElement.classList.add("js");

    (function () {
        var header = document.getElementById("top");
        var toggle = document.querySelector(".burger");
        var menu = document.querySelector(".mobile-menu");

        function onScroll() {
            header.classList.toggle("scrolled", window.scrollY > 40);
        }
        onScroll();
        window.addEventListener("scroll", onScroll, { passive: true });

        function closeMenu() {
            menu.classList.remove("open");
            toggle.classList.remove("open");
            header.classList.remove("menu-open");
            toggle.setAttribute("aria-expanded", "false");
        }

        if (toggle && menu) {
            toggle.addEventListener("click", function () {
                var open = menu.classList.toggle("open");
                toggle.classList.toggle("open", open);
                header.classList.toggle("menu-open", open);
                toggle.setAttribute("aria-expanded", open ? "true" : "false");
            });
            menu.querySelectorAll("a").forEach(function (a) {
                a.addEventListener("click", closeMenu);
            });
            window.addEventListener("resize", function () {
                if (window.innerWidth > 900) { closeMenu(); }
            });
        }

        var items = document.querySelectorAll(".reveal");
        if ("IntersectionObserver" in window) {
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (en) {
                    if (en.isIntersecting) {
                        en.target.classList.add("in");
                        io.unobserve(en.target);
                    }
                });
            }, { threshold: 0.12 });
            items.forEach(function (el) { io.observe(el); });
        } else {
            items.forEach(function (el) { el.classList.add("in"); });
        }

        document.querySelectorAll(".product-link").forEach(function (link) {
            link.addEventListener("click", function () {
                var product = this.getAttribute("data-product");
                var select = document.getElementById("product_id");
                if (!product || !select) { return; }
                Array.from(select.options).forEach(function (option) {
                    if (option.text.trim() === product.trim()) {
                        option.selected = true;
                    }
                });
            });
        });
    })();

    /* ===== MULTI LANGUAGE (EN / ID) ===== */
    const translations = {
        "en": {
                "nav_about": "About",
                "nav_products": "Products",
                "nav_process": "Process",
                "nav_sustainability": "Sustainability",
                "nav_gallery": "Gallery",
                "nav_contact": "Contact",
                "rq": "Request a Quote",
                "hero_eyebrow": "PT. CENTRAL CELEBES COCONUT INDONESIA",
                "hero_title": "From Farmers<br>to Global Markets.",
                "hero_sub": "Building a reliable and sustainable coconut supply chain from Indonesia to the world.",
                "hero_explore": "Explore Products",
                "hero_loc": "Central Sulawesi, Indonesia",
                "hero_global": "Global Supply",
                "direct_sourcing": "Direct Sourcing",
                "qc_controlled": "Quality Controlled",
                "reliable_supply": "Reliable Supply",
                "sust_partner": "Sustainable Partnership",
                "about_label": "WHO WE ARE",
                "about_title": "More than coconut.<br>We build the supply chain behind it.",
                "about_p2": "CCCI sees coconut as more than a commodity. It is a livelihood and an economic opportunity for farmers, partners, local workers, and the communities around them.",
                "about_note": "CCCI was founded by local sons and daughters of Morowali, committed to serving communities, the region, and the environment.",
                "vision": "Vision",
                "mission": "Mission",
                "vision_p": "A leading coconut trading and husk processing company, globally competitive, adding value for communities and sustainable economic growth.",
                "mission_p": "Trade quality coconuts reliably, process husk efficiently, empower farmers and local workers, and build strategic, eco-friendly partnerships.",
                "values_title": "What we stand on.",
                "values_sub": "The values behind every shipment, every farmer partnership, and every conversation with our buyers.",
                "v_quality": "Quality",
                "v_quality_p": "High-quality products and services that meet what the market needs.",
                "v_sust": "Sustainability",
                "v_sust_p": "Running the business with care for the environment and the welfare of communities.",
                "v_part": "Partnership",
                "v_part_p": "Working closely with farmers, partners, customers, and every stakeholder.",
                "v_integrity": "Integrity",
                "v_integrity_p": "Honesty, transparency, and responsibility in all our business activities.",
                "src_title": "Rooted in Indonesia",
                "src_p": "Our raw material network is organised into three sourcing zones, protecting supply availability, logistics efficiency, and continuity of procurement.",
                "chain_farmers": "Farmers",
                "collection": "Collection",
                "qc": "Quality Control",
                "processing": "Processing",
                "chain_market": "Global Market",
                "zone1": "Zone 1 &middot; Core supply",
                "zone2": "Zone 2 &middot; Supply expansion",
                "zone3": "Zone 3 &middot; Regional reserve",
                "central_sulawesi": "Central Sulawesi",
                "southeast_sulawesi": "Southeast Sulawesi",
                "kg_month": "kg / month",
                "zone1_p": "Farmer and collector networks in Morowali Regency, close to our base and with strong potential for continuous procurement.",
                "zone2_p": "Supplier networks beyond Morowali, including Tojo Una-Una, Poso, Banggai, and other parts of Central Sulawesi.",
                "zone3_p": "A regional area for diversifying raw material sources, including partner networks in North Konawe.",
                "prod_title": "Our Products",
                "prod_p": "Coconut trading and husk processing, sourced through our partner farmers and collectors.",
                "rq_short": "Request Quote",
                "view_details": "View Details",
                "pcta_title": "Looking for a specific coconut product?",
                "pcta_p": "Tell us what you need and our team will help you find the right supply solution.",
                "proc_title": "From Farm to Global Market",
                "step_sourcing": "Sourcing",
                "step_distribution": "Distribution",
                "step1_p": "Partnerships and procurement with local farmers.",
                "step2_p": "Coconuts gathered through our network of collectors.",
                "step3_p": "Coconut and husk prepared for industrial use, with husk processing in development.",
                "step4_p": "Receiving and sorting at our warehouse points.",
                "step5_p": "Shipped by land or sea, matched to what each buyer needs.",
                "cap_p": "Kilograms per month of mapped supply capacity across our partner farmer regions.",
                "cap_region": "Sourcing Region",
                "cap_reach": "Market Reach",
                "north_konawe": "North Konawe",
                "sus_title": "Building a better coconut supply chain.",
                "sus1": "Responsible sourcing",
                "sus1_p": "Raw materials come from areas with a clear origin, supported by documentation in the procurement process.",
                "sus2": "Farmer partnership",
                "sus2_p": "Fair purchasing, mentoring, and capacity building to support local farmers' welfare.",
                "sus3": "Efficient processing",
                "sus3_p": "Husk processing run efficiently and with innovation, making use of resources.",
                "sus4": "Sustainable practices",
                "sus4_p": "Long-term relationships with farmers, collectors, and customers, with care for the environment.",
                "gal_title": "Our Activities",
                "gal_p": "Field visits, partnerships, warehouses, and the supply chain as it grows.",
                "gal_empty_t": "Documentation will be updated.",
                "gal_empty_p": "Photos of our activities will appear here soon.",
                "why_title": "Why Partner With CCCI",
                "why_consistent": "Consistent Supply",
                "why_traceable": "Traceable Supply Chain",
                "why1_p": "We buy through farmer and collector networks in our own region.",
                "why2_p": "Every lot is received and sorted at our warehouse points.",
                "why3_p": "Three sourcing zones and 323K+ kg of mapped monthly capacity.",
                "why4_p": "Raw materials come from areas with a clear origin and documentation.",
                "why5_p": "Long-term relationships with farmers, collectors, and buyers.",
                "contact_desc": "Contact us for product information, raw material supply, and partnership opportunities.",
                "c_address": "Address",
                "c_phone": "Phone",
                "c_hours": "Operating Hours",
                "form_p": "Fill in the form and our team will follow up on your inquiry.",
                "f_name": "Full Name *",
                "f_company": "Company Name",
                "f_phone": "Phone / WhatsApp",
                "f_country": "Country",
                "f_product": "Product",
                "f_qty": "Estimated Quantity",
                "f_msg": "Message / Requirement",
                "f_select": "Select Product",
                "f_send": "Send Inquiry",
                "ph_country": "Indonesia",
                "ph_qty": "Example: 10 tons / month",
                "ph_msg": "Tell us about your requirement...",
                "final_title": "Let's build a reliable coconut supply partnership.",
                "final_p": "Tell us about your requirements.",
                "foot_desc": "From farmers to global markets. A sustainable coconut value chain built in Morowali for Indonesia and the world.",
                "foot_nav": "Navigation",
                "foot_products": "Products",
                "foot_contact": "Contact",
                "rights": "All rights reserved.",
                "foot_tag": "Quality &middot; Sustainability &middot; Partnership"
        },
        "id": {
                "nav_about": "Tentang",
                "nav_products": "Produk",
                "nav_process": "Proses",
                "nav_sustainability": "Keberlanjutan",
                "nav_gallery": "Galeri",
                "nav_contact": "Kontak",
                "rq": "Minta Penawaran",
                "hero_eyebrow": "PT. CENTRAL CELEBES COCONUT INDONESIA",
                "hero_title": "Dari Petani<br>ke Pasar Global.",
                "hero_sub": "Membangun rantai pasok kelapa yang andal dan berkelanjutan dari Indonesia untuk dunia.",
                "hero_explore": "Jelajahi Produk",
                "hero_loc": "Sulawesi Tengah, Indonesia",
                "hero_global": "Pasokan Global",
                "direct_sourcing": "Pengadaan Langsung",
                "qc_controlled": "Kualitas Terkontrol",
                "reliable_supply": "Pasokan Andal",
                "sust_partner": "Kemitraan Berkelanjutan",
                "about_label": "SIAPA KAMI",
                "about_title": "Lebih dari sekadar kelapa.<br>Kami membangun rantai pasok di baliknya.",
                "about_p2": "CCCI memandang kelapa bukan sekadar komoditas, melainkan sumber penghidupan dan peluang ekonomi bagi petani, mitra, tenaga kerja lokal, dan masyarakat sekitar.",
                "about_note": "CCCI didirikan oleh putra-putri lokal Morowali dengan komitmen untuk melayani masyarakat, daerah, dan lingkungan.",
                "vision": "Visi",
                "mission": "Misi",
                "vision_p": "Perusahaan terkemuka di bidang perdagangan kelapa dan pengolahan sabut kelapa yang berdaya saing global, memberi nilai tambah bagi masyarakat dan pertumbuhan ekonomi berkelanjutan.",
                "mission_p": "Memperdagangkan kelapa berkualitas secara andal, mengolah sabut secara efisien, memberdayakan petani dan tenaga kerja lokal, serta membangun kemitraan strategis yang ramah lingkungan.",
                "values_title": "Nilai yang kami pegang.",
                "values_sub": "Nilai di balik setiap pengiriman, setiap kemitraan dengan petani, dan setiap percakapan dengan pembeli kami.",
                "v_quality": "Kualitas",
                "v_quality_p": "Produk dan layanan berkualitas tinggi yang memenuhi kebutuhan pasar.",
                "v_sust": "Keberlanjutan",
                "v_sust_p": "Menjalankan usaha dengan memperhatikan lingkungan dan kesejahteraan masyarakat.",
                "v_part": "Kemitraan",
                "v_part_p": "Bekerja erat dengan petani, mitra, pelanggan, dan seluruh pemangku kepentingan.",
                "v_integrity": "Integritas",
                "v_integrity_p": "Kejujuran, transparansi, dan tanggung jawab dalam seluruh kegiatan usaha kami.",
                "src_title": "Berakar di Indonesia",
                "src_p": "Jaringan bahan baku kami disusun dalam tiga zona sumber untuk menjaga ketersediaan pasokan, efisiensi logistik, dan kesinambungan pengadaan.",
                "chain_farmers": "Petani",
                "collection": "Pengumpulan",
                "qc": "Pengendalian Mutu",
                "processing": "Pengolahan",
                "chain_market": "Pasar Global",
                "zone1": "Zona 1 &middot; Pasokan inti",
                "zone2": "Zona 2 &middot; Perluasan pasokan",
                "zone3": "Zona 3 &middot; Cadangan regional",
                "central_sulawesi": "Sulawesi Tengah",
                "southeast_sulawesi": "Sulawesi Tenggara",
                "kg_month": "kg / bulan",
                "zone1_p": "Jaringan petani dan pengepul di Kabupaten Morowali, dekat dengan basis kami dan berpotensi besar untuk pengadaan berkelanjutan.",
                "zone2_p": "Jaringan pemasok di luar Morowali, termasuk Tojo Una-Una, Poso, Banggai, dan wilayah Sulawesi Tengah lainnya.",
                "zone3_p": "Wilayah regional untuk mendiversifikasi sumber bahan baku, termasuk jaringan mitra di Konawe Utara.",
                "prod_title": "Produk Kami",
                "prod_p": "Perdagangan kelapa dan pengolahan sabut kelapa, bersumber dari petani dan pengepul mitra kami.",
                "rq_short": "Minta Penawaran",
                "view_details": "Lihat Detail",
                "pcta_title": "Mencari produk kelapa tertentu?",
                "pcta_p": "Sampaikan kebutuhan Anda dan tim kami akan membantu menemukan solusi pasokan yang tepat.",
                "proc_title": "Dari Kebun ke Pasar Global",
                "step_sourcing": "Pengadaan",
                "step_distribution": "Distribusi",
                "step1_p": "Kemitraan dan pengadaan dari petani lokal.",
                "step2_p": "Kelapa dikumpulkan melalui jaringan pengepul kami.",
                "step3_p": "Kelapa dan sabut disiapkan untuk kebutuhan industri, dengan pengolahan sabut yang sedang dikembangkan.",
                "step4_p": "Penerimaan dan sortasi di titik gudang kami.",
                "step5_p": "Dikirim melalui darat atau laut sesuai kebutuhan setiap pembeli.",
                "cap_p": "Kilogram per bulan kapasitas suplai terpetakan di seluruh wilayah petani mitra kami.",
                "cap_region": "Wilayah Sumber",
                "cap_reach": "Jangkauan Pasar",
                "north_konawe": "Konawe Utara",
                "sus_title": "Membangun rantai pasok kelapa yang lebih baik.",
                "sus1": "Pengadaan yang bertanggung jawab",
                "sus1_p": "Bahan baku berasal dari wilayah dengan asal-usul yang jelas, didukung dokumentasi dalam proses pengadaan.",
                "sus2": "Kemitraan petani",
                "sus2_p": "Pembelian yang adil, pendampingan, dan pengembangan kapasitas untuk mendukung kesejahteraan petani lokal.",
                "sus3": "Pengolahan yang efisien",
                "sus3_p": "Pengolahan sabut kelapa yang efisien dan inovatif dalam memanfaatkan sumber daya.",
                "sus4": "Praktik berkelanjutan",
                "sus4_p": "Hubungan jangka panjang dengan petani, pengepul, dan pelanggan, dengan kepedulian pada lingkungan.",
                "gal_title": "Kegiatan Kami",
                "gal_p": "Kunjungan lapangan, kemitraan, gudang, dan rantai pasok yang terus berkembang.",
                "gal_empty_t": "Dokumentasi akan segera diperbarui.",
                "gal_empty_p": "Foto kegiatan kami akan segera ditampilkan di sini.",
                "why_title": "Mengapa Bermitra dengan CCCI",
                "why_consistent": "Pasokan Konsisten",
                "why_traceable": "Rantai Pasok Terlacak",
                "why1_p": "Kami membeli melalui jaringan petani dan pengepul di wilayah kami sendiri.",
                "why2_p": "Setiap lot diterima dan disortir di titik gudang kami.",
                "why3_p": "Tiga zona sumber dan kapasitas bulanan terpetakan 323K+ kg.",
                "why4_p": "Bahan baku berasal dari wilayah dengan asal-usul dan dokumentasi yang jelas.",
                "why5_p": "Hubungan jangka panjang dengan petani, pengepul, dan pembeli.",
                "contact_desc": "Hubungi kami untuk informasi produk, pasokan bahan baku, dan peluang kemitraan.",
                "c_address": "Alamat",
                "c_phone": "Telepon",
                "c_hours": "Jam Operasional",
                "form_p": "Isi formulir berikut dan tim kami akan menindaklanjuti inquiry Anda.",
                "f_name": "Nama Lengkap *",
                "f_company": "Nama Perusahaan",
                "f_phone": "Telepon / WhatsApp",
                "f_country": "Negara",
                "f_product": "Produk",
                "f_qty": "Perkiraan Jumlah",
                "f_msg": "Pesan / Kebutuhan",
                "f_select": "Pilih Produk",
                "f_send": "Kirim Inquiry",
                "ph_country": "Indonesia",
                "ph_qty": "Contoh: 10 ton / bulan",
                "ph_msg": "Ceritakan kebutuhan Anda...",
                "final_title": "Mari bangun kemitraan pasokan kelapa yang andal.",
                "final_p": "Sampaikan kebutuhan Anda.",
                "foot_desc": "Dari petani ke pasar global. Rantai nilai kelapa berkelanjutan yang dibangun di Morowali untuk Indonesia dan dunia.",
                "foot_nav": "Navigasi",
                "foot_products": "Produk",
                "foot_contact": "Kontak",
                "rights": "Hak cipta dilindungi.",
                "foot_tag": "Kualitas &middot; Keberlanjutan &middot; Kemitraan"
        }
};

    function setLanguage(lang) {
        if (!translations[lang]) { lang = "en"; }
        var dict = translations[lang];

        document.documentElement.lang = lang;

        document.querySelectorAll("[data-i18n]").forEach(function (el) {
            var key = el.getAttribute("data-i18n");
            if (dict[key] !== undefined) { el.innerHTML = dict[key]; }
        });

        document.querySelectorAll("[data-i18n-placeholder]").forEach(function (el) {
            var key = el.getAttribute("data-i18n-placeholder");
            if (dict[key] !== undefined) { el.setAttribute("placeholder", dict[key]); }
        });

        document.querySelectorAll("[data-lang]").forEach(function (btn) {
            var active = btn.getAttribute("data-lang") === lang;
            btn.classList.toggle("active", active);
            btn.setAttribute("aria-pressed", active ? "true" : "false");
        });

        try { localStorage.setItem("ccci_language", lang); } catch (err) {}
    }

    document.querySelectorAll("[data-lang]").forEach(function (btn) {
        btn.addEventListener("click", function () {
            setLanguage(this.getAttribute("data-lang"));
        });
    });

    var savedLanguage = "en";
    try { savedLanguage = localStorage.getItem("ccci_language") || "en"; } catch (err) {}
    setLanguage(savedLanguage);
</script>

<script>
    (function () {
        var intro = document.getElementById("intro");
        if (!intro) { return; }
        function done() {
            intro.classList.add("hide");
            setTimeout(function () { if (intro.parentNode) { intro.parentNode.removeChild(intro); } }, 450);
        }
        var small = window.matchMedia && window.matchMedia("(max-width: 640px)").matches;
        setTimeout(done, small ? 1200 : 1500);
    })();
</script>

</body>
</html>
